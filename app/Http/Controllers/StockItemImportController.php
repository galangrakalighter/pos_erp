<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockItem;
use App\Models\StockItemHistory;
use App\Models\ClientStockItem;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class StockItemImportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $rows = [];
        $header = [];
        try {
            if (in_array($ext, ['xlsx', 'xls'])) {
                // Excel
                $data = Excel::toArray([], $file);
                $rows = $data[0] ?? [];
                $header = array_map('strtolower', array_map('trim', $rows[0] ?? []));
                $rows = array_slice($rows, 1);
            } else {
                // CSV
                $handle = fopen($file->getRealPath(), 'r');
                if ($handle) {
                    $header = array_map('strtolower', array_map('trim', fgetcsv($handle)));
                    while (($row = fgetcsv($handle)) !== false) {
                        $rows[] = $row;
                    }
                    fclose($handle);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal membaca file: ' . $e->getMessage()], 400);
        }

        // Wajib minimal: nama, lokasi, tersedia, harga. Kolom sku & diperbaharui opsional.
        $required = ['nama','tersedia','harga'];
        foreach ($required as $col) {
            if (!in_array($col, $header)) {
                return response()->json(['message' => "Kolom wajib '$col' tidak ditemukan di file."], 400);
            }
        }

        $imported = 0;
        $errors = [];
        $updated = 0;
        
        Log::info('Starting import process', ['total_rows' => count($rows)]);
        
        foreach ($rows as $i => $row) {
            $data = array_combine($header, $row);
            // Abaikan created_at & updated_at jika dikirim pada file
            if (isset($data['created_at'])) unset($data['created_at']);
            if (isset($data['updated_at'])) unset($data['updated_at']);

            // Log data yang akan diproses
            Log::info('Processing row', ['row_index' => $i + 2, 'raw' => $data]);

            $validator = Validator::make($data, [
                'nama' => 'required|string|max:150',
                'sku' => 'nullable|string|max:50',
                'lokasi' => 'nullable|string|max:100',
                'tersedia' => 'required|integer|min:0',
                'harga' => 'required|integer|min:0',
                'diperbaharui' => 'nullable|date',
            ]);
            if ($validator->fails()) {
                $errorMsg = 'Baris '.($i+2).': '.implode('; ', $validator->errors()->all());
                $errors[] = $errorMsg;
                Log::warning('Validation failed', ['row_index' => $i + 2, 'errors' => $validator->errors()->all()]);
                continue;
            }
            try {
                // Generate SKU jika kosong menggunakan nama item
                if (empty($data['sku'])) {
                    $data['sku'] = $this->generateSkuFromName($data['nama']);
                }

                // Jika diperbaharui kosong, isi dengan tanggal hari ini
                if (empty($data['diperbaharui'])) {
                    $data['diperbaharui'] = now()->toDateString();
                }
                // Admin
                if(Auth::user()->role == "admin"){
                    // Cek apakah item sudah ada berdasarkan nama
                    $existingItem = StockItem::where('nama', $data['nama'])->first();
                    
                    if ($existingItem) {
                        // Merge stok: tambah jumlah, jangan overwrite
                        $oldStock = (int) $existingItem->tersedia;
                        $added = (int) $data['tersedia'];
                        $newStock = $oldStock + $added;
                        // Hanya update SKU jika sebelumnya kosong
                        $skuToSave = $existingItem->sku ?: $data['sku'];
                        $existingItem->update([
                            'sku' => $skuToSave,
                            'kategori' => $data['kategori'] ?? $existingItem->kategori,
                            'lokasi' => $data['lokasi'],
                            'tersedia' => $newStock,
                            'harga' => (int) $data['harga'],
                            'diperbaharui' => $data['diperbaharui'],
                        ]);
                        
                        StockItemHistory::create([
                            'stock_item_id' => $existingItem->id,
                            'nama_item' => $existingItem->nama,
                            'tersedia' => $existingItem->tersedia,
                            'action' => 'Import Data - Merge',
                            'changes' => [
                                'nama' => $existingItem->nama,
                                'sku' => $existingItem->sku,
                                'lokasi' => $existingItem->lokasi,
                                'tersedia_lama' => $oldStock,
                                'ditambahkan' => $added,
                                'tersedia_baru' => $existingItem->tersedia,
                                'harga' => $existingItem->harga,
                                'catatan' => 'Update dari import file',
                            ],
                            'user' => Auth::user()->name ?? 'Import',
                        ]);
                        $updated++;
                        Log::info('Item updated', ['item_id' => $existingItem->id, 'nama' => $existingItem->nama]);
                    } else {
                        // Buat item baru
                        $item = StockItem::create([
                            'nama' => $data['nama'],
                            'sku' => $data['sku'],
                            'kategori' => $data['kategori'] ?? 'GAFI',
                            'kondisi' => 'Baru', // Default value untuk kondisi
                            'lokasi' => $data['lokasi'],
                            'tersedia' => $data['tersedia'],
                            'disimpan' => 0, // Default value untuk disimpan
                            'harga' => $data['harga'],
                            'diperbaharui' => $data['diperbaharui'],
                        ]);
                        
                        StockItemHistory::create([
                            'stock_item_id' => $item->id,
                            'nama_item' => $item->nama,
                            'tersedia' => $item->tersedia,
                            'action' => 'Import Data - New Item',
                            'changes' => [
                                'nama' => $item->nama,
                                'sku' => $item->sku,
                                'lokasi' => $item->lokasi,
                                'tersedia' => $item->tersedia,
                                'harga' => $item->harga,
                                'catatan' => 'Item baru dari import file',
                            ],
                            'user' => Auth::user()->name ?? 'Import',
                        ]);
                        $imported++;
                        Log::info('New item created', ['item_id' => $item->id, 'nama' => $item->nama]);
                    }
                }else{
                    $existingItem = ClientStockItem::where('nama', $data['nama'])->first();
                    $client_id = Client::where('client_id', Auth::user()->client_id)->first();
                    if ($existingItem) {
                        // Merge stok: tambah jumlah, jangan overwrite
                        $oldStock = (int) $existingItem->tersedia;
                        $added = (int) $data['tersedia'];
                        $newStock = $oldStock + $added;
                        // Hanya update SKU jika sebelumnya kosong
                        $skuToSave = $existingItem->sku ?: $data['sku'];
                        $existingItem->update([
                            'client_id' => $client_id->id,
                            'nama' => $data['nama'],
                            'sku' => $skuToSave,
                            'kategori' => "Umum",
                            'tersedia' => $newStock,
                            'harga' => (int) $data['harga'],
                            'diperbaharui' => $data['diperbaharui'],
                        ]);
                        $updated++;
                        Log::info('Item updated', ['item_id' => $existingItem->id, 'nama' => $existingItem->nama]);
                    } else {
                        // Buat item baru
                        $item = ClientStockItem::create([
                            'client_id' => $client_id->id,
                            'nama' => $data['nama'],
                            'sku' => $data['sku'],
                            'kategori' => "Umum",
                            'tersedia' => $data['tersedia'],
                            'harga' => (int) $data['harga'],
                            'diperbaharui' => $data['diperbaharui'],
                        ]);
                        $imported++;
                        Log::info('New item created', ['item_id' => $item->id, 'nama' => $item->nama]);
                    }
                }
            } catch (\Exception $e) {
                $errorMsg = 'Baris '.($i+2).': '.$e->getMessage();
                $errors[] = $errorMsg;
                Log::error('Import failed for row', ['row_index' => $i + 2, 'error' => $e->getMessage(), 'data' => $data]);
            }
        }
        Log::info('Import completed', [
            'imported' => $imported,
            'updated' => $updated,
            'errors_count' => count($errors)
        ]);
        
        return response()->json([
            'success' => true,
            'imported' => $imported,
            'updated' => $updated,
            'errors' => $errors,
            'message' => "Import selesai! {$imported} item baru ditambahkan, {$updated} item diperbarui." . (count($errors) > 0 ? " Ada " . count($errors) . " error." : "")
        ]);
    }

    private function generateSkuFromName(string $name): string
    {
        // Ambil huruf awal dari setiap kata sebagai dasar SKU
        $words = preg_split('/\s+/', trim($name));
        $letters = '';
        foreach ($words as $w) {
            $w = preg_replace('/[^A-Za-z0-9]/', '', $w);
            if ($w !== '') {
                $letters .= strtoupper(substr($w, 0, 1));
            }
        }
        if (strlen($letters) < 3) {
            $base = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $name));
            $letters = strtoupper(substr($base, 0, 3));
        }
        $letters = substr($letters, 0, 3);

        // Tambahkan suffix angka agar unik
        $attempts = 0;
        do {
            $suffix = str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
            $sku = $letters . '-' . $suffix;
            $exists = StockItem::where('sku', $sku)->exists();
            $attempts++;
        } while ($exists && $attempts < 50);

        if ($attempts >= 50) {
            $sku = $letters . '-' . substr((string) time(), -3);
        }

        return $sku;
    }
}

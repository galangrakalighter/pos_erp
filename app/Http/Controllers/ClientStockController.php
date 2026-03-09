<?php

namespace App\Http\Controllers;

use App\Models\ClientStockItem;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB; // Added DB facade

class ClientStockController extends Controller
{
    /**
     * Get all stock items for the authenticated client
     * Combines client's own stock (Umum) with admin stock (GAFI)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'client') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $client = Client::where('client_id', $user->client_id)->first();
        
        if (!$client) {
            return response()->json(['error' => 'Client not found'], 404);
        }

        // Get all stock items owned by the client (GAFI & Umum)
        $clientItems = ClientStockItem::where('client_id', $client->id);

        // Apply search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $clientItems->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('kategori', 'like', "%{$search}%");
            });
        }

        // Apply category filter
        if ($request->has('kategori') && $request->kategori) {
            $clientItems->where('kategori', $request->kategori);
        }

        // Apply date filter
        if ($request->has('periode') && $request->periode) {
            $clientItems->where('diperbaharui', 'like', "%{$request->periode}%");
        }

        $clientItems = $clientItems->orderBy('nama', 'asc')->get();

        // Format client items
        $formattedClientItems = $clientItems->map(function ($item) {
            return [
                'id' => $item->id,
                'nama' => $item->nama,
                'sku' => $item->sku,
                'kategori' => $item->kategori,
                'tersedia' => $item->tersedia,
                'harga' => $item->harga,
                'diperbaharui' => $item->diperbaharui,
                'gambar' => '/images/default-item.svg',
                'source' => 'client'
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedClientItems->values()
        ]);
    }

    /**
     * Store a new stock item
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'client') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $client = Client::where('client_id', $user->client_id)->first();
        
        if (!$client) {
            \Log::warning('Client not found', ['user_client_id' => $user->client_id]);
            return response()->json(['error' => 'Client not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'sku' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'tersedia' => 'required|integer|min:0',
            'harga' => 'nullable|integer|min:0', // Allow harga to be optional
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        // PRIORITAS MERGE: berdasarkan SKU milik client
        $existingBySku = ClientStockItem::where('client_id', $client->id)
            ->where('sku', $request->sku)
            ->first();

        if ($existingBySku) {
            // Jika harga tidak dikirim atau sama dengan existing, anggap tambah stok
            $incomingPrice = $request->has('harga') ? (int) $request->harga : null;
            $existingPrice = (int) ($existingBySku->harga ?? 0);
            if ($incomingPrice === null || $incomingPrice === $existingPrice) {
                $oldStock = $existingBySku->tersedia;
                $newStock = $oldStock + $request->tersedia;
                $existingBySku->update([
                    'tersedia' => $newStock,
                    'diperbaharui' => now()->toDateString(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Stock updated successfully',
                    'data' => $existingBySku,
                    'action' => 'update',
                    'oldStock' => $oldStock,
                    'addedStock' => $request->tersedia,
                    'newStock' => $newStock
                ]);
            }
            // Jika harga berbeda, lanjutkan sebagai kemungkinan item baru (cek kombinasi nama/kategori/harga)
        }

        // Fallback MERGE: berdasarkan kombinasi nama, kategori, harga/null
        $existingItem = null;
        if ($request->has('harga') && $request->harga !== null && $request->harga > 0) {
            $existingItem = ClientStockItem::where('client_id', $client->id)
                ->where('nama', $request->nama)
                ->where('kategori', $request->kategori)
                ->where('harga', $request->harga)
                ->first();
        } else {
            $existingItem = ClientStockItem::where('client_id', $client->id)
                ->where('nama', $request->nama)
                ->where('kategori', $request->kategori)
                ->whereNull('harga')
                ->first();
        }

        if ($existingItem) {
            // Update existing item - add to stock (only if exact match found)
            $oldStock = $existingItem->tersedia;
            $newStock = $oldStock + $request->tersedia;
            
            $existingItem->update([
                'tersedia' => $newStock,
                'diperbaharui' => now()->toDateString(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Stock updated successfully',
                'data' => $existingItem,
                'action' => 'update',
                'oldStock' => $oldStock,
                'addedStock' => $request->tersedia,
                'newStock' => $newStock
            ]);
        }

        // Create new item (harga validation is handled by frontend)
        $item = ClientStockItem::create([
            'client_id' => $client->id,
            'nama' => $request->nama,
            'sku' => $request->sku,
            'kategori' => $request->kategori,
            'tersedia' => $request->tersedia,
            'harga' => $request->harga,
            'diperbaharui' => now()->toDateString(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock item created successfully',
            'data' => $item,
            'action' => 'create'
        ]);
    }

    /**
     * Update a stock item
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'client') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $client = Client::where('client_id', $user->client_id)->first();
        
        if (!$client) {
            return response()->json(['error' => 'Client not found'], 404);
        }

        // Pastikan item yang akan diupdate benar-benar ada dan milik client ini
        $item = ClientStockItem::where('id', $id)
            ->where('client_id', $client->id)
            ->first();

        if (!$item) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        // Check if trying to edit GAFI item
        if ($item->kategori === 'GAFI') {
            return response()->json(['error' => 'GAFI items cannot be edited by clients'], 403);
        }

        // Simpan nilai sebelum update untuk tracking
        $beforeTersedia = (int) ($item->tersedia ?? 0);
        $beforeNama = $item->nama;
        $beforeSku = $item->sku;
        $beforeHarga = (int) ($item->harga ?? 0);

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'sku' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'tersedia' => 'required|integer|min:0',
            'harga' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        // Pastikan tersedia tidak menjadi 0 jika tidak valid
        $newTersedia = (int) $request->tersedia;
        if ($newTersedia < 0) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => ['tersedia' => ['Tersedia tidak boleh negatif']]
            ], 422);
        }

        // Update item yang sudah ada (tidak membuat item baru)
        $item->nama = $request->nama;
        $item->sku = $request->sku;
        $item->kategori = $request->kategori;
        $item->tersedia = $newTersedia;
        $item->harga = (int) $request->harga;
        $item->diperbaharui = now()->toDateString();
        $item->save();

        // Refresh untuk mendapatkan nilai terbaru
        $item->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Stock item updated successfully',
            'data' => $item,
            'changes' => [
                'tersedia' => [
                    'before' => $beforeTersedia,
                    'after' => $newTersedia,
                    'difference' => $newTersedia - $beforeTersedia
                ],
                'nama' => $beforeNama !== $item->nama ? ['before' => $beforeNama, 'after' => $item->nama] : null,
                'sku' => $beforeSku !== $item->sku ? ['before' => $beforeSku, 'after' => $item->sku] : null,
                'harga' => $beforeHarga !== $item->harga ? ['before' => $beforeHarga, 'after' => $item->harga] : null,
            ]
        ]);
    }

    /**
     * Delete a stock item
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'client') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $client = Client::where('client_id', $user->client_id)->first();
        
        if (!$client) {
            return response()->json(['error' => 'Client not found'], 404);
        }

        $item = ClientStockItem::where('id', $id)
            ->where('client_id', $client->id)
            ->first();

        if (!$item) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        // Check if trying to delete GAFI item
        if ($item->kategori === 'GAFI') {
            return response()->json(['error' => 'GAFI items cannot be deleted by clients'], 403);
        }

        $itemData = $item->toArray();
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Stock item deleted successfully',
            'data' => $itemData
        ]);
    }

    /**
     * Split a GAFI item into smaller grammage items
     */
    public function split(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'client') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $client = Client::where('client_id', $user->client_id)->first();
        
        if (!$client) {
            return response()->json(['error' => 'Client not found'], 404);
        }

        // Check if this is an admin item (GAFI) or client item
        if (str_starts_with($id, 'admin_')) {
            // This is an admin GAFI item - get from StockItem table
            $adminItemId = str_replace('admin_', '', $id);
            $parentItem = \App\Models\StockItem::where('id', $adminItemId)
                ->where('kategori', 'GAFI')
                ->first();

            if (!$parentItem) {
                return response()->json(['error' => 'GAFI item not found in admin stock'], 404);
            }

            // For admin items, we work directly with the admin item
            // Client doesn't need their own copy for GAFI items
            $isAdminItem = true;
        } else {
            // This is a client item - get from ClientStockItem table
        $parentItem = ClientStockItem::where('id', $id)
            ->where('client_id', $client->id)
            ->where('kategori', 'GAFI')
            ->first();

        if (!$parentItem) {
            return response()->json(['error' => 'GAFI item not found or not owned by client'], 404);
            }

            $isAdminItem = false;
        }

        $validator = Validator::make($request->all(), [
            'splitQuantity' => 'required|numeric|min:0.1',
            'splitGrams' => 'required|numeric|min:1',
            'splitPrice' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        $splitQuantity = (float) $request->splitQuantity;
        $splitGrams = (int) $request->splitGrams;
        $splitPrice = $request->splitPrice ? (int) $request->splitPrice : null;

        // Calculate deduction from parent stock
        // Formula: 1 pcs = 1000g, so deduction = quantity × (grams/1000)
        $gramsPerPiece = 1000;
        $deductionPerItem = $splitGrams / $gramsPerPiece; // e.g., 500g = 0.5 pcs
        $totalDeduction = $splitQuantity * $deductionPerItem;

        // Check if parent has enough stock
        if ($totalDeduction > $parentItem->tersedia) {
            return response()->json([
                'error' => 'Stok induk tidak mencukupi untuk split ini. Maksimal dapat membuat ' . 
                          floor($parentItem->tersedia / $deductionPerItem) . ' item baru.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Generate new item name and SKU
            $newItemName = $parentItem->nama . ' ' . $splitGrams . 'g';
            $newItemSku = $parentItem->sku . '-' . $splitGrams . 'g';
            
            // Use provided price or parent price
            $newItemPrice = $splitPrice ?: $parentItem->harga;

            // Check if item with same name/SKU already exists
            $existingItem = ClientStockItem::where('client_id', $client->id)
                ->where('nama', $newItemName)
                ->first();

            if ($existingItem) {
                // Update existing item stock
                $existingItem->tersedia += $splitQuantity;
                $existingItem->diperbaharui = now();
                $existingItem->save();
            } else {
                // Create new split item
                ClientStockItem::create([
                    'client_id' => $client->id,
                    'nama' => $newItemName,
                    'sku' => $newItemSku,
                    'kategori' => 'GAFI', // Split items tetap GAFI
                    'tersedia' => $splitQuantity,
                    'harga' => $newItemPrice,
                    'diperbaharui' => now(),
                ]);
            }

            // Reduce parent item stock based on whether it's admin or client item
            if ($isAdminItem) {
                // For admin items, reduce stock in StockItem table
            $parentItem->tersedia -= $totalDeduction;
            $parentItem->diperbaharui = now();
            $parentItem->save();
            } else {
                // For client items, reduce stock in ClientStockItem table
                $parentItem->tersedia -= $totalDeduction;
                $parentItem->diperbaharui = now();
                $parentItem->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil di-split',
                'data' => [
                    'splitDetails' => [
                        'newItemName' => $newItemName,
                        'newItemSku' => $newItemSku,
                        'newItemPrice' => $newItemPrice,
                        'quantityCreated' => $splitQuantity,
                        'gramsPerItem' => $splitGrams,
                        'deduction' => $totalDeduction,
                        'newParentStock' => $parentItem->tersedia,
                        'oldParentStock' => $parentItem->tersedia + $totalDeduction,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to split item: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Process checkout from cashier
     */
    public function checkout(Request $request)
    {
        // Debug logging
        \Log::info('Checkout request received', [
            'request_data' => $request->all(),
            'user' => Auth::user() ? Auth::user()->only(['id', 'name', 'role', 'client_id']) : null
        ]);

        $user = Auth::user();
        
        if (!$user || $user->role !== 'client') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $client = Client::where('client_id', $user->client_id)->first();
        
        if (!$client) {
            return response()->json(['error' => 'Client not found'], 404);
        }

        \Log::info('Client found', ['client' => $client->only(['id', 'nama', 'client_id'])]);

        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.nama' => 'required|string',
            'items.*.sku' => 'required|string',
            'items.*.harga' => 'required|integer|min:0',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.diskon' => 'nullable|integer|min:0',
            'payment_method' => 'required|string',
            'amount_paid' => 'required|integer|min:0',
            'total_amount' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $today = now()->toDateString();
            $orderNumber = 'CS-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

            \Log::info('Creating sale record', [
                'order_number' => $orderNumber,
                'client_id' => $client->id,
                'total_items' => count($request->items),
                'total_quantity' => array_sum(array_column($request->items, 'qty')),
                'total_amount' => $request->total_amount
            ]);

            // Create client sale record
            $sale = \App\Models\ClientSale::create([
                'client_id' => $client->id,
                'order_number' => $orderNumber,
                'total_items' => count($request->items),
                'total_quantity' => array_sum(array_column($request->items, 'qty')),
                'total_amount' => $request->total_amount,
                'payment_method' => $request->payment_method,
                'amount_paid' => $request->amount_paid,
                'change_amount' => $request->amount_paid - $request->total_amount,
                'status' => 'completed',
                'sale_date' => $today,
            ]);

            \Log::info('Sale record created', ['sale_id' => $sale->id]);

            // Process each item
            foreach ($request->items as $itemData) {
                // Find client stock item
                $clientStockItem = ClientStockItem::where('client_id', $client->id)
                    ->where('nama', $itemData['nama'])
                    ->where('sku', $itemData['sku'])
                    ->first();

                if (!$clientStockItem) {
                    throw new \Exception("Item {$itemData['nama']} tidak ditemukan di stock client");
                }

                if ($clientStockItem->tersedia < $itemData['qty']) {
                    throw new \Exception("Stock {$itemData['nama']} tidak mencukupi. Tersedia: {$clientStockItem->tersedia}, Dibutuhkan: {$itemData['qty']}");
                }

                // Reduce client stock
                $oldStock = $clientStockItem->tersedia;
                $clientStockItem->tersedia -= $itemData['qty'];
                $clientStockItem->diperbaharui = $today;
                $clientStockItem->save();

                // Jika ini item GAFI, kurangi juga stok admin
                if ($clientStockItem->kategori === 'GAFI') {
                    $adminStockItem = \App\Models\StockItem::where('sku', $itemData['sku'])
                        ->where('nama', $itemData['nama'])
                        ->first();
                    
                    if ($adminStockItem) {
                        $adminOldStock = $adminStockItem->tersedia;
                        $adminStockItem->update([
                            'tersedia' => max(0, $adminStockItem->tersedia - $itemData['qty']),
                            'diperbaharui' => now()->toDateString()
                        ]);
                        
                        // Log history untuk admin stock
                        \App\Models\StockItemHistory::create([
                            'stock_item_id' => $adminStockItem->id,
                            'nama_item' => $adminStockItem->nama,
                            'tersedia' => $adminStockItem->tersedia,
                            'action' => 'Stok Berkurang (Checkout Client)',
                            'changes' => [
                                'stok' => [
                                    'stok_lama' => (int) $adminOldStock,
                                    'stok_berkurang' => (int) $itemData['qty'],
                                    'stok_total' => (int) $adminStockItem->tersedia,
                                ],
                                'catatan' => "Dikurangi {$itemData['qty']} untuk checkout client {$client->nama} ({$client->client_id}) - Order: {$orderNumber}",
                                'client_id' => $client->id,
                                'client_name' => $client->nama,
                                'order_number' => $orderNumber,
                                'sale_id' => $sale->id
                            ],
                            'user' => $client->nama . ' (Client)',
                        ]);
                    }
                }

                // Create sale item record
                \App\Models\ClientSaleItem::create([
                    'client_sale_id' => $sale->id,
                    'item_name' => $itemData['nama'],
                    'item_sku' => $itemData['sku'],
                    'quantity' => $itemData['qty'],
                    'unit_price' => $itemData['harga'],
                    'discount_percent' => $itemData['diskon'] ?? 0,
                    'discount_amount' => $itemData['diskon_amount'] ?? 0,
                    'subtotal' => ($itemData['harga'] * $itemData['qty']) - ($itemData['diskon_amount'] ?? 0),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diproses',
                'data' => [
                    'sale_id' => $sale->id,
                    'order_number' => $orderNumber,
                    'total_amount' => $request->total_amount,
                    'change_amount' => $request->amount_paid - $request->total_amount,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Checkout failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Add a new sale record
     */
    public function addSale(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'client') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $client = Client::where('client_id', $user->client_id)->first();
        
        if (!$client) {
            return response()->json(['error' => 'Client not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'order_number' => 'required|string|max:255',
            'sale_date' => 'required|date',
            'total_items' => 'required|integer|min:1',
            'total_quantity' => 'required|integer|min:1',
            'total_amount' => 'required|integer|min:0',
            'payment_method' => 'required|string|max:255',
            'amount_paid' => 'required|integer|min:0',
            'change_amount' => 'required|integer|min:0',
            'status' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'customer_phone' => 'nullable|string|max:255',
            'customer_address' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string',
            'items.*.item_sku' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|integer|min:0',
            'items.*.discount_amount' => 'required|integer|min:0',
            'items.*.subtotal' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Cek apakah order number sudah ada untuk client ini
            $existingSale = \App\Models\ClientSale::where('client_id', $client->id)
                ->where('order_number', $request->order_number)
                ->first();
            
            if ($existingSale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order number sudah digunakan'
                ], 400);
            }

            // Create client sale record
            $sale = \App\Models\ClientSale::create([
                'client_id' => $client->id,
                'order_number' => $request->order_number,
                'total_items' => $request->total_items,
                'total_quantity' => $request->total_quantity,
                'total_amount' => $request->total_amount,
                'payment_method' => $request->payment_method,
                'amount_paid' => $request->amount_paid,
                'change_amount' => $request->change_amount,
                'notes' => $request->notes ?? '',
                'customer_phone' => $request->customer_phone ?? null,
                'customer_address' => $request->customer_address ?? null,
                'status' => $request->status,
                'sale_date' => $request->sale_date,
            ]);

            // Create sale items and update stock
            foreach ($request->items as $item) {
                \App\Models\ClientSaleItem::create([
                    'client_sale_id' => $sale->id,
                    'item_name' => $item['item_name'],
                    'item_sku' => $item['item_sku'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'],
                    'subtotal' => $item['subtotal']
                ]);
                
                // Update stok (kurangi quantity)
                // Cek di ClientStockItem dulu (item client)
                $stockItem = \App\Models\ClientStockItem::where('client_id', $client->id)
                    ->where('sku', $item['item_sku'])
                    ->first();
                
                if ($stockItem) {
                    // Kurangi stok client
                    $oldStock = $stockItem->tersedia;
                    $stockItem->update([
                        'tersedia' => max(0, $stockItem->tersedia - $item['quantity']),
                        'diperbaharui' => now()->toDateString()
                    ]);


                    
                    // Jika ini item GAFI, kurangi juga stok admin
                    // if ($stockItem->kategori === 'GAFI') {
                        $adminStockItem = \App\Models\StockItem::where('sku', $item['item_sku'])
                            ->where('nama', $item['item_name'])
                            ->first();
                        
                        if ($adminStockItem) {
                            $adminOldStock = $adminStockItem->tersedia;
                            $adminStockItem->update([
                                'tersedia' => max(0, $adminStockItem->tersedia - $item['quantity']),
                                'diperbaharui' => now()->toDateString()
                            ]);

                            // Update display penyesuaian jumlah unit dan item di client management admin
                            \App\Models\ClientItem::where('stock_item_id', $adminStockItem->id)
                            ->where('client_id', $client->id)->update([
                                'jumlah' => $stockItem->tersedia,
                            ]);
                            
                            // Log history untuk admin stock
                            \App\Models\StockItemHistory::create([
                                'stock_item_id' => $adminStockItem->id,
                                'nama_item' => $adminStockItem->nama,
                                'tersedia' => $adminStockItem->tersedia,
                                'action' => 'Stok Berkurang (Penjualan Client)',
                                'changes' => [
                                    'stok' => [
                                        'stok_lama' => (int) $adminOldStock,
                                        'stok_berkurang' => (int) $item['quantity'],
                                        'stok_total' => (int) $adminStockItem->tersedia,
                                    ],
                                    'catatan' => "Dikurangi {$item['quantity']} untuk penjualan client {$client->nama} ({$client->client_id}) - Order: {$request->order_number}",
                                    'client_id' => $client->id,
                                    'client_name' => $client->nama,
                                    'order_number' => $request->order_number,
                                    'sale_id' => $sale->id
                                ],
                                'user' => $client->nama . ' (Client)',
                            ]);
                        }
                    // }
                } else {
                    // Jika tidak ada di ClientStockItem, cek di StockItem (item admin GAFI)
                    $adminStockItem = \App\Models\StockItem::where('sku', $item['item_sku'])
                        ->where('kategori', 'GAFI')
                        ->first();
                    
                    if ($adminStockItem) {
                        $adminOldStock = $adminStockItem->tersedia;
                        $adminStockItem->update([
                            'tersedia' => max(0, $adminStockItem->tersedia - $item['quantity']),
                            'diperbaharui' => now()->toDateString()
                        ]);
                        
                        // Log history untuk admin stock
                        \App\Models\StockItemHistory::create([
                            'stock_item_id' => $adminStockItem->id,
                            'nama_item' => $adminStockItem->nama,
                            'tersedia' => $adminStockItem->tersedia,
                            'action' => 'Stok Berkurang (Penjualan Langsung Client)',
                            'changes' => [
                                'stok' => [
                                    'stok_lama' => (int) $adminOldStock,
                                    'stok_berkurang' => (int) $item['quantity'],
                                    'stok_total' => (int) $adminStockItem->tersedia,
                                ],
                                'catatan' => "Dikurangi {$item['quantity']} untuk penjualan langsung client {$client->nama} ({$client->client_id}) - Order: {$request->order_number}",
                                'client_id' => $client->id,
                                'client_name' => $client->nama,
                                'order_number' => $request->order_number,
                                'sale_id' => $sale->id
                            ],
                            'user' => $client->nama . ' (Client)',
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sale record added successfully',
                'data' => $sale->load('items')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to add sale: ' . $e->getMessage()
            ], 400);
        }
    }

    public function editSale($id, Request $request) {
        try {
            DB::beginTransaction();
            
            // 1. Ambil data transaksi lama
            $sale = \App\Models\ClientSale::with('items')->findOrFail($id);
            $client = \App\Models\Client::where('client_id', Auth::user()->client_id)->first();

            // 2. KEMBALIKAN STOK LAMA (Penting agar stok tidak kacau saat edit berkali-kali)
            foreach ($sale->items as $oldItem) {
                \App\Models\ClientStockItem::where('sku', $oldItem->item_sku)
                    ->increment('tersedia', $oldItem->quantity);
            }

            // 3. Update Header Transaksi
            $sale->update([
                'status'          => $request->status,
                'total_items'     => $request->total_items,
                'total_quantity'  => $request->total_quantity,
                'total_amount'    => $request->total_amount,
                'discount_type'   => $request->discount_type,
                'discount_amount' => $request->discount_amount,
                'notes'           => $request->notes,
            ]);

            // 4. Bersihkan Detail Item & History Lama (Hapus Sekali Saja)
            \App\Models\ClientSaleItem::where('client_sale_id', $id)->delete();
            \App\Models\StockItemHistory::where('changes->order_number', $request->order_number)->delete();

            // 5. Masukkan Data Baru & Kurangi Stok Baru
            foreach ($request->items as $item) {
                // A. Simpan Item Penjualan Baru
                \App\Models\ClientSaleItem::create([
                    'client_sale_id'  => $id,
                    'item_name'       => $item['item_name'],
                    'item_sku'        => $item['item_sku'],
                    'quantity'        => $item['quantity'],
                    'unit_price'      => $item['unit_price'],
                    'discount_amount' => $request->discount_amount, // Gunakan null coalescing
                    'subtotal'        => $item['subtotal'],
                ]);

                // B. Kurangi Stok Client
                $stockClient = \App\Models\ClientStockItem::where('sku', $item['item_sku'])->first();
                if ($stockClient) {
                    $stockClient->decrement('tersedia', $item['quantity']);
                    
                    // Refresh data stok setelah dikurangi untuk history yang akurat
                    $stockClient->refresh(); 
                }

                // C. Catat History
                $stockItem = \App\Models\StockItem::where('sku', $item['item_sku'])->first();
                if ($stockItem) {
                    \App\Models\StockItemHistory::create([
                        'stock_item_id' => $stockItem->id,
                        'nama_item'     => $item['item_name'],
                        'tersedia'      => (int) $stockClient->tersedia,
                        'action'        => 'Update Penjualan (Edit)',
                        'changes'       => [
                            'stock' => [
                                'stock_lama'     => (int) ($stockClient->tersedia + $item['quantity']),
                                'stok_berkurang' => (int) $item['quantity'],
                                'stock_total'    => (int) $stockClient->tersedia,
                            ],
                            'catatan'      => "Update qty: {$item['quantity']} untuk client {$client->nama} - Order: {$request->order_number}",
                            'client_id'    => $client->id,
                            'client_name'  => $client->nama,
                            'order_number' => $request->order_number,
                            'sale_id'      => $sale->id
                        ],
                        'user' => $client->nama . ' (Client)'
                    ]);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Transaksi berhasil diperbarui']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => 'Gagal: ' . $e->getMessage(),
                'line' => $e->getLine() // Membantu mencari lokasi error jika ada
            ], 500);
        }
    }

    /**
     * Delete a sale record (Soft delete - mark as deleted but keep for omzet calculation)
     */
    public function deleteSale($id)
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'client') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $client = Client::where('client_id', $user->client_id)->first();
        
        if (!$client) {
            return response()->json(['error' => 'Client not found'], 404);
        }

        $sale = \App\Models\ClientSale::where('id', $id)
            ->where('client_id', $client->id)
            ->first();

        if (!$sale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }

        DB::beginTransaction();
        try {
            \Log::info('Before soft delete', ['sale_id' => $sale->id, 'is_deleted' => $sale->is_deleted]);
            $updated = $sale->update([
                'is_deleted' => 1,
                'deleted_at' => now(),
                'deleted_by' => $user->id
            ]);
            \Log::info('After soft delete', ['sale_id' => $sale->id, 'is_deleted' => $sale->fresh()->is_deleted]);
            if (!$updated) {
                DB::rollBack();
                return response()->json(['error' => 'Failed to update is_deleted'], 500);
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Sale record deleted successfully (omzet tetap dihitung karena stok sudah terjual)'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Soft delete error', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Failed to delete sale: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get sales history for the authenticated client
     */
    public function getSalesHistory(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'client') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $client = Client::where('client_id', $user->client_id)->first();
        
        if (!$client) {
            return response()->json(['error' => 'Client not found'], 404);
        }

        $query = \App\Models\ClientSale::where('client_id', $client->id)
            ->where('is_deleted', 0); // Only show non-deleted sales

        // Apply search filter
        if ($request->has('search') && $request->search) {
            $query->where('order_number', 'like', '%' . $request->search . '%');
        }

        // Apply date filter
        if ($request->has('periode') && $request->periode) {
            $query->where('sale_date', 'like', "%{$request->periode}%");
        }

        // Apply status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $sales = $query->with('items')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'order_number' => $sale->order_number,
                    'sale_date' => $sale->sale_date,
                    'total_items' => $sale->total_items,
                    'total_quantity' => $sale->total_quantity,
                    'total_amount' => $sale->total_amount,
                    'payment_method' => $sale->payment_method,
                    'amount_paid' => $sale->amount_paid,
                    'change_amount' => $sale->change_amount,
                    'status' => $sale->status,
                    'notes' => $sale->notes ?? null,
                    'customer_phone' => $sale->customer_phone ?? null,
                    'customer_address' => $sale->customer_address ?? null,
                    'items' => $sale->items->map(function ($item) {
                        return [
                            'item_name' => $item->item_name,
                            'item_sku' => $item->item_sku,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'discount_percent' => $item->discount_percent,
                            'subtotal' => $item->subtotal,
                        ];
                    }),
                    'created_at' => $sale->created_at->toISOString(),
                ];
            });

        return response()->json([
            'data' => $sales
        ]);
    }

    /**
     * Get sale detail by ID
     */
    public function getSaleDetail($id)
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'client') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $client = Client::where('client_id', $user->client_id)->first();
        
        if (!$client) {
            return response()->json(['error' => 'Client not found'], 404);
        }

        $sale = \App\Models\ClientSale::where('id', $id)
            ->where('client_id', $client->id)
            ->where('is_deleted', 0) // Only show non-deleted sales
            ->with('items')
            ->first();

        if (!$sale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }

        $saleData = [
            'id' => $sale->id,
            'order_number' => $sale->order_number,
            'sale_date' => $sale->sale_date,
            'total_items' => $sale->total_items,
            'total_quantity' => $sale->total_quantity,
            'total_amount' => $sale->total_amount,
            'payment_method' => $sale->payment_method,
            'amount_paid' => $sale->amount_paid,
            'change_amount' => $sale->change_amount,
            'status' => $sale->status,
            'payment_reference' => $sale->payment_reference ?? null,
            'notes' => $sale->notes ?? null,
            'customer_phone' => $sale->customer_phone ?? null,
            'customer_address' => $sale->customer_address ?? null,
            'items' => $sale->items->map(function ($item) {
                return [
                    'item_name' => $item->item_name,
                    'item_sku' => $item->item_sku,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount_percent' => $item->discount_percent,
                    'subtotal' => $item->subtotal,
                ];
            }),
            'created_at' => $sale->created_at->toISOString(),
        ];

        return response()->json([
            'success' => true,
            'data' => $saleData
        ]);
    }

    /**
     * Get dashboard data for client (KPI and activities)
     */
    public function getDashboardData()
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'client') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $client = Client::where('client_id', $user->client_id)->first();
        
        if (!$client) {
            return response()->json(['error' => 'Client not found'], 404);
        }

        try {
            // Get today's date
            $today = now()->startOfDay();
            
            // KPI Data - Count only non-deleted sales for display
            $todaysOrders = \App\Models\ClientSale::where('client_id', $client->id)
                ->whereDate('sale_date', $today)
                ->where('is_deleted', 0)
                ->count();
            
            // Revenue calculation - include ALL sales (including deleted) for accurate omzet
            $todaysRevenue = \App\Models\ClientSale::where('client_id', $client->id)
                ->whereDate('sale_date', $today)
                ->where('status', 'completed')
                ->sum('total_amount'); // Include deleted sales for omzet
            
            $totalItems = ClientStockItem::where('client_id', $client->id)->count();
            
            $totalStock = ClientStockItem::where('client_id', $client->id)
                ->sum('tersedia');
            
            $rangeOrders = \App\Models\ClientSale::where('client_id', $client->id)
                ->whereDate('sale_date', '>=', $today->copy()->subDays(30))
                ->where('is_deleted', 0)
                ->count();
            
            $unpaidCount = \App\Models\ClientSale::where('client_id', $client->id)
                ->where('status', 'pending')
                ->where('is_deleted', 0)
                ->count();
            
            // Recent Activities (last 6 sales) - only non-deleted
            $recentActivities = \App\Models\ClientSale::where('client_id', $client->id)
                ->where('is_deleted', 0)
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get()
                ->map(function ($sale) {
                    return [
                        'id' => $sale->id,
                        'text' => 'Penjualan ' . $sale->order_number,
                        'date' => $sale->created_at->format('d M Y, H:i'),
                        'status' => $sale->status,
                        'amount' => $sale->total_amount
                    ];
                });
            
            // Revenue data for chart (last 30 days)
            $revenueData = [];
            for ($i = 29; $i >= 0; $i--) {
                $date = $today->copy()->subDays($i);
                $revenue = \App\Models\ClientSale::where('client_id', $client->id)
                    ->whereDate('sale_date', $date)
                    ->where('status', 'completed')
                    ->sum('total_amount');
                
                $revenueData[] = [
                    'label' => $date->format('d M'),
                    'value' => $revenue
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'kpi' => [
                        'todaysOrders' => $todaysOrders,
                        'todaysRevenue' => $todaysRevenue,
                        'totalItems' => $totalItems,
                        'totalStock' => $totalStock,
                        'rangeOrders' => $rangeOrders,
                        'unpaidCount' => $unpaidCount
                    ],
                    'activities' => $recentActivities,
                    'revenueData' => $revenueData
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error getting client dashboard data: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to get dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get categories for filtering
     */
    public function getCategories()
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'client') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $client = Client::where('client_id', $user->client_id)->first();
        
        if (!$client) {
            return response()->json(['error' => 'Client not found'], 404);
        }

        $categories = ClientStockItem::where('client_id', $client->id)
            ->distinct()
            ->pluck('kategori')
            ->filter()
            ->values();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Client;
use App\Models\ClientStockItem;
use App\Models\Sale;
use App\Models\User;

class PurchaseOrderController extends Controller
{
    /**
     * Get purchase orders for the authenticated client.
     */
    public function index(): JsonResponse
    {
        $purchaseOrders = PurchaseOrder::with(['items.stockItem'])
            ->where('client_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($po) {
                return [
                    'id' => $po->id,
                    'po_number' => $po->po_number,
                    'total_amount' => $po->total_amount,
                    'status' => $po->status,
                    'payment_status' => $po->payment_status,
                    'created_at' => $po->created_at,
                    'items' => $po->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'item_name' => $item->item_name,
                            'sku' => $item->sku,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'subtotal' => $item->subtotal,
                            'item_type' => $item->item_type,
                        ];
                    }),
                ];
            });

        return response()->json($purchaseOrders);
    }

    /**
     * Get a specific purchase order for the authenticated client.
     */
    public function show($id): JsonResponse
    {
        $purchaseOrder = PurchaseOrder::with(['items.stockItem', 'client'])
            ->where('id', $id)
            ->where('client_id', Auth::id())
            ->first();

        if (!$purchaseOrder) {
            return response()->json(['error' => 'Purchase order not found'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $purchaseOrder->id,
                'po_number' => $purchaseOrder->po_number,
                'total_amount' => $purchaseOrder->total_amount,
                'status' => $purchaseOrder->status,
                'payment_status' => $purchaseOrder->payment_status,
                'created_at' => $purchaseOrder->created_at,
                'client_name' => $purchaseOrder->client->name,
                'client_email' => $purchaseOrder->client->email,
                'client_phone' => $purchaseOrder->client->telepon ?? null,
                'items' => $purchaseOrder->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'item_name' => $item->item_name,
                        'sku' => $item->sku,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'subtotal' => $item->subtotal,
                        'item_type' => $item->item_type,
                    ];
                }),
            ]
        ]);
    }

    /**
     * Store a new purchase order.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'po_number' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.subtotal' => 'required|numeric|min:0',
            'items.*.item_type' => 'required|in:stock,external',
            'items.*.stock_item_id' => 'nullable|exists:stock_items,id',
            'items.*.sku' => 'nullable|string|max:255',
            'total_amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $client = Client::where('client_id', Auth::user()->client_id)->first();

        try {
            DB::beginTransaction();
            $purchaseOrder = PurchaseOrder::create([
                'client_id' => Auth::id(),
                'po_number' => $request->po_number,
                'total_amount' => $request->total_amount,
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ]);

            $totalItems = 0;
            $totalHarga = 0;

            foreach ($request->items as $itemData) {
                $totalItems += $itemData['quantity'];
                $totalHarga += $itemData['subtotal'];
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'stock_item_id' => $itemData['item_type'] === 'stock' ? $itemData['stock_item_id'] : null,
                    'item_name' => $itemData['item_name'],
                    'sku' => $itemData['sku'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'subtotal' => $itemData['subtotal'],
                    'item_type' => $itemData['item_type'],
                ]);
            }

            $sale = Sale::create([
                'nama_pemesan' => $client->nama,
                'id_pesanan' => $request->po_number,
                'nama_sales' => $client->nama_sales,
                'jenis_transaksi' => $request->payment_method,
                'telepon' => $client->telepon,
                'alamat' => $client->alamat,
                'diskon_tipe' => 'rupiah',
                'diskon_nilai' => 0,
                'diskon_ball_tipe' => 'rupiah',
                'diskon_ball_nilai' => 0.00,
                'nama_ekspedisi' => null,
                'ongkir' => 0.00,
                'notes' => null,
                'total_diskon_ball' => 0.00,
                'total_quantity' => $totalItems,
                'total_diskon' => 0,
                'total_harga' => $totalHarga,
                'status' => 'Belum approval',
                'periode' => now()->format('Y-m-d')
            ]);

            foreach ($request->items as $itemData) {
                DB::table('sale_items')->insert([
                    'sale_id' => $sale->id,
                    'stock_item_id' => $itemData['item_type'] === 'stock' ? $itemData['stock_item_id'] : null,
                    'quantity' => $itemData['quantity'],
                    'harga' => $itemData['unit_price'],
                    'subtotal' => $itemData['subtotal'],
                ]);
            }



            DB::commit();

            return response()->json([
                'message' => 'Purchase order created successfully',
                'purchase_order' => $purchaseOrder->load('items')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create purchase order'], 500);
        }
    }

    /**
     * Cancel a purchase order.
     */
    public function cancel($id): JsonResponse
    {
        $purchaseOrder = PurchaseOrder::where('client_id', Auth::id())->findOrFail($id);

        if ($purchaseOrder->status !== 'pending') {
            return response()->json(['error' => 'Only pending purchase orders can be cancelled'], 400);
        }

        $purchaseOrder->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Purchase order cancelled successfully']);
    }

    /**
     * Get all purchase orders for admin.
     */
    public function adminIndex(): JsonResponse
    {
        $purchaseOrders = PurchaseOrder::with(['client', 'items.stockItem'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($po) {
                return [
                    'id' => $po->id,
                    'po_number' => $po->po_number,
                    'client_name' => $po->client->name,
                    'client_email' => $po->client->email,
                    'client_phone' => $po->client->telepon ?? null,
                    'total_amount' => $po->total_amount,
                    'status' => $po->status,
                    'payment_status' => $po->payment_status,
                    'created_at' => $po->created_at,
                    'items' => $po->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'item_name' => $item->item_name,
                            'sku' => $item->sku,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'subtotal' => $item->subtotal,
                            'item_type' => $item->item_type,
                        ];
                    }),
                ];
            });

        return response()->json($purchaseOrders);
    }

    public function clientIdentity($id, $po_number): JsonResponse
    {
        $data = User::findOrFail($id);
        $client = Client::where('client_id', $data->client_id)->first();
        $purchaseOrder = PurchaseOrder::where('client_id', $data->id)->where('po_number', $po_number)->first();
        $sale = Sale::where('id_pesanan', $po_number)->first();
        $result = [
            "phone" => $client->telepon,
            "email" => $data->email,
            "address" => $client->alamat,
            "bank" => $data->bank,
            "rekening" => $data->no_rekening,
            "id_pesanan" => $po_number,
            "periode" => $sale->periode,
            "status" => $purchaseOrder->status,
            "account" => $data->no_rekening
        ];
        return response()->json($result);
    }

    /**
     * Approve a purchase order.
     */
    public function approve($id): JsonResponse
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        
        if ($purchaseOrder->status !== 'pending') {
            return response()->json(['error' => 'Only pending purchase orders can be approved'], 400);
        }

        $purchaseOrder->update(['status' => 'approved']);
    
        Sale::where('id_pesanan', $purchaseOrder->po_number)->update([
            'status' => 'Dalam Proses-Belum Dibayar'
        ]);

        return response()->json(['message' => 'Purchase order approved successfully']);
    }

    public function invoice($id): JsonResponse
    {
        $data = PurchaseOrder::where('po_number', $id)->first();

        $items = PurchaseOrderItem::where('purchase_order_id', $data->id)->get();
        
        $periode = Sale::where('id_pesanan', $data->po_number)->first();

        $user = User::findOrFail($data->client_id);

        $clients = Client::where('client_id', $user->client_id)->first();
        $result = [
            "nama_pemesan" => $clients->nama,
            "client_id" => $data->client_id,
            "items" => $items,
            "id_pesanan" => $data->po_number,
            "periode" => $periode->periode,
            "telepon" => $clients->telepon,
            "alamat" => $user->alamat,
            "ongkir" => $clients->ongkir,
            "total_diskon" => $periode->total_diskon,
            "diskon_nilai" => $periode->diskon_nilai,
            "total_harga" => $data->total_amount,
            "status" => $data->status,
            "nama_sales" => $clients->nama_sales
        ];

        return response()->json($result);
    }



    /**
     * Mark purchase order as paid.
     */
    public function markAsPaid($id): JsonResponse
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);

        if ($purchaseOrder->status !== 'approved') {
            return response()->json(['error' => 'Only approved purchase orders can be marked as paid'], 400);
        }

        if ($purchaseOrder->payment_status === 'paid') {
            return response()->json(['error' => 'Purchase order is already paid'], 400);
        }

        $purchaseOrder->update(['payment_status' => 'paid']);

        Sale::where('id_pesanan', $purchaseOrder->po_number)->update([
            'status' => 'Dalam Proses-Sudah Dibayar'
        ]);

        return response()->json(['message' => 'Purchase order marked as paid successfully']);
    }

    /**
     * Mark purchase order as received.
     */
public function markAsReceived($id): JsonResponse
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $user = User::findorFail($purchaseOrder->client_id);
        $client = Client::where('client_id', $user->client_id)->first();

        if ($purchaseOrder->status !== 'approved') {
            return response()->json(['error' => 'Only approved purchase orders can be marked as received'], 400);
        }

        if ($purchaseOrder->payment_status !== 'paid') {
            return response()->json(['error' => 'Purchase order must be paid before marking as received'], 400);
        }

        $purchaseOrder->update(['status' => 'received']);

        // Ambil Data Dari Table Purchase Order Items
        $purchaseOrderItems = PurchaseOrderItem::where('purchase_order_id', $purchaseOrder->id)->get();
        foreach($purchaseOrderItems as $po_items){
            StockItem::where('sku', $po_items->sku)
            ->decrement('tersedia', $po_items->quantity);
            
            ClientStockItem::create([
                "client_id" => $client->id,
                "nama" => $po_items->item_name,
                "sku" => $po_items->sku,
                "kategori" => "Umum",
                "tersedia" => $po_items->quantity,
                "harga" => $po_items->unit_price,
                "diperbaharui" => date('Y-m-d')
            ]);
        }

        Sale::where('id_pesanan', $purchaseOrder->po_number)->update([
            'status' => 'Selesai'
        ]);


        return response()->json(['message' => 'Purchase order marked as received successfully']);
    }

    /**
     * Reject a purchase order (admin). Hidden from admin list, client sees status 'rejected'.
     */
    public function reject($id): JsonResponse
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);

        if (!in_array($purchaseOrder->status, ['pending', 'approved'])) {
            return response()->json(['error' => 'Only pending/approved purchase orders can be rejected'], 400);
        }

        $purchaseOrder->update([
            'status' => 'rejected',
            'payment_status' => 'unpaid',
        ]);

        return response()->json(['message' => 'Purchase order rejected successfully']);
    }

    /**
     * Permanently delete a purchase order by client if not processed.
     */
    public function destroy($id): JsonResponse
    {
        $purchaseOrder = PurchaseOrder::where('client_id', Auth::id())->with('items')->findOrFail($id);

        if (in_array($purchaseOrder->status, ['approved', 'received'])) {
            return response()->json(['error' => 'Cannot delete approved/received purchase orders'], 400);
        }

        DB::transaction(function () use ($purchaseOrder) {
            PurchaseOrderItem::where('purchase_order_id', $purchaseOrder->id)->delete();
            $purchaseOrder->delete();
        });

        return response()->json(['message' => 'Purchase order deleted successfully']);
    }

    /**
     * Permanently delete a purchase order by admin. Allowed only when status is rejected.
     */
    public function adminDestroy($id): JsonResponse
    {
        $purchaseOrder = PurchaseOrder::with('items')->findOrFail($id);

        if ($purchaseOrder->status !== 'rejected') {
            return response()->json(['error' => 'Only rejected purchase orders can be deleted by admin'], 400);
        }

        DB::transaction(function () use ($purchaseOrder) {
            PurchaseOrderItem::where('purchase_order_id', $purchaseOrder->id)->delete();
            $purchaseOrder->delete();
        });

        return response()->json(['message' => 'Purchase order deleted successfully']);
    }
}

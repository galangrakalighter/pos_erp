<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

// DRY helper untuk validasi admin role
function onlyAdmin(Closure $callback) {
    return function (...$args) use ($callback) {
        $user = auth()->user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Akses ditolak');
        }

        return $callback(...$args);
    };
}

// DRY helper untuk validasi client role
function onlyClient(Closure $callback) {
    return function (...$args) use ($callback) {
        $user = auth()->user();

        if (!$user || $user->role !== 'client') {
            abort(403, 'Akses ditolak');
        }

        return $callback(...$args);
    };
}

// Halaman utama - redirect ke login
Route::get('/', function () {
    return redirect()->route('login');
});

// Redirect awal berdasarkan role
Route::get('/dashboard', function () {
    $user = auth()->user();
    
    if (!$user) {
        return redirect('/login');
    }

    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($user->role === 'client') {
        return redirect()->route('client.dashboard');
    } else {
        abort(403, 'Role tidak dikenali');
    }
})->middleware(['auth'])->name('dashboard');

// Route untuk profile (breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Route untuk check session dan refresh CSRF token
    Route::get('/check-session', function () {
        return response()->json(['status' => 'authenticated', 'user' => auth()->user()->only(['name', 'email', 'role'])]);
    });
    
    Route::get('/csrf-token', function () {
        return response()->json(['token' => csrf_token()]);
    });
});


Route::get('/admin/dashboard', onlyAdmin(function () {
    return app(AdminDashboardController::class)->index();
}))->name('admin.dashboard');

Route::get('/admin/overview', onlyAdmin(function () {
    return view('admin.overview');
}))->name('admin.overview');

Route::get('/admin/client', onlyAdmin(function () {
    return view('admin.client');
}))->name('admin.client');


Route::get('/admin/sales', onlyAdmin(function () {
    return app(SalesController::class)->index(request());
}))->name('admin.sales');

Route::post('/admin/sales', onlyAdmin(function () {
    return app(SalesController::class)->store(request());
}))->name('admin.sales.store');

Route::put('/admin/sales/{id}', onlyAdmin(function ($id) {
    return app(SalesController::class)->update(request(), $id);
}));

Route::delete('/admin/sales/{id}', onlyAdmin(function ($id) {
    return app(SalesController::class)->destroy($id);
}));

// Route untuk stock management
Route::get('/admin/stock-items', onlyAdmin(function () {
    return app(\App\Http\Controllers\StockItemController::class)->index(request());
}))->name('admin.stock-items');

// Route client management (fetch data & tambah client via fetch, hanya admin)
Route::post('/admin/client', onlyAdmin(function () {
    return app(\App\Http\Controllers\ClientController::class)->store(request());
}));
Route::put('/admin/client/{id}', onlyAdmin(function ($id) {
    return app(\App\Http\Controllers\ClientController::class)->update(request(), (int) $id);
}));
Route::delete('/admin/client/{id}', onlyAdmin(function ($id) {
    return app(\App\Http\Controllers\ClientController::class)->destroy((int) $id);
}));

Route::get('/admin/purchase', onlyAdmin(function () {
    return view('admin.purchase');
}))->name('admin.purchase');


Route::get('/client/purchase', onlyClient(function () {
    return view('client.purchase');
}))->name('client.purchase');

// Purchase Order API Routes
Route::middleware(['auth', 'web'])->group(function () {
    // Client Purchase Order Routes
    Route::get('/client/purchase-orders', [\App\Http\Controllers\PurchaseOrderController::class, 'index'])->name('client.purchase-orders.index');
    Route::get('/client/purchase-orders/{id}', [\App\Http\Controllers\PurchaseOrderController::class, 'show'])->name('client.purchase-orders.show');
    Route::post('/client/purchase-orders', [\App\Http\Controllers\PurchaseOrderController::class, 'store'])->name('client.purchase-orders.store');
    Route::put('/client/purchase-orders/{id}/cancel', [\App\Http\Controllers\PurchaseOrderController::class, 'cancel'])->name('client.purchase-orders.cancel');
    Route::delete('/client/purchase-orders/{id}', [\App\Http\Controllers\PurchaseOrderController::class, 'destroy'])->name('client.purchase-orders.destroy');

    // Admin Purchase Order Routes
    Route::get('/admin/purchase-orders', onlyAdmin(function () {
        return app(\App\Http\Controllers\PurchaseOrderController::class)->adminIndex();
    }))->name('admin.purchase-orders.index');
    Route::put('/admin/purchase-orders/{id}/approve', onlyAdmin(function ($id) {
        return app(\App\Http\Controllers\PurchaseOrderController::class)->approve($id);
    }))->name('admin.purchase-orders.approve');
    Route::put('/admin/purchase-orders/{id}/invoice', onlyAdmin(function ($id) {
        return app(\App\Http\Controllers\PurchaseOrderController::class)->invoice($id);
    }))->name('admin.purchase-orders.invoice');
    Route::put('/admin/purchase-orders/{id}/reject', onlyAdmin(function ($id) {
        return app(\App\Http\Controllers\PurchaseOrderController::class)->reject($id);
    }))->name('admin.purchase-orders.reject');
    Route::put('/admin/purchase-orders/{id}/payment', onlyAdmin(function ($id) {
        return app(\App\Http\Controllers\PurchaseOrderController::class)->markAsPaid($id);
    }))->name('admin.purchase-orders.payment');
    Route::put('/admin/purchase-orders/{id}/received', onlyAdmin(function ($id) {
        return app(\App\Http\Controllers\PurchaseOrderController::class)->markAsReceived($id);
    }))->name('admin.purchase-orders.received');
    Route::delete('/admin/purchase-orders/{id}', onlyAdmin(function ($id) {
        return app(\App\Http\Controllers\PurchaseOrderController::class)->adminDestroy($id);
    }))->name('admin.purchase-orders.destroy');
    
    // Admin Internal Purchase Routes
    Route::get('/admin/purchases', onlyAdmin(function () {
        return view('admin.purchases');
    }))->name('admin.purchases');
    
    Route::get('/admin/purchases/api', onlyAdmin(function () {
        return app(\App\Http\Controllers\PurchaseController::class)->index();
    }))->name('admin.purchases.index');
    Route::post('/admin/purchases', onlyAdmin(function () {
        return app(\App\Http\Controllers\PurchaseController::class)->store(request());
    }))->name('admin.purchases.store');
    Route::put('/admin/purchases/{id}/approve', onlyAdmin(function ($id) {
        return app(\App\Http\Controllers\PurchaseController::class)->approve($id);
    }))->name('admin.purchases.approve');
    Route::put('/admin/purchases/{id}/reject', onlyAdmin(function ($id) {
        return app(\App\Http\Controllers\PurchaseController::class)->reject($id);
    }))->name('admin.purchases.reject');
    Route::put('/admin/purchases/{id}/complete', onlyAdmin(function ($id) {
        return app(\App\Http\Controllers\PurchaseController::class)->complete($id);
    }))->name('admin.purchases.complete');
    Route::put('/admin/purchases/{id}/payment-status', onlyAdmin(function ($id) {
        return app(\App\Http\Controllers\PurchaseController::class)->updatePaymentStatus(request(), $id);
    }))->name('admin.purchases.payment-status');
    Route::put('/admin/purchases/{id}/returned', onlyAdmin(function ($id) {
        return app(\App\Http\Controllers\PurchaseController::class)->markReturned($id);
    }))->name('admin.purchases.returned');
});

Route::get('/client/dashboard', onlyClient(function () {
    return view('client.dashboard');
}))->name('client.dashboard');

// Route test untuk debug
Route::get('/debug', function () {
    return 'Debug: Route ini bisa diakses tanpa auth!';
})->name('debug');

// Route cashier client (POS)
Route::get('/client/cashier', onlyClient(function () {
    return view('client.cashier');
}))->name('client.cashier');

// Route sales history client
Route::get('/client/salesc', onlyClient(function () {
    return view('client.salesc');
}))->name('client.salesc');

// Route stock client
Route::get('/client/stockc', onlyClient(function () {
    return view('client.stockc');
}))->name('client.stockc');

// API Client Stock Items (client only)
Route::middleware(['auth', 'web'])->group(function () {
    Route::get('/client/stock-items', function () {
        return app(\App\Http\Controllers\ClientStockController::class)->index(request());
    })->name('client.stock-items.index');
    
    // Route::get('/client/stock-items', function () {
    //     return app(\App\Http\Controllers\ClientStockController::class)->index(request());
    // })->name('client.stock-items.index');

    Route::put('/client/purchase-orders/{id}/received', function ($id) {
        return app(\App\Http\Controllers\PurchaseOrderController::class)->markAsReceived($id);
    })->name('admin.purchase-orders.received');

    Route::post('/client/stock-items/import', function () {
        return app(\App\Http\Controllers\StockItemImportController::class)->import(request());
    });
    
    Route::post('/client/identity/{id}/{po_number}', function ($id, $po) {
        return app(\App\Http\Controllers\PurchaseOrderController::class)->clientIdentity($id, $po);
    });
    
    Route::post('/client/purchase-orders/{id}/getstock', onlyAdmin(function ($id) {
        return app(\App\Http\Controllers\PurchaseOrderController::class)->getStock($id);
    }))->name('client.purchase-orders.getStock');

    Route::post('/client/stock-items', function () {
        return app(\App\Http\Controllers\ClientStockController::class)->store(request());
    })->name('client.stock-items.store');
    
    Route::put('/client/stock-items/{id}', function ($id) {
        return app(\App\Http\Controllers\ClientStockController::class)->update(request(), $id);
    })->name('client.stock-items.update');
    
    Route::delete('/client/stock-items/{id}', function ($id) {
        return app(\App\Http\Controllers\ClientStockController::class)->destroy($id);
    })->name('client.stock-items.destroy');
    
    Route::post('/client/stock-items/{id}/split', function ($id) {
        return app(\App\Http\Controllers\ClientStockController::class)->split(request(), $id);
    })->name('client.stock-items.split');
    
    Route::post('/client/stock-items/checkout', function () {
        return app(\App\Http\Controllers\ClientStockController::class)->checkout(request());
    })->name('client.stock-items.checkout');
    
    Route::get('/client/sales-history', function () {
        return app(\App\Http\Controllers\ClientStockController::class)->getSalesHistory(request());
    })->name('client.sales-history');
    
    Route::post('/client/sales', function () {
        return app(\App\Http\Controllers\ClientStockController::class)->addSale(request());
    })->name('client.sales.store');

    Route::post('/client/sales/update/{id}', [\App\Http\Controllers\ClientStockController::class, 'editSale'])->name('client.sales.update');
    
    Route::get('/client/sales/{id}', function ($id) {
        return app(\App\Http\Controllers\ClientStockController::class)->getSaleDetail($id);
    })->name('client.sales.show');
    
    Route::get('/client/dashboard-data', function () {
        return app(\App\Http\Controllers\ClientStockController::class)->getDashboardData();
    })->name('client.dashboard-data');
    
    Route::delete('/client/sales/{id}', function ($id) {
        return app(\App\Http\Controllers\ClientStockController::class)->deleteSale($id);
    })->name('client.sales.destroy');
    
    Route::get('/client/stock-items/categories', function () {
        return app(\App\Http\Controllers\ClientStockController::class)->getCategories();
    })->name('client.stock-items.categories');
    
    // External Items Routes
    Route::get('/client/external-items', function () {
        return app(\App\Http\Controllers\ExternalItemController::class)->index(request());
    })->name('client.external-items.index');
    Route::post('/client/external-items', function () {
        return app(\App\Http\Controllers\ExternalItemController::class)->store(request());
    })->name('client.external-items.store');
    Route::get('/client/external-items/{id}', function ($id) {
        return app(\App\Http\Controllers\ExternalItemController::class)->show($id);
    })->name('client.external-items.show');
    Route::put('/client/external-items/{id}', function ($id) {
        return app(\App\Http\Controllers\ExternalItemController::class)->update(request(), $id);
    })->name('client.external-items.update');
    Route::delete('/client/external-items/{id}', function ($id) {
        return app(\App\Http\Controllers\ExternalItemController::class)->destroy($id);
    })->name('client.external-items.destroy');
});

// API Stock Items (admin only)
Route::middleware('auth')->group(function () {
    Route::get('/admin/stock-items', onlyAdmin(function () {
        return app(\App\Http\Controllers\StockItemController::class)->list();
    }));
    Route::post('/admin/stock-items', onlyAdmin(function () {
        return app(\App\Http\Controllers\StockItemController::class)->store(request());
    }));
    Route::get('/admin/stock-items/{id}/history', onlyAdmin(function ($id) {
        return app(\App\Http\Controllers\StockItemController::class)->history((int) $id);
    }));
    Route::put('/admin/stock-items/{id}', onlyAdmin(function ($id) {
        return app(\App\Http\Controllers\StockItemController::class)->update(request(), (int) $id);
    }));
    Route::delete('/admin/stock-items/{id}', onlyAdmin(function ($id) {
        return app(\App\Http\Controllers\StockItemController::class)->destroy((int) $id);
    }));
    Route::post('/admin/stock-items/import', onlyAdmin(function () {
        return app(\App\Http\Controllers\StockItemImportController::class)->import(request());
    }));
});

// API Clients (admin only)
Route::middleware('auth')->group(function () {
    Route::get('/admin/clients', onlyAdmin(function () {
        return app(\App\Http\Controllers\ClientController::class)->list();
    }));
    Route::get('/admin/clients/{id}', onlyAdmin(function ($id) {
        return app(\App\Http\Controllers\ClientController::class)->show($id);
    }));
    Route::get('/admin/clients/{id}/history', onlyAdmin(function ($id) {
        return app(\App\Http\Controllers\ClientController::class)->history($id);
    }));
    Route::post('/admin/clients', onlyAdmin(function () {
        return app(\App\Http\Controllers\ClientController::class)->store(request());
    }));
    Route::put('/admin/clients/{id}', onlyAdmin(function ($id) {
        return app(\App\Http\Controllers\ClientController::class)->update(request(), $id);
    }));
    Route::delete('/admin/clients/{id}', onlyAdmin(function ($id) {
        return app(\App\Http\Controllers\ClientController::class)->destroy($id);
    }));
});

// Route untuk validasi client_id sebelum register
Route::post('/validate-client-id', function (Request $request) {
    $request->validate([
        'client_id' => 'required|string|max:255'
    ]);
    
    $client = \App\Models\Client::where('client_id', $request->client_id)->first();
    
    if ($client) {
        return response()->json([
            'valid' => true,
            'client' => [
                'id' => $client->id,
                'client_id' => $client->client_id,
                'name' => $client->nama
            ]
        ]);
    }
    
    return response()->json([
        'valid' => false,
        'message' => 'Client ID tidak ditemukan atau tidak valid.'
    ]);
})->name('validate-client-id');

// Route untuk validasi client_id di admin (cek keunikan)
Route::get('/admin/validate-client-id', function (Request $request) {
    $request->validate([
        'client_id' => 'required|string|max:255'
    ]);
    
    $client = \App\Models\Client::where('client_id', $request->client_id)->first();
    
    return response()->json([
        'unique' => !$client, // true jika client_id unik (tidak ada), false jika sudah ada
        'message' => $client ? 'Client ID sudah ada' : 'Client ID tersedia'
    ]);
})->name('admin.validate-client-id');

// Route untuk mengambil dan menyimpan data identitas admin
Route::get('/admin/identity', function () {
    $user = auth()->user();
    if (!$user || $user->role !== 'admin') {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    
    return response()->json([
        'telepon' => $user->telepon,
        'alamat' => $user->alamat,
        'email' => $user->email,
        'bank' => $user->bank,
        'no_rekening' => $user->no_rekening,
    ]);
})->name('admin.identity.get');

Route::post('/admin/identity', function (Request $request) {
    $user = auth()->user();
    if (!$user || $user->role !== 'admin') {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    
    $request->validate([
        'telepon' => 'nullable|string|max:255',
        'alamat' => 'nullable|string',
        'email' => 'nullable|email|max:255',
        'bank' => 'nullable|string|max:255',
        'no_rekening' => 'nullable|string|max:255',
    ]);
    
    $user->update([
        'telepon' => $request->telepon,
        'alamat' => $request->alamat,
        'email' => $request->email,
        'bank' => $request->bank,
        'no_rekening' => $request->no_rekening,
    ]);
    
    return response()->json(['success' => true, 'message' => 'Data identitas berhasil disimpan']);
})->name('admin.identity.store');

// Route untuk dismiss notifikasi stock
Route::post('/admin/dismiss-notification/{id}', function (Request $request, $id) {
    $user = auth()->user();
    if (!$user || $user->role !== 'admin') {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    
    try {
        $notification = \App\Models\StockNotification::findOrFail($id);
        $notification->dismiss($user->id);
        
        return response()->json(['success' => true, 'message' => 'Notifikasi berhasil dihilangkan']);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Gagal menghilangkan notifikasi: ' . $e->getMessage()], 500);
    }
})->name('admin.dismiss.notification');

// Route untuk dismiss notifikasi client
Route::post('/client/dismiss-notification/{id}', function (Request $request, $id) {
    $user = auth()->user();
    if (!$user || $user->role !== 'client') {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    
    try {
        $notification = \App\Models\StockNotification::findOrFail($id);
        $notification->dismiss($user->id);
        
        return response()->json(['success' => true, 'message' => 'Notifikasi berhasil dihilangkan']);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Gagal menghilangkan notifikasi: ' . $e->getMessage()], 500);
    }
})->name('client.dismiss.notification');

// Route untuk data overview admin
Route::get('/admin/overview-data', function (Request $request) {
    $user = auth()->user();
    if (!$user || $user->role !== 'admin') {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    
    $lowStockThreshold = $request->get('threshold', 10);
    $clientLowStockThreshold = $request->get('clientThreshold', 10);
    $period = $request->get('period', 'month');
    $now = now();
    
    // FIXED: Logic tanggal yang lebih masuk akal
    if ($period === 'week') {
        $startDate = $now->copy()->subDays(7);
    } elseif ($period === 'year') {
        $startDate = $now->copy()->subYear();
    } else {
        // FIXED: Untuk month, ambil 14 hari terakhir saja (lebih masuk akal)
        $startDate = $now->copy()->subDays(14);
    }

    // Ambil semua data sales dan client items dalam rentang waktu
    $allSales = \App\Models\Sale::whereIn('status', ['Selesai', 'Dalam Proses-Sudah Dibayar'])
        ->where('created_at', '>=', $startDate)
        ->where('created_at', '<=', $now)
        ->get();

    $allClientItems = \App\Models\ClientItem::where('created_at', '>=', $startDate)
        ->where('created_at', '<=', $now)
        ->get();
    
    // Debug: Log data yang ditemukan secara detail
    \Log::info('Data Collection Details', [
        'period' => $period,
        'startDate' => $startDate->format('Y-m-d H:i:s'),
        'endDate' => $now->format('Y-m-d H:i:s'),
        'salesFound' => $allSales->count(),
        'clientItemsFound' => $allClientItems->count(),
        'allSalesData' => $allSales->map(function($s) {
            return [
                'id' => $s->id,
                'id_pesanan' => $s->id_pesanan,
                'total_harga' => $s->total_harga,
                'created_at' => $s->created_at->format('Y-m-d H:i:s'),
                'created_at_raw' => $s->created_at->toDateTimeString(),
                'created_at_timestamp' => $s->created_at->timestamp
            ];
        })->toArray(),
        'allClientItemsData' => $allClientItems->map(function($c) {
            return [
                'id' => $c->id,
                'total_harga' => $c->total_harga,
                'created_at' => $c->created_at->format('Y-m-d H:i:s'),
                'created_at_raw' => $c->created_at->toDateTimeString(),
                'created_at_timestamp' => $c->created_at->timestamp
            ];
        })->toArray()
    ]);

    // Hitung total omzet untuk KPI
    $salesOmzet = $allSales->sum('total_harga');
    $clientOmzet = $allClientItems->sum('total_harga');
    $totalOmzet = $salesOmzet + $clientOmzet;

    // Debug logging untuk memastikan perhitungan yang benar
    \Log::info('Overview Data Calculation', [
        'period' => $period,
        'startDate' => $startDate->format('Y-m-d'),
        'endDate' => $now->format('Y-m-d'),
        'allSales' => $allSales->count(),
        'salesOmzet' => $salesOmzet,
        'allClientItems' => $allClientItems->count(),
        'clientOmzet' => $clientOmzet,
        'totalOmzet' => $totalOmzet
    ]);

    // Data untuk grafik omzet per hari/bulan - LOGIC YANG LEBIH SEDERHANA
    $revenueData = [];
    
    if ($period === 'year') {
        // Data per bulan untuk 12 bulan terakhir
        for ($i = 11; $i >= 0; $i--) {
            $monthStart = $now->copy()->subMonths($i)->startOfMonth();
            $monthEnd = $now->copy()->subMonths($i)->endOfMonth();
            
            // Hitung omzet bulan ini dengan cara yang lebih sederhana
            $monthRevenue = 0;
            
            // Loop semua sales
            foreach ($allSales as $sale) {
                $saleDate = $sale->created_at;
                if ($saleDate >= $monthStart && $saleDate <= $monthEnd) {
                    $monthRevenue += $sale->total_harga;
                }
            }
            
            // Loop semua client items
            foreach ($allClientItems as $item) {
                $itemDate = $item->created_at;
                if ($itemDate >= $monthStart && $itemDate <= $monthEnd) {
                    $monthRevenue += $item->total_harga;
                }
            }
            
            $revenueData[] = [
                'label' => $monthStart->format('M Y'),
                'value' => $monthRevenue,
                'date' => $monthStart->format('Y-m')
            ];
        }
    } else {
        // Data per hari untuk minggu/bulan
        $days = $period === 'week' ? 7 : 14; // FIXED: month = 14 hari, bukan 30
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            // FIXED: Gunakan startOfDay() dan endOfDay() yang benar
            $dayStart = $date->copy()->startOfDay();
            $dayEnd = $date->copy()->endOfDay();
            
            // Debug: Log tanggal yang sedang diproses
            \Log::info('Processing day - FIXED', [
                'dayIndex' => $i,
                'date' => $date->format('Y-m-d'),
                'dayStart' => $dayStart->format('Y-m-d H:i:s'),
                'dayEnd' => $dayEnd->format('Y-m-d H:i:s')
            ]);
            
            // Hitung omzet hari ini dengan cara yang lebih sederhana
            $dayRevenue = 0;
            $salesInDay = 0;
            $clientItemsInDay = 0;
            
            // Loop semua sales
            foreach ($allSales as $sale) {
                $saleDate = $sale->created_at;
                $isInRange = $saleDate >= $dayStart && $saleDate <= $dayEnd;
                
                // Debug: Log setiap sale yang dicek
                \Log::info('Checking sale in loop', [
                    'date' => $date->format('Y-m-d'),
                    'saleId' => $sale->id,
                    'saleDate' => $saleDate->format('Y-m-d H:i:s'),
                    'dayStart' => $dayStart->format('Y-m-d H:i:s'),
                    'dayEnd' => $dayEnd->format('Y-m-d H:i:s'),
                    'isInRange' => $isInRange,
                    'total_harga' => $sale->total_harga
                ]);
                
                if ($isInRange) {
                    $dayRevenue += $sale->total_harga;
                    $salesInDay++;
                }
            }
            
            // Loop semua client items
            foreach ($allClientItems as $item) {
                $itemDate = $item->created_at;
                $isInRange = $itemDate >= $dayStart && $itemDate <= $dayEnd;
                
                // Debug: Log setiap client item yang dicek
                \Log::info('Checking client item in loop', [
                    'date' => $date->format('Y-m-d'),
                    'itemId' => $item->id,
                    'itemDate' => $itemDate->format('Y-m-d H:i:s'),
                    'dayStart' => $dayStart->format('Y-m-d H:i:s'),
                    'dayEnd' => $dayEnd->format('Y-m-d H:i:s'),
                    'isInRange' => $isInRange,
                    'total_harga' => $item->total_harga
                ]);
                
                if ($isInRange) {
                    $dayRevenue += $item->total_harga;
                    $clientItemsInDay++;
                }
            }
            
            // Debug: Log hari yang ada data
            if ($dayRevenue > 0) {
                \Log::info('Day with revenue found - FIXED', [
                    'date' => $date->format('Y-m-d'),
                    'salesInDay' => $salesInDay,
                    'clientItemsInDay' => $clientItemsInDay,
                    'dayRevenue' => $dayRevenue
                ]);
            }
            
            $revenueData[] = [
                'label' => $date->format('d M'),
                'value' => $dayRevenue,
                'date' => $date->format('Y-m-d')
            ];
        }
    }

    // Debug: Log revenue data untuk memastikan data terisi
    \Log::info('Revenue Data Generated', [
        'period' => $period,
        'dataCount' => count($revenueData),
        'sampleData' => array_slice($revenueData, 0, 5), // 5 data pertama
        'totalRevenue' => array_sum(array_column($revenueData, 'value')),
        'nonZeroDays' => count(array_filter($revenueData, function($d) { return $d['value'] > 0; })),
        'zeroDays' => count(array_filter($revenueData, function($d) { return $d['value'] == 0; })),
        'fullRevenueData' => $revenueData, // Semua data untuk debugging
        'firstFewItems' => array_slice($revenueData, 0, 3) // 3 item pertama dengan detail
    ]);

    // Debug: Log data yang akan dikirim ke frontend
    \Log::info('Data being sent to frontend', [
        'revenueData' => $revenueData,
        'kpi' => [
            'totalClients' => \App\Models\Client::count(),
            'totalItems' => \App\Models\StockItem::count(),
            'lowStockCount' => \App\Models\StockItem::where('tersedia', '<=', $lowStockThreshold)->count(),
            'txInRange' => $allSales->count() + $allClientItems->count(),
            'revenueInRange' => $totalOmzet,
            'unpaidCount' => \App\Models\Sale::whereIn('status', ['Belum approval', 'Dalam Proses-Belum Dibayar'])->count()
        ]
    ]);

    // Ambil pending payments (status belum dibayar)
    $pendingPayments = \App\Models\Sale::whereIn('status', ['Belum approval', 'Dalam Proses-Belum Dibayar'])
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get()
        ->map(function ($sale) {
            return [
                'id' => $sale->id,
                'id_pesanan' => $sale->id_pesanan,
                'nama_pemesan' => $sale->nama_pemesan,
                'status' => $sale->status,
                'total_harga' => $sale->total_harga,
                'total_diskon' => $sale->total_diskon,
                'items' => $sale->items ? json_decode($sale->items, true) : [],
                'created_at' => $sale->created_at->format('Y-m-d H:i:s')
            ];
        });
    
    // Ambil aktivitas terakhir dari sales dan stock history
    $recentActivities = collect();
    
    // Aktivitas dari sales (5 terakhir)
    $salesActivities = \App\Models\Sale::orderBy('created_at', 'desc')
        ->limit(5)
        ->get()
        ->map(function ($sale) {
            return [
                'id' => 'sale_' . $sale->id,
                'type' => 'sale',
                'text' => "Penjualan {$sale->id_pesanan} - {$sale->nama_pemesan}",
                'time' => $sale->created_at->format('Y-m-d'),
                'created_at' => $sale->created_at
            ];
        });
    
    // Aktivitas dari stock history (5 terakhir) - tampilkan nama item yang stocknya habis
    // Gunakan groupBy untuk menghindari duplikasi item yang sama
    $stockActivities = \App\Models\StockItemHistory::orderBy('created_at', 'desc')
        ->get()
        ->groupBy('nama_item') // Group berdasarkan nama item
        ->map(function ($group) {
            // Ambil yang terbaru dari setiap group
            $latest = $group->first();
            $stockText = $latest->tersedia <= 0 ? 
                "Stok {$latest->nama_item} habis (0)" : 
                "Stok {$latest->nama_item} tersisa {$latest->tersedia}";
            
            return [
                'id' => 'stock_' . $latest->id,
                'type' => 'stock',
                'text' => $stockText,
                'time' => $latest->created_at->format('Y-m-d'),
                'created_at' => $latest->created_at
            ];
        })
        ->take(5) // Ambil 5 terbaru setelah deduplikasi
        ->values();
    
    // Gabungkan dan urutkan berdasarkan waktu terbaru
    $recentActivities = $salesActivities->concat($stockActivities)
        ->sortByDesc('created_at')
        ->take(10)
        ->values()
        ->map(function ($activity) {
            unset($activity['created_at']);
            return $activity;
        });
    
    // Data untuk KPI
    $totalClients = \App\Models\Client::count();
    $totalItems = \App\Models\StockItem::count();
    
    // Low stock count berdasarkan threshold admin
    $lowStockCount = \App\Models\StockItem::where('tersedia', '<=', $lowStockThreshold)->count();
    
    // Transaksi dalam rentang waktu (gabungan sales lunas + client items)
    $txInRange = $allSales->count() + $allClientItems->count();
    
    // Unpaid count
    $unpaidCount = \App\Models\Sale::whereIn('status', ['Belum approval', 'Dalam Proses-Belum Dibayar'])->count();
    
    // Low stock items dengan threshold yang bisa diubah
    $lowStockItems = \App\Models\StockItem::where('tersedia', '<=', $lowStockThreshold)
        ->orderBy('tersedia', 'asc')
        ->limit(5)
        ->get()
        ->map(function ($item) {
            return [
                'id' => $item->id,
                'nama' => $item->nama,
                'sku' => $item->sku,
                'tersedia' => $item->tersedia
            ];
        });
    
                // Generate notifikasi low stock menggunakan tabel StockNotification dengan threshold yang benar
        \App\Models\StockNotification::generateLowStockNotifications(
            $user->id, 
            $lowStockThreshold, 
            $clientLowStockThreshold
        );
        
        // Ambil notifikasi yang aktif (belum di-dismiss)
        $lowStockNotif = \App\Models\StockNotification::active()
            ->where('user_id', $user->id)
            ->orderBy('current_stock', 'asc')
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id, // ID notifikasi untuk dismiss
                    'nama' => $notification->item_name,
                    'sku' => $notification->sku,
                    'tersedia' => $notification->current_stock,
                    'tipe' => $notification->item_type,
                    'lokasi' => $notification->item_type === 'admin' ? 'Admin/Pusat' : 'Client',
                    'client_nama' => $notification->item_type === 'admin' ? null : 'GAFI',
                    'threshold' => $notification->threshold,
                    'notification_type' => $notification->notification_type
                ];
            });
    
    return response()->json([
        'pendingPayments' => $pendingPayments,
        'recentActivities' => $recentActivities,
        'kpi' => [
            'totalClients' => $totalClients,
            'totalItems' => $totalItems,
            'lowStockCount' => $lowStockCount,
            'txInRange' => $txInRange,
            'revenueInRange' => $totalOmzet,
            'unpaidCount' => $unpaidCount
        ],
        'lowStockItems' => $lowStockItems,
        'revenueData' => $revenueData,
        'totalOmzet' => $totalOmzet,
        'debug' => [
            'salesOmzet' => $salesOmzet,
            'clientOmzet' => $clientOmzet,
            'totalOmzet' => $totalOmzet,
            'salesCount' => $allSales->count(),
            'clientItemsCount' => $allClientItems->count(),
            'revenueDataCount' => count($revenueData),
            'revenueDataSample' => array_slice($revenueData, 0, 3), // 3 data pertama untuk debug
            'revenueDataFull' => $revenueData, // Semua data untuk debug
            'period' => $period,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $now->format('Y-m-d'),
            'nonZeroDays' => count(array_filter($revenueData, function($d) { return $d['value'] > 0; })),
            'zeroDays' => count(array_filter($revenueData, function($d) { return $d['value'] == 0; }))
        ],
        'lowStockNotif' => $lowStockNotif
    ]);
})->name('admin.overview.data');

// Route test untuk debug revenue data
Route::get('/admin/test-revenue', function () {
    $now = now();
    
    // FIXED: Ambil range yang lebih masuk akal - 7 hari terakhir saja untuk testing
    $startDate = $now->copy()->subDays(7);
    
    // Ambil data sales dan client items
    $allSales = \App\Models\Sale::whereIn('status', ['Selesai', 'Dalam Proses-Sudah Dibayar'])
        ->where('created_at', '>=', $startDate)
        ->where('created_at', '<=', $now)
        ->get();
    
    $allClientItems = \App\Models\ClientItem::where('created_at', '>=', $startDate)
        ->where('created_at', '<=', $now)
        ->get();
    
    // Debug: Log data yang ditemukan
    \Log::info('Test Revenue Debug - FIXED', [
        'startDate' => $startDate->format('Y-m-d H:i:s'),
        'endDate' => $now->format('Y-m-d H:i:s'),
        'salesFound' => $allSales->count(),
        'clientItemsFound' => $allClientItems->count(),
        'sampleSales' => $allSales->take(3)->map(function($s) {
            return [
                'id' => $s->id,
                'total_harga' => $s->total_harga,
                'created_at' => $s->created_at->format('Y-m-d H:i:s'),
                'created_at_raw' => $s->created_at->toDateTimeString()
            ];
        })->toArray(),
        'sampleClientItems' => $allClientItems->take(3)->map(function($c) {
            return [
                'id' => $c->id,
                'total_harga' => $c->total_harga,
                'created_at' => $c->created_at->format('Y-m-d H:i:s'),
                'created_at_raw' => $c->created_at->toDateTimeString()
            ];
        })->toArray()
    ]);
    
    // Test revenue data generation - FIXED: 7 hari terakhir saja
    $revenueData = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = $now->copy()->subDays($i);
        $dayStart = $date->startOfDay();
        $dayEnd = $date->endOfDay();
        
        $dayRevenue = 0;
        $salesInDay = 0;
        $clientItemsInDay = 0;
        
        // Debug: Log tanggal yang sedang diproses
        \Log::info('Processing day - FIXED', [
            'dayIndex' => $i,
            'date' => $date->format('Y-m-d'),
            'dayStart' => $dayStart->format('Y-m-d H:i:s'),
            'dayEnd' => $dayEnd->format('Y-m-d H:i:s')
        ]);
        
        foreach ($allSales as $sale) {
            $saleDate = $sale->created_at;
            $isInRange = $saleDate >= $dayStart && $saleDate <= $dayEnd;
            
            // Debug: Log setiap sale yang dicek
            \Log::info('Checking sale - FIXED', [
                'saleId' => $sale->id,
                'saleDate' => $saleDate->format('Y-m-d H:i:s'),
                'dayStart' => $dayStart->format('Y-m-d H:i:s'),
                'dayEnd' => $dayEnd->format('Y-m-d H:i:s'),
                'isInRange' => $isInRange,
                'total_harga' => $sale->total_harga
            ]);
            
            if ($isInRange) {
                $dayRevenue += $sale->total_harga;
                $salesInDay++;
            }
        }
        
        foreach ($allClientItems as $item) {
            $itemDate = $item->created_at;
            $isInRange = $itemDate >= $dayStart && $itemDate <= $dayEnd;
            
            // Debug: Log setiap client item yang dicek
            \Log::info('Checking client item - FIXED', [
                'itemId' => $item->id,
                'itemDate' => $itemDate->format('Y-m-d H:i:s'),
                'dayStart' => $dayStart->format('Y-m-d H:i:s'),
                'dayEnd' => $dayEnd->format('Y-m-d H:i:s'),
                'isInRange' => $isInRange,
                'total_harga' => $item->total_harga
            ]);
            
            if ($isInRange) {
                $dayRevenue += $item->total_harga;
                $clientItemsInDay++;
            }
        }
        
        // Debug: Log hasil per hari
        \Log::info('Day result - FIXED', [
            'date' => $date->format('Y-m-d'),
            'salesInDay' => $salesInDay,
            'clientItemsInDay' => $clientItemsInDay,
            'dayRevenue' => $dayRevenue
        ]);
        
        $revenueData[] = [
            'label' => $date->format('d M'),
            'value' => $dayRevenue,
            'date' => $date->format('Y-m-d')
        ];
    }
    
    return response()->json([
        'test' => 'Revenue Data Generation Test - FIXED',
        'period' => 'week',
        'startDate' => $startDate->format('Y-m-d'),
        'endDate' => $now->format('Y-m-d'),
        'salesCount' => $allSales->count(),
        'clientItemsCount' => $allClientItems->count(),
        'revenueData' => $revenueData,
        'totalRevenue' => array_sum(array_column($revenueData, 'value')),
        'nonZeroDays' => count(array_filter($revenueData, function($d) { return $d['value'] > 0; })),
        'zeroDays' => count(array_filter($revenueData, function($d) { return $d['value'] == 0; })),
        'debug' => [
            'salesFound' => $allSales->count(),
            'clientItemsFound' => $allClientItems->count(),
            'sampleSales' => $allSales->take(3)->map(function($s) {
                return [
                    'id' => $s->id,
                    'total_harga' => $s->total_harga,
                    'created_at' => $s->created_at->format('Y-m-d H:i:s')
                ];
            })->toArray(),
            'sampleClientItems' => $allClientItems->take(3)->map(function($c) {
                return [
                    'id' => $c->id,
                    'total_harga' => $c->total_harga,
                    'created_at' => $c->created_at->format('Y-m-d H:i:s')
                ];
            })->toArray()
        ]
    ]);
})->name('admin.test.revenue');

// Route test sederhana untuk cek data
Route::get('/admin/test-simple', function () {
    // Cek data sales
    $sales = \App\Models\Sale::whereIn('status', ['Selesai', 'Dalam Proses-Sudah Dibayar'])->get();
    $clientItems = \App\Models\ClientItem::all();
    
    return response()->json([
        'test' => 'Simple Data Check',
        'sales' => [
            'count' => $sales->count(),
            'data' => $sales->map(function($s) {
                return [
                    'id' => $s->id,
                    'total_harga' => $s->total_harga,
                    'created_at' => $s->created_at->format('Y-m-d H:i:s'),
                    'status' => $s->status
                ];
            })->toArray()
        ],
        'clientItems' => [
            'count' => $clientItems->count(),
            'data' => $clientItems->map(function($c) {
                return [
                    'id' => $c->id,
                    'total_harga' => $c->total_harga,
                    'created_at' => $c->created_at->format('Y-m-d H:i:s')
                ];
            })->toArray()
        ]
    ]);
})->name('admin.test.simple');

// Route test untuk cek logic tanggal yang sudah diperbaiki
Route::get('/admin/test-date-logic', function () {
    $now = now();
    $startDate = $now->copy()->subDays(14); // 14 hari terakhir
    
    // Ambil data dalam range yang benar
    $sales = \App\Models\Sale::whereIn('status', ['Selesai', 'Dalam Proses-Sudah Dibayar'])
        ->where('created_at', '>=', $startDate)
        ->where('created_at', '<=', $now)
        ->get();
    
    $clientItems = \App\Models\ClientItem::where('created_at', '>=', $startDate)
        ->where('created_at', '<=', $now)
        ->get();
    
    // Test logic revenue per hari
    $revenueData = [];
    for ($i = 13; $i >= 0; $i--) {
        $date = $now->copy()->subDays($i);
        $dayStart = $date->startOfDay();
        $dayEnd = $date->endOfDay();
        
        $dayRevenue = 0;
        
        foreach ($sales as $sale) {
            if ($sale->created_at >= $dayStart && $sale->created_at <= $dayEnd) {
                $dayRevenue += $sale->total_harga;
            }
        }
        
        foreach ($clientItems as $item) {
            if ($item->created_at >= $dayStart && $item->created_at <= $dayEnd) {
                $dayRevenue += $item->total_harga;
            }
        }
        
        $revenueData[] = [
            'label' => $date->format('d M'),
            'value' => $dayRevenue,
            'date' => $date->format('Y-m-d')
        ];
    }
    
    return response()->json([
        'test' => 'Date Logic Test - FIXED',
        'startDate' => $startDate->format('Y-m-d'),
        'endDate' => $now->format('Y-m-d'),
        'salesFound' => $sales->count(),
        'clientItemsFound' => $clientItems->count(),
        'revenueData' => $revenueData,
        'totalRevenue' => array_sum(array_column($revenueData, 'value')),
        'nonZeroDays' => count(array_filter($revenueData, function($d) { return $d['value'] > 0; })),
        'zeroDays' => count(array_filter($revenueData, function($d) { return $d['value'] == 0; }))
    ]);
})->name('admin.test.date.logic');

// API: Client fetch stock admin (read-only, untuk POS client)
Route::middleware('auth')->get('/client/admin-stock-items', function () {
    $user = auth()->user();
    if (!$user || $user->role !== 'client') {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    
    $stockItems = \App\Models\StockItem::select('id', 'nama', 'sku', 'harga', 'tersedia', 'kategori', 'lokasi')
        // Tampilkan semua item termasuk yang stock 0 (untuk Purchase Order)
        ->orderBy('nama')
        ->get()
        ->map(function ($item) {
            return [
                'id' => $item->id,
                'nama' => $item->nama,
                'sku' => $item->sku,
                'harga' => $item->harga,
                'tersedia' => $item->tersedia,
                'kategori' => $item->kategori,
                'lokasi' => $item->lokasi ?: 'Admin/Pusat'
            ];
        });
    
    return response()->json($stockItems);
})->name('client.admin-stock-items');

// API: Client low stock notification menggunakan tabel StockNotification
Route::middleware('auth')->get('/client/low-stock-notif', function (Request $request) {
    $user = auth()->user();
    if (!$user || $user->role !== 'client') {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    
    $thresholdGafi = $request->get('threshold', 10);
    $thresholdUmum = 10;
    
    // Get client ID
    $client = \App\Models\Client::where('client_id', $user->client_id)->first();
    if (!$client) {
        return response()->json(['error' => 'Client not found'], 404);
    }
    
            // Generate notifikasi low stock untuk client ini dengan threshold yang benar
        \App\Models\StockNotification::generateLowStockNotifications(
            $user->id, 
            null, // admin threshold tidak digunakan untuk client
            $thresholdGafi // client threshold
        );
        
        // Ambil notifikasi yang aktif (belum di-dismiss) untuk client ini
        $lowStockItems = \App\Models\StockNotification::active()
            ->where('user_id', $user->id)
            ->where('item_type', 'client') // Hanya item client
            ->orderBy('current_stock', 'asc')
            ->get()
            ->map(function ($notification) use ($thresholdGafi, $thresholdUmum) {
                $isGafi = $notification->item_type === 'client';
                $threshold = $isGafi ? $thresholdGafi : $thresholdUmum;
                
                return [
                    'id' => $notification->id, // ID notifikasi untuk dismiss
                    'itemName' => $notification->item_name,
                    'sku' => $notification->sku,
                    'stock' => $notification->current_stock,
                    'threshold' => $threshold,
                    'category' => $isGafi ? 'GAFI' : 'Umum',
                    'source' => 'client',
                    'nama' => $notification->item_name,
                    'tersedia' => $notification->current_stock
                ];
            });
    
    return response()->json([
        'lowStockNotif' => $lowStockItems->values()
    ]);
});

// Client identity endpoints (gabungan users dan clients)
Route::middleware('auth')->get('/client/identity', function () {
    $user = auth()->user();
    if (!$user || $user->role !== 'client') {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    $client = \App\Models\Client::where('client_id', $user->client_id)->first();
    return response()->json([
        'nama' => $user->name,
        'mitra_nama' => $user->mitra_name,
        'telepon' => $user->telepon,
        'alamat' => $client ? $client->alamat : '',
        'email' => $user->email,
        'bank' => $user->bank,
        'no_rekening' => $user->no_rekening,
        'logo_url' => $user->logo_path ? route('client.logo') . '?v=' . time() : null,
    ]);
});
Route::middleware('auth')->post('/client/identity', function (Request $request) {
    $user = auth()->user();
    if (!$user || $user->role !== 'client') {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    $client = \App\Models\Client::where('client_id', $user->client_id)->first();
    $request->validate([
        'mitra_nama' => 'nullable|string|max:255',
        'telepon' => 'nullable|string|max:255',
        'alamat' => 'nullable|string',
        'email' => 'nullable|email|max:255',
        'bank' => 'nullable|string|max:255',
        'no_rekening' => 'nullable|string|max:255',
        'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:1024',
    ]);
    // Set field satu per satu (hindari mass assignment)
    if ($request->filled('mitra_nama')) $user->mitra_name = $request->mitra_nama;
    if ($request->filled('telepon')) $user->telepon = $request->telepon;
    if ($request->filled('email')) $user->email = $request->email;
    if ($request->filled('bank')) $user->bank = $request->bank;
    if ($request->filled('no_rekening')) $user->no_rekening = $request->no_rekening;

    // Handle upload logo bila ada
    if ($request->hasFile('logo')) {
        $path = $request->file('logo')->store('logos', 'public');
        $user->logo_path = $path;
    }

    $user->save();
    if ($client) {
        $client->update([
            'alamat' => $request->alamat,
        ]);
    }
    return response()->json([
        'success' => true,
        'message' => 'Data identitas berhasil disimpan',
        'logo_url' => $user->logo_path ? route('client.logo') . '?v=' . time() : null,
        'mitra_nama' => $user->mitra_name,
    ]);
});

// Endpoint aman untuk menyajikan logo client (hindari masalah symlink 403)
Route::middleware('auth')->get('/client/logo', function () {
    $user = auth()->user();
    if (!$user || $user->role !== 'client') {
        abort(403);
    }
    if (!$user->logo_path || !Storage::disk('public')->exists($user->logo_path)) {
        abort(404);
    }
    $content = Storage::disk('public')->get($user->logo_path);
    $mime = Storage::disk('public')->mimeType($user->logo_path) ?: 'image/png';
    return response($content, 200)->header('Content-Type', $mime);
})->name('client.logo');

// Client sales detail endpoint (untuk modal detail)
Route::middleware('auth')->get('/client/sales/{id}', function (Request $request, $id) {
    $user = auth()->user();
    if (!$user || $user->role !== 'client') {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    
    try {
        // Get client ID from user's client_id
        $client = \App\Models\Client::where('client_id', $user->client_id)->first();
        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client tidak ditemukan'
            ], 404);
        }
        
        $clientSale = \App\Models\ClientSale::where('id', $id)
            ->where('client_id', $client->id)
            ->with('items') // Load relasi items
            ->first();
        
        if (!$clientSale) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $clientSale
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal mengambil detail transaksi: ' . $e->getMessage()
        ], 500);
    }
});

// Client sales update status endpoint
Route::middleware('auth')->put('/client/sales/{id}/status', function (Request $request, $id) {
    $user = auth()->user();
    if (!$user || $user->role !== 'client') {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    
    try {
        // Get client ID from user's client_id
        $client = \App\Models\Client::where('client_id', $user->client_id)->first();
        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client tidak ditemukan'
            ], 404);
        }
        
        $request->validate([
            'status' => 'required|in:pending,completed,cancelled'
        ]);
        
        $clientSale = \App\Models\ClientSale::where('id', $id)
            ->where('client_id', $client->id)
            ->first();
        
        if (!$clientSale) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
            ], 404);
        }
        
        $clientSale->update([
            'status' => $request->status
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Status transaksi berhasil diperbarui',
            'data' => $clientSale
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal memperbarui status: ' . $e->getMessage()
        ], 500);
    }
});

require __DIR__.'/auth.php';

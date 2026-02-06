@extends('layouts.client')

@section('content')
<style>
    [x-cloak] { display: none !important; }
</style>

<div class="bg-white rounded-xl shadow p-8" x-data="purchaseOrder()" x-init="init()">
    <h1 class="text-2xl font-bold text-[#28C328] mb-6">Purchase Order</h1>
    
    <!-- Action Buttons -->
    <div class="mb-6 flex justify-between items-center">
        <div class="flex gap-2">
            <button @click="showCreateModal = true" class="rounded-lg bg-[#28C328] px-4 py-2 text-white text-sm font-semibold flex items-center gap-2 hover:bg-[#22a322] transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Buat PO Baru
            </button>
            <button @click="showExternalModal = true" class="rounded-lg bg-blue-600 px-4 py-2 text-white text-sm font-semibold flex items-center gap-2 hover:bg-blue-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Tambah Item Luar
            </button>
            <button @click="exportPOListExcel" class="rounded-lg bg-[#28C328] px-4 py-2 text-white text-sm font-semibold flex items-center gap-2 hover:bg-[#22a322] transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 2h8v4H8z" /></svg>
                Excel
            </button>
            <button @click="exportPOListPDF" class="rounded-lg bg-[#28C328] px-4 py-2 text-white text-sm font-semibold flex items-center gap-2 hover:bg-[#22a322] transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20h9" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                PDF
            </button>
        </div>
        
        <!-- Filter & Search -->
        <div class="flex gap-2">
            <div class="relative">
                <input type="text" x-model="search" placeholder="Cari PO..." class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            
            <!-- Filter Tanggal -->
            <div class="flex gap-2 items-center">
                <select x-model="dateFilter" @change="applyDateFilter()" class="rounded-lg border border-gray-300 px-3 py-2 text-gray-700 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-[#28C328] focus:border-transparent">
                    <option value="">Semua Tanggal</option>
                    <option value="today">Hari Ini</option>
                    <option value="week">Minggu Ini</option>
                    <option value="month">Bulan Ini</option>
                    <option value="year">Tahun Ini</option>
                    <option value="custom">Custom Range</option>
                </select>
                
                <!-- Custom date range inputs -->
                <div x-show="dateFilter === 'custom'" class="flex gap-2 items-center">
                    <input type="date" x-model="customStartDate" @change="applyDateFilter()" class="rounded-lg border border-gray-300 px-3 py-2 text-gray-700 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-[#28C328] focus:border-transparent" placeholder="Tanggal Mulai">
                    <span class="text-gray-500">sampai</span>
                    <input type="date" x-model="customEndDate" @change="applyDateFilter()" class="rounded-lg border border-gray-300 px-3 py-2 text-gray-700 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-[#28C328] focus:border-transparent" placeholder="Tanggal Akhir">
                </div>
                
                <!-- Clear filter button -->
                <button x-show="dateFilter" @click="clearDateFilter()" class="px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition text-sm flex items-center gap-1" title="Hapus Filter Tanggal">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Clear
                </button>
            </div>
            
            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                <button @click="open = !open" class="px-4 py-2 border border-gray-300 rounded-lg text-sm flex items-center gap-2">
                    <span x-text="statusFilter === '' ? 'Semua Status' : statusFilter"></span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                    <div @click="statusFilter = ''; open = false" class="px-4 py-2 hover:bg-gray-100 cursor-pointer rounded-t-lg">Semua Status</div>
                    <div @click="statusFilter = 'pending'; open = false" class="px-4 py-2 hover:bg-gray-100 cursor-pointer">Pending</div>
                    <div @click="statusFilter = 'approved'; open = false" class="px-4 py-2 hover:bg-gray-100 cursor-pointer">Approved</div>
                    <div @click="statusFilter = 'received'; open = false" class="px-4 py-2 hover:bg-gray-100 cursor-pointer">Received</div>
                    <div @click="statusFilter = 'rejected'; open = false" class="px-4 py-2 hover:bg-gray-100 cursor-pointer rounded-b-lg">Rejected</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter status indicator -->
    <div x-show="dateFilter" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-center gap-2 text-sm text-blue-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z" />
            </svg>
            <span x-text="getFilterStatusText()"></span>
        </div>
    </div>

    <!-- Purchase Orders Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-[#28C328] text-white">
                    <th class="p-3 text-left rounded-tl-xl">No. PO</th>
                    <th class="p-3 text-left">Tanggal</th>
                    <th class="p-3 text-left">Items</th>
                    <th class="p-3 text-right">Total</th>
                    <th class="p-3 text-center">Status</th>
                    <th class="p-3 text-center">Payment</th>
                    <th class="p-3 text-center rounded-tr-xl">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                <template x-for="po in paginatedPOs" :key="po.id">
                    <tr class="hover:bg-gray-50">
                        <td class="p-3">
                            <div class="font-semibold text-gray-800" x-text="po.po_number"></div>
                        </td>
                        <td class="p-3 text-gray-600" x-text="formatDate(po.created_at)"></td>
                        <td class="p-3">
                            <div class="text-sm text-gray-800" x-text="po.items.length + ' items'"></div>
                            <div class="text-xs text-gray-500" x-text="po.items.map(i => i.item_name).join(', ')"></div>
                        </td>
                        <td class="p-3 text-right">
                            <div class="font-semibold text-gray-800">Rp<span x-text="formatNumber(po.total_amount)"></span></div>
                        </td>
                        <td class="p-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold" 
                                  :class="getStatusClass(po.status)" 
                                  x-text="getStatusText(po.status)"></span>
                        </td>
                        <td class="p-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold" 
                                  :class="po.payment_status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800'" 
                                  x-text="po.payment_status === 'paid' ? 'Lunas' : 'Belum Lunas'"></span>
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex gap-3 justify-center">
                                <button @click="viewPODetail(po)" class="text-[#28C328] hover:text-[#22a322] text-sm font-medium">
                                    Detail
                                </button>
                                <template x-if="po.status === 'pending' || po.status === 'cancelled' || po.status === 'rejected'">
                                    <button @click="deletePO(po.id)" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                        Delete
                                    </button>
                                </template>
                                <template x-if="po.status === 'approved' && po.payment_status === 'paid'">
                                    <button @click="markAsReceived(po.id)" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                        Received
                                    </button>
                                </template>
                            </div>
                        </td>
                    </tr>
                </template>
                <template x-if="paginatedPOs.length === 0">
                    <tr>
                        <td colspan="7" class="p-8 text-center text-gray-500">
                            Belum ada Purchase Order
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Pagination for Purchase Orders -->
    <div x-show="filteredPOs.length > 0" class="mt-4">
        <div class="flex items-center justify-between mb-4">
            <div class="text-sm text-gray-700">
                Menampilkan <span x-text="Math.min((currentPage - 1) * perPage + 1, filteredPOs.length)"></span> sampai 
                <span x-text="Math.min(currentPage * perPage, filteredPOs.length)"></span> 
                dari <span x-text="filteredPOs.length"></span> Purchase Order
            </div>
        </div>
        <div class="flex justify-center">
            <nav class="flex items-center space-x-2">
            <button @click="prevPage()" :disabled="currentPage === 1" class="w-8 h-8 flex items-center justify-center rounded-full border border-gray-300 text-gray-500 hover:bg-gray-100 disabled:opacity-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </button>
            
            <!-- First page -->
            <template x-if="totalPages > 0">
                <button @click="goToPage(1)" :class="{'bg-[#28C328] text-white': currentPage === 1, 'bg-white text-gray-700': currentPage !== 1 }" class="w-8 h-8 flex items-center justify-center rounded-full border border-gray-300 font-semibold">
                    <span>1</span>
                </button>
            </template>
            
            <!-- Ellipsis before current page -->
            <template x-if="currentPage > 3">
                <span class="w-8 h-8 flex items-center justify-center text-gray-500">...</span>
            </template>
            
            <!-- Pages around current page -->
            <template x-for="page in getVisiblePages()" :key="page">
                <template x-if="page !== 1 && page !== totalPages">
                    <button @click="goToPage(page)" :class="{'bg-[#28C328] text-white': currentPage === page, 'bg-white text-gray-700': currentPage !== page }" class="w-8 h-8 flex items-center justify-center rounded-full border border-gray-300 font-semibold">
                        <span x-text="page"></span>
                    </button>
                </template>
            </template>
            
            <!-- Ellipsis after current page -->
            <template x-if="currentPage < totalPages - 2">
                <span class="w-8 h-8 flex items-center justify-center text-gray-500">...</span>
            </template>
            
            <!-- Last page -->
            <template x-if="totalPages > 1">
                <button @click="goToPage(totalPages)" :class="{'bg-[#28C328] text-white': currentPage === totalPages, 'bg-white text-gray-700': currentPage !== totalPages }" class="w-8 h-8 flex items-center justify-center rounded-full border border-gray-300 font-semibold">
                    <span x-text="totalPages"></span>
                </button>
            </template>
            
            <button @click="nextPage()" :disabled="currentPage === totalPages" class="w-8 h-8 flex items-center justify-center rounded-full border border-gray-300 text-gray-500 hover:bg-gray-100 disabled:opacity-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
            </nav>
        </div>
    </div>

    <!-- External Items Section -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-[#28C328]">Item Luar (Pencatatan)</h2>
        </div>
        
        <!-- External Items Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-blue-600 text-white">
                        <th class="p-3 text-left rounded-tl-xl">Nama Item</th>
                        <th class="p-3 text-left">Tanggal</th>
                        <th class="p-3 text-left">SKU</th>
                        <th class="p-3 text-center">Quantity</th>
                        <th class="p-3 text-right">Harga Satuan</th>
                        <th class="p-3 text-right">Subtotal</th>
                        <th class="p-3 text-center rounded-tr-xl">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <template x-for="item in paginatedExternalItems" :key="item.id">
                        <tr class="hover:bg-gray-50">
                            <td class="p-3">
                                <div class="font-semibold text-gray-800" x-text="item.item_name"></div>
                                <div class="text-xs text-gray-500" x-text="item.description || ''"></div>
                            </td>
                            <td class="p-3 text-gray-600" x-text="formatDate(item.created_at)"></td>
                            <td class="p-3">
                                <span class="font-mono text-sm text-gray-600 bg-gray-100 px-2 py-1 rounded" x-text="item.sku || '-'"></span>
                            </td>
                            <td class="p-3 text-center">
                                <span class="font-semibold text-gray-800" x-text="item.quantity"></span>
                            </td>
                            <td class="p-3 text-right">
                                <span class="font-semibold text-gray-800">Rp<span x-text="formatNumber(item.unit_price)"></span></span>
                            </td>
                            <td class="p-3 text-right">
                                <span class="font-semibold text-gray-800">Rp<span x-text="formatNumber(item.subtotal)"></span></span>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex gap-2 justify-center">
                                    <button @click="editExternalItem(item)" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Edit
                                    </button>
                                    <button @click="deleteExternalItem(item.id)" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="paginatedExternalItems && paginatedExternalItems.length === 0">
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-500">
                                Belum ada item luar
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination for External Items -->
        <div x-show="filteredExternalItems.length > 0" class="mt-4">
            <div class="flex items-center justify-between mb-4">
                <div class="text-sm text-gray-700">
                    Menampilkan <span x-text="Math.min((externalItems.current_page - 1) * 5 + 1, filteredExternalItems.length)"></span> sampai 
                    <span x-text="Math.min(externalItems.current_page * 5, filteredExternalItems.length)"></span> 
                    dari <span x-text="filteredExternalItems.length"></span> item luar
                </div>
            </div>
            <div class="flex justify-center">
                <nav class="flex items-center space-x-2">
                <button @click="prevExternalPage()" 
                        :disabled="externalItems.current_page === 1" 
                        class="w-8 h-8 flex items-center justify-center rounded-full border border-gray-300 text-gray-500 hover:bg-gray-100 disabled:opacity-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </button>
                <template x-for="page in Math.ceil(filteredExternalItems.length / 5)" :key="page">
                    <button @click="goToExternalPage(page)" 
                            :class="{'bg-blue-600 text-white': externalItems.current_page === page, 'bg-white text-gray-700': externalItems.current_page !== page }" 
                            class="w-8 h-8 flex items-center justify-center rounded-full border border-gray-300 font-semibold">
                        <span x-text="page"></span>
                    </button>
                </template>
                <button @click="nextExternalPage()" 
                        :disabled="externalItems.current_page >= Math.ceil(filteredExternalItems.length / 5)" 
                        class="w-8 h-8 flex items-center justify-center rounded-full border border-gray-300 text-gray-500 hover:bg-gray-100 disabled:opacity-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </button>
                </nav>
            </div>
        </div>
    </div>

    <!-- Create PO Modal -->
    <div x-show="showCreateModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 p-4" x-cloak>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-6xl mx-4 relative max-h-[90vh] overflow-hidden">
            <!-- Header -->
            <div class="bg-[#28C328] text-white px-6 py-4 flex items-center justify-between">
                <div class="font-semibold text-lg">Buat Purchase Order Baru</div>
                <button @click="showCreateModal = false; resetForm()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Left: Item Selection -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Pilih Items</h3>
                        
                        <!-- Search & Filter -->
                        <div class="mb-4 space-y-3">
                            <div class="relative">
                                <input type="text" x-model="itemSearch" placeholder="Cari item..." class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>
                            <div class="flex gap-2 overflow-x-auto">
                                <button @click="selectedCategory = ''" :class="selectedCategory === '' ? 'bg-[#28C328] text-white' : 'bg-gray-100 text-gray-600'" class="px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap">Semua</button>
                                <template x-for="cat in categories" :key="cat">
                                    <button @click="selectedCategory = cat" :class="selectedCategory === cat ? 'bg-[#28C328] text-white' : 'bg-gray-100 text-gray-600'" class="px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap" x-text="cat"></button>
                                </template>
                            </div>
                        </div>

                        <!-- Available Items -->
                        <div class="space-y-2 max-h-96 overflow-y-auto">
                            <template x-for="item in filteredAvailableItems" :key="item.id">
                                <div class="border rounded-lg p-3 transition-colors" :class="item.tersedia === 0 ? 'border-orange-300 bg-orange-50 hover:border-orange-400' : 'border-gray-200 hover:border-[#28C328]'">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <div class="font-semibold text-gray-800" x-text="item.nama"></div>
                                            <div class="text-xs text-gray-500 font-mono" x-text="item.sku"></div>
                                            <div class="text-xs text-gray-600" x-text="item.lokasi"></div>
                                        </div>
                                        <div class="text-right mr-3">
                                            <div class="text-sm font-semibold text-[#28C328]">Rp<span x-text="formatNumber(item.harga)"></span></div>
                                            <div class="text-xs" :class="item.tersedia === 0 ? 'text-orange-600 font-semibold' : 'text-gray-500'">
                                                Stock: <span x-text="item.tersedia"></span>
                                                <span x-show="item.tersedia === 0" class="ml-1 text-orange-600">(Habis)</span>
                                            </div>
                                        </div>
                                        <button @click="addItemToPO(item)" class="px-3 py-1 bg-[#28C328] text-white text-xs rounded-lg hover:bg-[#22a322] transition">
                                            Tambah
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Right: PO Details -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Detail Purchase Order</h3>
                        
                        <!-- PO Info -->
                        <div class="mb-4 space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No. PO</label>
                                <input type="text" x-model="poNumber" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal PO</label>
                                <input type="text" x-model="poDate" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Pembayaran</label>
                                <select x-model="poType" @change="handlePaymentChange($event.target.value)" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-[#28C328] focus:border-[#28C328] outline-none transition-all">
                                    <option value="" disabled>Pilih Tipe Pembayaran</option>
                                    <option value="Tunai">Tunai</option>
                                    <option value="Transfer">Transfer</option>
                                </select>
                            </div>
                        </div>

                        <!-- Selected Items -->
                        <div class="mb-4">
                            <h4 class="font-medium text-gray-700 mb-2">Items yang Dipilih</h4>
                            <div class="space-y-2 max-h-48 overflow-y-auto">
                                <template x-for="(item, index) in selectedItems" :key="index">
                                    <div class="border rounded-lg p-3" :class="item.item_type === 'external' ? 'border-blue-200 bg-blue-50' : 'border-gray-200 bg-gray-50'">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2">
                                                    <div class="font-semibold text-gray-800" x-text="item.nama"></div>
                                                    <span class="px-2 py-1 rounded-full text-xs font-semibold" 
                                                          :class="item.item_type === 'external' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                                                          x-text="item.item_type === 'external' ? 'Luar' : 'Stock'"></span>
                                                </div>
                                                <div class="text-xs text-gray-500" x-text="item.sku"></div>
                                            </div>
                                            <div class="flex items-center gap-2 mr-3">
                                                <input type="number" x-model="item.quantity" min="1" class="w-16 px-2 py-1 border border-gray-300 rounded text-sm" @input="updateTotal()">
                                                <span class="text-sm text-gray-600">x</span>
                                                <span class="text-sm font-semibold" :class="item.item_type === 'external' ? 'text-blue-600' : 'text-[#28C328]'">Rp<span x-text="formatNumber(item.harga)"></span></span>
                                            </div>
                                            <button @click="removeItemFromPO(index)" class="text-red-600 hover:text-red-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="selectedItems.length === 0">
                                    <div class="text-center text-gray-500 py-4 text-sm">Belum ada item dipilih</div>
                                </template>
                            </div>
                        </div>

                        <!-- Total & Submit -->
                        <div class="border-t pt-4">
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">Total Quantity:</span>
                                    <span class="text-sm font-semibold text-gray-800" x-text="selectedItems.reduce((sum, item) => sum + (Number(item.quantity) || 0), 0)"></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-semibold text-gray-800">Total:</span>
                                    <span class="text-2xl font-bold text-[#28C328]">Rp<span x-text="formatNumber(totalAmount)"></span></span>
                                </div>
                            </div>
                            <button @click="submitPO()" :disabled="selectedItems.length === 0" class="w-full bg-[#28C328] text-white py-3 rounded-lg font-semibold hover:bg-[#22a322] transition disabled:opacity-50 disabled:cursor-not-allowed">
                                Buat Purchase Order
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PO Detail Modal -->
    <div x-show="showDetailModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" x-cloak>
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-5xl mx-4 relative max-h-[95vh] overflow-hidden border border-gray-100">
            <!-- Header -->
            <div class="bg-gradient-to-r from-[#28C328] to-[#22a322] text-white px-8 py-6 flex items-center justify-between rounded-t-3xl">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-bold text-xl">Detail Purchase Order</div>
                        <div class="text-white/80 text-sm" x-text="selectedPO ? selectedPO.po_number : ''"></div>
                    </div>
                </div>
                <button @click="showDetailModal = false" class="w-10 h-10 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition-all duration-200 hover:scale-105">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-8 overflow-y-auto max-h-[calc(95vh-140px)]">
                <template x-if="selectedPO">
                    <!-- PO Header -->
                    <div class="mb-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs text-blue-600 font-medium uppercase tracking-wide">No. PO</div>
                                        <div class="font-bold text-lg text-blue-800" x-text="selectedPO.po_number"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-4 border border-yellow-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-yellow-500 rounded-lg flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs text-yellow-600 font-medium uppercase tracking-wide">Status</div>
                                        <span class="px-3 py-1 rounded-full text-sm font-semibold" 
                                              :class="getStatusClass(selectedPO.status)" 
                                              x-text="getStatusText(selectedPO.status)"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs text-green-600 font-medium uppercase tracking-wide">Tanggal Dibuat</div>
                                        <div class="font-bold text-lg text-green-800" x-text="formatDate(selectedPO.created_at)"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs text-purple-600 font-medium uppercase tracking-wide">Payment Status</div>
                                        <span class="px-3 py-1 rounded-full text-sm font-semibold" 
                                              :class="selectedPO.payment_status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800'" 
                                              x-text="selectedPO.payment_status === 'paid' ? 'Lunas' : 'Belum Lunas'"></span>
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200 cursor-pointer hover:from-purple-100 hover:to-purple-200 transition-all duration-200" @click="showInvoiceModal = true">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs text-purple-600 font-medium uppercase tracking-wide">Lihat Invoice</div>
                                        <div class="text-xs text-purple-500">Klik untuk melihat</div>
                                    </div>
                                </div>
                            </div> -->
                        </div>
                        
                        <!-- Ringkasan Pembelian -->
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-2xl p-6 mb-6 border border-gray-200">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-8 h-8 bg-[#28C328] rounded-lg flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18M3 7h18M5 11h14M5 15h10M7 19h6" />
                                    </svg>
                                </div>
                                <h4 class="font-bold text-lg text-gray-800">Ringkasan Pembelian</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <template x-for="item in (selectedPO.items || [])" :key="item.id">
                                    <div class="bg-white rounded-xl p-4 border border-gray-200">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <div class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Item</div>
                                                <div class="font-bold text-gray-800" x-text="item.item_name"></div>
                                                <div class="text-xs text-gray-400" x-text="item.sku || '-' "></div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Jumlah</div>
                                                <div class="font-bold text-lg text-gray-800" x-text="item.quantity + ' pcs'"></div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                
                                <template x-if="!selectedPO.items || selectedPO.items.length === 0">
                                    <div class="col-span-3 bg-white rounded-xl p-6 border border-gray-200 text-center text-gray-500">
                                        Tidak ada item dalam Purchase Order ini
                                    </div>
                                </template>
                            </div>
                            <div class="mt-4 flex flex-wrap items-center gap-4 justify-between">
                                <div class="text-sm text-gray-600">
                                    Total Item: <span class="font-semibold" x-text="selectedPO.items ? selectedPO.items.length : 0"></span>
                                    • Total Qty: <span class="font-semibold" x-text="selectedPO.items ? selectedPO.items.reduce((s,i)=>s + (Number(i.quantity)||0), 0) : 0"></span>
                                </div>
                                <div class="text-right font-bold text-lg text-[#28C328]">
                                    Total: Rp<span x-text="formatNumber(selectedPO.total_amount)"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Ringkasan Items (compact badges) -->
                        <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-8 h-8 bg-[#28C328] rounded-lg flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                                <h4 class="font-bold text-lg text-gray-800">Ringkasan Items</h4>
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700" x-text="(selectedPO.items && selectedPO.items.length) ? (selectedPO.items.length + ' items') : '0 items'"></span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <template x-if="!selectedPO.items || selectedPO.items.length === 0">
                                    <span class="text-sm text-gray-500">Tidak ada items</span>
                                </template>
                                <template x-for="item in (selectedPO.items || [])" :key="item.id">
                                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full border text-sm"
                                          :class="item.item_type === 'external' ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-green-50 border-green-200 text-green-700'">
                                        <span class="font-medium" x-text="item.item_name"></span>
                                        <span class="text-gray-400">•</span>
                                        <span class="font-semibold" x-text="item.quantity + 'x'"></span>
                                    </span>
                                </template>
                            </div>
                        </div>

                    <!-- Items Table -->
                    <div class="mb-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 bg-[#28C328] rounded-lg flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-xl text-gray-800">Items Purchase Order</h3>
                        </div>
                        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                        <tr>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Item</th>
                                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">SKU</th>
                                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Quantity</th>
                                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Harga Satuan</th>
                                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <template x-for="item in selectedPO.items" :key="item.id">
                                            <tr class="hover:bg-gray-50 transition-colors">
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center" 
                                                             :class="item.item_type === 'external' ? 'bg-blue-100' : 'bg-green-100'">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" 
                                                                 :class="item.item_type === 'external' ? 'text-blue-600' : 'text-green-600'" 
                                                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <div class="font-bold text-gray-800" x-text="item.item_name"></div>
                                                            <span class="px-2 py-1 rounded-full text-xs font-semibold" 
                                                                  :class="item.item_type === 'external' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                                                                  x-text="item.item_type === 'external' ? 'Luar' : 'Stock'"></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <span class="font-mono text-sm text-gray-600 bg-gray-100 px-2 py-1 rounded" x-text="item.sku"></span>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <span class="font-bold text-lg text-gray-800" x-text="item.quantity"></span>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <span class="font-semibold text-gray-800">Rp<span x-text="formatNumber(item.unit_price)"></span></span>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <span class="font-bold text-lg text-[#28C328]">Rp<span x-text="formatNumber(item.subtotal)"></span></span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot class="bg-gradient-to-r from-[#28C328] to-[#22a322]">
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-right font-bold text-white text-lg">Total:</td>
                                            <td class="px-6 py-4 text-right font-bold text-white text-2xl">Rp<span x-text="formatNumber(selectedPO.total_amount)"></span></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Summary Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                            <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs text-blue-600 font-medium uppercase tracking-wide">Total Items</div>
                                        <div class="font-bold text-lg text-blue-800" x-text="selectedPO.items ? selectedPO.items.length : 0"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs text-green-600 font-medium uppercase tracking-wide">Total Quantity</div>
                                        <div class="font-bold text-lg text-green-800" x-text="selectedPO.items ? selectedPO.items.reduce((sum, item) => sum + item.quantity, 0) : 0"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs text-purple-600 font-medium uppercase tracking-wide">Total Harga</div>
                                        <div class="font-bold text-lg text-purple-800">Rp<span x-text="formatNumber(selectedPO.total_amount)"></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                        <div class="flex gap-3">
                            <template x-if="selectedPO.status === 'pending'">
                                <button @click="cancelPO(selectedPO.id)" class="px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 font-semibold flex items-center gap-2 shadow-lg hover:shadow-xl transform hover:scale-105">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Batalkan PO
                                </button>
                            </template>
                            <button @click="showDetailModal = false" class="px-6 py-3 bg-gradient-to-r from-gray-200 to-gray-300 text-gray-700 rounded-xl hover:from-gray-300 hover:to-gray-400 transition-all duration-200 font-semibold flex items-center gap-2 shadow-lg hover:shadow-xl transform hover:scale-105">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Tutup
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Invoice Modal -->
    <!-- <div x-show="showInvoiceModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" x-cloak>
        <div class="bg-white rounded-2xl shadow-xl p-0 w-full max-w-2xl mx-4 relative overflow-y-auto max-h-screen">
            <button @click="showInvoiceModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold z-10">&times;</button>
            <div class="p-8"> -->
                <!-- Header Logo & Company -->
                <!-- <div class="flex items-center gap-4 border-b pb-4 mb-6">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center overflow-hidden bg-gradient-to-br from-[#28C328] to-yellow-500">
                        <template x-if="company.logoUrl">
                            <img :src="company.logoUrl" alt="Logo" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!company.logoUrl">
                            <span class="text-white font-bold text-lg" x-text="(company.name || 'GAFI').substring(0,1)"></span>
                        </template>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-700" x-text="company.name || 'GAFI Client'" ></h2>
                        <div class="text-xs text-gray-400" x-text="company.address || 'Alamat Perusahaan' "></div>
                        <div class="text-xs text-gray-400">
                            <span x-text="company.phone ? ('Telp: ' + company.phone) : 'Telp: -'"></span>
                            <span x-show="company.phone && company.email"> | </span>
                            <span x-text="company.email ? ('Email: ' + company.email) : 'Email: -'"></span>
                        </div>
                    </div>
                    <div class="ml-auto text-right">
                        <div class="text-lg font-bold text-gray-700">PURCHASE ORDER</div>
                        <div class="text-xs text-gray-500">No. PO: <span x-text="selectedPO ? selectedPO.po_number : ''"></span></div>
                        <div class="text-xs text-gray-500">Tanggal: <span x-text="selectedPO ? formatDate(selectedPO.created_at) : ''"></span></div>
                        <div class="text-xs text-gray-500">Status: <span class="font-semibold text-[#28C328]" x-text="selectedPO ? getStatusText(selectedPO.status) : ''"></span></div>
                        <div class="text-xs text-gray-500">Payment: <span class="font-semibold" :class="selectedPO && selectedPO.payment_status === 'paid' ? 'text-green-600' : 'text-orange-600'" x-text="selectedPO ? (selectedPO.payment_status === 'paid' ? 'Lunas' : 'Belum Lunas') : ''"></span></div>
                    </div>
                </div> -->

                <!-- Info Client -->
                <!-- <div class="mb-6">
                    <div class="font-semibold text-gray-700">Kepada:</div>
                    <div class="font-bold text-[#28C328] text-lg" x-text="selectedPO ? selectedPO.client_name : ''"></div>
                    <div class="text-xs text-gray-500" x-text="selectedPO ? selectedPO.client_email : ''"></div>
                    <div class="text-xs text-gray-500" x-text="selectedPO ? (selectedPO.client_phone || '-') : ''"></div>
                </div> -->
                
                <!-- Tabel Items -->
                <!-- <div class="overflow-x-auto mb-6">
                    <table class="min-w-full border text-sm">
                        <thead>
                            <tr class="bg-[#BAFFBA] text-gray-700">
                                <th class="py-2 px-4 border-b text-left">Nama Item</th>
                                <th class="py-2 px-4 border-b text-left">SKU</th>
                                <th class="py-2 px-4 border-b text-center">Quantity</th>
                                <th class="py-2 px-4 border-b text-right">Harga Satuan</th>
                                <th class="py-2 px-4 border-b text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="item in (selectedPO ? selectedPO.items : [])" :key="item.id">
                                <tr>
                                    <td class="py-2 px-4 border-b">
                                        <div x-text="item.item_name"></div>
                                        <div class="text-xs text-gray-500" x-text="item.item_type === 'external' ? 'Item Luar' : 'Item Stock'"></div>
                                    </td>
                                    <td class="py-2 px-4 border-b text-xs font-mono" x-text="item.sku || '-'"></td>
                                    <td class="py-2 px-4 border-b text-center font-semibold" x-text="item.quantity + ' pcs'"></td>
                                    <td class="py-2 px-4 border-b text-right">Rp<span x-text="formatNumber(item.unit_price)"></span></td>
                                    <td class="py-2 px-4 border-b text-right font-bold">Rp<span x-text="formatNumber(item.subtotal)"></span></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div> -->
                
                <!-- Summary & Footer -->
                <!-- <div class="flex flex-col md:flex-row md:justify-between items-start md:items-center mb-2 gap-4">
                    <div class="space-y-2">
                        <div class="text-xs text-gray-500">* Purchase Order ini sah tanpa tanda tangan dan dicetak otomatis oleh sistem GAFI.</div>
                        <div class="text-sm text-gray-700">
                            <div class="font-semibold">Pembayaran ke:</div>
                            <div x-text="company.bank ? (company.bank + ' - ' + company.no_rekening) : 'BCA - 64835868'"></div>
                        </div>
                    </div>
                    <div class="text-right space-y-1">
                        <div class="text-lg font-semibold text-[#28C328]">Total PO: <span class="text-2xl font-bold">Rp<span x-text="selectedPO ? formatNumber(selectedPO.total_amount) : '0'"></span></span></div>
                    </div>
                </div>
                
                <button class="rounded-lg bg-[#28C328] text-white font-semibold px-6 py-2 text-sm mt-4 w-full md:w-auto" @click="exportPOInvoicePDF">Export PDF</button>
            </div>
        </div>
    </div> -->

    <!-- External Item Modal -->
    <div x-show="showExternalModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 p-4" x-cloak>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-4 relative max-h-[90vh] overflow-hidden">
            <!-- Header -->
            <div class="bg-blue-600 text-white px-6 py-4 flex items-center justify-between">
                <div class="font-semibold text-lg" x-text="editingExternalItem ? 'Edit Item Luar' : 'Tambah Item Luar'"></div>
                <button @click="showExternalModal = false; resetExternalForm()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
                <div class="space-y-4">
                    <!-- Item Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Item</label>
                        <input type="text" x-model="externalItem.item_name" placeholder="Masukkan nama item" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- SKU -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SKU (Optional)</label>
                        <input type="text" x-model="externalItem.sku" placeholder="Masukkan SKU" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Quantity -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                        <input type="number" x-model="externalItem.quantity" min="1" placeholder="Masukkan quantity" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Unit Price -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga Satuan</label>
                        <input type="number" x-model="externalItem.unit_price" min="0" step="0.01" placeholder="Masukkan harga satuan" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi (Optional)</label>
                        <textarea x-model="externalItem.description" placeholder="Masukkan deskripsi item" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>

                    <!-- Subtotal Display -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Subtotal:</span>
                            <span class="text-lg font-bold text-blue-600">Rp<span x-text="formatNumber(externalItem.quantity * externalItem.unit_price)"></span></span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-3 pt-4">
                        <button @click="addExternalItem()" :disabled="!externalItem.item_name || !externalItem.quantity || !externalItem.unit_price" class="flex-1 bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-text="editingExternalItem ? 'Update Item' : 'Simpan Item'"></span>
                        </button>
                        <button @click="showExternalModal = false; resetExternalForm()" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div x-show="confirm.show" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" x-cloak>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 overflow-hidden">
            <div class="px-6 py-4 border-b bg-gradient-to-r from-[#28C328] to-[#22a322] text-white">
                <div class="font-semibold" x-text="confirm.title"></div>
            </div>
            <div class="p-6 text-gray-700" x-text="confirm.message"></div>
            <div class="px-6 py-4 border-t flex justify-end gap-3 bg-gray-50">
                <button @click="closeConfirm()" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300">Batal</button>
                <button @click="confirm.onConfirm && confirm.onConfirm()" :disabled="confirm.loading" class="px-4 py-2 rounded-lg bg-[#28C328] text-white hover:bg-[#22a322] disabled:opacity-50">
                    <span x-show="!confirm.loading">Konfirmasi</span>
                    <span x-show="confirm.loading">Memproses...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function purchaseOrder() {
    return {
        // Data
        search: '',
        statusFilter: '',
        dateFilter: '',
        customStartDate: '',
        customEndDate: '',
        showCreateModal: false,
        showDetailModal: false,
        showExternalModal: false,
        selectedPO: null,
        confirm: { show: false, title: '', message: '', onConfirm: null, loading: false },
        // Company identity (loaded from /client/identity)
        company: { name: '', phone: '', email: '', address: '', logoUrl: '', bank: '', no_rekening: '' },
        
        // Available items from admin stock
        availableItems: [],
        selectedItems: [],
        itemSearch: '',
        selectedCategory: '',
        
        // Form data
        poNumber: '',
        poDate: '',
        poType: '',
        totalAmount: 0,
        
        // External item form
        externalItem: {
            item_name: '',
            sku: '',
            quantity: 1,
            unit_price: 0,
            description: ''
        },
        
        // Purchase orders
        purchaseOrders: [],
        currentPage: 1,
        perPage: 10,
        
        // External items
        externalItems: { data: [], current_page: 1, last_page: 1, from: 0, to: 0, total: 0, prev_page_url: null, next_page_url: null },
        allExternalItems: [], // Store all external items for filtering
        editingExternalItem: null,
        
        async init() {
            await this.loadAvailableItems();
            await this.loadPurchaseOrders();
            await this.loadExternalItems();
            await this.loadCompanyIdentity();
            this.generatePONumber();
            this.setPODate();
            
            // Watch for search and status filter changes to reset pagination
            this.$watch('search', () => {
                this.currentPage = 1;
            });
            
            this.$watch('statusFilter', () => {
                this.currentPage = 1;
            });
        },
        
        async loadAvailableItems() {
            try {
                const response = await fetch('/client/admin-stock-items', {
                    headers: { 'Accept': 'application/json' }
                });
                if (response.ok) {
                    this.availableItems = await response.json();
                } else {
                    console.error('Failed to load items:', response.status);
                }
            } catch (error) {
                console.error('Error loading items:', error);
            }
        },
        
        async loadPurchaseOrders() {
            try {
                const response = await fetch('/client/purchase-orders', {
                    headers: { 'Accept': 'application/json' }
                });
                if (response.ok) {
                    this.purchaseOrders = await response.json();
                }
            } catch (error) {
                console.error('Error loading POs:', error);
            }
        },
        
        async loadExternalItems(page = 1) {
            try {
                const response = await fetch(`/client/external-items?page=${page}&per_page=100`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (response.ok) {
                    const data = await response.json();
                    this.allExternalItems = data.data || [];
                    this.externalItems = data;
                }
            } catch (error) {
                console.error('Error loading external items:', error);
            }
        },

        async loadCompanyIdentity() {
            try {
                const res = await fetch('/client/identity', { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const data = await res.json();
                this.company = {
                    name: data.mitra_nama || data.nama || 'GAFI Client',
                    phone: data.telepon || '',
                    email: data.email || '',
                    address: data.alamat || '',
                    logoUrl: data.logo_url || '',
                    bank: data.bank || '',
                    no_rekening: data.no_rekening || ''
                };
            } catch (e) {
                // ignore
            }
        },
        
        generatePONumber() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            this.poNumber = `PO-${year}${month}${day}-${random}`;
        },

        handlePaymentChange(val){
            this.poType = val;
        },
        
        setPODate() {
            this.poDate = new Date().toLocaleDateString('id-ID');
        },
        
        get filteredAvailableItems() {
            let items = this.availableItems;
            
            if (this.itemSearch) {
                items = items.filter(item => 
                    item.nama.toLowerCase().includes(this.itemSearch.toLowerCase()) ||
                    item.sku.toLowerCase().includes(this.itemSearch.toLowerCase())
                );
            }
            
            if (this.selectedCategory) {
                items = items.filter(item => item.kategori === this.selectedCategory);
            }
            
            return items;
        },
        
        get categories() {
            return [...new Set(this.availableItems.map(item => item.kategori).filter(Boolean))];
        },
        
        get filteredPOs() {
            let pos = this.purchaseOrders;
            
            if (this.search) {
                pos = pos.filter(po => 
                    po.po_number.toLowerCase().includes(this.search.toLowerCase()) ||
                    po.items.some(item => item.item_name.toLowerCase().includes(this.search.toLowerCase()))
                );
            }
            
            if (this.statusFilter) {
                if (this.statusFilter === 'rejected') {
                    pos = pos.filter(po => po.status === 'rejected' || po.status === 'cancelled');
                } else {
                    pos = pos.filter(po => po.status === this.statusFilter);
                }
            }
            
            if (this.dateFilter) {
                pos = this.applyDateFilterToPOs(pos);
            }
            
            return pos;
        },
        
        get paginatedPOs() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredPOs.slice(start, start + this.perPage);
        },
        
        get totalPages() {
            return Math.max(1, Math.ceil(this.filteredPOs.length / this.perPage));
        },
        getVisiblePages() {
            const total = this.totalPages;
            const current = this.currentPage;
            const pages = [];
            
            if (total <= 7) {
                // Show all pages if 7 or fewer
                for (let i = 1; i <= total; i++) {
                    pages.push(i);
                }
            } else {
                // Always show first and last page
                pages.push(1);
                pages.push(total);
                
                // Show pages around current page
                const start = Math.max(2, current - 1);
                const end = Math.min(total - 1, current + 1);
                
                for (let i = start; i <= end; i++) {
                    if (!pages.includes(i)) {
                        pages.push(i);
                    }
                }
            }
            
            return pages.sort((a, b) => a - b);
        },
        
        get filteredExternalItems() {
            let items = this.allExternalItems;
            
            if (this.dateFilter) {
                items = this.applyDateFilterToExternalItems(items);
            }
            
            return items;
        },
        
        get paginatedExternalItems() {
            const start = (this.externalItems.current_page - 1) * 5;
            return this.filteredExternalItems.slice(start, start + 5);
        },
        
        // Pagination functions for Purchase Orders
        prevPage() { 
            if (this.currentPage > 1) this.currentPage--; 
        },
        nextPage() { 
            if (this.currentPage < this.totalPages) this.currentPage++; 
        },
        goToPage(page) { 
            this.currentPage = page; 
        },
        
        // Pagination functions for External Items
        prevExternalPage() { 
            if (this.externalItems.current_page > 1) this.externalItems.current_page--; 
        },
        nextExternalPage() { 
            if (this.externalItems.current_page < Math.ceil(this.filteredExternalItems.length / 5)) this.externalItems.current_page++; 
        },
        goToExternalPage(page) { 
            this.externalItems.current_page = page; 
        },
        
        addItemToPO(item) {
            if (item.tersedia <= 0) {
                alert('Barang Sudah Habis');
                return;
            }

            const existingItem = this.selectedItems.find(i => i.id === item.id);

            if (existingItem) {
                let currentQty = Number(existingItem.quantity);
                if (currentQty < item.tersedia) {
                    existingItem.quantity = currentQty + 1;
                } else {
                    alert("Stock Tidak Mencukupi");
                }
            } else {
                this.selectedItems.push({
                    id: item.id,
                    nama: item.nama,
                    sku: item.sku,
                    harga: item.harga,
                    quantity: 1, // Default angka
                    item_type: 'stock'
                });
            }
            this.updateTotal();
        },
        
        removeItemFromPO(index) {
            this.selectedItems.splice(index, 1);
            this.updateTotal();
        },
        
        updateTotal() {
            this.totalAmount = this.selectedItems.reduce((total, item) => {
                return total + (item.harga * item.quantity);
            }, 0);
        },
        
        async submitPO() {
            if(this.poType == ""){
                alert("Harap Pilih Metode Pembayaran");
                return;
            }
            
            if (this.selectedItems.length === 0) return;

            this.selectedItems.forEach(element => {
                let data = this.availableItems;
                data.forEach(data1 => {
                    const existingItem = this.selectedItems.find(i => i.id === data1.id);
                    if(existingItem && Number(existingItem.quantity) > data1.tersedia){
                        alert("Stock Yang Dipesan Melebihi Stock Yang Ada")
                        return;
                    }
                });
            });

            try {
                const poData = {
                    po_number: this.poNumber,
                    items: this.selectedItems.map(item => ({
                        stock_item_id: item.item_type === 'stock' ? item.id : null,
                        item_name: item.nama,
                        sku: item.sku || null,
                        quantity: parseInt(item.quantity),
                        unit_price: parseFloat(item.harga),
                        subtotal: parseFloat(item.harga * item.quantity),
                        item_type: item.item_type || 'stock'
                    })),
                    total_amount: parseFloat(this.totalAmount),
                    status: 'pending',
                    payment_status: 'unpaid',
                    payment_method: this.poType
                };
                
                const response = await fetch('/client/purchase-orders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                    },
                    body: JSON.stringify(poData)
                });
                
                if (response.ok) {
                    await this.loadPurchaseOrders();
                    this.showCreateModal = false;
                    this.resetForm();
                    // Show success notification
                    this.showSuccessNotification('Purchase Order berhasil dibuat!');
                } else {
                    throw new Error('Failed to create PO');
                }
            } catch (error) {
                console.error('Error creating PO:', error);
                // Show error notification
                this.showErrorNotification('Gagal membuat Purchase Order');
            }
        },
        
        resetForm() {
            this.selectedItems = [];
            this.totalAmount = 0;
            this.generatePONumber();
            this.setPODate();
        },
        
        resetExternalForm() {
            this.externalItem = {
                item_name: '',
                sku: '',
                quantity: 1,
                unit_price: 0,
                description: ''
            };
            this.editingExternalItem = null;
        },
        
        async addExternalItem() {
            if (!this.externalItem.item_name || !this.externalItem.quantity || !this.externalItem.unit_price) {
                this.showErrorNotification('Mohon lengkapi semua field yang diperlukan');
                return;
            }
            
            try {
                const externalItemData = {
                    item_name: this.externalItem.item_name,
                    sku: this.externalItem.sku || null,
                    quantity: parseInt(this.externalItem.quantity),
                    unit_price: parseFloat(this.externalItem.unit_price),
                    description: this.externalItem.description || null
                };
                
                const url = this.editingExternalItem ? 
                    `/client/external-items/${this.editingExternalItem.id}` : 
                    '/client/external-items';
                const method = this.editingExternalItem ? 'PUT' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                    },
                    body: JSON.stringify(externalItemData)
                });
                
                if (response.ok) {
                    await this.loadExternalItems();
                    this.showExternalModal = false;
                    this.resetExternalForm();
                    const message = this.editingExternalItem ? 
                        'Item luar berhasil diperbarui!' : 
                        'Item luar berhasil disimpan!';
                    this.showSuccessNotification(message);
                } else {
                    throw new Error('Failed to save external item');
                }
            } catch (error) {
                console.error('Error saving external item:', error);
                this.showErrorNotification('Gagal menyimpan item luar');
            }
        },
        
        editExternalItem(item) {
            this.editingExternalItem = item;
            this.externalItem = {
                item_name: item.item_name,
                sku: item.sku || '',
                quantity: item.quantity,
                unit_price: item.unit_price,
                description: item.description || ''
            };
            this.showExternalModal = true;
        },
        
        async deleteExternalItem(itemId) {
            this.showConfirm({
                title: 'Konfirmasi Hapus',
                message: 'Apakah Anda yakin ingin menghapus item luar ini?',
                onConfirm: async () => {
                    this.confirm.loading = true;
                    try {
                        const response = await fetch(`/client/external-items/${itemId}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                            }
                        });
                        
                        if (response.ok) {
                            await this.loadExternalItems();
                            this.showSuccessNotification('Item luar berhasil dihapus!');
                            this.closeConfirm();
                        } else {
                            this.showErrorNotification('Gagal menghapus item luar');
                            this.confirm.loading = false;
                        }
                    } catch (error) {
                        console.error('Error deleting external item:', error);
                        this.showErrorNotification('Gagal menghapus item luar');
                        this.confirm.loading = false;
                    }
                }
            });
        },
        
        viewPODetail(po) {
            this.selectedPO = po;
            this.showDetailModal = true;
        },
        
        async cancelPO(poId) {
            this.showConfirm({
                title: 'Konfirmasi Pembatalan',
                message: 'Apakah Anda yakin ingin membatalkan PO ini?',
                onConfirm: async () => {
                    this.confirm.loading = true;
                    try {
                        const response = await fetch(`/client/purchase-orders/${poId}/cancel`, {
                            method: 'PUT',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                            }
                        });
                        
                        if (response.ok) {
                            await this.loadPurchaseOrders();
                            this.showDetailModal = false;
                            this.showSuccessNotification('PO berhasil dibatalkan!');
                            this.closeConfirm();
                        } else {
                            this.showErrorNotification('Gagal membatalkan PO');
                            this.confirm.loading = false;
                        }
                    } catch (error) {
                        console.error('Error cancelling PO:', error);
                        this.showErrorNotification('Gagal membatalkan PO');
                        this.confirm.loading = false;
                    }
                }
            });
        },

        async deletePO(poId) {
            this.showConfirm({
                title: 'Hapus Purchase Order',
                message: 'Yakin ingin menghapus PO ini? Tindakan tidak dapat dibatalkan.',
                onConfirm: async () => {
                    this.confirm.loading = true;
                    try {
                        const response = await fetch(`/client/purchase-orders/${poId}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                            }
                        });
                        if (response.ok) {
                            await this.loadPurchaseOrders();
                            this.showSuccessNotification('PO berhasil dihapus');
                            this.closeConfirm();
                        } else {
                            this.showErrorNotification('Gagal menghapus PO');
                            this.confirm.loading = false;
                        }
                    } catch (e) {
                        console.error('Error deleting PO:', e);
                        this.showErrorNotification('Gagal menghapus PO');
                        this.confirm.loading = false;
                    }
                }
            });
        },

        async markAsReceived(poId, { silent = false } = {}) {
            if (!silent) {
                if (!confirm('Apakah Anda yakin ingin menandai PO ini sebagai received?')) return;
            }
            
            try {
                const response = await fetch(`/client/purchase-orders/${poId}/received`, {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    await this.loadPurchaseOrders();
                    
                    if (!silent) this.showDetailModal = false;
                    if (!silent) this.showSuccessNotification('PO berhasil ditandai sebagai received!');
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error marking PO as received:', error);
                if (!silent) this.showErrorNotification('Gagal update status PO');
            }
        },
        
        getStatusClass(status) {
            const classes = {
                'pending': 'bg-yellow-100 text-yellow-800',
                'approved': 'bg-blue-100 text-blue-800',
                'received': 'bg-green-100 text-green-800',
                'cancelled': 'bg-red-100 text-red-800',
                'rejected': 'bg-red-100 text-red-800'
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        },
        
        getStatusText(status) {
            const texts = {
                'pending': 'Pending',
                'approved': 'Approved',
                'received': 'Received',
                'cancelled': 'Rejected',
                'rejected': 'Rejected'
            };
            return texts[status] || status;
        },
        
        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('id-ID');
        },
        
        formatNumber(number) {
            return Number(number).toLocaleString('id-ID');
        },
        
        // Export list (uses current filters)
        exportPOListExcel() {
            const rows = this.filteredPOs.map(po => ({
                'No. PO': po.po_number,
                'Tanggal': this.formatDate(po.created_at),
                'Items': po.items ? po.items.length : 0,
                'Daftar Item': po.items ? po.items.map(i => i.item_name).join(', ') : '',
                'Total': `Rp ${this.formatNumber(po.total_amount)}`,
                'Status': this.getStatusText(po.status),
                'Payment': po.payment_status === 'paid' ? 'Lunas' : 'Belum Lunas'
            }));

            const worksheet = XLSX.utils.json_to_sheet(rows);
            worksheet['!cols'] = [
                { wch: 18 }, { wch: 14 }, { wch: 8 }, { wch: 40 }, { wch: 16 }, { wch: 12 }, { wch: 14 }
            ];
            worksheet['!freeze'] = { xSplit: 0, ySplit: 1 };
            worksheet['!autofilter'] = { ref: 'A1:G1' };

            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, 'PO List');
            XLSX.writeFile(workbook, 'client_purchase_orders.xlsx');
        },

        exportPOListPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            const head = [["No. PO", "Tanggal", "Items", "Total", "Status", "Payment"]];
            const body = this.filteredPOs.map(po => [
                po.po_number,
                this.formatDate(po.created_at),
                (po.items ? po.items.length : 0) + ' items',
                `Rp ${this.formatNumber(po.total_amount)}`,
                this.getStatusText(po.status),
                po.payment_status === 'paid' ? 'Lunas' : 'Belum Lunas'
            ]);

            doc.autoTable({
                head,
                body,
                startY: 20,
                styles: { fontSize: 8, cellPadding: 2 },
                headStyles: { fillColor: [40, 195, 40], textColor: 255 }
            });
            doc.save('client_purchase_orders.pdf');
        },

        // Export individual PO as invoice PDF
        async exportPOInvoicePDF(po) {
            if (!po) return;
            
            try {
                // Load full PO details with items
                const response = await fetch(`/client/purchase-orders/${po.id}`, {
                    headers: { 'Accept': 'application/json' }
                });
                
                if (!response.ok) {
                    throw new Error('Failed to load PO details');
                }
                
                const poDetails = await response.json();
                const fullPO = poDetails.data || poDetails;
                
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF({ unit: 'pt', format: 'a4' });

                // Layout constants
                const margin = 40;
                const line = (y) => doc.line(margin, y, doc.internal.pageSize.getWidth() - margin, y);
                const rightX = doc.internal.pageSize.getWidth() - margin;

                // Company header with logo
                doc.setFontSize(12);
                doc.setFont(undefined, 'bold');
                doc.text(this.company.name || 'GAFI Client', margin, margin + 12);
                doc.setFont(undefined, 'normal');
                doc.setFontSize(10);
                if (this.company.address) doc.text(this.company.address, margin, margin + 28);
                const contact = `${this.company.phone ? 'Telp: ' + this.company.phone : ''}${this.company.phone && this.company.email ? '  |  ' : ''}${this.company.email ? 'Email: ' + this.company.email : ''}`;
                if (contact.trim()) doc.text(contact, margin, margin + 44);

                // Add logo if available
                if (this.company.logoUrl) {
                    try {
                        const logoResponse = await fetch(this.company.logoUrl);
                        const logoBlob = await logoResponse.blob();
                        const logoDataUrl = await new Promise((resolve) => {
                            const reader = new FileReader();
                            reader.onload = () => resolve(reader.result);
                            reader.readAsDataURL(logoBlob);
                        });
                        
                        // Add logo to top right
                        doc.addImage(logoDataUrl, 'PNG', rightX - 60, margin, 50, 50);
                    } catch (e) {
                        console.log('Could not load logo:', e);
                    }
                }

                // Invoice title and meta
                doc.setFontSize(16);
                doc.setFont(undefined, 'bold');
                doc.text('INVOICE', rightX, margin + 12, { align: 'right' });
                doc.setFontSize(10);
                doc.setFont(undefined, 'normal');
                const metaY = margin + 28;
                doc.text(`No. PO: ${fullPO.po_number}`, rightX, metaY, { align: 'right' });
                doc.text(`Tanggal: ${this.formatDate(fullPO.created_at)}`, rightX, metaY + 14, { align: 'right' });
                doc.text(`Status: ${this.getStatusText(fullPO.status)}`, rightX, metaY + 28, { align: 'right' });
                doc.text(`Payment: ${fullPO.payment_status === 'paid' ? 'Lunas' : 'Belum Lunas'}`, rightX, metaY + 42, { align: 'right' });

                // Separator
                const sepY = metaY + 56;
                line(sepY);

                // Client info
                doc.setFontSize(10);
                doc.setFont(undefined, 'bold');
                const clientStartY = sepY + 18;
                doc.text('Kepada:', margin, clientStartY);
                doc.setFont(undefined, 'normal');
                doc.text(fullPO.client_name || 'Client', margin, clientStartY + 16);
                if (fullPO.client_email) doc.text(fullPO.client_email, margin, clientStartY + 30);
                if (fullPO.client_phone) doc.text(fullPO.client_phone, margin, clientStartY + 44);

                // Items table
                const tableColumn = ['Nama Item', 'SKU', 'Quantity', 'Harga Satuan', 'Subtotal'];
                const tableRows = (fullPO.items || []).map(item => [
                    item.item_name + (item.item_type === 'external' ? ' (Luar)' : ' (Stock)'),
                    item.sku || '-',
                    item.quantity + ' pcs',
                    'Rp ' + this.formatNumber(item.unit_price),
                    'Rp ' + this.formatNumber(item.subtotal)
                ]);

                doc.autoTable({
                    head: [tableColumn],
                    body: tableRows,
                    startY: clientStartY + 70,
                    margin: { left: margin, right: margin },
                    styles: { fontSize: 9, cellPadding: 6, valign: 'middle' },
                    headStyles: { fillColor: [40, 195, 40], textColor: 255, fontStyle: 'bold' },
                    columnStyles: {
                        2: { halign: 'center' },
                        3: { halign: 'right' },
                        4: { halign: 'right' }
                    }
                });

                // Summary block
                const afterTableY = doc.lastAutoTable.finalY + 16;
                doc.setFont(undefined, 'bold');
                doc.setFontSize(12);
                doc.text('Total Purchase Order:', rightX - 180, afterTableY + 20);
                doc.text('Rp ' + this.formatNumber(fullPO.total_amount), rightX, afterTableY + 20, { align: 'right' });

                // Footer note
                doc.setFont(undefined, 'normal');
                doc.setFontSize(9);
                const footerY = afterTableY + 50;
                doc.text('* Invoice ini sah tanpa tanda tangan dan dicetak otomatis oleh sistem GAFI.', margin, footerY);
                const bankInfo = this.company.bank && this.company.no_rekening ? 
                    `Pembayaran ke: ${this.company.bank} - ${this.company.no_rekening}` : 
                    'Pembayaran ke: BCA - 64835868';
                doc.text(bankInfo, margin, footerY + 14);

                doc.save('Invoice_' + fullPO.po_number + '.pdf');
                
            } catch (error) {
                console.error('Error exporting invoice:', error);
                this.showErrorNotification('Gagal mengexport invoice');
            }
        },
        
        // Date filtering functions
        applyDateFilter() {
            // Reset to first page when filtering
            this.currentPage = 1;
            this.externalItems.current_page = 1;
        },
        
        clearDateFilter() {
            this.dateFilter = '';
            this.customStartDate = '';
            this.customEndDate = '';
            this.currentPage = 1; // Reset to first page
            this.externalItems.current_page = 1; // Reset external items page
        },
        
        getFilterStatusText() {
            switch (this.dateFilter) {
                case 'today': return 'Menampilkan data hari ini';
                case 'week': return 'Menampilkan data minggu ini';
                case 'month': return 'Menampilkan data bulan ini';
                case 'year': return 'Menampilkan data tahun ini';
                case 'custom':
                    if (this.customStartDate && this.customEndDate) {
                        const start = new Date(this.customStartDate).toLocaleDateString('id-ID');
                        const end = new Date(this.customEndDate).toLocaleDateString('id-ID');
                        return `Menampilkan data dari ${start} sampai ${end}`;
                    }
                    return 'Menampilkan data berdasarkan rentang tanggal custom';
                default: return '';
            }
        },
        
        applyDateFilterToPOs(pos) {
            if (!this.dateFilter) return pos;
            const now = new Date();
            let startDate, endDate;
            
            switch (this.dateFilter) {
                case 'today':
                    startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                    endDate = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59, 999);
                    break;
                case 'week':
                    const startOfWeek = new Date(now);
                    startOfWeek.setDate(now.getDate() - now.getDay());
                    startOfWeek.setHours(0, 0, 0, 0);
                    startDate = startOfWeek;
                    endDate = new Date(startOfWeek);
                    endDate.setDate(startOfWeek.getDate() + 6);
                    endDate.setHours(23, 59, 59, 999);
                    break;
                case 'month':
                    startDate = new Date(now.getFullYear(), now.getMonth(), 1);
                    endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59, 999);
                    break;
                case 'year':
                    startDate = new Date(now.getFullYear(), 0, 1);
                    endDate = new Date(now.getFullYear(), 11, 31, 23, 59, 59, 999);
                    break;
                case 'custom':
                    if (!this.customStartDate || !this.customEndDate) return pos;
                    startDate = new Date(this.customStartDate);
                    endDate = new Date(this.customEndDate);
                    endDate.setHours(23, 59, 59, 999);
                    break;
                default:
                    return pos;
            }
            
            return pos.filter(po => {
                const created = new Date(po.created_at);
                return created >= startDate && created <= endDate;
            });
        },
        
        applyDateFilterToExternalItems(items) {
            if (!this.dateFilter) return items;
            const now = new Date();
            let startDate, endDate;
            
            switch (this.dateFilter) {
                case 'today':
                    startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                    endDate = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59, 999);
                    break;
                case 'week':
                    const startOfWeek = new Date(now);
                    startOfWeek.setDate(now.getDate() - now.getDay());
                    startOfWeek.setHours(0, 0, 0, 0);
                    startDate = startOfWeek;
                    endDate = new Date(startOfWeek);
                    endDate.setDate(startOfWeek.getDate() + 6);
                    endDate.setHours(23, 59, 59, 999);
                    break;
                case 'month':
                    startDate = new Date(now.getFullYear(), now.getMonth(), 1);
                    endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59, 999);
                    break;
                case 'year':
                    startDate = new Date(now.getFullYear(), 0, 1);
                    endDate = new Date(now.getFullYear(), 11, 31, 23, 59, 59, 999);
                    break;
                case 'custom':
                    if (!this.customStartDate || !this.customEndDate) return items;
                    startDate = new Date(this.customStartDate);
                    endDate = new Date(this.customEndDate);
                    endDate.setHours(23, 59, 59, 999);
                    break;
                default:
                    return items;
            }
            
            return items.filter(item => {
                const created = new Date(item.created_at);
                return created >= startDate && created <= endDate;
            });
        },

        // Confirmation helpers
        showConfirm({ title, message, onConfirm }) {
            this.confirm = { show: true, title, message, onConfirm, loading: false };
        },
        closeConfirm() {
            this.confirm = { show: false, title: '', message: '', onConfirm: null, loading: false };
        },
        
        // Notification methods (using the same system as topbar)
        showSuccessNotification(message) {
            // Dispatch event to topbar notification system
            window.dispatchEvent(new CustomEvent('show-notification', {
                detail: { type: 'success', message: message }
            }));
        },
        
        showErrorNotification(message) {
            window.dispatchEvent(new CustomEvent('show-notification', {
                detail: { type: 'error', message: message }
            }));
        },

        //  
    }
}
</script>
@endsection

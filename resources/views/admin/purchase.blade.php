@extends('layouts.admin')

@section('content')
<div class="bg-white rounded-xl shadow p-8" x-data="purchaseApproval()" x-init="init()">
    <h1 class="text-2xl font-bold text-[#28C328] mb-6">Purchase Order Approval</h1>
    
    <!-- Filter & Search -->
    <div class="mb-6 flex flex-wrap items-center gap-4 justify-between">
        <div class="flex gap-2">
            <div class="relative">
                <input type="text" x-model="search" placeholder="Cari PO..." class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                <button @click="open = !open" class="px-4 py-2 border border-gray-300 rounded-lg text-sm flex items-center gap-2">
                    <span x-text="statusFilter === '' ? 'Semua Status' : statusFilter"></span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open" x-transition class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                    <div @click="statusFilter = ''; open = false" class="px-4 py-2 hover:bg-gray-100 cursor-pointer rounded-t-lg">Semua Status</div>
                    <div @click="statusFilter = 'pending'; open = false" class="px-4 py-2 hover:bg-gray-100 cursor-pointer">Pending</div>
                    <div @click="statusFilter = 'approved'; open = false" class="px-4 py-2 hover:bg-gray-100 cursor-pointer">Approved</div>
                    <div @click="statusFilter = 'received'; open = false" class="px-4 py-2 hover:bg-gray-100 cursor-pointer">Received</div>
                    <div @click="statusFilter = 'rejected'; open = false" class="px-4 py-2 hover:bg-gray-100 cursor-pointer">Rejected</div>
                </div>
            </div>
            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                <button @click="open = !open" class="px-4 py-2 border border-gray-300 rounded-lg text-sm flex items-center gap-2">
                    <span x-text="clientFilter === '' ? 'Semua Client' : clientFilter"></span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open" x-transition class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                    <div @click="clientFilter = ''; open = false" class="px-4 py-2 hover:bg-gray-100 cursor-pointer rounded-t-lg">Semua Client</div>
                    <template x-for="client in clients" :key="client">
                        <div @click="clientFilter = client; open = false" class="px-4 py-2 hover:bg-gray-100 cursor-pointer" x-text="client"></div>
                    </template>
                </div>
            </div>
            <!-- Filter Tanggal -->
            <select x-model="dateFilter" @change="applyDateFilter()" class="rounded-lg border border-gray-300 px-3 py-2 text-gray-700 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-[#28C328] focus:border-transparent">
                <option value="">Semua Tanggal</option>
                <option value="today">Hari Ini</option>
                <option value="week">Minggu Ini</option>
                <option value="month">Bulan Ini</option>
                <option value="year">Tahun Ini</option>
                <option value="custom">Custom Range</option>
            </select>
            <div x-show="dateFilter === 'custom'" class="flex gap-2 items-center">
                <input type="date" x-model="customStartDate" @change="applyDateFilter()" class="rounded-lg border border-gray-300 px-3 py-2 text-gray-700 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-[#28C328] focus:border-transparent" placeholder="Tanggal Mulai">
                <span class="text-gray-500">sampai</span>
                <input type="date" x-model="customEndDate" @change="applyDateFilter()" class="rounded-lg border border-gray-300 px-3 py-2 text-gray-700 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-[#28C328] focus:border-transparent" placeholder="Tanggal Akhir">
            </div>
            <button x-show="dateFilter" @click="clearDateFilter()" class="px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition text-sm flex items-center gap-1" title="Hapus Filter Tanggal">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span x-text="getFilterStatusText()"></span>
            </button>
        </div>
        
        <div class="flex gap-2">
            <button @click="exportExcel()" class="rounded-lg bg-[#28C328] px-4 py-2 text-white text-sm font-semibold flex items-center gap-2 hover:bg-[#22a322] transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                </svg>
                Excel
            </button>
            <button @click="exportPDF()" class="rounded-lg bg-[#28C328] px-4 py-2 text-white text-sm font-semibold flex items-center gap-2 hover:bg-[#22a322] transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20h9" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                PDF
            </button>
        </div>
    </div>

    <!-- Purchase Orders Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-[#28C328] text-white">
                    <th class="p-3 text-left rounded-tl-xl">No. PO</th>
                    <th class="p-3 text-left">Client</th>
                    <th class="p-3 text-left">Tanggal</th>
                    <th class="p-3 text-left">Items</th>
                    <th class="p-3 text-right">Total</th>
                    <th class="p-3 text-center">Status</th>
                    <th class="p-3 text-center">Payment</th>
                    <th class="p-3 text-center rounded-tr-xl">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                <template x-for="po in filteredPOs" :key="po.id">
                    <tr class="hover:bg-gray-50">
                        <td class="p-3">
                            <div class="font-semibold text-gray-800" x-text="po.po_number"></div>
                        </td>
                        <td class="p-3">
                            <div class="text-sm text-gray-800" x-text="po.client_name"></div>
                            <div class="text-xs text-gray-500" x-text="po.client_email"></div>
                        </td>
                        <td class="p-3 text-gray-600" x-text="formatDate(po.created_at)"></td>
                        <td class="p-3">
                            <div class="text-sm text-gray-800" x-text="po.items.length + ' items'"></div>
                            <div class="text-xs text-gray-500 max-w-xs">
                                <template x-for="(item, index) in po.items.slice(0, 2)" :key="item.id">
                                    <div class="flex justify-between">
                                        <span x-text="item.item_name"></span>
                                        <span class="font-semibold" x-text="item.quantity + 'x'"></span>
                                    </div>
                                </template>
                                <div x-show="po.items.length > 2" class="text-xs text-gray-400 italic">
                                    +<span x-text="po.items.length - 2"></span> item lainnya
                                </div>
                            </div>
                            <div class="text-xs text-gray-400 mt-1">
                                <span class="inline-flex items-center gap-1">
                                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                    <span x-text="po.items.filter(i => i.item_type === 'stock').length + ' Stock'"></span>
                                </span>
                                <span class="inline-flex items-center gap-1 ml-2" x-show="po.items.filter(i => i.item_type === 'external').length > 0">
                                    <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                    <span x-text="po.items.filter(i => i.item_type === 'external').length + ' Luar'"></span>
                                </span>
                            </div>
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
                            <div class="flex gap-2 justify-center">
                                <button @click="viewPODetail(po)" class="text-[#28C328] hover:text-[#22a322] text-sm font-medium">
                                    Detail
                                </button>
                                <template x-if="po.status === 'pending'">
                                    <button @click="approvePO(po.id)" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Approve
                                    </button>
                                </template>
                                <template x-if="po.status === 'pending'">
                                    <button @click="rejectPO(po.id)" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                        Reject
                                    </button>
                                </template>
                                <template x-if="po.status === 'rejected'">
                                    <button @click="deletePOAdmin(po.id)" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                        Delete
                                    </button>
                                </template>
                                <template x-if="po.status === 'approved' && po.payment_status !== 'paid'">
                                    <button @click="markAsPaid(po.id)" class="text-green-600 hover:text-green-800 text-sm font-medium">
                                        Payment
                                    </button>
                                </template>
                                <template x-if="po.status !== 'pending'">
                                    <button @click="invoiceDetail(po)" class="text-yellow-600 hover:text-green-800 text-sm font-medium">
                                        Invoice
                                    </button>
                                </template>
                                {{-- <template x-if="po.status === 'approved' && po.payment_status === 'paid'">
                                    <button @click="markAsReceived(po.id)" class="text-orange-600 hover:text-orange-800 text-sm font-medium">
                                        Received
                                    </button>
                                </template> --}}
                            </div>
                        </td>
                    </tr>
                </template>
                <template x-if="filteredPOs.length === 0">
                    <tr>
                        <td colspan="8" class="p-8 text-center text-gray-500">
                            Belum ada Purchase Order
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- PO Detail Modal -->
    <div x-show="showDetailModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" x-cloak @click.self="showDetailModal = false">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-6xl mx-4 relative max-h-[95vh] overflow-hidden border border-gray-100" @click.stop>
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
                        </div>
                        
                        <!-- Client Info -->
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-2xl p-6 mb-6 border border-gray-200">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-8 h-8 bg-[#28C328] rounded-lg flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <h4 class="font-bold text-lg text-gray-800">Informasi Client</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="bg-white rounded-xl p-4 border border-gray-200">
                                    <div class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Nama Client</div>
                                    <div class="font-bold text-gray-800" x-text="selectedPO.client_name"></div>
                                </div>
                                <div class="bg-white rounded-xl p-4 border border-gray-200">
                                    <div class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Email</div>
                                    <div class="text-gray-800 font-medium" x-text="selectedPO.client_email"></div>
                                </div>
                                <div class="bg-white rounded-xl p-4 border border-gray-200">
                                    <div class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Telepon</div>
                                    <div class="text-gray-800 font-medium" x-text="selectedPO.client_phone || '-'"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Ringkasan Pembelian (paling bawah): daftar item + jumlah -->
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
                            <h3 class="font-bold text-xl text-gray-800">Detail Items yang Dibeli</h3>
                            <span class="px-3 py-1 bg-[#28C328] text-white text-sm font-semibold rounded-full" x-text="selectedPO.items ? selectedPO.items.length : 0"></span>
                        </div>
                        
                        <!-- Items Table -->
                        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                        <tr>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">No</th>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Nama Item</th>
                                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">SKU</th>
                                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Jumlah</th>
                                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Harga Satuan</th>
                                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <template x-for="(item, index) in selectedPO.items" :key="item.id">
                                            <tr class="hover:bg-gray-50 transition-colors">
                                                <td class="px-6 py-4 text-center">
                                                    <span class="font-bold text-gray-600" x-text="index + 1"></span>
                                                </td>
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
                                                                  x-text="item.item_type === 'external' ? 'Item Luar' : 'Item Stock'"></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <span class="font-mono text-sm text-gray-600 bg-gray-100 px-2 py-1 rounded" x-text="item.sku || '-'"></span>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <span class="font-bold text-lg text-gray-800 bg-blue-50 px-3 py-1 rounded-full" x-text="item.quantity + ' pcs'"></span>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <span class="font-semibold text-gray-800">Rp<span x-text="formatNumber(item.unit_price)"></span></span>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <span class="font-bold text-lg text-[#28C328]">Rp<span x-text="formatNumber(item.subtotal)"></span></span>
                                                </td>
                                            </tr>
                                        </template>
                                        
                                        <!-- Fallback jika tidak ada items -->
                                        <template x-if="!selectedPO.items || selectedPO.items.length === 0">
                                            <tr>
                                                <td colspan="6" class="px-6 py-12 text-center">
                                                    <div class="flex flex-col items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                                        </svg>
                                                        <div class="text-gray-500 text-lg font-medium">Tidak ada items dalam Purchase Order ini</div>
                                                        <div class="text-gray-400 text-sm mt-2">Items mungkin belum ditambahkan atau terjadi kesalahan loading data</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot class="bg-gradient-to-r from-[#28C328] to-[#22a322]">
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 text-right font-bold text-white text-lg">Total Pembelian:</td>
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
                    <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                        <template x-if="selectedPO.status === 'pending'">
                            <button @click="approvePO(selectedPO.id)" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 font-semibold flex items-center gap-2 shadow-lg hover:shadow-xl transform hover:scale-105">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Approve PO
                            </button>
                        </template>
                        <template x-if="selectedPO.status === 'approved' && selectedPO.payment_status !== 'paid'">
                            <button @click="markAsPaid(selectedPO.id)" class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 font-semibold flex items-center gap-2 shadow-lg hover:shadow-xl transform hover:scale-105">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                </svg>
                                Mark as Paid
                            </button>
                        </template>
                        <template x-if="selectedPO.status === 'approved' && selectedPO.payment_status === 'paid'">
                            <button @click="markAsReceived(selectedPO.id)" class="px-6 py-3 bg-gradient-to-r from-orange-600 to-orange-700 text-white rounded-xl hover:from-orange-700 hover:to-orange-800 transition-all duration-200 font-semibold flex items-center gap-2 shadow-lg hover:shadow-xl transform hover:scale-105">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Mark as Received
                            </button>
                        </template>
                        <template x-if="selectedPO.status === 'rejected'">
                            <button @click="deletePOAdmin(selectedPO.id)" class="px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 font-semibold flex items-center gap-2 shadow-lg hover:shadow-xl transform hover:scale-105">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete
                            </button>
                        </template>
                        <button @click="showDetailModal = false" class="px-6 py-3 bg-gradient-to-r from-gray-200 to-gray-300 text-gray-700 rounded-xl hover:from-gray-300 hover:to-gray-400 transition-all duration-200 font-semibold flex items-center gap-2 shadow-lg hover:shadow-xl transform hover:scale-105">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Tutup
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <div x-show="showDetailModalInvoice" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" x-cloak @click.self="showDetailModalInvoice = false">
        <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-5xl mx-4 relative overflow-y-auto max-h-[80vh]" @click.stop>
            <button @click="showDetailModalInvoice = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-4">
                    <div class="flex items-center justify-between bg-[#BAFFBA] rounded-t-2xl px-8 py-4">
                        <span class="font-bold text-lg">Detail Transaksi</span>
                    </div>
                    <div class="w-full mt-6 bg-white rounded-b-2xl px-4">  
                        {{-- Daftar Item Yang Pesan --}}
                        <div class="mb-6">
                            <div class="font-semibold mb-3 text-gray-800">Daftar Item yang Dibeli:</div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <template x-if="detailInvoice.items && detailInvoice.items.length > 0">
                                    <div class="space-y-3">
                                        <template x-for="(item, idx) in detailInvoice.items" :key="idx">
                                            <div class="flex items-center justify-between bg-white rounded-lg p-3 border border-gray-200">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 bg-[#28C328] rounded-full flex items-center justify-center text-white font-bold text-sm">
                                                        <span x-text="idx + 1"></span>
                                                    </div>
                                                    <div>
                                                        <div class="font-semibold text-gray-800" x-text="item.item_name"></div>
                                                        <div class="text-xs text-gray-500">SKU: <span x-text="item.sku"></span></div>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-sm text-gray-600">
                                                        <span x-text="item.quantity"></span> x Rp<span x-text="Number(item.unit_price).toLocaleString('id-ID')"></span>
                                                    </div>
                                                    <div class="font-semibold text-[#28C328]">
                                                        Rp<span x-text="Number(item.subtotal) * (item.quantity).toLocaleString('id-ID')"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="!detailInvoice.items || detailInvoice.items.length === 0">
                                    <div class="text-center text-gray-500 py-4">
                                        <div class="text-sm">Data item tidak tersedia</div>
                                        <div class="text-xs">Ini mungkin transaksi lama dengan format data berbeda</div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- INVOICE -->
                        <div class="mt-6">
                            <!-- Header Logo & Company + Invoice Box (match PDF) -->
                            <div class="flex items-center gap-4 border-b pb-4 mb-4 px-2">
                                <img src="/images/logo.png" alt="Logo" class="w-16 h-16 rounded-full border object-contain bg-white">
                                <div>
                                    <div class="text-xs text-gray-500 font-semibold">PT Golden Aroma Food Indonesia</div>
                                    <div class="text-xs text-gray-400 leading-snug max-w-xl">
                                    <span class="block" x-text="(getClientAddressLines()[0] || '')"></span>
                                        <span class="block" x-text="(getClientAddressLines()[1] || '')"></span>
                                    </div>
                                    <div class="text-xs text-gray-400">Telp: <span x-text="activeIdentity.phone"></span> | Email: <span x-text="activeIdentity.phone"></span></div>
                                </div>
                                <div class="ml-auto text-right">
                                    <div class="text-lg font-bold text-gray-700">INVOICE</div>
                                    <div class="text-xs text-gray-500">No. Invoice: <span x-text="getInvoiceNo(detailInvoice)"></span></div>
                                    <div class="text-xs text-gray-500">ID Pesanan: <span x-text="detailInvoice.id_pesanan"></span></div>
                                    <div class="text-xs text-gray-500">Tanggal: <span x-text="detailInvoice.periode"></span></div>
                                    <div class="mt-2">
                                        <span :class="invoiceStatusClass(detailInvoice.status)" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold" x-text="detailInvoice.status"></span>
                                    </div>
                                    </div>
                            </div>

                                <!-- Kepada -->
                                <div class="mb-4">
                                    <div class="font-semibold text-gray-700 mb-1">Kepada:</div>
                                    <div class="p-4 bg-gray-50 rounded-lg border">
                                        <div class="space-y-2">
                                            <div>
                                                <span class="text-xs text-gray-500 font-medium">Nama:</span>
                                                <div class="font-bold text-[#28C328] text-lg" x-text="detailInvoice.nama_pemesan"></div>
                                            </div>
                                            <div>
                                                <span class="text-xs text-gray-500 font-medium">ID Pesanan:</span>
                                                <div class="text-sm text-gray-700" x-text="detailInvoice.id_pesanan"></div>
                                            </div>
                                            <div>
                                                <span class="text-xs text-gray-500 font-medium">No. Telepon:</span>
                                                <div class="text-sm text-gray-700" x-text="detailInvoice.telepon || '-'"></div>
                                            </div>
                                            <div>
                                                <span class="text-xs text-gray-500 font-medium">Alamat:</span>
                                                <div class="text-sm text-gray-700" x-text="detailInvoice.alamat || '-'"></div>
                                            </div>
                                            <div x-show="detailInvoice.notes || detailInvoice.nama_ekspedisi">
                                                <span class="text-xs text-gray-500 font-medium">Catatan:</span>
                                                <div class="text-sm text-gray-700">
                                                    <span x-text="detailInvoice.notes || ''"></span>
                                                    <span x-show="detailInvoice.nama_ekspedisi && detailInvoice.notes" class="ml-2">| Ekspedisi: <span x-text="detailInvoice.nama_ekspedisi"></span></span>
                                                    <span x-show="detailInvoice.nama_ekspedisi && !detailInvoice.notes">Ekspedisi: <span x-text="detailInvoice.nama_ekspedisi"></span></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tabel Transaksi (match PDF) -->
                                <div class="overflow-x-auto mb-4 px-2">
                                    <template x-if="detailInvoice.items && detailInvoice.items.length > 0">
                                        <table class="min-w-full border text-sm divide-y divide-gray-200">
                                            <thead>
                                                <tr class="bg-[#BAFFBA] text-gray-700">
                                                    <th class="py-2 px-4 border-b text-left">Nama Pesanan</th>
                                                    <th class="py-2 px-4 border-b text-left">Harga Barang</th>
                                                    <th class="py-2 px-4 border-b text-left">Quantity</th>
                                                    <th class="py-2 px-4 border-b text-left">Jumlah Total</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white">
                                                <template x-for="(item, iidx) in detailInvoice.items" :key="iidx">
                                                    <tr class="even:bg-gray-50">
                                                        <td class="py-2 px-4 border-b" x-text="item.item_name"></td>
                                                        <td class="py-2 px-4 border-b text-left font-mono" x-text="'Rp'+Number(item.unit_price).toLocaleString('id-ID')"></td>
                                                        <td class="py-2 px-4 border-b text-left font-mono" x-text="item.quantity"></td>
                                                        <td class="py-2 px-4 border-b text-left font-bold font-mono" x-text="'Rp'+Number(item.subtotal).toLocaleString('id-ID')"></td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                            <tfoot>
                                                <tr class="bg-gray-50">
                                                    <td class="py-2 px-4 border-t font-semibold text-gray-700" colspan="2">Total Quantity:</td>
                                                    <td class="py-2 px-4 border-t text-left font-bold font-mono" x-text="detailInvoice.items ? detailInvoice.items.reduce((sum, item) => sum + (Number(item.quantity )), 0) : 0"></td>
                                                    <td class="py-2 px-4 border-t"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </template>
                                    <template x-if="!detailInvoice.items || detailInvoice.items.length === 0">
                                        <table class="min-w-full border text-sm divide-y divide-gray-200">
                                            <thead>
                                                <tr class="bg-[#BAFFBA] text-gray-700">
                                                    <th class="py-2 px-4 border-b text-left">Nama Pesanan</th>
                                                    <th class="py-2 px-4 border-b text-left">Harga Barang</th>
                                                    <th class="py-2 px-4 border-b text-left">Quantity</th>
                                                    <th class="py-2 px-4 border-b text-left">Jumlah Total</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white">
                                                <tr class="even:bg-gray-50">
                                                <td class="py-2 px-4 border-b" x-text="detailInvoice.item_name"></td>
                                                    <td class="py-2 px-4 border-b text-left font-mono" x-text="'Rp'+Number(detailInvoice.unit_price || 0).toLocaleString('id-ID')"></td>
                                                    <td class="py-2 px-4 border-b text-left font-mono" x-text="detailInvoice.quantity || 0"></td>
                                                    <td class="py-2 px-4 border-b text-left font-bold font-mono" x-text="'Rp'+((Number(detailInvoice.unit_price || 0)*Number(detailInvoice.quantity || 0)))).toLocaleString('id-ID')"></td>
                                                    </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr class="bg-gray-50">
                                                    <td class="py-2 px-4 border-t font-semibold text-gray-700" colspan="2">Total Quantity:</td>
                                                    <td class="py-2 px-4 border-t text-left font-bold font-mono" x-text="detailInvoice.quantity || 0"></td>
                                                    <td class="py-2 px-4 border-t"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </template>
                                </div>

                            <!-- Summary & Footer -->
                            <div class="px-2">
                                <div class="flex justify-end mb-2">
                                    <div class="w-full md:w-1/2 lg:w-1/3">
                                        <div class="flex justify-between text-sm text-gray-600">
                                            <span>Subtotal</span>
                                            <span x-text="'Rp'+Number(saleItemsSubtotal(detailInvoice)).toLocaleString('id-ID')"></span>
                                        </div>
                                        <div class="flex justify-between text-sm text-gray-600">
                                            <span>Total Diskon Reguler</span>
                                            <span x-text="saleTotalDiskon(detailInvoice) > 0 ? ('Rp'+Number(saleTotalDiskon(detailInvoice)).toLocaleString('id-ID')) : '-' "></span>
                                        </div>
                                        <div class="flex justify-between text-sm text-gray-600">
                                            <span>Total Diskon Ball</span>
                                            <span x-text="saleTotalDiskonBall(detailInvoice) > 0 ? ('Rp'+Number(saleTotalDiskonBall(detailInvoice)).toLocaleString('id-ID')) : '-' "></span>
                                        </div>
                                        <div class="flex justify-between text-sm text-gray-600">
                                            <span>Total Ongkir</span>
                                            <span x-text="formatOngkirWithExpedition(detailInvoice)"></span>
                                        </div>
                                        <div class="flex justify-between text-lg font-bold text-[#28C328] border-t mt-2 pt-2">
                                            <span>Total Bayar</span>
                                            <span x-text="'Rp'+(Number(detailInvoice.total_harga || 0)).toLocaleString('id-ID')"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500">* Invoice ini sah tanpa tanda tangan dan dicetak otomatis oleh sistem GAFI.</div>
                                <div class="text-xs text-gray-600 mt-1">Pembayaran ke:</div>
                                <div class="text-xs text-gray-600" x-show="getClientIdentity(detailInvoice.client_id).bank && getClientIdentity(detailInvoice.client_id).account" x-text="getClientIdentity(detailInvoice.client_id).bank + ' - ' + getClientIdentity(detailInvoice.client_id).account"></div>
                                <button class="rounded-lg bg-[#28C328] text-white font-semibold px-6 py-2 text-sm mt-3" @click="exportInvoicePDF( detailInvoice.client_id, detailInvoice.id_pesanan)">Export PDF</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice Modal -->
    {{-- <div x-show="showInvoiceModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" x-cloak>
        <div class="bg-white rounded-2xl shadow-xl p-0 w-full max-w-2xl mx-4 relative overflow-y-auto max-h-screen">
            <button @click="showInvoiceModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold z-10">&times;</button>
            <div class="p-8">
                <!-- Header Logo & Company -->
                <div class="flex items-center gap-4 border-b pb-4 mb-6">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center overflow-hidden bg-gradient-to-br from-[#28C328] to-yellow-500">
                        <template x-if="company.logoUrl">
                            <img :src="company.logoUrl" alt="Logo" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!company.logoUrl">
                            <span class="text-white font-bold text-lg" x-text="(company.name || 'GAFI').substring(0,1)"></span>
                        </template>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-700" x-text="company.name || 'GAFI Admin'" ></h2>
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
                </div>

                <!-- Info Client -->
                <div class="mb-6">
                    <div class="font-semibold text-gray-700">Kepada:</div>
                    <div class="font-bold text-[#28C328] text-lg" x-text="selectedPO ? selectedPO.client_name : ''"></div>
                    <div class="text-xs text-gray-500" x-text="selectedPO ? selectedPO.client_email : ''"></div>
                    <div class="text-xs text-gray-500" x-text="selectedPO ? (selectedPO.client_phone || '-') : ''"></div>
                </div>
                
                <!-- Tabel Items -->
                <div class="overflow-x-auto mb-6">
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
                </div>
                
                <!-- Summary & Footer -->
                <div class="flex flex-col md:flex-row md:justify-between items-start md:items-center mb-2 gap-4">
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
    </div> --}}
</div>

<script>
function purchaseApproval() {
    return {
        // Data
        search: '',
        statusFilter: '',
        clientFilter: '',
        dateFilter: '',
        customStartDate: '',
        customEndDate: '',
        showDetailModal: false,
        showDetailModalInvoice: false,
        clientIdentityCache: null,
        selectedPO: null,
        detailInvoice: {},
        purchaseOrders: [],
        purchaseInvoice: [],
        activeIdentity: { phone: '', address: '', email: '', bank: '', account: '' },
        clients: [],
        // Company identity (loaded from /admin/identity)
        company: { name: '', phone: '', email: '', address: '', logoUrl: '', bank: '', no_rekening: '' },
        
        async init() {
            await this.loadPurchaseOrders();
            this.extractClients();
            await this.loadCompanyIdentity();
            // Auto-mark received untuk PO yang sudah > 14 hari (approved & paid)
            this.autoMarkOverdueAsReceived();
            // Cek berkala setiap 1 jam selama halaman terbuka
            if (!this.__autoReceivedTimer) {
                this.__autoReceivedTimer = setInterval(() => this.autoMarkOverdueAsReceived(), 60 * 60 * 1000);
            }
        },
        
        async loadPurchaseOrders() {
            try {
                const response = await fetch('/admin/purchase-orders', {
                    headers: { 'Accept': 'application/json' }
                });
                if (response.ok) {
                    this.purchaseOrders = await response.json();
                }
            } catch (error) {
                console.error('Error loading POs:', error);
            }
        },
        
        extractClients() {
            this.clients = [...new Set(this.purchaseOrders.map(po => po.client_name).filter(Boolean))];
        },
        
        get filteredPOs() {
            let pos = this.purchaseOrders;

            if (this.search) {
                pos = pos.filter(po => 
                    po.po_number.toLowerCase().includes(this.search.toLowerCase()) ||
                    po.client_name.toLowerCase().includes(this.search.toLowerCase()) ||
                    po.items.some(item => item.item_name.toLowerCase().includes(this.search.toLowerCase()))
                );
            }
            
            if (this.statusFilter) {
                pos = pos.filter(po => po.status === this.statusFilter);
            }
            
            if (this.clientFilter) {
                pos = pos.filter(po => po.client_name === this.clientFilter);
            }
            
            if (this.dateFilter) {
                pos = this.applyDateFilterToPOs(pos);
            }
            
            return pos;
        },
        
        viewPODetail(po) {
            this.selectedPO = po;
            this.showDetailModal = true;
        },

        async invoiceDetail(sale) {
            try {
                const responsebaru = await fetch(`/admin/purchase-orders/${sale.po_number}/invoice`, {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                    }
                });
                if (responsebaru.ok) {
                    this.purchaseInvoice = await responsebaru.json();
                }
            } catch (error) {
                console.error('Error loading POs:', error);
            }
            console.log(this.purchaseInvoice);
            this.detailInvoice = {...this.purchaseInvoice};
            this.showDetailModalInvoice = true;
        },

        saleItemsSubtotal(sale) {
            if (sale && sale.items && Array.isArray(sale.items) && sale.items.length > 0) {
                return sale.items.reduce((total, item) => {
                    const harga = Number(item.harga || 0);
                    const qty = Number(item.quantity || 0);
                    const subtotal = Number(item.subtotal || 0) || 0;
                    return total + subtotal;
                }, 0);
            }
            console.log(sale);
            // Fallback untuk data lama
            return (Number(sale.unit_price) || 0) * (Number(sale.quantity) || 0);
        },

        saleTotalDiskon(sale) {
            const subtotal = this.saleItemsSubtotal(sale);
            let totalDiskon = Number(sale && sale.total_diskon) || 0;
            
            if (!totalDiskon && sale) {
                if (sale.diskon_tipe === 'rupiah') {
                    totalDiskon = Math.min(Number(sale.diskon_nilai) || 0, subtotal);
                } else if (sale.diskon_tipe === 'persen') {
                    totalDiskon = Math.round(subtotal * (Number(sale.diskon_nilai) || 0) / 100);
                }
            }
            return Math.min(totalDiskon, subtotal);
        },

        saleTotalDiskonBall(sale) {
            // Gunakan nilai dari database untuk memastikan konsistensi
            return Number(sale && sale.total_diskon_ball) || 0;
        },

        formatOngkirWithExpedition(sale) {
            if (sale && sale.ongkir !== undefined && sale.ongkir !== null) {
                const ongkirValue = Number(sale.ongkir);
                if (ongkirValue > 0) {
                    return `Rp ${ongkirValue.toLocaleString('id-ID')}`;
                } else if (ongkirValue === 0) {
                    return 'Free Ongkir';
                }
            }
            return '-';
        },

        saleTotalOngkir(sale) {
            return Number(sale && sale.ongkir) || 0;
        },

        async getClientIdentity(id, po_number) {
            if(this.clientIdentityCache){
                return this.clientIdentityCache;
            }

            // Default values
            const defaultIdentity = {
                phone: '(021) 12345678',
                address: 'Gerbang Kuning Gudang Bumbu, Jalan Ceuri no 51 Kampung Sindang Asih, Katapang, Pamentasan, Kabupaten Bandung, Jawa Barat 40921',
                email: 'info@gafi.co.id',
                bank: 'BCA',
                account: '1234567890'
            };
            
            const response = await fetch(`/client/identity/${id}/${po_number}`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            if(response.ok){
                // WAJIB gunakan 'await' untuk .json()
                const data = await response.json();
                this.clientIdentityCache = data; // Simpan ke cache
            }

            const identity = {
                phone: this.clientIdentityCache.phone,
                address: this.clientIdentityCache.address,
                email: this.clientIdentityCache.email,
                bank: this.clientIdentityCache.bank,
                account: this.clientIdentityCache.rekening,
                id_pesanan: this.clientIdentityCache.id_pesanan,
                periode: this.clientIdentityCache.periode,
                status: this.clientIdentityCache.status,
                account: this.clientIdentityCache.account
            }
            this.activeIdentity = identity;
            return this.activeIdentity;
        },

        async exportInvoicePDF(id, po_number) {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({ unit: 'pt', format: 'a4' });
            const pageWidth = doc.internal.pageSize.getWidth();
            const pageHeight = doc.internal.pageSize.getHeight();
            const margin = 40;
            const headerHeight = 120;
            const adminIdent = await this.getClientIdentity(id, po_number);
            const detail = this.detailInvoice || {};
            const fallbackAddress = 'Gerbang Kuning Gudang Bumbu, Jalan Ceuri no 51 Kampung Sindang Asih, Katapang, Pamentasan, Kabupaten Bandung, Jawa Barat 40921';
            const address = adminIdent.address || fallbackAddress;
            const paymentBank = adminIdent.bank || '-';
            const paymentAccount = adminIdent.account || '-';
            const formatCurrency = (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`;

            const loadImage = async (src) => {
                if (!src) return null;
                return new Promise((resolve) => {
                    const img = new Image();
                    img.crossOrigin = 'anonymous';
                    img.onload = () => resolve(img);
                    img.onerror = () => resolve(null);
                    img.src = src;
                });
            };

            let logoImage = null;
            const logoCandidates = [adminIdent.logo_url, window.location.origin + '/images/logo.png'];
            for (const src of logoCandidates) {
                logoImage = await loadImage(src);
                if (logoImage) break;
            }

            const drawHeader = (pageNumber) => {
                if (logoImage) {
                    doc.addImage(logoImage, 'PNG', margin, margin, 48, 48);
                } else {
                    doc.setFont(undefined, 'bold');
                    doc.setFontSize(20);
                    doc.setTextColor(40, 195, 40);
                    doc.text('GAFI', margin + 24, margin + 28, { align: 'center' });
                }

                doc.setTextColor(60);
                doc.setFontSize(12);
                doc.setFont(undefined, 'bold');
                doc.text('PT Golden Aroma Food Indonesia', margin + 60, margin + 12);
                doc.setFontSize(10);
                doc.setFont(undefined, 'normal');
                const addrLines = doc.splitTextToSize(address, 220);
                doc.text(addrLines, margin + 60, margin + 28);
                const contactY = margin + 28 + addrLines.length * 12;
                const contactParts = [
                    `Telp: ${adminIdent.phone || '(021) 12345678'}`,
                    `Email: ${adminIdent.email || 'info@gafi.co.id'}`
                ];
                doc.text(contactParts.join('  |  '), margin + 60, contactY);

                doc.setFontSize(16);
                doc.setFont(undefined, 'bold');
                doc.text('INVOICE', pageWidth - margin, margin + 12, { align: 'right' });
                doc.setFontSize(10);
                doc.setFont(undefined, 'normal');
                const metaY = margin + 28;
                doc.text(`No. Invoice: ${this.getInvoiceNo(detail) || '-'}`, pageWidth - margin, metaY, { align: 'right' });
                doc.text(`ID Pesanan: ${detail.id_pesanan || '-'}`, pageWidth - margin, metaY + 14, { align: 'right' });
                doc.text(`Tanggal: ${detail.periode || detail.tanggal || '-'}`, pageWidth - margin, metaY + 28, { align: 'right' });
                doc.text(`Status: ${detail.status || '-'}`, pageWidth - margin, metaY + 42, { align: 'right' });

                doc.setDrawColor(230);
                doc.line(margin, margin + headerHeight, pageWidth - margin, margin + headerHeight);
            };

            const drawFooter = (pageNumber) => {
                doc.setFontSize(9);
                doc.setTextColor(130);
                doc.text(`Halaman ${pageNumber}`, margin, pageHeight - 25);
            };

            const recipientStartY = margin + headerHeight + 18;
            let currentY = recipientStartY;
            doc.setFontSize(10);
            doc.setFont(undefined, 'bold');
            doc.setTextColor(55, 120, 70);
            doc.text('Kepada:', margin, currentY);
            currentY += 14;

            doc.setFont(undefined, 'normal');
            doc.setTextColor(60);
            doc.text(detail.nama_pemesan || '-', margin, currentY);
            currentY += 12;
            doc.text(`ID Pesanan: ${detail.id_pesanan || '-'}`, margin, currentY);
            currentY += 12;
            doc.text(`Telepon: ${detail.telepon || '-'}`, margin, currentY);
            currentY += 12;
            const alamatLines = doc.splitTextToSize(`Alamat: ${detail.alamat || '-'}`, pageWidth - margin * 2);
            doc.text(alamatLines, margin, currentY);
            currentY += alamatLines.length * 12;
            doc.text(`Sales: ${detail.nama_sales || '-'}`, margin, currentY + 8);
            currentY += 20;

            const extraNotes = [];
            if (detail.notes) extraNotes.push(detail.notes);
            if (detail.nama_ekspedisi) extraNotes.push(`Ekspedisi: ${detail.nama_ekspedisi}`);
            if (extraNotes.length) {
                doc.setFont(undefined, 'bold');
                doc.setTextColor(180, 130, 0);
                doc.text('Catatan:', margin, currentY);
                doc.setFont(undefined, 'normal');
                doc.setTextColor(90);
                const noteLines = doc.splitTextToSize(extraNotes.join(' | '), pageWidth - margin * 2);
                doc.text(noteLines, margin, currentY + 12);
                currentY += 12 + noteLines.length * 12;
            }

            const hasItems = Array.isArray(detail.items) && detail.items.length > 0;
            const itemsSubtotal = this.saleItemsSubtotal(detail);
            const totalDiskon = this.saleTotalDiskon(detail);
            const totalDiskonBall = this.saleTotalDiskonBall(detail);
            const totalOngkir = this.saleTotalOngkir(detail);

            let totalQuantity = 0;
            const rows = hasItems
                ? detail.items.map(item => {
                    const harga = Number(item.unit_price || 0);
                    const qty = Number(item.quantity || 0);
                    const subtotal = Number(harga * qty) || 0;
                    totalQuantity += qty;
                    return [
                        String(item.item_name || ''),
                        formatCurrency(harga),
                        String(qty),
                        formatCurrency(subtotal)
                    ];
                })
                : (() => {
                    const harga = Number(detail.unit_price || 0);
                    const qty = Number(detail.quantity || 0);
                    const subtotal = harga * qty;
                    totalQuantity = qty;
                    return [[String(detail.nama_pesanan || detail.id_pesanan || ''), formatCurrency(harga), String(qty), formatCurrency(subtotal - Number(totalDiskon || 0))]];
                })();

            doc.autoTable({
                head: [['Nama Pesanan', 'Harga Barang', 'Quantity', 'Jumlah Total']],
                body: rows,
                foot: [['Total Quantity', '', String(totalQuantity), '']],
                startY: currentY + 16,
                margin: { left: margin, right: margin, top: margin + headerHeight + 15, bottom: 80 },
                styles: { 
                    fontSize: 9, 
                    cellPadding: 6, 
                    valign: 'middle' // Menjaga teks di tengah secara vertikal
                },
                headStyles: { fillColor: [186, 255, 186], textColor: 60, fontStyle: 'bold', halign: 'center' }, // Header ikut rata tengah
                footStyles: { fillColor: [240, 240, 240], textColor: 60, fontStyle: 'bold' },
                columnStyles: {
                    0: { halign: 'left' },   // Nama Pesanan rata kiri
                    1: { halign: 'center' }, // Harga Barang JADI TENGAH
                    2: { halign: 'center' }, // Quantity TETAP TENGAH
                    3: { halign: 'center' }   // Jumlah Total rata kanan (biasanya untuk mata uang)
                },
                didDrawPage: (data) => {
                    drawHeader(data.pageNumber);
                    drawFooter(data.pageNumber);
                }
            });

            let summaryStartY = doc.lastAutoTable.finalY + 20;
            const ensureSpace = () => {
                if (summaryStartY + 120 > pageHeight - margin) {
                    doc.addPage();
                    const { pageNumber } = doc.internal.getCurrentPageInfo();
                    drawHeader(pageNumber);
                    drawFooter(pageNumber);
                    summaryStartY = margin + headerHeight + 20;
                }
            };

            ensureSpace();

            doc.setFontSize(10);
            doc.setFont(undefined, 'normal');
            doc.setTextColor(60);
            doc.text('Subtotal', pageWidth - margin - 120, summaryStartY, { align: 'right' });
            doc.text(formatCurrency(itemsSubtotal), pageWidth - margin, summaryStartY, { align: 'right' });
            summaryStartY += 14;

            if (totalDiskon > 0) {
                doc.text('Diskon Reguler', pageWidth - margin - 120, summaryStartY, { align: 'right' });
                doc.text(`- ${formatCurrency(totalDiskon)}`, pageWidth - margin, summaryStartY, { align: 'right' });
                summaryStartY += 14;
            }

            if (totalDiskonBall > 0) {
                doc.text('Diskon Ball', pageWidth - margin - 120, summaryStartY, { align: 'right' });
                doc.text(`- ${formatCurrency(totalDiskonBall)}`, pageWidth - margin, summaryStartY, { align: 'right' });
                summaryStartY += 14;
            }

            if (totalOngkir !== undefined && totalOngkir !== null) {
                doc.text('Ongkir', pageWidth - margin - 120, summaryStartY, { align: 'right' });
                const ongkirText = totalOngkir > 0 ? `+ ${formatCurrency(totalOngkir)}` : 'Free Ongkir';
                doc.text(ongkirText, pageWidth - margin, summaryStartY, { align: 'right' });
                summaryStartY += 14;
            }

            doc.setFont(undefined, 'bold');
            doc.setFontSize(12);
            doc.setTextColor(40, 195, 40);
            doc.text('Total Bayar', pageWidth - margin - 120, summaryStartY + 6, { align: 'right' });
            doc.setFontSize(16);
            doc.text(formatCurrency(detail.total_harga || detail.total_amount || 0), pageWidth - margin, summaryStartY + 6, { align: 'right' });
            summaryStartY += 32;

            ensureSpace();
            doc.setFontSize(9);
            doc.setFont(undefined, 'normal');
            doc.setTextColor(120);
            doc.text('* Invoice ini sah tanpa tanda tangan dan dicetak otomatis oleh sistem GAFI.', margin, summaryStartY);
            doc.text(`Pembayaran ke: ${paymentBank} - ${paymentAccount}`, margin, summaryStartY + 14);

            doc.save(`invoice_${detail.id_pesanan || detail.order_number || 'gafi'}.pdf`);
        },

        getClientAddressLines() {
            const addr = (this.activeIdentity.address || 'Gerbang Kuning Gudang Bumbu, Jalan Ceuri no 51 Kampung Sindang Asih, Katapang, Pamentasan, Kabupaten Bandung, Jawa Barat 40921');
            // Split into ~3 lines for layout consistency
            const parts = String(addr).split(',').map(s => s.trim());
            return [
                parts.slice(0, 3).join(', '),
                parts.slice(3, 6).join(', '),
                parts.slice(6).join(', ')
            ];
        },

        getInvoiceNo(sale) {
            try {
                const d = sale && sale.periode ? new Date(sale.periode) : new Date();
                const y = d.getFullYear();
                const m = String(d.getMonth() + 1).padStart(2, '0');
                const id = String((sale && (sale.id_pesanan || sale.id)) || '0001').replace(/\s+/g, '');
                return `INV-${y}${m}-${id}`;
            } catch (_) {
                return `INV-${Date.now()}`;
            }
        },
        
        async approvePO(poId) {
            if (!confirm('Apakah Anda yakin ingin approve PO ini?')) return;
            
            try {
                const response = await fetch(`/admin/purchase-orders/${poId}/approve`, {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    await this.loadPurchaseOrders();
                    this.showDetailModal = false;
                    this.showSuccessNotification('PO berhasil diapprove!');
                }
            } catch (error) {
                console.error('Error approving PO:', error);
                this.showErrorNotification('Gagal approve PO');
            }
        },
        
        async markAsPaid(poId) {
            if (!confirm('Apakah Anda yakin ingin menandai PO ini sebagai sudah dibayar?')) return;
            
            try {
                const response = await fetch(`/admin/purchase-orders/${poId}/payment`, {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    await this.loadPurchaseOrders();
                    this.showDetailModal = false;
                    this.showSuccessNotification('PO berhasil ditandai sebagai sudah dibayar!');
                }
            } catch (error) {
                console.error('Error marking PO as paid:', error);
                this.showErrorNotification('Gagal update payment status PO');
            }
        },
        
        async markAsReceived(poId, { silent = false } = {}) {
            if (!silent) {
                if (!confirm('Apakah Anda yakin ingin menandai PO ini sebagai received?')) return;
            }
            
            try {
                const response = await fetch(`/admin/purchase-orders/${poId}/received`, {
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
                }
            } catch (error) {
                console.error('Error marking PO as received:', error);
                if (!silent) this.showErrorNotification('Gagal update status PO');
            }
        },
        
        // Auto mark as received jika sudah melewati 14 hari sejak dibuat,
        // status masih Approved dan payment sudah Lunas
        async autoMarkOverdueAsReceived() {
            try {
                const now = new Date();
                const overduePOs = (this.purchaseOrders || []).filter(po => {
                    if (!po || !po.created_at) return false;
                    if (po.status !== 'approved') return false;
                    if (po.payment_status !== 'paid') return false;
                    const created = new Date(po.created_at);
                    const diffDays = Math.floor((now - created) / (1000 * 60 * 60 * 24));
                    return diffDays >= 14;
                });
                for (const po of overduePOs) {
                    // Skip bila sudah received karena data mungkin belum ter-refresh
                    if (po.status === 'received') continue;
                    await this.markAsReceived(po.id, { silent: true });
                }
            } catch (e) {
                // noop: auto proses bersifat best-effort
            }
        },
        async rejectPO(poId) {
            if (!confirm('Tolak PO ini? Status akan menjadi Rejected dan tetap tampil sampai dihapus.')) return;
            try {
                const response = await fetch(`/admin/purchase-orders/${poId}/reject`, {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                    }
                });
                if (response.ok) {
                    // Update status lokal menjadi rejected, jangan hilangkan dari list
                    this.purchaseOrders = this.purchaseOrders.map(po => po.id === poId ? { ...po, status: 'rejected', payment_status: 'unpaid' } : po);
                    if (this.selectedPO && this.selectedPO.id === poId) {
                        this.selectedPO.status = 'rejected';
                        this.selectedPO.payment_status = 'unpaid';
                    }
                    this.showSuccessNotification('PO berhasil direject');
                }
            } catch (e) {
                console.error('Error rejecting PO:', e);
                this.showErrorNotification('Gagal reject PO');
            }
        },

        async deletePOAdmin(poId) {
            if (!confirm('Hapus PO berstatus Rejected ini? Tindakan tidak dapat dibatalkan.')) return;
            try {
                const response = await fetch(`/admin/purchase-orders/${poId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                    }
                });
                if (response.ok) {
                    this.purchaseOrders = this.purchaseOrders.filter(po => po.id !== poId);
                    if (this.selectedPO && this.selectedPO.id === poId) {
                        this.showDetailModal = false;
                        this.selectedPO = null;
                    }
                    this.showSuccessNotification('PO berhasil dihapus');
                } else {
                    this.showErrorNotification('Gagal menghapus PO');
                }
            } catch (e) {
                console.error('Error deleting PO:', e);
                this.showErrorNotification('Gagal menghapus PO');
            }
        },
        
        getStatusClass(status) {
            const classes = {
                'pending': 'bg-yellow-100 text-yellow-800',
                'approved': 'bg-blue-100 text-blue-800',
                'received': 'bg-green-100 text-green-800',
                'rejected': 'bg-red-100 text-red-800'
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        },
        
        getStatusText(status) {
            const texts = {
                'pending': 'Pending',
                'approved': 'Approved',
                'received': 'Received',
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
        
        exportExcel() {
            const excelData = this.filteredPOs.map(po => ({
                'No. PO': po.po_number,
                'Client': po.client_name,
                'Tanggal': this.formatDate(po.created_at),
                'Items': po.items.length + ' items',
                'Total': `Rp ${this.formatNumber(po.total_amount)}`,
                'Status': this.getStatusText(po.status),
                'Payment': po.payment_status === 'paid' ? 'Lunas' : 'Belum Lunas'
            }));

            const worksheet = XLSX.utils.json_to_sheet(excelData);
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, "Purchase Orders");
            XLSX.writeFile(workbook, "purchase_orders.xlsx");
        },
        
        exportPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            const tableColumn = ["No. PO", "Client", "Tanggal", "Items", "Total", "Status"];
            const tableRows = this.filteredPOs.map(po => [
                po.po_number,
                po.client_name,
                this.formatDate(po.created_at),
                po.items.length + ' items',
                `Rp ${this.formatNumber(po.total_amount)}`,
                this.getStatusText(po.status)
            ]);

            doc.autoTable({
                head: [tableColumn],
                body: tableRows,
                startY: 20,
                styles: { fontSize: 8, cellPadding: 2 },
                headStyles: { fillColor: [40, 195, 40], textColor: 255 }
            });
            doc.save("purchase_orders.pdf");
        },

        async loadCompanyIdentity() {
            try {
                const res = await fetch('/admin/identity', { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const data = await res.json();
                this.company = {
                    name: 'GAFI Admin',
                    phone: data.telepon || '',
                    email: data.email || '',
                    address: data.alamat || '',
                    logoUrl: '', // Admin tidak punya logo upload
                    bank: data.bank || '',
                    no_rekening: data.no_rekening || ''
                };
            } catch (e) {
                // ignore
            }
        },

        // async exportPOInvoicePDF() {
        //     if (!this.selectedPO) return;
            
        //     const { jsPDF } = window.jspdf;
        //     const doc = new jsPDF({ unit: 'pt', format: 'a4' });

        //     // Layout constants
        //     const margin = 40;
        //     const line = (y) => doc.line(margin, y, doc.internal.pageSize.getWidth() - margin, y);
        //     const rightX = doc.internal.pageSize.getWidth() - margin;

        //     // Company block
        //     doc.setFontSize(12);
        //     doc.setFont(undefined, 'bold');
        //     doc.text(this.company.name || 'GAFI Admin', margin, margin + 12);
        //     doc.setFont(undefined, 'normal');
        //     doc.setFontSize(10);
        //     if (this.company.address) doc.text(this.company.address, margin, margin + 28);
        //     const contact = `${this.company.phone ? 'Telp: ' + this.company.phone : ''}${this.company.phone && this.company.email ? '  |  ' : ''}${this.company.email ? 'Email: ' + this.company.email : ''}`;
        //     if (contact.trim()) doc.text(contact, margin, margin + 44);

        //     // PO title and meta
        //     doc.setFontSize(16);
        //     doc.setFont(undefined, 'bold');
        //     doc.text('PURCHASE ORDER', rightX, margin + 12, { align: 'right' });
        //     doc.setFontSize(10);
        //     doc.setFont(undefined, 'normal');
        //     const metaY = margin + 28;
        //     doc.text(`No. PO: ${this.selectedPO.po_number}`, rightX, metaY, { align: 'right' });
        //     doc.text(`Tanggal: ${this.formatDate(this.selectedPO.created_at)}`, rightX, metaY + 14, { align: 'right' });
        //     doc.text(`Status: ${this.getStatusText(this.selectedPO.status)}`, rightX, metaY + 28, { align: 'right' });
        //     doc.text(`Payment: ${this.selectedPO.payment_status === 'paid' ? 'Lunas' : 'Belum Lunas'}`, rightX, metaY + 42, { align: 'right' });

        //     // Separator
        //     const sepY = metaY + 56;
        //     line(sepY);

        //     // Client info
        //     doc.setFontSize(10);
        //     doc.setFont(undefined, 'bold');
        //     const clientStartY = sepY + 18;
        //     doc.text('Kepada:', margin, clientStartY);
        //     doc.setFont(undefined, 'normal');
        //     doc.text(this.selectedPO.client_name, margin, clientStartY + 16);
        //     doc.text(this.selectedPO.client_email, margin, clientStartY + 30);
        //     if (this.selectedPO.client_phone) doc.text(this.selectedPO.client_phone, margin, clientStartY + 44);

        //     // Items table
        //     const tableColumn = ['Nama Item', 'SKU', 'Quantity', 'Harga Satuan', 'Subtotal'];
        //     const tableRows = (this.selectedPO.items || []).map(item => [
        //         item.item_name + (item.item_type === 'external' ? ' (Luar)' : ' (Stock)'),
        //         item.sku || '-',
        //         item.quantity + ' pcs',
        //         'Rp ' + this.formatNumber(item.unit_price),
        //         'Rp ' + this.formatNumber(item.subtotal)
        //     ]);

        //     doc.autoTable({
        //         head: [tableColumn],
        //         body: tableRows,
        //         startY: clientStartY + 70,
        //         margin: { left: margin, right: margin },
        //         styles: { fontSize: 9, cellPadding: 6, valign: 'middle' },
        //         headStyles: { fillColor: [40, 195, 40], textColor: 255, fontStyle: 'bold' },
        //         columnStyles: {
        //             2: { halign: 'center' },
        //             3: { halign: 'right' },
        //             4: { halign: 'right' }
        //         }
        //     });

        //     // Summary block
        //     const afterTableY = doc.lastAutoTable.finalY + 16;
        //     doc.setFont(undefined, 'bold');
        //     doc.setFontSize(12);
        //     doc.text('Total Purchase Order:', rightX - 180, afterTableY + 20);
        //     doc.text('Rp ' + this.formatNumber(this.selectedPO.total_amount), rightX, afterTableY + 20, { align: 'right' });

        //     // Footer note
        //     doc.setFont(undefined, 'normal');
        //     doc.setFontSize(9);
        //     const footerY = afterTableY + 50;
        //     doc.text('* Purchase Order ini sah tanpa tanda tangan dan dicetak otomatis oleh sistem GAFI.', margin, footerY);
        //     const bankInfo = this.company.bank && this.company.no_rekening ? 
        //         `Pembayaran ke: ${this.company.bank} - ${this.company.no_rekening}` : 
        //         'Pembayaran ke: BCA - 64835868';
        //     doc.text(bankInfo, margin, footerY + 14);

        //     doc.save('PO_' + this.selectedPO.po_number + '.pdf');
        // },

        // Date filter methods
        applyDateFilter() {
            // Trigger reactivity by accessing filteredPOs
            this.filteredPOs;
        },

        clearDateFilter() {
            this.dateFilter = '';
            this.customStartDate = '';
            this.customEndDate = '';
        },

        getFilterStatusText() {
            switch (this.dateFilter) {
                case 'today': return 'Hari Ini';
                case 'week': return 'Minggu Ini';
                case 'month': return 'Bulan Ini';
                case 'year': return 'Tahun Ini';
                case 'custom': return 'Custom';
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
                    endDate = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59);
                    break;
                case 'week':
                    const startOfWeek = new Date(now);
                    startOfWeek.setDate(now.getDate() - now.getDay());
                    startDate = new Date(startOfWeek.getFullYear(), startOfWeek.getMonth(), startOfWeek.getDate());
                    endDate = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59);
                    break;
                case 'month':
                    startDate = new Date(now.getFullYear(), now.getMonth(), 1);
                    endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59);
                    break;
                case 'year':
                    startDate = new Date(now.getFullYear(), 0, 1);
                    endDate = new Date(now.getFullYear(), 11, 31, 23, 59, 59);
                    break;
                case 'custom':
                    if (!this.customStartDate || !this.customEndDate) return pos;
                    startDate = new Date(this.customStartDate);
                    endDate = new Date(this.customEndDate);
                    endDate.setHours(23, 59, 59, 999);
                    break;
                default: return pos;
            }

            return pos.filter(po => {
                const poDate = new Date(po.created_at);
                return poDate >= startDate && poDate <= endDate;
            });
        },
        
        // Notification methods
        showSuccessNotification(message) {
            window.dispatchEvent(new CustomEvent('show-notification', {
                detail: { type: 'success', message: message }
            }));
        },
        
        showErrorNotification(message) {
            window.dispatchEvent(new CustomEvent('show-notification', {
                detail: { type: 'error', message: message }
            }));
        }
    }
}
</script>
@endsection

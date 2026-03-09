@extends('layouts.client')

@section('content')
<style>
    [x-cloak] { display: none !important; }
    
    /* Custom notification styles */
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        max-width: 400px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        transform: translateX(100%);
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }
    
    .notification.show {
        transform: translateX(0);
    }
    
    .notification.success {
        background: linear-gradient(135deg, #10b981, #059669);
        border-left: 4px solid #047857;
    }
    
    .notification.error {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        border-left: 4px solid #b91c1c;
    }
    
    .notification.info {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        border-left: 4px solid #1d4ed8;
    }
    
    .notification-content {
        padding: 16px 20px;
        color: white;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .notification-icon {
        width: 24px;
        height: 24px;
        flex-shrink: 0;
    }
    
    .notification-text {
        flex: 1;
    }
    
    .notification-title {
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 2px;
    }
    
    .notification-message {
        font-size: 13px;
        opacity: 0.9;
    }
    
    .notification-close {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    
    .notification-close:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.1);
    }
    
    /* Improved delete confirmation modal */
    .delete-modal {
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(8px);
    }
    
    .delete-modal-content {
        background: white;
        border-radius: 20px;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.2);
        overflow: hidden;
        transform: scale(0.9);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    
    .delete-modal.show .delete-modal-content {
        transform: scale(1);
    }
    
    .delete-modal-header {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        padding: 24px;
        text-align: center;
        color: white;
    }
    
    .delete-modal-icon {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
    }
    
    .delete-modal-title {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 8px;
    }
    
    .delete-modal-subtitle {
        font-size: 14px;
        opacity: 0.9;
    }
    
    .delete-modal-body {
        padding: 24px;
    }
    
    .delete-modal-message {
        text-align: center;
        margin-bottom: 24px;
    }
    
    .delete-modal-question {
        font-size: 16px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 16px;
    }
    
    .delete-modal-details {
        background: #f9fafb;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 20px;
        border: 1px solid #e5e7eb;
    }
    
    .delete-modal-detail-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .delete-modal-detail-item:last-child {
        border-bottom: none;
    }
    
    .delete-modal-detail-label {
        font-size: 14px;
        color: #6b7280;
        font-weight: 500;
    }
    
    .delete-modal-detail-value {
        font-size: 14px;
        color: #111827;
        font-weight: 600;
    }
    
    .delete-modal-warning {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 8px;
        padding: 12px;
        text-align: center;
        margin-bottom: 24px;
    }
    
    .delete-modal-warning-icon {
        color: #dc2626;
        margin-right: 8px;
    }
    
    .delete-modal-warning-text {
        color: #991b1b;
        font-size: 13px;
        font-weight: 500;
    }
    
    .delete-modal-actions {
        display: flex;
        gap: 12px;
    }
    
    .delete-modal-btn {
        flex: 1;
        padding: 12px 20px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }
    
    .delete-modal-btn-cancel {
        background: #f3f4f6;
        color: #6b7280;
        border: 1px solid #d1d5db;
    }
    
    .delete-modal-btn-cancel:hover {
        background: #e5e7eb;
        border-color: #9ca3af;
    }
    
    .delete-modal-btn-confirm {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }
    
    .delete-modal-btn-confirm:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
    }
</style>

<div class="bg-white rounded-xl shadow p-8" x-data="salesHistory()" x-init="initRealtime()">
    <h1 class="text-2xl font-bold text-[#28C328] mb-6">Riwayat Transaksi</h1>
    
    <!-- Custom Notification Component -->
    <div x-show="showNotification" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-x-full"
         x-transition:enter-end="opacity-100 transform translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-x-0"
         x-transition:leave-end="opacity-0 transform translate-x-full"
         class="notification show"
         :class="notificationType === 'success' ? 'success' : notificationType === 'error' ? 'error' : 'info'"
         x-cloak>
        <div class="notification-content">
            <div class="notification-icon">
                <svg x-show="notificationType === 'success'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <svg x-show="notificationType === 'error'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <svg x-show="notificationType === 'info'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="notification-text">
                <div class="notification-title" x-text="notificationTitle"></div>
                <div class="notification-message" x-text="notificationMessage"></div>
            </div>
            <button @click="showNotification = false" class="notification-close">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>

    <div class="mb-6">
        <div class="flex flex-wrap items-center gap-2 justify-between">
            <div class="flex flex-1 gap-2 items-center">
                <!-- Search -->
                <div class="w-64">
                    <div class="flex items-center border border-gray-300 rounded-lg px-4 py-1 bg-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#28C328] mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" /></svg>
                        <input type="text" placeholder="Cari Order Number" x-model="search" @input.debounce.500ms="onSearchChange()" class="flex-1 bg-transparent border-none outline-none text-gray-400 text-sm font-medium placeholder-gray-400 h-6">
                    </div>
                </div>
                
                <!-- Filter Tanggal (preset + custom) -->
                <div class="flex gap-2 items-center">
                    <select x-model="dateFilter" @change="applyDateFilter()" class="rounded-lg border border-gray-300 px-3 py-1 text-gray-700 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-[#28C328] focus:border-transparent">
                        <option value="">Semua Tanggal</option>
                        <option value="today">Hari Ini</option>
                        <option value="week">Minggu Ini</option>
                        <option value="month">Bulan Ini</option>
                        <option value="year">Tahun Ini</option>
                        <option value="custom">Custom Range</option>
                    </select>
                    <div x-show="dateFilter === 'custom'" class="flex gap-2 items-center">
                        <input type="date" x-model="customStartDate" @change="applyDateFilter()" class="rounded-lg border border-gray-300 px-3 py-1 text-gray-700 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-[#28C328] focus:border-transparent" placeholder="Tanggal Mulai">
                        <span class="text-gray-500">sampai</span>
                        <input type="date" x-model="customEndDate" @change="applyDateFilter()" class="rounded-lg border border-gray-300 px-3 py-1 text-gray-700 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-[#28C328] focus:border-transparent" placeholder="Tanggal Akhir">
                    </div>
                    <button x-show="dateFilter" @click="clearDateFilter()" class="px-3 py-1 bg-red-500 text-white rounded-lg hover:bg-red-600 transition text-sm flex items-center gap-1" title="Hapus Filter Tanggal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        Clear
                    </button>
                </div>
                
                <!-- Filter Status -->
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button type="button" @click="open = !open" class="flex items-center justify-between w-36 px-4 py-1 border border-gray-300 rounded-lg bg-white text-gray-700 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#28C328] focus:border-[#28C328] transition">
                        <span class="flex-1 text-left" x-text="status === '' ? 'Status' : status"></span>
                        <span class="h-5 border-l border-gray-300 mx-2"></span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-transition class="absolute z-10 mt-2 w-36 bg-white rounded-2xl shadow-lg border border-gray-100">
                        <div @click="status = ''; open = false; onSearchChange()" :class="{'bg-[#eafbe6] text-[#28C328]': status === ''}" class="px-4 py-2 cursor-pointer hover:bg-[#eafbe6] rounded-2xl">Semua Status</div>
                        <template x-for="s in statuses" :key="s">
                            <div @click="status = s; open = false; onSearchChange()" :class="{'bg-[#eafbe6] text-[#28C328]': status === s}" class="px-4 py-2 cursor-pointer hover:bg-[#eafbe6] rounded-2xl" x-text="s"></div>
                        </template>
                    </div>
                </div>
            </div>
            
            <div class="flex gap-2 ml-auto items-center">
                <button class="rounded-lg bg-[#28C328] px-4 py-2 text-white text-sm font-semibold flex items-center gap-2 hover:bg-[#22a322] transition" @click="exportExcel">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 2h8v4H8z" /></svg>
                    Excel
                </button>
                <button class="rounded-lg bg-[#28C328] px-4 py-2 text-white text-sm font-semibold flex items-center gap-2 hover:bg-[#22a322] transition" @click="exportPDF">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20h9" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    PDF
                </button>
                <button class="rounded-lg bg-[#28C328] px-4 py-2 text-white text-sm font-semibold flex items-center gap-2 hover:bg-[#22a322] transition" @click="showAddModal = true; resetAddForm()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                     Tambahkan Data
                </button>
            </div>
        </div>
        <!-- Filter status indicator -->
        <div x-show="dateFilter" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-center gap-2 text-sm text-blue-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z" /></svg>
                <span x-text="getFilterStatusText()"></span>
            </div>
        </div>
        <!-- Loading banner removed for cleaner UX -->

        <div x-show="error" class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-red-700" x-text="error"></span>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto mt-4" x-show="!loading && !error">
            <table class="min-w-full text-sm text-center">
                <thead>
                    <tr class="bg-[#28C328] text-white">
                        <th class="p-3 cursor-pointer select-none rounded-tl-xl" @click="sortBy('order_number')">
                            <div class="flex items-center gap-1 justify-center">
                                <span>Order Number</span>
                                <span class="flex flex-col">
                                    <svg :class="{'opacity-100': sortKey==='order_number' && sortAsc, 'opacity-50': !(sortKey==='order_number' && sortAsc)}" class="w-3 h-3" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                    <svg :class="{'opacity-100': sortKey==='order_number' && !sortAsc, 'opacity-50': !(sortKey==='order_number' && !sortAsc)}" class="w-3 h-3 -mt-1" fill="white" viewBox="0 0 20 20"><path d="M10 14l4-4H6l4 4z"/></svg>
                                </span>
                            </div>
                        </th>
                        <th class="p-3 cursor-pointer select-none" @click="sortBy('sale_date')">
                            <div class="flex items-center gap-1 justify-center">
                                <span>Tanggal</span>
                                <span class="flex flex-col">
                                    <svg :class="{'opacity-100': sortKey==='sale_date' && sortAsc, 'opacity-50': !(sortKey==='sale_date' && sortAsc)}" class="w-3 h-3" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                    <svg :class="{'opacity-100': sortKey==='sale_date' && !sortAsc, 'opacity-50': !(sortKey==='sale_date' && !sortAsc)}" class="w-3 h-3 -mt-1" fill="white" viewBox="0 0 20 20"><path d="M10 14l4-4H6l4 4z"/></svg>
                                </span>
                            </div>
                        </th>
                        <th class="p-3 cursor-pointer select-none" @click="sortBy('quantity')">
                            <div class="flex items-center gap-1 justify-center">
                                <span>Item Name</span>
                                <span class="flex flex-col">
                                    <svg :class="{'opacity-100': sortKey==='total_items' && sortAsc, 'opacity-50': !(sortKey==='total_items' && sortAsc)}" class="w-3 h-3" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                    <svg :class="{'opacity-100': sortKey==='total_items' && !sortAsc, 'opacity-50': !(sortKey==='total_items' && !sortAsc)}" class="w-3 h-3 -mt-1" fill="white" viewBox="0 0 20 20"><path d="M10 14l4-4H6l4 4z"/></svg>
                                </span>
                            </div>
                        </th>
                        <th class="p-3 cursor-pointer select-none" @click="sortBy('total_items')">
                            <div class="flex items-center gap-1 justify-center">
                                <span>Total Items</span>
                                <span class="flex flex-col">
                                    <svg :class="{'opacity-100': sortKey==='total_items' && sortAsc, 'opacity-50': !(sortKey==='total_items' && sortAsc)}" class="w-3 h-3" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                    <svg :class="{'opacity-100': sortKey==='total_items' && !sortAsc, 'opacity-50': !(sortKey==='total_items' && !sortAsc)}" class="w-3 h-3 -mt-1" fill="white" viewBox="0 0 20 20"><path d="M10 14l4-4H6l4 4z"/></svg>
                                </span>
                            </div>
                        </th>
                        <th class="p-3 cursor-pointer select-none" @click="sortBy('quantity')">
                            <div class="flex items-center gap-1 justify-center">
                                <span>Quantity</span>
                                <span class="flex flex-col">
                                    <svg :class="{'opacity-100': sortKey==='total_items' && sortAsc, 'opacity-50': !(sortKey==='total_items' && sortAsc)}" class="w-3 h-3" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                    <svg :class="{'opacity-100': sortKey==='total_items' && !sortAsc, 'opacity-50': !(sortKey==='total_items' && !sortAsc)}" class="w-3 h-3 -mt-1" fill="white" viewBox="0 0 20 20"><path d="M10 14l4-4H6l4 4z"/></svg>
                                </span>
                            </div>
                        </th>
                        <th class="p-3 cursor-pointer select-none" @click="sortBy('total_amount')">
                            <div class="flex items-center gap-1 justify-center">
                                <span>Total Amount</span>
                                <span class="flex flex-col">
                                    <svg :class="{'opacity-100': sortKey==='total_amount' && sortAsc, 'opacity-50': !(sortKey==='total_amount' && sortAsc)}" class="w-3 h-3" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                    <svg :class="{'opacity-100': sortKey==='total_amount' && !sortAsc, 'opacity-50': !(sortKey==='total_amount' && !sortAsc)}" class="w-3 h-3 -mt-1" fill="white" viewBox="0 0 20 20"><path d="M10 14l4-4H6l4 4z"/></svg>
                                </span>
                            </div>
                        </th>
                        <th class="p-3 cursor-pointer select-none" @click="sortBy('payment_method')">
                            <div class="flex items-center gap-1 justify-center">
                                <span>Payment Method</span>
                                <span class="flex flex-col">
                                    <svg :class="{'opacity-100': sortKey==='payment_method' && sortAsc, 'opacity-50': !(sortKey==='payment_method' && sortAsc)}" class="w-3 h-3" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                    <svg :class="{'opacity-100': sortKey==='payment_method' && !sortAsc, 'opacity-50': !(sortKey==='payment_method' && !sortAsc)}" class="w-3 h-3 -mt-1" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                </span>
                            </div>
                        </th>
                        <th class="p-3 cursor-pointer select-none" @click="sortBy('status')">
                            <div class="flex items-center gap-1 justify-center">
                                <span>Status</span>
                                <span class="flex flex-col">
                                    <svg :class="{'opacity-100': sortKey==='status' && sortAsc, 'opacity-50': !(sortKey==='status' && sortAsc)}" class="w-3 h-3" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                    <svg :class="{'opacity-100': sortKey==='status' && !sortAsc, 'opacity-50': !(sortKey==='status' && !sortAsc)}" class="w-3 h-3 -mt-1" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                </span>
                            </div>
                        </th>
                        <th class="p-3 rounded-tr-xl">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100 text-center align-middle">
                    <template x-for="(sale, idx) in paginatedSales" :key="sale.id">
                        <tr>
                            <td class="p-3 align-middle">
                                <span class="font-mono text-sm" x-text="sale.order_number"></span>
                            </td>
                            <td class="p-3 align-middle" x-text="new Date(sale.sale_date).toLocaleDateString('id-ID')"></td>
                            <td class="p-3 align-middle" x-text="sale.items[0].item_name"></td>
                            <td class="p-3 align-middle" x-text="sale.total_items"></td>
                            <td class="p-3 align-middle" x-text="sale.items[0].quantity"></td>
                            <td class="p-3 align-middle">Rp<span x-text="Number(sale.total_amount).toLocaleString('id-ID')"></span></td>
                            <td class="p-3 align-middle" x-text="sale.payment_method"></td>
                            <td class="p-3 align-middle">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800" x-text="sale.status === 'completed' ? 'Selesai' : sale.status"></span>
                            </td>
                            <td class="p-3 align-middle">
                                <div class="relative">
                                    <button @click="openActionMenuIndex = openActionMenuIndex === idx ? null : idx" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="6" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="18" r="1.5"/></svg>
                                    </button>
                                    <div x-show="openActionMenuIndex === idx" x-transition class="absolute right-0 mt-2 w-32 bg-white rounded-xl shadow-lg border border-gray-100 z-10">
                                        <button @click="showSaleDetail(sale); openActionMenuIndex = null" class="block w-full text-left px-4 py-2 hover:bg-[#eafbe6] rounded-t-xl">Detail</button>
                                        <button @click="$dispatch('edit-status', sale); openActionMenuIndex = null" class="block w-full text-left px-4 py-2 hover:bg-[#eafbe6] text-blue-600">Edit Status</button>
                                        <button @click="deleteSale(sale); openActionMenuIndex = null" class="block w-full text-left px-4 py-2 hover:bg-[#ffeaea] text-red-600 rounded-b-xl">Hapus</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="flex justify-center mt-4">
            <nav class="flex items-center space-x-2">
                <button @click="prevPage" :disabled="currentPage === 1" class="w-8 h-8 flex items-center justify-center rounded-full border border-gray-300 text-gray-500 hover:bg-gray-100 disabled:opacity-50">
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
                
                <button @click="nextPage" :disabled="currentPage === totalPages" class="w-8 h-8 flex items-center justify-center rounded-full border border-gray-300 text-gray-500 hover:bg-gray-100 disabled:opacity-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </button>
            </nav>
        </div>
    </div>

    <!-- Modal Tambah Data Sales - Tahap 1 (Data Transaksi) -->
    <div x-show="showAddModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" x-cloak>
        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-2xl mx-2 relative overflow-y-auto max-h-[70vh]">
            <button @click="closeAddModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold text-[#28C328]">Tahap 1: Data Transaksi</h2>
                <p class="text-gray-600 text-sm">Lengkapi informasi transaksi terlebih dahulu</p>
            </div>
            <form class="grid grid-cols-1 md:grid-cols-2 gap-4" @submit.prevent="nextToStep2()">
                <div x-show="addErrorMsg" class="col-span-1 md:col-span-2 text-red-500 text-sm mb-2" x-text="addErrorMsg"></div>
                <div>
                    <label class="block font-semibold mb-2">Order Number</label>
                    <input type="text" x-model="addOrderNumber" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400" placeholder="Masukkan Order Number">
                </div>
                <div>
                    <label class="block font-semibold mb-2">Tanggal</label>
                    <input type="date" x-model="addSaleDate" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700">
                </div>
                <div>
                    <label class="block font-semibold mb-2">Payment Method</label>
                    <select x-model="addPaymentMethod" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700">
                        <option value="">Pilih Metode</option>
                        <option value="Cash">Cash</option>
                        <option value="Transfer">Transfer</option>
                        <option value="Card">Card</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold mb-2">Status</label>
                    <select x-model="addStatus" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700">
                        <option value="completed">Selesai</option>
                        <option value="pending">Pending</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold mb-2">No Telepon Pelanggan</label>
                    <input type="text" x-model="addCustomerPhone" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400" placeholder="Contoh: 0812-3456-7890">
                </div>
                <div class="md:col-span-2">
                    <label class="block font-semibold mb-2">Alamat Pelanggan</label>
                    <textarea x-model="addCustomerAddress" rows="3" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400 resize-none" placeholder="Contoh: Jl. Melati no 10, Bandung"></textarea>
                </div>
                <!-- Tombol bawah -->
                <div class="col-span-2 flex flex-col md:flex-row gap-2 mt-2">
                    <button type="submit" :disabled="loading" class="w-full rounded-lg bg-[#28C328] text-white font-semibold py-3 text-lg hover:bg-[#22a322] transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!loading">Selanjutnya</span>
                        <span x-show="loading" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Loading...
                        </span>
                    </button>
                </div>
                <div class="col-span-2 flex flex-col md:flex-row gap-2">
                    <button type="reset" @click.prevent="resetAddForm()" class="w-full rounded-lg bg-gray-200 text-gray-500 font-semibold py-3 text-lg">Reset</button>
                    <button type="button" @click="closeAddModal()" class="w-full rounded-lg bg-gray-200 text-gray-500 font-semibold py-3 text-lg">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tambah Data Sales - Tahap 2 (Data Pesanan) -->
    <div x-show="showAddStep2Modal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" x-cloak>
        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-4xl mx-2 relative overflow-y-auto max-h-[80vh]">
            <button @click="backToStep1()" class="absolute top-4 left-4 text-gray-400 hover:text-gray-600 text-2xl font-bold">&larr;</button>
            <button @click="closeAddModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold text-[#28C328]">Tahap 2: Data Pesanan</h2>
                <p class="text-gray-600 text-sm">Pilih item yang akan dibeli</p>
            </div>
            
            <!-- Error Message -->
            <div x-show="addErrorMsg" class="col-span-1 md:col-span-2 text-red-500 text-sm mb-4 bg-red-50 p-3 rounded-lg" x-text="addErrorMsg"></div>

            <!-- Informasi Transaksi -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-gray-800 mb-2">Informasi Transaksi:</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
                    <div><span class="text-gray-600">Order Number:</span> <span class="font-semibold" x-text="addOrderNumber"></span></div>
                    <div><span class="text-gray-600">Tanggal:</span> <span class="font-semibold" x-text="addSaleDate"></span></div>
                    <div><span class="text-gray-600">Payment:</span> <span class="font-semibold" x-text="addPaymentMethod"></span></div>
                    <div><span class="text-gray-600">Status:</span> <span class="font-semibold" x-text="addStatus"></span></div>
                    <div><span class="text-gray-600">Telepon:</span> <span class="font-semibold" x-text="addCustomerPhone || '-'"></span></div>
                    <div class="md:col-span-3"><span class="text-gray-600">Alamat:</span> <span class="font-semibold" x-text="addCustomerAddress || '-'"></span></div>
                </div>
            </div>

            <!-- Input Diskon -->
            <div class="bg-blue-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-blue-800 mb-3">Pengaturan Diskon</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-semibold mb-2 text-sm">Tipe Diskon</label>
                        <select x-model="addDiskonTipe" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="rupiah">Rupiah</option>
                            <option value="persen">Persen (%)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold mb-2 text-sm">Nilai Diskon</label>
                        <input type="number" x-model="addDiskonNilai" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="0" min="0">
                    </div>
                    <div class="flex items-end">
                        <button type="button" @click="hitungDiskon()" class="w-full rounded-lg bg-blue-500 text-white font-semibold py-2 text-sm hover:bg-blue-600 transition">Hitung Diskon</button>
                    </div>
                </div>
                <div x-show="addDiskonNilai > 0" class="mt-2 text-sm">
                    <span class="text-blue-700">Diskon: </span>
                    <span x-show="addDiskonTipe === 'rupiah'" class="font-semibold text-blue-800">Rp <span x-text="Number(addDiskonNilai).toLocaleString('id-ID')"></span></span>
                    <span x-show="addDiskonTipe === 'persen'" class="font-semibold text-blue-800"><span x-text="addDiskonNilai"></span>%</span>
                </div>
            </div>

            <!-- Daftar Item Stok -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-gray-800">Pilih Item dari Stok:</h3>
                    <div class="flex items-center bg-gray-50 rounded-lg px-3 py-2 border w-64">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z" />
                        </svg>
                        <input type="text" x-model="stockItemSearch" class="flex-1 bg-transparent border-none outline-none text-sm" placeholder="Cari item berdasarkan nama atau SKU..." />
                    </div>
                </div>
                <div x-show="filteredStockItems.length === 0" class="text-center text-gray-500 py-8">
                    <div class="text-lg font-semibold mb-2" x-show="!stockItemSearch">Tidak ada stok tersedia</div>
                    <div class="text-sm" x-show="!stockItemSearch">Silakan tambahkan stok terlebih dahulu</div>
                    <div class="text-lg font-semibold mb-2" x-show="stockItemSearch">Tidak ada item yang cocok</div>
                    <div class="text-sm" x-show="stockItemSearch">Coba kata kunci yang berbeda</div>
                </div>
                <div x-show="filteredStockItems.length > 0" class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#28C328] text-white">
                                <th class="p-2 text-left">Gambar</th>
                                <th class="p-2 text-left">Nama Item</th>
                                <th class="p-2 text-left">SKU</th>
                                <th class="p-2 text-left">Stok Tersedia</th>
                                <th class="p-2 text-left">Harga Satuan</th>
                                <th class="p-2 text-left">Quantity</th>
                                <th class="p-2 text-left">Subtotal</th>
                                <th class="p-2 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <template x-for="item in filteredStockItems" :key="item.id">
                                <tr>
                                    <td class="p-2">
                                        <img :src="item.gambar || '/images/default-item.svg'" class="w-8 h-8 rounded object-cover" alt="item">
                                    </td>
                                    <td class="p-2" x-text="item.nama"></td>
                                    <td class="p-2 font-mono text-xs" x-text="item.sku"></td>
                                    <td class="p-2" x-text="Number(item.tersedia).toLocaleString('id-ID')"></td>
                                    <td class="p-2">Rp<span x-text="Number(item.harga).toLocaleString('id-ID')"></span></td>
                                    <td class="p-2">
                                        <input type="number" 
                                               x-model="item.selectedQuantity" 
                                               @input="updateItemSubtotal(item)"
                                               class="w-16 rounded border px-2 py-1 text-sm" 
                                               :class="item.selectedQuantity > item.tersedia ? 'border-red-500 bg-red-50' : 'border-gray-300'"
                                               min="0" 
                                               :max="item.tersedia"
                                               placeholder="0">
                                        <div x-show="item.selectedQuantity > item.tersedia" class="text-xs text-red-500 mt-1">
                                            Melebihi stok
                                        </div>
                                    </td>
                                    <td class="p-2 font-semibold">
                                        <span x-show="item.selectedQuantity > 0">Rp<span x-text="(Number(item.harga) * Number(item.selectedQuantity || 0)).toLocaleString('id-ID')"></span></span>
                                        <span x-show="item.selectedQuantity <= 0" class="text-gray-400">-</span>
                                    </td>
                                    <td class="p-2">
                                        <button type="button" 
                                                @click="addItemToCart(item)" 
                                                :disabled="!item.selectedQuantity || item.selectedQuantity <= 0 || item.selectedQuantity > item.tersedia"
                                                class="px-3 py-1 rounded text-xs font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                                                :class="item.selectedQuantity > 0 && item.selectedQuantity <= item.tersedia ? 'bg-[#28C328] text-white hover:bg-[#22a322]' : 'bg-gray-300 text-gray-500'">
                                            Tambah
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Keranjang Pesanan -->
            <div class="bg-green-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-green-800 mb-3">Keranjang Pesanan:</h3>
                <div x-show="cartItems.length === 0" class="text-center text-gray-500 py-4">
                    Belum ada item yang dipilih
                </div>
                <div x-show="cartItems.length > 0" class="space-y-3">
                    <template x-for="(cartItem, idx) in cartItems" :key="cartItem.id">
                        <div class="grid grid-cols-12 gap-3 items-center bg-white rounded-lg p-3 border border-green-200">
                            <div class="col-span-6 flex items-center gap-3">
                                <img :src="cartItem.gambar || '/images/default-item.svg'" class="w-10 h-10 rounded object-cover" alt="item">
                                <div>
                                    <div class="font-semibold text-gray-800 leading-tight" x-text="cartItem.nama"></div>
                                    <div class="text-[11px] text-gray-500 uppercase tracking-wide">SKU: <span x-text="cartItem.sku"></span></div>
                                </div>
                            </div>
                            <div class="col-span-3 text-sm text-gray-600 text-right">
                                <span x-text="cartItem.selectedQuantity"></span>
                                <span class="text-gray-400">x</span>
                                <span>Rp<span x-text="Number(cartItem.harga).toLocaleString('id-ID')"></span></span>
                            </div>
                            <div class="col-span-2 text-right font-semibold text-green-800">
                                Rp<span x-text="(Number(cartItem.harga) * Number(cartItem.selectedQuantity)).toLocaleString('id-ID')"></span>
                            </div>
                            <div class="col-span-1 text-right">
                                <button @click="removeFromCart(idx)" class="text-red-500 hover:text-red-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>

                    <div class="bg-white rounded-xl border border-green-200 divide-y divide-green-100">
                        <div class="flex justify-between items-center px-4 py-3">
                            <span class="font-semibold text-gray-700">Subtotal</span>
                            <span class="font-semibold text-gray-900">Rp<span x-text="cartSubtotal.toLocaleString('id-ID')"></span></span>
                        </div>
                        <div x-show="addDiskonNilai > 0" class="flex justify-between items-center px-4 py-3 text-sm text-red-600">
                            <span>Diskon</span>
                            <span>
                                <span x-show="addDiskonTipe === 'rupiah'">-Rp<span x-text="Number(addDiskonNilai).toLocaleString('id-ID')"></span></span>
                                <span x-show="addDiskonTipe === 'persen'">-<span x-text="addDiskonNilai"></span>%</span>
                            </span>
                        </div>
                        <div class="flex justify-between items-center px-4 py-3">
                            <span class="font-bold text-gray-800">Total</span>
                            <span class="text-xl font-bold text-[#28C328]">Rp<span x-text="cartTotal.toLocaleString('id-ID')"></span></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Catatan Pesanan -->
            <div class="bg-yellow-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-yellow-800 mb-3">Catatan Pesanan:</h3>
                <textarea x-model="orderNotes" 
                          class="w-full rounded-lg border border-yellow-200 px-3 py-2 text-sm resize-none focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400" 
                          rows="3" 
                          placeholder="Tambahkan catatan khusus untuk pesanan ini (opsional)..."></textarea>
                <div class="text-xs text-yellow-700 mt-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Catatan ini akan tersimpan bersama dengan data transaksi dan dapat dilihat di detail pesanan.
                </div>
            </div>

            <!-- Total dan Tombol -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="flex justify-between items-center mb-2">
                    <span class="font-semibold text-gray-700">Subtotal:</span>
                    <span class="font-semibold text-gray-800">Rp<span x-text="cartSubtotal.toLocaleString('id-ID')"></span></span>
                </div>
                <div x-show="addDiskonNilai > 0" class="flex justify-between items-center mb-2">
                    <span class="text-gray-600">Diskon:</span>
                    <span class="text-red-600">
                        <span x-show="addDiskonTipe === 'rupiah'">-Rp<span x-text="Number(addDiskonNilai).toLocaleString('id-ID')"></span></span>
                        <span x-show="addDiskonTipe === 'persen'">-<span x-text="addDiskonNilai"></span>%</span>
                    </span>
                </div>
                <div class="flex justify-between items-center mb-4 pt-2 border-t border-gray-200">
                    <span class="text-lg font-bold text-gray-800">Total:</span>
                    <span class="text-xl font-bold text-[#28C328]">Rp<span x-text="cartTotal.toLocaleString('id-ID')"></span></span>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex flex-col md:flex-row gap-4">
                <button type="button" @click="backToStep1()" class="flex-1 rounded-lg bg-gray-200 text-gray-700 font-semibold py-3 hover:bg-gray-300 transition">Kembali</button>
                <button type="button" @click="submitAddForm()" :disabled="cartItems.length === 0 || loading" class="flex-1 rounded-lg bg-[#28C328] text-white font-semibold py-3 hover:bg-[#22a322] transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!loading">Simpan Transaksi</span>
                    <span x-show="loading" class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Menyimpan...
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Detail Sale -->
    <div x-show="showDetailModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 p-4" x-cloak>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-5xl mx-4 relative overflow-hidden max-h-[90vh] flex flex-col">
            <!-- Compact Header -->
            <div class="bg-[#28C328] rounded-t-2xl p-4 text-white relative">
                <button @click="showDetailModal = false" class="absolute top-3 right-3 w-7 h-7 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-full flex items-center justify-center text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                
                <div class="text-center">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                </div>
                    
                    <h2 class="text-xl font-bold mb-1">Detail Transaksi</h2>
                    <p class="text-white text-opacity-80 text-sm">Informasi lengkap transaksi penjualan</p>
                </div>
                </div>
            
            <!-- Clean Content -->
            <div class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <!-- Info Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Payment Method Card -->
                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                                Metode Pembayaran
                            </h3>
                </div>
                        <div class="space-y-3">
                <div>
                                <div class="text-sm text-gray-600 font-medium mb-1">Jenis Transaksi</div>
                                <div class="text-lg font-bold text-gray-800" x-text="detailSaleData.payment_method"></div>
                                <div class="text-xs text-gray-500 mt-1">Jenis pembayaran yang digunakan</div>
                </div>
                <div>
                                <div class="text-sm text-gray-600 font-medium mb-1">ID Referensi</div>
                                <div class="font-mono text-sm font-semibold text-gray-800 bg-gray-100 px-3 py-1 rounded-lg inline-block" x-text="detailSaleData.payment_reference || '-'"></div>
                                <div class="text-xs text-gray-500 mt-1">Referensi pembayaran (opsional)</div>
                </div>
                </div>
                </div>
                    
                    <!-- Timeline Card -->
                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Timeline Transaksi
                            </h3>
                </div>
                        <div class="relative flex flex-col gap-4">
                            <div class="flex items-start gap-3">
                                <div class="w-3 h-3 rounded-full bg-green-500 mt-1.5 flex-shrink-0"></div>
                                <div class="flex-1">
                                    <div class="text-sm font-semibold text-gray-800">Pemesanan</div>
                                    <div class="text-xs text-gray-600" x-text="new Date(detailSaleData.sale_date || '').toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })"></div>
                </div>
                </div>
                            <div class="flex items-start gap-3">
                                <div class="w-3 h-3 rounded-full bg-green-500 mt-1.5 flex-shrink-0"></div>
                                <div class="flex-1">
                                    <div class="text-sm font-semibold text-gray-800">Penerbitan Invoice</div>
                                    <div class="text-xs text-gray-600" x-text="new Date(detailSaleData.sale_date || '').toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })"></div>
                </div>
                </div>
                            <div class="flex items-start gap-3">
                                <div class="w-3 h-3 rounded-full mt-1.5 flex-shrink-0" :class="detailSaleData.status === 'completed' ? 'bg-green-500' : 'bg-gray-400'"></div>
                                <div class="flex-1">
                                    <div class="text-sm font-semibold" :class="detailSaleData.status === 'completed' ? 'text-gray-800' : 'text-gray-600'">Pembayaran</div>
                                    <div class="text-xs" :class="detailSaleData.status === 'completed' ? 'text-gray-600' : 'text-gray-500'" x-text="detailSaleData.status === 'completed' ? 'Selesai' : detailSaleData.status"></div>
                </div>
        </div>
    </div>
                </div>
                    
                    <!-- Summary Card -->
                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                Ringkasan
                            </h3>
                    </div>
                        <div class="space-y-3">
                            <div>
                                <div class="text-sm text-gray-600 font-medium mb-1">No Pesanan</div>
                                <div class="font-mono text-lg font-bold text-gray-800 bg-gray-100 px-3 py-1 rounded-lg inline-block" x-text="detailSaleData.order_number"></div>
                                </div>
                            <div>
                                <div class="text-sm text-gray-600 font-medium mb-1">Tanggal</div>
                                <div class="text-sm text-gray-800" x-text="new Date(detailSaleData.sale_date || '').toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })"></div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-600 font-medium mb-1">Status</div>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold" 
                                      :class="detailSaleData.status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800'" 
                                      x-text="detailSaleData.status === 'completed' ? 'Selesai' : detailSaleData.status"></span>
                                </div>
                            <div>
                                <div class="text-sm text-gray-600 font-medium mb-1">Telepon Pelanggan</div>
                                <div class="text-sm font-semibold text-gray-800" x-text="detailSaleData.customer_phone || '-'"></div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-600 font-medium mb-1">Alamat Pelanggan</div>
                                <div class="text-xs text-gray-600 leading-relaxed" x-text="detailSaleData.customer_address || '-'"></div>
                            </div>
                            <button class="w-full bg-[#28C328] hover:bg-[#22a322] text-white font-semibold py-2 px-4 rounded-xl transition-colors" 
                                    @click="showInvoiceModal = true">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Lihat Invoice
                            </button>
                            </div>
                                </div>
                            </div>
                
                <!-- Clean Transaction Details -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="bg-[#28C328] px-6 py-4">
                        <h3 class="text-lg font-bold text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            Rincian Transaksi
                        </h3>
                        </div>
                    
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="py-3 px-4 text-left font-semibold text-gray-700">Item</th>
                                        <th class="py-3 px-4 text-right font-semibold text-gray-700">Harga</th>
                                        <th class="py-3 px-4 text-center font-semibold text-gray-700">Qty</th>
                                        <th class="py-3 px-4 text-center font-semibold text-gray-700">Diskon (Rp)</th>
                                        <th class="py-3 px-4 text-right font-semibold text-gray-700">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <template x-for="item in detailSaleData.items" :key="item.item_sku">
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="py-3 px-4 align-middle">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 bg-[#28C328] rounded-lg flex items-center justify-center">
                                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                        </svg>
                    </div>
                                                    <div class="leading-tight">
                                                        <div class="font-semibold text-gray-800" x-text="item.item_name"></div>
                                                        <div class="text-xs text-gray-500 font-mono" x-text="item.item_sku"></div>
                            </div>
                        </div>
                                            </td>
                                            <td class="py-3 px-4 align-middle text-right">
                                                <span class="font-medium text-gray-800">Rp<span x-text="Number(item.unit_price).toLocaleString('id-ID')"></span></span>
                                            </td>
                                            <td class="py-3 px-4 align-middle text-center">
                                                <span class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full" x-text="item.quantity"></span>
                                            </td>
                                            <td class="py-3 px-4 align-middle text-center">
                                                <span class="inline-flex items-center px-2 py-1 bg-orange-100 text-orange-800 text-xs font-medium rounded-full" x-text="item.discount_amount > 0 ? 'Rp' + Number(item.discount_amount).toLocaleString('id-ID') : '-'"></span>
                                            </td>
                                            <td class="py-3 px-4 align-middle text-right">
                                                <span class="font-bold text-gray-800">Rp<span x-text="Number(item.subtotal).toLocaleString('id-ID')"></span></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                    </div>
                        
                        <!-- Clean Summary -->
                        <div class="mt-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-center">
                        <div>
                                    <div class="text-xs text-gray-500 mb-1">Subtotal</div>
                                    <div class="text-sm font-semibold text-gray-700">Rp<span x-text="calculateDetailSubtotal(detailSaleData.items || []).toLocaleString('id-ID')"></span></div>
                        </div>
                        <div>
                                    <div class="text-xs text-gray-500 mb-1">Diskon</div>
                                    <div class="text-sm font-semibold text-red-600">Rp<span x-text="calculateDetailDiscount(detailSaleData.items || []).toLocaleString('id-ID')"></span></div>
                        </div>
                                <div class="md:col-span-1">
                                    <div class="text-xs text-gray-500 mb-1">Total</div>
                                    <div class="text-lg font-bold text-[#28C328]">Rp<span x-text="Number(detailSaleData.total_amount || 0).toLocaleString('id-ID')"></span></div>
                        </div>
                        <div>
                                    <div class="text-xs text-gray-500 mb-1">Bayar</div>
                                    <div class="text-sm font-semibold text-gray-700">Rp<span x-text="Number(detailSaleData.amount_paid || 0).toLocaleString('id-ID')"></span></div>
                        </div>
                        <div>
                                    <div class="text-xs text-gray-500 mb-1">Kembalian</div>
                                    <div class="text-sm font-semibold text-gray-700">Rp<span x-text="Number(detailSaleData.change_amount || 0).toLocaleString('id-ID')"></span></div>
                        </div>
                        </div>
                        </div>
                        
                        <!-- Catatan Pesanan -->
                        <div x-show="detailSaleData.notes && detailSaleData.notes.trim()" class="mt-4 p-4 bg-yellow-50 rounded-xl border border-yellow-200">
                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-semibold text-yellow-800 mb-1">Catatan Pesanan:</div>
                                    <div class="text-sm text-yellow-700 leading-relaxed" x-text="detailSaleData.notes"></div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- Modal Edit Status -->
    <div x-show="showEditStatusModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" x-cloak>
        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md mx-4 relative">
            <button @click="showEditStatusModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
            
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold text-[#28C328]">Edit Status Transaksi</h2>
                <p class="text-gray-600 text-sm">Update status atau lanjut ke detail pesanan</p>
            </div>
            
            <div class="mb-6 space-y-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="text-xs text-gray-500 uppercase">Order Number:</div>
                    <div class="font-bold text-gray-800" x-text="editStatusData.order_number"></div>
                </div>
                
                <div>
                    <label class="block font-semibold mb-2 text-sm">Status Baru</label>
                    <select x-model="editStatusData.newStatus" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 focus:ring-2 focus:ring-[#28C328] outline-none">
                        <option value="pending">Pending</option>
                        <option value="completed">Selesai</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>
                <div x-show="editStatusError" class="text-red-500 text-sm" x-text="editStatusError"></div>
            </div>
            
            <div class="flex flex-col gap-3">
                <button @click="updateSaleStatus()" :disabled="loading" class="w-full rounded-lg bg-[#28C328] text-white font-semibold py-3 hover:bg-[#22a322] transition disabled:opacity-50">
                    <span x-show="!loading">Update Status Saja</span>
                    <span x-show="loading">Memproses...</span>
                </button>

                <button @click="$dispatch('edit-status-2', editStatusData)" class="w-full rounded-lg bg-blue-50 text-blue-600 font-semibold py-3 border border-blue-100 hover:bg-blue-100 transition">
                    Edit Detail Pesanan &rarr;
                </button>
            </div>
        </div>
    </div>

    <div x-show="showEditModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" x-cloak>
        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-4xl mx-2 relative overflow-y-auto max-h-[90vh]">
            <button @click="showEditModal = false" class="absolute top-4 left-4 text-gray-400 hover:text-gray-600 text-2xl font-bold">&larr;</button>
            <button @click="showEditModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
            
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold text-blue-600">Edit Data Pesanan</h2>
                <p class="text-gray-600 text-sm">Sesuaikan item, kuantitas, dan diskon pesanan</p>
            </div>

            <div x-show="editErrorMsg" class="text-red-500 text-sm mb-4 bg-red-50 p-3 rounded-lg" x-text="editErrorMsg"></div>

            <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
                <h3 class="font-semibold text-gray-800 mb-2">Informasi Transaksi:</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
                    <div><span class="text-gray-600">Order Number:</span> <span class="font-semibold" x-text="editStatusData.order_number"></span></div>
                    <div><span class="text-gray-600">Tanggal:</span> <span class="font-semibold" x-text="formatDate(editStatusData.sale_date)"></span></div>
                    <div><span class="text-gray-600">Customer:</span> <span class="font-semibold" x-text="editStatusData.customer_name"></span></div>
                    <div><span class="text-gray-600">Status:</span> <span class="font-semibold text-blue-600 uppercase" x-text="editStatusData.newStatus"></span></div>
                </div>
            </div>

            <div class="bg-blue-50 rounded-lg p-4 mb-6 border border-blue-100">
                <h3 class="font-semibold text-blue-800 mb-3">Pengaturan Diskon</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-semibold mb-2 text-sm">Tipe Diskon</label>
                        <select x-model="editStatusData.diskon_tipe" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="rupiah">Rupiah</option>
                            <option value="persen">Persen (%)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold mb-2 text-sm">Nilai Diskon</label>
                        <input type="number" x-model="editStatusData.diskon_nilai" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="0">
                    </div>
                    <div class="flex items-end">
                        <button type="button" class="w-full rounded-lg bg-blue-500 text-white font-semibold py-2 text-sm hover:bg-blue-600 transition">Update Diskon</button>
                    </div>
                </div>
                <div x-show="editStatusData.diskon_nilai > 0" class="mt-2 text-sm">
                    <span class="text-blue-700">Diskon Terpasang: </span>
                    <span x-show="editStatusData.diskon_tipe === 'rupiah'" class="font-semibold text-blue-800">Rp <span x-text="Number(editStatusData.diskon_nilai).toLocaleString('id-ID')"></span></span>
                    <span x-show="editStatusData.diskon_tipe === 'persen'" class="font-semibold text-blue-800"><span x-text="editStatusData.diskon_nilai"></span>%</span>
                </div>
            </div>

            <div class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-gray-800">Cari & Tambah Produk Baru:</h3>
                    <div class="flex items-center bg-gray-50 rounded-lg px-3 py-2 border w-64 focus-within:ring-1 focus-within:ring-[#28C328]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z" />
                        </svg>
                        <input type="text" x-model="stockItemSearch" class="flex-1 bg-transparent border-none outline-none text-sm" placeholder="Cari SKU atau nama..." />
                    </div>
                </div>
                
                <div class="overflow-x-auto border rounded-xl max-h-60 shadow-sm">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#28C328] text-white sticky top-0 text-[11px] uppercase tracking-wider">
                                <th class="p-2 text-left w-12">Foto</th>
                                
                                <th class="p-2 text-left">Produk</th>
                                
                                <th class="p-2 text-left w-24">SKU</th>
                                
                                <th class="p-2 text-center w-16">Stok</th>
                                
                                <th class="p-2 text-right w-24">Harga</th>
                                
                                <th class="p-2 text-center w-20">Jumlah</th>
                                
                                <th class="p-2 text-right w-28">Subtotal</th>
                                
                                <th class="p-2 text-center w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <template x-for="item in filteredStockItems" :key="item.id">
                                <tr class="hover:bg-green-50 transition">
                                    <td class="p-2">
                                        <img :src="item.gambar || '/images/default-item.svg'" class="w-8 h-8 rounded object-cover" alt="item">
                                    </td>
                                    
                                    <td class="p-2 text-xs font-medium text-gray-800" x-text="item.nama"></td>
                                    
                                    <td class="p-2 font-mono text-[10px] text-gray-400 uppercase" x-text="item.sku"></td>
                                    
                                    <td class="p-2 text-center text-xs text-gray-600" x-text="Number(item.tersedia).toLocaleString('id-ID')"></td>
                                    
                                    <td class="p-2 text-right text-xs">
                                        <span class="text-gray-400 text-[10px]">Rp</span><span x-text="Number(item.harga).toLocaleString('id-ID')"></span>
                                    </td>
                                    
                                    <td class="p-2">
                                        <input type="number" 
                                            x-model.number="item.tempQty" 
                                            x-init="item.tempQty = 0"
                                            class="w-16 rounded border px-2 py-1 text-xs text-center outline-none focus:ring-1 focus:ring-[#28C328]" 
                                            :class="item.tempQty > item.tersedia ? 'border-red-500 bg-red-50' : 'border-gray-300'"
                                            min="0" 
                                            :max="item.tersedia"
                                            placeholder="0">
                                        <div x-show="item.tempQty > item.tersedia" class="text-[9px] text-red-500 mt-1 leading-tight">
                                            Melebihi stok
                                        </div>
                                    </td>
                                    
                                    <td class="p-2 text-right font-semibold text-xs">
                                        <span x-show="item.tempQty > 0" class="text-green-700">
                                            Rp<span x-text="(Number(item.harga) * Number(item.tempQty || 0)).toLocaleString('id-ID')"></span>
                                        </span>
                                        <span x-show="!item.tempQty || item.tempQty <= 0" class="text-gray-300">-</span>
                                    </td>
                                    
                                    <td class="p-2 text-center">
                                        <button type="button" 
                                                @click="addItemToEditCart(item)" 
                                                :disabled="!item.tempQty || item.tempQty <= 0 || item.tempQty > item.tersedia"
                                                class="px-3 py-1 rounded text-[10px] font-bold transition disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                                                :class="item.tempQty > 0 && item.tempQty <= item.tersedia ? 'bg-[#28C328] text-white hover:bg-[#22a322]' : 'bg-gray-200 text-gray-400'">
                                            TAMBAH
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-green-50 rounded-lg p-4 mb-6 border border-green-100">
                <h3 class="font-semibold text-green-800 mb-3">Item Dalam Pesanan:</h3>
                <div x-show="editStatusData.items.length === 0" class="text-center text-gray-500 py-4 bg-white rounded-lg border border-dashed">
                    Pesanan kosong
                </div>
                
                <div x-show="editStatusData.items.length > 0" class="space-y-3">
                    <template x-for="(cartItem, idx) in editStatusData.items" :key="idx">
                        <div class="grid grid-cols-12 gap-3 items-center bg-white rounded-lg p-3 border border-green-200 shadow-sm">
                            <div class="col-span-6 flex items-center gap-3">
                                <img :src="cartItem.gambar || '/images/default-item.svg'" class="w-10 h-10 rounded object-cover">
                                <div>
                                    <div class="font-semibold text-gray-800 leading-tight text-sm" x-text="cartItem.nama"></div>
                                    <div class="text-[11px] text-gray-500 uppercase tracking-wide">SKU: <span x-text="cartItem.sku"></span></div>
                                </div>
                            </div>
                            <div class="col-span-3 text-sm text-gray-600 text-right">
                                <span class="inline-block bg-gray-100 px-3 py-1 rounded-md font-bold text-gray-800 border mr-1">
                                    <span x-text="cartItem.selectedQuantity"></span>
                                </span>
                                
                                <span class="text-gray-400">x</span>
                                <span x-text="'Rp' + Number(cartItem.harga).toLocaleString('id-ID')"></span>
                            </div>
                            <div class="col-span-2 text-right font-semibold text-green-800 text-sm">
                                Rp<span x-text="(Number(cartItem.harga) * Number(cartItem.selectedQuantity)).toLocaleString('id-ID')"></span>
                            </div>
                            <div class="col-span-1 text-right">
                                <button @click="removeItemFromEditCart(idx)" class="text-red-500 hover:text-red-700 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>

                    <div class="bg-white rounded-xl border border-green-200 divide-y divide-green-100 shadow-sm">
                        <div class="flex justify-between items-center px-4 py-3 text-sm">
                            <span class="font-semibold text-gray-700">Subtotal</span>
                            <span class="font-semibold text-gray-900">Rp<span x-text="calculateEditSubtotal().toLocaleString('id-ID')"></span></span>
                        </div>
                        <div x-show="editStatusData.diskon_nilai > 0" class="flex justify-between items-center px-4 py-3 text-sm text-red-600">
                            <span>Diskon</span>
                            <span class="font-medium">
                                <span x-show="editStatusData.diskon_tipe === 'rupiah'">-Rp<span x-text="Number(editStatusData.diskon_nilai).toLocaleString('id-ID')"></span></span>
                                <span x-show="editStatusData.diskon_tipe === 'persen'">-<span x-text="editStatusData.diskon_nilai"></span>%</span>
                            </span>
                        </div>
                        <div class="flex justify-between items-center px-4 py-3 bg-gray-50 rounded-b-xl">
                            <span class="font-bold text-gray-800">Total Akhir</span>
                            <span class="text-xl font-bold text-[#28C328]">Rp<span x-text="(calculateEditSubtotal() - calculateEditDiscount()).toLocaleString('id-ID')"></span></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-yellow-50 rounded-lg p-4 mb-6 border border-yellow-100">
                <h3 class="font-semibold text-yellow-800 mb-3 text-sm">Catatan Internal Perubahan:</h3>
                <textarea x-model="editStatusData.notes" 
                    class="w-full rounded-lg border border-yellow-200 px-3 py-2 text-sm focus:ring-yellow-400 focus:ring-1 outline-none" 
                    rows="2" placeholder="Contoh: Customer minta tambah barang atau revisi diskon..."></textarea>
            </div>

            <div class="flex flex-col md:flex-row gap-4">
                <button type="button" @click="showEditModal = false" class="flex-1 rounded-lg bg-gray-200 text-gray-700 font-semibold py-3 hover:bg-gray-300 transition">Batal</button>
                <button type="button" @click="updateTransaction()" :disabled="loading || editStatusData.items.length === 0" 
                    class="flex-1 rounded-lg bg-blue-600 text-white font-semibold py-3 hover:bg-blue-700 transition shadow-lg disabled:opacity-50">
                    <span x-show="!loading">Simpan Perubahan Pesanan</span>
                    <span x-show="loading" class="flex items-center justify-center">
                        <svg class="animate-spin h-5 w-5 text-white mr-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Menyimpan...
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center delete-modal" x-cloak>
        <div class="delete-modal-content w-full max-w-md mx-4">
            <div class="delete-modal-header">
                <div class="delete-modal-icon">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </div>
                <div class="delete-modal-title">Hapus Transaksi</div>
                <div class="delete-modal-subtitle">Konfirmasi penghapusan data</div>
            </div>
            
            <div class="delete-modal-body">
                <div class="delete-modal-message">
                    <div class="delete-modal-question">Apakah Anda yakin ingin menghapus transaksi ini?</div>
                    
                    <div class="delete-modal-details">
                        <div class="delete-modal-detail-item">
                            <span class="delete-modal-detail-label">Order Number:</span>
                            <span class="delete-modal-detail-value" x-text="deleteSaleData?.order_number || '-'"></span>
                        </div>
                        <div class="delete-modal-detail-item">
                            <span class="delete-modal-detail-label">Total Amount:</span>
                            <span class="delete-modal-detail-value">Rp<span x-text="deleteSaleData ? Number(deleteSaleData.total_amount).toLocaleString('id-ID') : '-'"></span></span>
                        </div>
                        <div class="delete-modal-detail-item">
                            <span class="delete-modal-detail-label">Tanggal:</span>
                            <span class="delete-modal-detail-value" x-text="deleteSaleData ? new Date(deleteSaleData.sale_date).toLocaleDateString('id-ID') : '-'"></span>
                        </div>
                    </div>
                    
                    <div class="delete-modal-warning">
                        <span class="delete-modal-warning-icon">⚠️</span>
                        <span class="delete-modal-warning-text">Tindakan ini tidak dapat dibatalkan!</span>
                    </div>
                </div>
                
                <div class="delete-modal-actions">
                    <button @click="showDeleteModal = false; deleteSaleData = null" class="delete-modal-btn delete-modal-btn-cancel">
                        Batal
                    </button>
                    <button @click="confirmDeleteSale()" :disabled="loading" class="delete-modal-btn delete-modal-btn-confirm">
                        <span x-show="!loading">Hapus Transaksi</span>
                        <span x-show="loading" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Menghapus...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Invoice -->
    <div x-show="showInvoiceModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" x-cloak>
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
                            <span class="text-white font-bold text-lg" x-text="(company.name || 'Toko').substring(0,1)"></span>
                        </template>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-700" x-text="company.name || 'Nama Toko'" ></h2>
                        <div class="text-xs text-gray-400" x-text="company.address || '-' "></div>
                        <div class="text-xs text-gray-400">
                            <span x-text="company.phone ? ('Telp: ' + company.phone) : ''"></span>
                            <span x-show="company.phone && company.email"> | </span>
                            <span x-text="company.email ? ('Email: ' + company.email) : ''"></span>
                        </div>
                    </div>
                    <div class="ml-auto text-right">
                        <div class="text-lg font-bold text-gray-700">INVOICE</div>
                        <div class="text-xs text-gray-500">No. Invoice: INV-202508-<span x-text="detailSaleData.order_number"></span></div>
                        <div class="text-xs text-gray-500">ID Pesanan: <span x-text="detailSaleData.order_number"></span></div>
                        <div class="text-xs text-gray-500">Tanggal: <span x-text="new Date(detailSaleData.sale_date || '').toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })"></span></div>
                        <div class="text-xs text-gray-500">Jatuh Tempo: <span x-text="new Date(new Date(detailSaleData.sale_date || '').getTime() + 7 * 24 * 60 * 60 * 1000).toLocaleDateString('id-ID', { day: '2-digit', month: 'numeric', year: 'numeric' })"></span></div>
                        <div class="text-xs text-gray-500">Status: <span class="font-semibold text-[#28C328]" x-text="detailSaleData.status === 'completed' ? 'Selesai' : detailSaleData.status"></span></div>
                    </div>
                </div>

                <!-- Info Pelanggan -->
                <div class="mb-6">
                    <div class="font-semibold text-gray-700">Kepada:</div>
                    <div class="font-bold text-[#28C328] text-lg" x-text="detailSaleData.customer_name || 'Pelanggan'"></div>
                    <div class="text-xs text-gray-500 mb-1" x-text="detailSaleData.order_number"></div>
                    <div class="text-xs text-gray-600" x-show="detailSaleData.customer_phone">
                        Telp: <span x-text="detailSaleData.customer_phone"></span>
                    </div>
                    <div class="text-xs text-gray-600" x-show="detailSaleData.customer_address">
                        Alamat: <span x-text="detailSaleData.customer_address"></span>
                    </div>
                </div>
                
                <!-- Catatan Pesanan di Invoice -->
                <div x-show="detailSaleData.notes && detailSaleData.notes.trim()" class="mb-6 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="font-semibold text-yellow-800 text-sm mb-2">Catatan Pesanan:</div>
                    <div class="text-sm text-yellow-700 leading-relaxed" x-text="detailSaleData.notes"></div>
                </div>
                
                <!-- Tabel Transaksi -->
                <div class="overflow-x-auto mb-6">
                    <table class="min-w-full border text-sm">
                        <thead>
                            <tr class="bg-[#BAFFBA] text-gray-700">
                                <th class="py-2 px-4 border-b text-left">Nama Pesanan</th>
                                <th class="py-2 px-4 border-b text-left">Harga Barang</th>
                                <th class="py-2 px-4 border-b text-left">Quantity</th>
                                <th class="py-2 px-4 border-b text-left">Diskon</th>
                                <th class="py-2 px-4 border-b text-left">Jumlah Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="item in detailSaleData.items" :key="item.item_sku">
                                <tr>
                                    <td class="py-2 px-4 border-b" x-text="item.item_name"></td>
                                    <td class="py-2 px-4 border-b">Rp<span x-text="Number(item.unit_price).toLocaleString('id-ID')"></span></td>
                                    <td class="py-2 px-4 border-b" x-text="item.quantity"></td>
                                    <td class="py-2 px-4 border-b" x-text="item.discount_amount > 0 ? 'Rp' + Number(item.discount_amount).toLocaleString('id-ID') : '-'"></td>
                                    <td class="py-2 px-4 border-b font-bold">Rp<span x-text="Number(item.subtotal).toLocaleString('id-ID')"></span></td>
                            </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                
                <!-- Summary & Footer -->
                <div class="flex flex-col md:flex-row md:justify-between items-start md:items-center mb-2 gap-4">
                    <div class="space-y-2">
                        <div class="text-xs text-gray-500">* Invoice ini sah tanpa tanda tangan dan dicetak otomatis oleh sistem GAFI.</div>
                        <div class="text-sm text-gray-700">
                            <div class="font-semibold">Pembayaran ke:</div>
                            <div x-text="company.bank && company.account ? company.bank + ' - ' + company.account : 'Belum diatur'"></div>
                        </div>
                    </div>
                    <div class="text-right space-y-1">
                        <div class="text-sm text-gray-600">Subtotal: <span class="font-semibold">Rp<span x-text="calculateDetailSubtotal(detailSaleData.items || []).toLocaleString('id-ID')"></span></span></div>
                        <div class="text-sm text-gray-600">Total Diskon: <span class="font-semibold text-red-600">Rp<span x-text="calculateDetailDiscount(detailSaleData.items || []).toLocaleString('id-ID')"></span></span></div>
                        <div class="text-lg font-semibold text-[#28C328]">Total Bayar: <span class="text-2xl font-bold">Rp<span x-text="Number(detailSaleData.total_amount || 0).toLocaleString('id-ID')"></span></span></div>
            </div>
        </div>
                
                <button class="rounded-lg bg-[#28C328] text-white font-semibold px-6 py-2 text-sm mt-4 w-full md:w-auto" @click="exportInvoicePDF">Export PDF</button>
    </div>
                </div>
    </div>
</div>
@endsection

<script>
function salesHistory() {
    return {
        sales: [],
        loading: false,
        error: null,
        search: '',
        periode: '',
        // New date filter state
        dateFilter: '',
        customStartDate: '',
        customEndDate: '',
        status: '',
        currentPage: 1,
        perPage: 10,
        sortKey: '',
        sortAsc: true,
        showDetailModal: false,
        detailSaleData: {},
        openActionMenuIndex: null, // Added for action menu
        showAddModal: false, // Added for add modal
        addOrderNumber: '',
        addSaleDate: '',
        addTotalItems: '',
        addTotalAmount: '',
        addPaymentMethod: '',
        addStatus: 'completed',
        addCustomerPhone: '',
        addCustomerAddress: '',
        showAddStep2Modal: false, // Added for step 2 modal
        addDiskonTipe: 'rupiah', // Added for discount type
        addDiskonNilai: 0, // Added for discount value
        cartItems: [], // Added for cart items
        availableStockItems: [], // Added for available stock items
        stockItemSearch: '', // Added for stock item search
        orderNotes: '', // Added for order notes
        addErrorMsg: '', // Added for error messages
        showInvoiceModal: false, // Added for invoice modal
        showEditStatusModal: false, // Added for edit status modal
        showEditModal: false, // Added for edit status modal
        editStatusData: { items: [] }, // Added for edit status data
        editStatusError: '', // Added for edit status error
        showDeleteModal: false, // Added for delete confirmation modal
        deleteSaleData: null, // Added for delete sale data
        showNotification: false, // Added for custom notifications
        notificationType: 'success', // Added for notification type
        notificationTitle: '', // Added for notification title
        notificationMessage: '', // Added for notification message
        // Company identity (loaded from /client/identity)
        company: { name: '', phone: '', email: '', address: '', logoUrl: '', bank: '', account: '' },

        sortBy(key) {
            if (this.sortKey !== key) {
                this.sortKey = key;
                this.sortAsc = true;
            } else if (this.sortAsc) {
                this.sortAsc = false;
            } else {
                this.sortKey = '';
                this.sortAsc = true;
            }
        },

        get filteredSales() {
            let filtered = this.sales;
            
            if (this.search) {
                const searchLower = this.search.toLowerCase();
                filtered = filtered.filter(sale => 
                    sale.order_number.toLowerCase().includes(searchLower)
                );
                
            }
            
            if (this.status) {
                filtered = filtered.filter(sale => sale.status === this.status);
            }
            
            // Apply date filter presets/custom
            if (this.dateFilter) {
                filtered = this.applyDateFilterToSales(filtered);
            }
            
            return filtered;
        },

        get sortedSales() {
            if (!this.sortKey) return this.filteredSales;
            return this.filteredSales.slice().sort((a, b) => {
                let valA = a[this.sortKey];
                let valB = b[this.sortKey];
                
                if (['total_items', 'total_quantity', 'total_amount', 'amount_paid', 'change_amount'].includes(this.sortKey)) {
                    valA = Number(valA);
                    valB = Number(valB);
                } else {
                    if (typeof valA === 'string') valA = valA.toLowerCase();
                    if (typeof valB === 'string') valB = valB.toLowerCase();
                }
                
                if (valA < valB) return this.sortAsc ? -1 : 1;
                if (valA > valB) return this.sortAsc ? 1 : -1;
                return 0;
            });
        },

        get paginatedSales() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.sortedSales.slice(start, start + this.perPage);
        },

        get totalPages() {
            return Math.max(1, Math.ceil(this.sortedSales.length / this.perPage));
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

        prevPage() { if (this.currentPage > 1) this.currentPage--; },
        nextPage() { if (this.currentPage < this.totalPages) this.currentPage++; },
        goToPage(page) { this.currentPage = page; },

        get periodes() {
            return [...new Set(this.sales.map(sale => sale.sale_date ? sale.sale_date.split('-')[0] : ''))];
        },

        get statuses() {
            return [...new Set(this.sales.map(sale => sale.status).filter(Boolean))];
        },

        get filteredStockItems() {
            if (!this.stockItemSearch) {
                return this.availableStockItems;
            }
            
            const searchQuery = this.stockItemSearch.toLowerCase();
            return this.availableStockItems.filter(item => {
                return item.nama.toLowerCase().includes(searchQuery) || 
                       item.sku.toLowerCase().includes(searchQuery);
            });
        },

        async onSearchChange() {
            // Reset to first page when searching
            this.currentPage = 1;
            // Add debounce to prevent too many requests
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(async () => {
                await this.loadSales();
            }, 500);
        },

        async loadSales() {
            this.loading = true;
            this.error = null;
            
            try {
                const params = new URLSearchParams();
                if (this.search) params.append('search', this.search);
                if (this.periode) params.append('periode', this.periode);
                if (this.status) params.append('status', this.status);
                
                // Add timeout to prevent infinite loading
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout
                
                const response = await fetch(`/client/sales-history?${params}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: controller.signal
                });
                
                clearTimeout(timeoutId);
                
                if (!response.ok) {
                    if (response.status === 401) {
                        throw new Error('Sesi telah berakhir. Silakan login ulang.');
                    } else if (response.status === 500) {
                        throw new Error('Terjadi kesalahan server. Silakan coba lagi.');
                    } else {
                        throw new Error(`HTTP ${response.status}: Gagal memuat data`);
                    }
                }
                
                const result = await response.json();
                
                // Handle different response formats
                if (result.data !== undefined) {
                    this.sales = result.data || [];
                } else if (Array.isArray(result)) {
                    this.sales = result;
                } else {
                    this.sales = [];
                }

                console.log(this.sales);
                
                this.error = null;
            } catch (error) {
                console.error('Error loading sales:', error);
                if (error.name === 'AbortError') {
                    this.error = 'Request timeout. Silakan coba lagi.';
                } else {
                    this.error = 'Gagal memuat data: ' + error.message;
                }
                this.sales = [];
            } finally {
                this.loading = false;
            }
        },

        initRealtime() {
            // Clear any existing interval
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
            }
            
            // Load initial data
            this.loadSales();
            this.loadCompanyIdentity();
            
            // Auto-refresh removed to avoid redundant fetches
            this.refreshInterval = null;
        },

        // Date filter helpers
        applyDateFilter() {
            this.currentPage = 1;
        },
        clearDateFilter() {
            this.dateFilter = '';
            this.customStartDate = '';
            this.customEndDate = '';
            this.currentPage = 1;
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
        applyDateFilterToSales(sales) {
            if (!this.dateFilter) return sales;
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
                    if (!this.customStartDate || !this.customEndDate) return sales;
                    startDate = new Date(this.customStartDate);
                    endDate = new Date(this.customEndDate);
                    endDate.setHours(23, 59, 59, 999);
                    break;
                default:
                    return sales;
            }
            return sales.filter(sale => {
                const d = new Date(sale.sale_date);
                return d >= startDate && d <= endDate;
            });
        },

        async loadCompanyIdentity() {
            try {
                const res = await fetch('/client/identity', { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const data = await res.json();
                this.company = {
                    name: data.mitra_nama || data.nama || '',
                    phone: data.telepon || '',
                    email: data.email || '',
                    address: data.alamat || '',
                    logoUrl: data.logo_url || '',
                    bank: data.bank || '',
                    account: data.no_rekening || ''
                };
            } catch (e) {
                // ignore
            }
        },

        // Cleanup interval when component is destroyed
        destroy() {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
            }
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }
        },
        calculateEditSubtotal() {
            const items = this.editStatusData?.items || [];
            return items.reduce((sum, i) => {
                return sum + (Number(i.harga || 0) * Number(i.selectedQuantity || 0));
            }, 0);
        },

        calculateEditDiscount() {
            const subtotal = this.calculateEditSubtotal();
            const nilai = Number(this.editStatusData?.diskon_nilai || 0);
            
            if (this.editStatusData?.diskon_tipe === 'persen') {
                return (subtotal * nilai) / 100;
            }
            return nilai;
        },
        addItemToEditCart(item) {
            const requestedQty = Number(item.tempQty) || 0;
            const availableQty = Number(item.tersedia) || 0;

            // 1. Validasi input
            if (requestedQty <= 0) return;

            // 2. Validasi stok (Cek apakah permintaan melebihi stok yang tampil di tabel)
            if (requestedQty > availableQty) {
                this.showErrorNotification('Stok Tidak Cukup', `Sisa stok hanya ${availableQty}`);
                return;
            }

            // 3. LOGIKA PENGURANGAN VISUAL (State Management)
            // Mengurangi stok yang tampil di tabel pencarian agar user tidak bisa input melebihi batas
            item.tersedia = availableQty - requestedQty;

            // 4. Masukkan ke keranjang edit
            // Kita cari berdasarkan ID atau SKU
            const exist = this.editStatusData.items.find(i => i.id === item.id || i.sku === item.sku);
            
            if (exist) {
                // Jika item sudah ada di pesanan, tambahkan jumlahnya
                exist.selectedQuantity = Number(exist.selectedQuantity) + requestedQty;
            } else {
                // Jika belum ada, push data baru (Gunakan spread operator agar data asli tetap aman)
                this.editStatusData.items.push({
                    ...item,
                    harga: Number(item.harga) || 0,
                    selectedQuantity: requestedQty,
                    originalStock: availableQty // Simpan info stok asli untuk referensi jika dibutuhkan
                });
            }

            // 5. Reset input sementara di tabel pencarian kembali ke 0
            item.tempQty = 0;
        },
        removeItemFromEditCart(index) {
            const itemToRemove = this.editStatusData.items[index];
            if (itemToRemove) {
                const originalItem = this.availableStockItems.find(s => s.sku === itemToRemove.sku);
                
                if (originalItem) {
                    originalItem.tersedia = Number(originalItem.tersedia) + Number(itemToRemove.selectedQuantity);
                }
                this.editStatusData.items.splice(index, 1);
            }
        },

        generateOrderNumber() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const random = Math.random().toString(36).substring(2, 6).toUpperCase();
            return `CS-${year}${month}${day}-${hours}${minutes}${seconds}-${random}`;
        },

        async showSaleDetail(sale) {
            try {
                const response = await fetch(`/client/sales/${sale.id}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    if (response.status === 401) {
                        throw new Error('Sesi telah berakhir. Silakan login ulang.');
                    } else if (response.status === 404) {
                        throw new Error('Data transaksi tidak ditemukan.');
                    } else {
                        throw new Error(`HTTP ${response.status}: Gagal memuat detail`);
                    }
                }

                const result = await response.json();
                if (result.success) {
                    this.detailSaleData = result.data;
                    this.showDetailModal = true;
                } else {
                    throw new Error(result.message || 'Gagal memuat detail transaksi');
                }
            } catch (error) {
                console.error('Error loading sale detail:', error);
                this.showErrorNotification('Gagal Memuat Detail', 'Gagal memuat detail transaksi: ' + error.message);
            }
        },

        showEditStatusModalasdw(sale) {
            this.editStatusError = '';
            this.editErrorMsg = '';
            
            // Inisialisasi data untuk kedua tahap
            this.editStatusData = {
                id: sale.id,
                order_number: sale.order_number,
                sale_date: sale.sale_date,
                status: sale.status,
                newStatus: sale.status,
                customer_name: sale.customer_name || 'Umum',
                
                // Load item yang sudah ada (Deep Copy)
                items: sale.items ? JSON.parse(JSON.stringify(sale.items.map(i => ({
                    id: i.stock_id || i.id,
                    nama: i.item_name,
                    sku: i.item_sku,
                    harga: i.unit_price,
                    selectedQuantity: i.quantity
                })))) : [],
                
                diskon_tipe: sale.discount_type || 'rupiah',
                diskon_nilai: sale.discount_amount || 0,
                notes: sale.notes || ''
            };
            
            this.showEditStatusModal = true;
        },

        async updateSaleStatus() {
            if (!this.editStatusData.newStatus) {
                this.editStatusError = 'Pilih status baru';
                return;
            }

            this.loading = true;
            this.editStatusError = '';
            
            try {
                const response = await fetch(`/client/sales/${this.editStatusData.id}/status`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        status: this.editStatusData.newStatus
                    })
                });

                if (!response.ok) {
                    if (response.status === 401) {
                        throw new Error('Sesi telah berakhir. Silakan login ulang.');
                    } else if (response.status === 404) {
                        throw new Error('Data transaksi tidak ditemukan.');
                    } else {
                        throw new Error(`HTTP ${response.status}: Gagal memperbarui status`);
                    }
                }

                const result = await response.json();
                if (result.success) {
                    this.showSuccessNotification('Status Berhasil Diperbarui', 'Status transaksi telah berhasil diperbarui.');
                    this.showEditStatusModal = false;
                    this.loadSales(); // Refresh the sales history
                } else {
                    throw new Error(result.message || 'Gagal memperbarui status');
                }
            } catch (error) {
                console.error('Error updating sale status:', error);
                this.editStatusError = error.message;
            } finally {
                this.loading = false;
            }
        },

        async updateTransaction() {
            this.loading = true;
            this.editErrorMsg = '';

            const subtotal = this.calculateEditSubtotal();
            const discountAmount = this.calculateEditDiscount();

            const payload = {
                order_number: this.editStatusData.order_number,
                status: this.editStatusData.newStatus,
                total_items: this.editStatusData.items.length,
                total_quantity: this.editStatusData.items.reduce((t, i) => t + Number(i.selectedQuantity), 0),
                total_amount: subtotal - discountAmount,
                discount_type: this.editStatusData.diskon_tipe,
                discount_amount: discountAmount,
                notes: this.editStatusData.notes,
                items: this.editStatusData.items.map(item => ({
                    product_id: item.id, // TAMBAHKAN INI agar backend tahu ID produknya
                    item_name: item.nama,
                    item_sku: item.sku,
                    quantity: Number(item.selectedQuantity),
                    unit_price: Number(item.harga),
                    subtotal: Number(item.harga) * Number(item.selectedQuantity)
                }))
            };

            try {
                const response = await fetch(`/client/sales/update/${this.editStatusData.id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
                });
                const result = await response.json();
                if (result.success) {
                    this.showSuccessNotification('Update Berhasil', 'Seluruh data pesanan diperbarui.');
                    this.showEditModal = false;
                    this.showEditStatusModal = false;
                    this.loadSales();
                } else { throw new Error(result.message); }
            } catch (e) { this.editErrorMsg = e.message; }
            finally { this.loading = false; }
        },

        async deleteSale(sale) {
            this.deleteSaleData = sale;
            this.showDeleteModal = true;
        },

        async confirmDeleteSale() {
            if (!this.deleteSaleData) return;
            
            this.loading = true;
            this.error = null;
            
            try {
                const response = await fetch(`/client/sales/${this.deleteSaleData.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });

                if (!response.ok) {
                    if (response.status === 401) {
                        throw new Error('Sesi telah berakhir. Silakan login ulang.');
                    } else if (response.status === 404) {
                        throw new Error('Data transaksi tidak ditemukan.');
                    } else if (response.status === 403) {
                        throw new Error('Anda tidak memiliki izin untuk menghapus transaksi ini.');
                    } else if (response.status === 500) {
                        throw new Error('Terjadi kesalahan server. Silakan coba lagi.');
                    } else {
                        throw new Error(`HTTP ${response.status}: Gagal menghapus data`);
                    }
                }

                const result = await response.json();
                if (result.success) {
                    this.showSuccessNotification('Transaksi Berhasil Dihapus', `Transaksi ${this.deleteSaleData.order_number} telah berhasil dihapus dari sistem.`);
                    
                    // Refresh data sales history
                    await this.loadSales();
                    
                    // Reset pagination jika halaman saat ini kosong
                    if (this.paginatedSales.length === 0 && this.currentPage > 1) {
                        this.currentPage = Math.max(1, this.currentPage - 1);
                    }
                    
                    this.showDeleteModal = false;
                    this.deleteSaleData = null;
                } else {
                    throw new Error(result.message || 'Gagal menghapus transaksi');
                }
            } catch (error) {
                console.error('Error deleting sale:', error);
                this.showErrorNotification('Gagal Menghapus Transaksi', error.message);
            } finally {
                this.loading = false;
            }
        },

        async submitAddForm() {
            // Validasi frontend
            if (!this.addOrderNumber) { 
                this.addOrderNumber = this.generateOrderNumber();
            }
            if (!this.addSaleDate) { 
                this.addSaleDate = new Date().toISOString().split('T')[0];
            }
            if (!this.addPaymentMethod) { 
                this.addPaymentMethod = 'Cash';
            }
            if (!this.addStatus) { 
                this.addStatus = 'completed';
            }

            // Validasi cart items
            if (this.cartItems.length === 0) {
                this.addErrorMsg = 'Pilih minimal satu item untuk dibeli.';
                return;
            }

            // Validasi stok tersedia untuk semua item di cart
            for (const cartItem of this.cartItems) {
                const stockItem = this.availableStockItems.find(item => item.id === cartItem.id);
                if (stockItem && cartItem.selectedQuantity > stockItem.tersedia) {
                    this.addErrorMsg = `Stok tidak cukup untuk ${cartItem.nama}. Tersedia: ${stockItem.tersedia}, Diminta: ${cartItem.selectedQuantity}`;
                    return;
                }
            }

            // Hitung total items dan quantity
            const totalItems = this.cartItems.length;
            const totalQuantity = this.cartItems.reduce((total, item) => total + Number(item.selectedQuantity), 0);
            const totalAmount = this.cartTotal;
            const amountPaid = totalAmount; // Untuk client, biasanya bayar sesuai total
            const changeAmount = 0; // Untuk client, biasanya tidak ada kembalian

            // Siapkan payload untuk backend
            const payload = {
                order_number: this.addOrderNumber,
                sale_date: this.addSaleDate,
                total_items: totalItems,
                total_quantity: totalQuantity,
                total_amount: totalAmount,
                payment_method: this.addPaymentMethod,
                amount_paid: amountPaid,
                change_amount: changeAmount,
                status: this.addStatus,
                notes: this.orderNotes || '',
                customer_phone: this.addCustomerPhone || '',
                customer_address: this.addCustomerAddress || '',
                items: this.cartItems.map(item => ({
                    item_name: item.nama,
                    item_sku: item.sku,
                    quantity: Number(item.selectedQuantity) || 0,
                    unit_price: Number(item.harga) || 0,
                    discount_percent: 0, // Diskon global, bukan per item
                    discount_amount: 0, // Diskon dalam rupiah
                    subtotal: Number(item.harga) * Number(item.selectedQuantity)
                }))
            };

            this.loading = true;
            this.error = null;
            try {
                const response = await fetch('/client/sales', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    if (response.status === 401) {
                        throw new Error('Sesi telah berakhir. Silakan login ulang.');
                    } else if (response.status === 400) {
                        const result = await response.json();
                        throw new Error(result.message || 'Data tidak valid');
                    } else {
                        throw new Error(`HTTP ${response.status}: Gagal menyimpan transaksi`);
                    }
                }

                const result = await response.json();
                if (result.success) {
                    this.showSuccessNotification('Transaksi Berhasil Ditambahkan', 'Transaksi baru telah berhasil ditambahkan ke sistem.');
                    this.loadSales(); // Refresh the sales history
                    this.showAddModal = false;
                    this.showAddStep2Modal = false;
                    this.resetAddForm();
                } else {
                    throw new Error(result.message || 'Gagal menyimpan transaksi');
                }
            } catch (error) {
                console.error('Error adding sale:', error);
                this.addErrorMsg = error.message;
            } finally {
                this.loading = false;
            }
        },

        async nextToStep2() {
            // Validasi form tahap 1
            if (!this.addOrderNumber) { 
                this.addOrderNumber = this.generateOrderNumber();
            }
            if (!this.addSaleDate) { 
                this.addSaleDate = new Date().toISOString().split('T')[0];
            }
            if (!this.addPaymentMethod) { 
                this.addPaymentMethod = 'Cash';
            }
            if (!this.addStatus) { 
                this.addStatus = 'completed';
            }
            
            this.loading = true;
            this.addErrorMsg = '';
            
            // Load data stok dari backend
            await this.loadStockItems();
            
            // Cek apakah ada stok tersedia
            if (this.availableStockItems.length === 0) {
                this.addErrorMsg = 'Tidak ada stok tersedia. Silakan tambahkan stok terlebih dahulu.';
                this.loading = false;
                return;
            }
            
            // Pindah ke modal tahap 2
            this.showAddModal = false;
            this.showAddStep2Modal = true;
            this.loading = false;
        },

        backToStep1() {
            this.showAddStep2Modal = false;
            this.showAddModal = true;
        },

        closeAddModal() {
            this.showAddModal = false;
            this.showAddStep2Modal = false;
            this.resetAddForm();
            this.addErrorMsg = '';
        },

        resetAddForm() {
            this.addOrderNumber = this.generateOrderNumber();
            this.addSaleDate = new Date().toISOString().split('T')[0];
            this.addPaymentMethod = '';
            this.addStatus = 'completed';
            this.addDiskonTipe = 'rupiah';
            this.addDiskonNilai = 0;
            this.addCustomerPhone = '';
            this.addCustomerAddress = '';
            this.cartItems = [];
            this.availableStockItems = [];
            this.stockItemSearch = '';
            this.orderNotes = '';
            this.addErrorMsg = '';
        },

        async loadStockItems() {
            try {
                const response = await fetch('/client/stock-items', {
                    headers: { 
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!response.ok) {
                    if (response.status === 401) {
                        throw new Error('Sesi telah berakhir. Silakan login ulang.');
                    } else if (response.status === 500) {
                        throw new Error('Terjadi kesalahan server. Silakan coba lagi.');
                    } else {
                        throw new Error(`HTTP ${response.status}: Gagal memuat data stok`);
                    }
                }
                
                const stockItems = await response.json();
                
                // Handle different response formats
                let items = [];
                if (Array.isArray(stockItems)) {
                    items = stockItems;
                } else if (stockItems.data && Array.isArray(stockItems.data)) {
                    items = stockItems.data;
                } else {
                    items = [];
                }
                
                // Filter hanya item dengan kategori "Umum" atau source "client"
                const filteredItems = items.filter(item => 
                    (item.kategori === 'Umum' || item.source === 'client') && 
                    item.source !== 'admin'
                );
                
                this.availableStockItems = filteredItems.map(item => ({
                    ...item,
                    harga: Number(item.harga) || 0,
                    tersedia: Number(item.tersedia) || 0,
                    selectedQuantity: 0
                }));
            } catch (error) {
                console.error('Error loading stock items:', error);
                this.addErrorMsg = 'Gagal memuat data stok: ' + error.message;
                this.availableStockItems = [];
            }
        },

        updateItemSubtotal(item) {
            // Update subtotal otomatis saat quantity berubah
            if (item.selectedQuantity < 0) {
                item.selectedQuantity = 0;
            }
        },

        addItemToCart(item) {
            if (!item.selectedQuantity || item.selectedQuantity <= 0) return;
            
            // Validasi quantity tidak melebihi stok tersedia
            const requestedQty = Number(item.selectedQuantity) || 0;
            const availableQty = Number(item.tersedia) || 0;
            
            if (requestedQty > availableQty) {
                this.showErrorNotification('Stok Tidak Cukup', `Quantity melebihi stok tersedia. Stok: ${availableQty}, Diminta: ${requestedQty}`);
                return;
            }
            
            // Cek apakah item sudah ada di cart
            const existingCartItem = this.cartItems.find(cartItem => cartItem.id === item.id);
            
            if (existingCartItem) {
                // Update quantity jika sudah ada
                const newTotalQty = Number(existingCartItem.selectedQuantity) + requestedQty;
                if (newTotalQty > availableQty) {
                    this.showErrorNotification('Stok Tidak Cukup', `Total quantity melebihi stok tersedia. Stok: ${availableQty}, Total: ${newTotalQty}`);
                    return;
                }
                existingCartItem.selectedQuantity = newTotalQty;
            } else {
                // Tambah item baru ke cart
                this.cartItems.push({
                    ...item,
                    harga: Number(item.harga) || 0,
                    selectedQuantity: requestedQty
                });
            }
            
            // Reset quantity di available items
            item.selectedQuantity = 0;
        },

        removeFromCart(index) {
            this.cartItems.splice(index, 1);
        },

        calculateDiscount(subtotal) {
            if (this.addDiskonNilai <= 0) return 0;
            
            if (this.addDiskonTipe === 'rupiah') {
                return Math.min(this.addDiskonNilai, subtotal);
            } else if (this.addDiskonTipe === 'persen') {
                return (subtotal * this.addDiskonNilai) / 100;
            }
            return 0;
        },

        hitungDiskon() {
            // Fungsi ini dipanggil saat tombol "Hitung Diskon" diklik
            // Bisa ditambahkan logika tambahan jika diperlukan
        },

        get cartSubtotal() {
            return this.cartItems.reduce((total, item) => {
                return total + (Number(item.harga) * Number(item.selectedQuantity));
            }, 0);
        },

        get cartTotal() {
            const subtotal = Number(this.cartSubtotal) || 0;
            const discount = Number(this.calculateDiscount(subtotal)) || 0;
            return Math.max(0, subtotal - discount);
        },

        async exportExcel() {
            const excelData = this.sortedSales.map(sale => ({
                'Order Number': sale.order_number,
                'Tanggal': new Date(sale.sale_date).toLocaleDateString('id-ID'),
                'Total Items': sale.total_items,
                'Total Quantity': sale.total_quantity,
                'Total Amount': `Rp ${Number(sale.total_amount).toLocaleString('id-ID')}`,
                'Payment Method': sale.payment_method,
                'Status': sale.status,
            }));

            const worksheet = XLSX.utils.json_to_sheet(excelData);
            worksheet['!cols'] = [
                { wch: 20 }, { wch: 15 }, { wch: 12 }, { wch: 15 }, 
                { wch: 18 }, { wch: 15 }, { wch: 12 }
            ];
            worksheet['!freeze'] = { xSplit: 0, ySplit: 1 };
            worksheet['!autofilter'] = { ref: "A1:G1" };

            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, "Sales History");
            XLSX.writeFile(workbook, "client_sales_history.xlsx");
        },

        exportPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            const tableColumn = ["Order Number", "Tanggal", "Total Items", "Total Amount", "Payment Method", "Status"];
            const tableRows = this.sortedSales.map(sale => [
                sale.order_number,
                new Date(sale.sale_date).toLocaleDateString('id-ID'),
                sale.total_items,
                `Rp ${Number(sale.total_amount).toLocaleString('id-ID')}`,
                sale.payment_method,
                sale.status
            ]);

            doc.autoTable({
                head: [tableColumn],
                body: tableRows,
                startY: 20,
                styles: { fontSize: 8, cellPadding: 2 },
                headStyles: { fillColor: [40, 195, 40], textColor: 255 }
            });
            doc.save("client_sales_history.pdf");
        },

        async exportInvoicePDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({ unit: 'pt', format: 'a4' });
            const pageWidth = doc.internal.pageSize.getWidth();
            const pageHeight = doc.internal.pageSize.getHeight();
            const margin = 40;
            const headerHeight = 110;
            let logoImage = null;

            if (this.company.logoUrl) {
                try {
                    logoImage = await new Promise((resolve) => {
                        const img = new Image();
                        img.crossOrigin = 'anonymous';
                        img.onload = () => resolve(img);
                        img.onerror = () => resolve(null);
                        img.src = this.company.logoUrl;
                    });
                } catch (e) {
                    logoImage = null;
                }
            }

            const formatCurrency = (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`;
            const dueDate = this.detailSaleData.sale_date
                ? new Date(new Date(this.detailSaleData.sale_date).getTime() + 7 * 24 * 60 * 60 * 1000)
                : new Date();

            const drawHeader = (pageNumber) => {
                if (logoImage) {
                    doc.addImage(logoImage, 'PNG', margin, margin, 48, 48);
                } else {
                    doc.setFont(undefined, 'bold');
                    doc.setFontSize(20);
                    doc.setTextColor(40, 195, 40);
                    doc.text((this.company.name || 'Toko').substring(0, 1), margin + 24, margin + 28, { align: 'center' });
                }

                doc.setTextColor(60);
                doc.setFontSize(12);
                doc.setFont(undefined, 'bold');
                doc.text(this.company.name || 'Nama Toko', margin + 60, margin + 12);
                doc.setFont(undefined, 'normal');
                doc.setFontSize(10);
                if (this.company.address) {
                    const addr = doc.splitTextToSize(this.company.address, 200);
                    doc.text(addr, margin + 60, margin + 26);
                }
                const contactParts = [];
                if (this.company.phone) contactParts.push(`Telp: ${this.company.phone}`);
                if (this.company.email) contactParts.push(`Email: ${this.company.email}`);
                if (contactParts.length) {
                    doc.text(contactParts.join('  |  '), margin + 60, margin + 46);
                }

                doc.setFontSize(16);
                doc.setFont(undefined, 'bold');
                doc.text('INVOICE', pageWidth - margin, margin + 12, { align: 'right' });
                doc.setFontSize(10);
                doc.setFont(undefined, 'normal');
                const metaY = margin + 28;
                doc.text(`No. Invoice: INV-${new Date().getFullYear()}-${this.detailSaleData.order_number || ''}`, pageWidth - margin, metaY, { align: 'right' });
                doc.text(`ID Pesanan: ${this.detailSaleData.order_number || '-'}`, pageWidth - margin, metaY + 14, { align: 'right' });
                doc.text(`Tanggal: ${new Date(this.detailSaleData.sale_date || Date.now()).toLocaleString('id-ID')}`, pageWidth - margin, metaY + 28, { align: 'right' });
                doc.text(`Jatuh Tempo: ${dueDate.toLocaleDateString('id-ID')}`, pageWidth - margin, metaY + 42, { align: 'right' });
                doc.text(`Status: ${this.detailSaleData.status === 'completed' ? 'Selesai' : (this.detailSaleData.status || '-')}`, pageWidth - margin, metaY + 56, { align: 'right' });

                doc.setDrawColor(230);
                doc.line(margin, margin + headerHeight, pageWidth - margin, margin + headerHeight);
            };

            const drawFooter = (pageNumber) => {
                doc.setFontSize(9);
                doc.setTextColor(130);
                doc.text(`Halaman ${pageNumber}`, margin, pageHeight - 25);
            };

            const recipientStartY = margin + headerHeight + 14;
            let currentY = recipientStartY;
            doc.setFontSize(10);
            doc.setFont(undefined, 'bold');
            doc.setTextColor(55, 120, 70);
            doc.text('Kepada:', margin, currentY);
            currentY += 14;

            doc.setFont(undefined, 'normal');
            doc.setTextColor(60);
            const customerName = this.detailSaleData.customer_name || 'Pelanggan';
            doc.text(customerName, margin, currentY);
            currentY += 12;
            doc.text(this.detailSaleData.order_number || '-', margin, currentY);
            if (this.detailSaleData.customer_phone) {
                currentY += 12;
                doc.text(`Telp: ${this.detailSaleData.customer_phone}`, margin, currentY);
            }
            if (this.detailSaleData.customer_address) {
                currentY += 12;
                const addressLines = doc.splitTextToSize(`Alamat: ${this.detailSaleData.customer_address}`, pageWidth - margin * 2);
                doc.text(addressLines, margin, currentY);
                currentY += addressLines.length * 12 - 12;
            }

            if (this.detailSaleData.notes && this.detailSaleData.notes.trim()) {
                currentY += 18;
                doc.setFont(undefined, 'bold');
                doc.setTextColor(180, 130, 0);
                doc.text('Catatan Pesanan:', margin, currentY);
                currentY += 12;
                doc.setFont(undefined, 'normal');
                doc.setTextColor(90);
                const noteLines = doc.splitTextToSize(this.detailSaleData.notes, pageWidth - margin * 2);
                doc.text(noteLines, margin, currentY);
                currentY += noteLines.length * 12;
            }

            const tableStartY = currentY + 16;
            const tableColumn = ['Nama Pesanan', 'Harga Barang', 'Quantity', 'Diskon', 'Jumlah Total'];
            const tableRows = (this.detailSaleData.items || []).map(item => [
                item.item_name,
                formatCurrency(item.unit_price),
                String(item.quantity),
                item.discount_amount > 0 ? formatCurrency(item.discount_amount) : '-',
                formatCurrency(item.subtotal)
            ]);

            doc.autoTable({
                head: [tableColumn],
                body: tableRows,
                startY: tableStartY,
                margin: { left: margin, right: margin, top: margin + headerHeight + 15, bottom: 80 },
                styles: { fontSize: 9, cellPadding: 6, valign: 'middle' },
                headStyles: { fillColor: [40, 195, 40], textColor: 255, fontStyle: 'bold' },
                columnStyles: {
                    1: { halign: 'right' },
                    2: { halign: 'center' },
                    3: { halign: 'center' },
                    4: { halign: 'right' }
                },
                didDrawPage: (data) => {
                    drawHeader(data.pageNumber);
                    drawFooter(data.pageNumber);
                }
            });

            const subtotalAmount = this.calculateDetailSubtotal(this.detailSaleData.items || []);
            const discountAmount = this.calculateDetailDiscount(this.detailSaleData.items || []);
            let summaryStartY = doc.lastAutoTable.finalY + 20;

            const ensureSpaceForSummary = () => {
                if (summaryStartY + 100 > pageHeight - margin) {
                    doc.addPage();
                    const { pageNumber } = doc.internal.getCurrentPageInfo();
                    drawHeader(pageNumber);
                    drawFooter(pageNumber);
                    summaryStartY = margin + headerHeight + 20;
                }
            };

            ensureSpaceForSummary();

            doc.setFontSize(10);
            doc.setFont(undefined, 'normal');
            doc.setTextColor(60);
            doc.text(`Subtotal: ${formatCurrency(subtotalAmount)}`, pageWidth - margin, summaryStartY, { align: 'right' });
            doc.text(`Total Diskon: ${formatCurrency(discountAmount)}`, pageWidth - margin, summaryStartY + 14, { align: 'right' });
            doc.setFont(undefined, 'bold');
            doc.setFontSize(12);
            doc.setTextColor(40, 195, 40);
            doc.text(`Total Bayar: ${formatCurrency(this.detailSaleData.total_amount || 0)}`, pageWidth - margin, summaryStartY + 34, { align: 'right' });

            summaryStartY += 70;
            ensureSpaceForSummary();

            doc.setFontSize(9);
            doc.setFont(undefined, 'normal');
            doc.setTextColor(120);
            doc.text('* Invoice ini sah tanpa tanda tangan dan dicetak otomatis oleh sistem GAFI.', margin, summaryStartY);
            const paymentInfo = this.company.bank && this.company.account ? `${this.company.bank} - ${this.company.account}` : 'Belum diatur';
            doc.text(`Pembayaran ke: ${paymentInfo}`, margin, summaryStartY + 14);

            doc.save(`invoice_${this.detailSaleData.order_number || 'gafi'}.pdf`);
        },

        init() {
            this.$watch('showEditStatusModal', value => {
                if (!value) this.editStatusData = {};
            });
            this.$el.addEventListener('edit-status', e => {
                this.showEditStatusModalHandler(e.detail);
            });
            this.$el.addEventListener('edit-status-2', e => {
                this.showEditModalHandler(e.detail);
            }); 
        },

        showEditStatusModalHandler(sale) {
            
            this.editStatusError = '';
            this.editErrorMsg = '';

            // 2. Mapping data secara lengkap untuk Tahap 1 & Tahap 2
            this.editStatusData = {
                id: sale.id,
                order_number: sale.order_number,
                customer_name: sale.customer_name || (sale.customer ? sale.customer.name : 'Umum'),
                sale_date: sale.sale_date,
                currentStatus: sale.status,
                newStatus: sale.status,
                
                // Memuat item: Pastikan formatnya sesuai dengan yang dibutuhkan loop x-for di Tahap 2
                // Gunakan JSON.parse(JSON.stringify()) untuk membuat salinan mandiri (deep copy)
                items: sale.items ? JSON.parse(JSON.stringify(sale.items.map(i => ({
                    id: i.stock_id || i.product_id || i.id,
                    nama: i.item_name || i.product_name || i.nama,
                    sku: i.item_sku || i.sku || '',
                    harga: Number(i.unit_price || i.price || i.harga || 0),
                    selectedQuantity: Number(i.quantity || 1)
                })))) : [],
                
                // Data Diskon & Catatan
                diskon_tipe: sale.discount_type || 'rupiah',
                diskon_nilai: Number(sale.discount_amount || 0),
                notes: sale.notes || sale.internal_notes || ''
            };

            // 3. Tampilkan Modal Tahap 1
            this.showEditStatusModal = true;
        },

        async showEditModalHandler(sale){
            await this.loadStockItems();
            this.editStatusError = '';
            this.editErrorMsg = '';

            // 2. Mapping data secara lengkap untuk Tahap 1 & Tahap 2
            this.editStatusData = {
                id: sale.id,
                order_number: sale.order_number,
                customer_name: sale.customer_name || (sale.customer ? sale.customer.name : 'Umum'),
                sale_date: sale.sale_date,
                currentStatus: sale.status,
                newStatus: sale.newStatus,
                
                // Memuat item: Pastikan formatnya sesuai dengan yang dibutuhkan loop x-for di Tahap 2
                // Gunakan JSON.parse(JSON.stringify()) untuk membuat salinan mandiri (deep copy)
                items: sale.items ? JSON.parse(JSON.stringify(sale.items.map(i => ({
                    id: i.stock_id || i.product_id || i.id,
                    nama: i.item_name || i.product_name || i.nama,
                    sku: i.item_sku || i.sku || '',
                    harga: Number(i.unit_price || i.price || i.harga || 0),
                    selectedQuantity: Number(i.selectedQuantity || 1)
                })))) : [],
                
                // Data Diskon & Catatan
                diskon_tipe: sale.discount_type || 'rupiah',
                diskon_nilai: Number(sale.discount_amount || 0),
                notes: sale.notes || sale.internal_notes || ''
            };

            // 3. Tampilkan Modal Tahap 1
            this.showEditModal = true;
        },

        formatDate(dateString) {
            if (!dateString) return '-';
            try {
                const date = new Date(dateString);
                return new Intl.DateTimeFormat('id-ID', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric'
                }).format(date);
            } catch (e) {
                return dateString;
            }
        },

        // Calculation helper functions for detail modal
        calculateDetailSubtotal(items) {
            if (!items || !Array.isArray(items)) return 0;
            return items.reduce((total, item) => {
                const price = Number(item.unit_price) || 0;
                const qty = Number(item.quantity) || 0;
                return total + (price * qty);
            }, 0);
        },

        calculateDetailDiscount(items) {
            if (!items || !Array.isArray(items)) return 0;
            return items.reduce((total, item) => {
                const discount = Number(item.discount_amount) || 0;
                return total + discount;
            }, 0);
        },

        // Notification helper functions
        showSuccessNotification(title, message) {
            this.notificationType = 'success';
            this.notificationTitle = title;
            this.notificationMessage = message;
            this.showNotification = true;
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                this.showNotification = false;
            }, 5000);
        },

        showErrorNotification(title, message) {
            this.notificationType = 'error';
            this.notificationTitle = title;
            this.notificationMessage = message;
            this.showNotification = true;
            
            // Auto-hide after 7 seconds for errors
            setTimeout(() => {
                this.showNotification = false;
            }, 7000);
        },

        showInfoNotification(title, message) {
            this.notificationType = 'info';
            this.notificationTitle = title;
            this.notificationMessage = message;
            this.showNotification = true;
            
            // Auto-hide after 4 seconds
            setTimeout(() => {
                this.showNotification = false;
            }, 4000);
        }
    }
}
</script>

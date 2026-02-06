@extends('layouts.admin')

@section('content')
<div class="bg-white rounded-xl shadow p-8" x-data="salesTable()">
    <h1 class="text-2xl font-bold text-[#28C328] mb-6">Sales History</h1>
    <div class="mb-6">
        <div class="flex flex-wrap items-center gap-2 justify-between">
            <div class="flex flex-1 gap-2 items-center">
                <!-- Search kiri -->
                <div class="w-64">
                    <div class="flex items-center border border-gray-300 rounded-lg px-4 py-1 bg-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#28C328] mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" /></svg>
                        <input type="text" placeholder="Cari Transaksi" x-model="search" class="flex-1 bg-transparent border-none outline-none text-gray-400 text-sm font-medium placeholder-gray-400 h-6">
                    </div>
                </div>
                <!-- Filter Tanggal Modern -->
                <div class="flex gap-2">
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
                        <div @click="status = ''; open = false" :class="{'bg-[#eafbe6] text-[#28C328]': status === ''}" class="px-4 py-2 cursor-pointer hover:bg-[#eafbe6] rounded-2xl">Status</div>
                        <template x-for="s in statuses" :key="s">
                            <div @click="status = s; open = false" :class="{'bg-[#eafbe6] text-[#28C328]': status === s}" class="px-4 py-2 cursor-pointer hover:bg-[#eafbe6] rounded-2xl" x-text="s"></div>
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
                <button class="rounded-lg bg-[#28C328] px-4 py-2 text-white text-sm font-semibold flex items-center gap-2 hover:bg-[#22a322] transition" @click="showAddModal = true; prepareAutocomplete()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Tambahkan Data
                </button>
            </div>
        </div>
        <!-- Table -->
        <div class="overflow-x-auto mt-4">
            <table class="min-w-full text-sm text-center">
                <thead>
                    <tr class="bg-[#28C328] text-white">
                        <th class="p-3 cursor-pointer select-none" @click="sortBy('nama_pemesan')">
                            <div class="flex items-center gap-1 justify-center">
                                <span>Nama Pemesan</span>
                                <span class="flex flex-col">
                                    <svg :class="{'opacity-100': sortKey==='nama_pemesan' && sortAsc, 'opacity-50': !(sortKey==='nama_pemesan' && sortAsc)}" class="w-3 h-3" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                    <svg :class="{'opacity-100': sortKey==='nama_pemesan' && !sortAsc, 'opacity-50': !(sortKey==='nama_pemesan' && !sortAsc)}" class="w-3 h-3 -mt-1" fill="white" viewBox="0 0 20 20"><path d="M10 14l4-4H6l4 4z"/></svg>
                                </span>
                            </div>
                        </th>
                        <th class="p-3 cursor-pointer select-none" @click="sortBy('id_pesanan')">
                            <div class="flex items-center gap-1 justify-center">
                                <span>ID Pesanan</span>
                                <span class="flex flex-col">
                                    <svg :class="{'opacity-100': sortKey==='id_pesanan' && sortAsc, 'opacity-50': !(sortKey==='id_pesanan' && sortAsc)}" class="w-3 h-3" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                    <svg :class="{'opacity-100': sortKey==='id_pesanan' && !sortAsc, 'opacity-50': !(sortKey==='id_pesanan' && !sortAsc)}" class="w-3 h-3 -mt-1" fill="white" viewBox="0 0 20 20"><path d="M10 14l4-4H6l4 4z"/></svg>
                                </span>
                            </div>
                        </th>
                        <th class="p-3 cursor-pointer select-none" @click="sortBy('jenis_transaksi')">
                            <div class="flex items-center gap-1 justify-center">
                                <span>Jenis Transaksi</span>
                                <span class="flex flex-col">
                                    <svg :class="{'opacity-100': sortKey==='jenis_transaksi' && sortAsc, 'opacity-50': !(sortKey==='jenis_transaksi' && sortAsc)}" class="w-3 h-3" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                    <svg :class="{'opacity-100': sortKey==='jenis_transaksi' && !sortAsc, 'opacity-50': !(sortKey==='jenis_transaksi' && !sortAsc)}" class="w-3 h-3 -mt-1" fill="white" viewBox="0 0 20 20"><path d="M10 14l4-4H6l4 4z"/></svg>
                                </span>
                            </div>
                        </th>
                        <th class="p-3 cursor-pointer select-none" @click="sortBy('total_quantity')">
                            <div class="flex items-center gap-1 justify-center">
                                <span>Jumlah Produk</span>
                                <span class="flex flex-col">
                                    <svg :class="{'opacity-100': sortKey==='total_quantity' && sortAsc, 'opacity-50': !(sortKey==='total_quantity' && sortAsc)}" class="w-3 h-3" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                    <svg :class="{'opacity-100': sortKey==='total_quantity' && !sortAsc, 'opacity-50': !(sortKey==='total_quantity' && !sortAsc)}" class="w-3 h-3 -mt-1" fill="white" viewBox="0 0 20 20"><path d="M10 14l4-4H6l4 4z"/></svg>
                                </span>
                            </div>
                        </th>
                        <th class="p-3 cursor-pointer select-none" @click="sortBy('diskon')">
                            <div class="flex items-center gap-1 justify-center">
                                <span>Diskon</span>
                                <span class="flex flex-col">
                                    <svg :class="{'opacity-100': sortKey==='diskon' && sortAsc, 'opacity-50': !(sortKey==='diskon' && sortAsc)}" class="w-3 h-3" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                    <svg :class="{'opacity-100': sortKey==='diskon' && !sortAsc, 'opacity-50': !(sortKey==='diskon' && !sortAsc)}" class="w-3 h-3 -mt-1" fill="white" viewBox="0 0 20 20"><path d="M10 14l4-4H6l4 4z"/></svg>
                                </span>
                            </div>
                        </th>
                        <th class="p-3 cursor-pointer select-none" @click="sortBy('total_harga')">
                            <div class="flex items-center gap-1 justify-center">
                                <span>Total Harga</span>
                                <span class="flex flex-col">
                                    <svg :class="{'opacity-100': sortKey==='total_harga' && sortAsc, 'opacity-50': !(sortKey==='total_harga' && sortAsc)}" class="w-3 h-3" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                    <svg :class="{'opacity-100': sortKey==='total_harga' && !sortAsc, 'opacity-50': !(sortKey==='total_harga' && !sortAsc)}" class="w-3 h-3 -mt-1" fill="white" viewBox="0 0 20 20"><path d="M10 14l4-4H6l4 4z"/></svg>
                                </span>
                            </div>
                        </th>
                        <th class="p-3 cursor-pointer select-none" @click="sortBy('status')">
                            <div class="flex items-center gap-1 justify-center">
                                <span>Status</span>
                                <span class="flex flex-col">
                                    <svg :class="{'opacity-100': sortKey==='status' && sortAsc, 'opacity-50': !(sortKey==='status' && sortAsc)}" class="w-3 h-3" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                    <svg :class="{'opacity-100': sortKey==='status' && !sortAsc, 'opacity-50': !(sortKey==='status' && !sortAsc)}" class="w-3 h-3 -mt-1" fill="white" viewBox="0 0 20 20"><path d="M10 14l4-4H6l4 4z"/></svg>
                                </span>
                            </div>
                        </th>
                        <th class="p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100 text-center align-middle">
                    <template x-for="(sale, idx) in paginatedSales" :key="sale.id">
                        <tr>
                            <td class="p-3 align-middle" x-text="sale.nama_pemesan"></td>
                            <td class="p-3 align-middle" x-text="sale.id_pesanan"></td>
                            <td class="p-3 align-middle">
                                <span :class="sale.jenis_transaksi === 'Transfer' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'" class="px-3 py-1 rounded-full text-xs font-semibold" x-text="sale.jenis_transaksi"></span>
                            </td>
                            <td class="p-3 align-middle" x-text="getTotalQuantityText(sale)"></td>
                            <td class="p-3 align-middle">
                                <span x-show="(saleTotalDiskon(sale) + saleTotalDiskonBall(sale)) > 0" class="text-red-600 font-semibold" x-text="formatTotalDiskonCombined(sale)"></span>
                                <span x-show="(saleTotalDiskon(sale) + saleTotalDiskonBall(sale)) <= 0" class="text-gray-400">-</span>
                            </td>
                            <td class="p-3 align-middle font-semibold" x-text="'Rp' + getSaleTotal(sale).toLocaleString('id-ID')"></td>
                            <td class="p-3 align-middle">
                                <span :class="statusClass(sale.status)" class="px-3 py-1 rounded-full text-xs font-semibold" x-text="sale.status"></span>
                            </td>
                            <td class="p-3 align-middle">
                                <div class="relative">
                                    <button @click="openActionMenuIndex = openActionMenuIndex === idx ? null : idx" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="6" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="18" r="1.5"/></svg>
                                    </button>
                                    <div x-show="openActionMenuIndex === idx" x-transition class="absolute right-0 mt-2 w-32 bg-white rounded-xl shadow-lg border border-gray-100 z-10">
                                        <button @click="detailSale(sale); openActionMenuIndex = null" class="block w-full text-left px-4 py-2 hover:bg-[#eafbe6] rounded-t-xl">Detail</button>
                                        <button @click="editSale(sale, idx); openActionMenuIndex = null" class="block w-full text-left px-4 py-2 hover:bg-[#eafbe6]">Edit</button>
                                        <button @click="deleteSale(sale, idx); openActionMenuIndex = null" class="block w-full text-left px-4 py-2 hover:bg-[#ffeaea] text-red-600 rounded-b-xl">Hapus</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <!-- Filter status indicator -->
        <div x-show="dateFilter" class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-center gap-2 text-sm text-blue-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z" />
                </svg>
                <span x-text="getFilterStatusText()"></span>
                <span class="font-semibold" x-text="'(' + filteredSales.length + ' dari ' + sales.length + ' transaksi)'"></span>
            </div>
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
    <!-- Modal Tambah Data - Tahap 1 (Data Transaksi) -->
    <div x-show="showAddModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" x-cloak @click.self="closeAddModal()">
        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-2xl mx-2 relative overflow-y-auto max-h-[70vh]" @click.stop>
            <button @click="closeAddModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold text-[#28C328]">Tahap 1: Data Transaksi</h2>
                <p class="text-gray-600 text-sm">Lengkapi informasi transaksi terlebih dahulu</p>
            </div>
            <form class="grid grid-cols-1 md:grid-cols-2 gap-4" @submit.prevent="nextToStep2()">
                <div x-show="addErrorMsg" class="col-span-1 md:col-span-2 text-red-500 text-sm mb-2" x-text="addErrorMsg"></div>
                <div>
                    <label class="block font-semibold mb-2">Nama Pemesan</label>
                    <div class="relative" x-data="{ showDropdown: false }">
                        <input type="text" 
                               x-model="addNamaPemesan" 
                               @focus="showDropdown = true"
                               @blur="setTimeout(() => showDropdown = false, 200)"
                               @input="filterCustomerNames()"
                               class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400" 
                               placeholder="Masukan Nama Pemesan atau pilih dari daftar">
                        <div x-show="showDropdown && filteredCustomerNames.length > 0" 
                             x-transition
                             class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            <template x-for="(customer, idx) in filteredCustomerNames" :key="idx">
                                <div @click="selectCustomer(customer)" 
                                     class="px-4 py-2 hover:bg-[#eafbe6] cursor-pointer border-b border-gray-100 last:border-b-0">
                                    <div class="font-semibold text-gray-800" x-text="customer.nama_pemesan"></div>
                                    <div class="text-xs text-gray-500">ID: <span x-text="customer.id_pesanan"></span></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold mb-2">ID Pesanan</label>
                    <div class="relative" x-data="{ showDropdown: false }">
                        <input type="text" 
                               x-model="addIdPesanan" 
                               @focus="showDropdown = true"
                               @blur="setTimeout(() => showDropdown = false, 200)"
                               @input="filterOrderIds()"
                               class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400" 
                               placeholder="Masukan ID Pesanan atau pilih dari daftar">
                        <div x-show="showDropdown && filteredOrderIds.length > 0" 
                             x-transition
                             class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            <template x-for="(order, idx) in filteredOrderIds" :key="idx">
                                <div @click="selectOrder(order)" 
                                     class="px-4 py-2 hover:bg-[#eafbe6] cursor-pointer border-b border-gray-100 last:border-b-0">
                                    <div class="font-semibold text-gray-800" x-text="order.id_pesanan"></div>
                                    <div class="text-xs text-gray-500">Nama: <span x-text="order.nama_pemesan"></span></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold mb-2">Nama Sales</label>
                    <input type="text" x-model="addNamaSales" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400" placeholder="Masukan Nama Sales">
                </div>
                <div>
                    <label class="block font-semibold mb-2">Jenis Transaksi</label>
                    <select x-model="addJenisTransaksi" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700">
                        <option value="">Pilih Jenis Transaksi</option>
                        <option value="Transfer">Transfer</option>
                        <option value="Tunai">Tunai</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold mb-2">Nomor Telepon</label>
                    <input type="text" x-model="addTelepon" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400" placeholder="Masukan Nomor Telepon">
                </div>
                <div>
                    <label class="block font-semibold mb-2">Alamat</label>
                    <textarea x-model="addAlamat" rows="3" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400 resize-none" placeholder="Masukan Alamat Lengkap"></textarea>
                </div>
                <div>
                    <label class="block font-semibold mb-2">Status</label>
                    <select x-model="addStatus" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700">
                        <option value="">Pilih Status</option>
                        <option value="Selesai">Selesai</option>
                        <option value="Dalam Proses-Sudah Dibayar">Dalam Proses-Sudah Dibayar</option>
                        <option value="Dalam Proses-Belum Dibayar">Dalam Proses-Belum Dibayar</option>
                        <option value="Belum approval">Belum approval</option>
                        <option value="Dibatalkan">Dibatalkan</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold mb-2">Periode (Tanggal)</label>
                    <input type="date" x-model="addPeriode" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700">
                </div>
                <!-- Tombol bawah -->
                <div class="col-span-2 flex flex-col md:flex-row gap-2 mt-2">
                    <button type="submit" class="w-full rounded-lg bg-[#28C328] text-white font-semibold py-3 text-lg hover:bg-[#22a322] transition">Selanjutnya</button>
                </div>
                <div class="col-span-2 flex flex-col md:flex-row gap-2">
                    <button type="reset" @click.prevent="resetAddForm()" class="w-full rounded-lg bg-gray-200 text-gray-500 font-semibold py-3 text-lg">Reset</button>
                    <button type="button" @click="closeAddModal()" class="w-full rounded-lg bg-gray-200 text-gray-500 font-semibold py-3 text-lg">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tambah Data - Tahap 2 (Data Pesanan) -->
    <div x-show="showAddStep2Modal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" x-cloak @click.self="backToStep1()">
        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-4xl mx-2 relative overflow-y-auto max-h-[80vh]" @click.stop>
            <button @click="backToStep1()" class="absolute top-4 left-4 text-gray-400 hover:text-gray-600 text-2xl font-bold">&larr;</button>
            <button @click="closeAddModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold text-[#28C328]">Tahap 2: Data Pesanan</h2>
                <p class="text-gray-600 text-sm">Pilih item yang akan dibeli</p>
            </div>
            
            <!-- Informasi Transaksi -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-gray-800 mb-2">Informasi Transaksi:</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
                    <div><span class="text-gray-600">Nama:</span> <span class="font-semibold" x-text="addNamaPemesan"></span></div>
                    <div><span class="text-gray-600">ID:</span> <span class="font-semibold" x-text="addIdPesanan"></span></div>
                    <div><span class="text-gray-600">Status:</span> <span class="font-semibold" x-text="addStatus"></span></div>
                    <div><span class="text-gray-600">Tanggal:</span> <span class="font-semibold" x-text="addPeriode"></span></div>
                </div>
            </div>

            <!-- Input Diskon Reguler -->
            <div class="bg-blue-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-blue-800 mb-3">Pengaturan Diskon Reguler</h3>
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
                    <span class="text-blue-700">Diskon Reguler: </span>
                    <span x-show="addDiskonTipe === 'rupiah'" class="font-semibold text-blue-800">Rp <span x-text="Number(addDiskonNilai).toLocaleString('id-ID')"></span></span>
                    <span x-show="addDiskonTipe === 'persen'" class="font-semibold text-blue-800"><span x-text="addDiskonNilai"></span>%</span>
                </div>
            </div>

            <!-- Input Diskon Ball -->
            <div class="bg-purple-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-purple-800 mb-3">Pengaturan Diskon Ball</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-semibold mb-2 text-sm">Tipe Diskon Ball</label>
                        <select x-model="addDiskonBallTipe" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="rupiah">Rupiah</option>
                            <option value="persen">Persen (%)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold mb-2 text-sm">Nilai Diskon Ball</label>
                        <input type="number" x-model="addDiskonBallNilai" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="0" min="0">
                    </div>
                    <div class="flex items-end">
                        <button type="button" @click="hitungDiskonBall()" class="w-full rounded-lg bg-purple-500 text-white font-semibold py-2 text-sm hover:bg-purple-600 transition">Hitung Diskon Ball</button>
                    </div>
                </div>
                <div x-show="addDiskonBallNilai > 0" class="mt-2 text-sm">
                    <span class="text-purple-700">Diskon Ball: </span>
                    <span x-show="addDiskonBallTipe === 'rupiah'" class="font-semibold text-purple-800">Rp <span x-text="Number(addDiskonBallNilai).toLocaleString('id-ID')"></span></span>
                    <span x-show="addDiskonBallTipe === 'persen'" class="font-semibold text-purple-800"><span x-text="addDiskonBallNilai"></span>%</span>
                </div>
            </div>

            <!-- Daftar Item Stok -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="font-semibold text-gray-800">Pilih Item dari Stok:</h3>
                        <div class="text-sm text-gray-600 mt-1">
                            Menampilkan <span x-text="Math.min((stockItemCurrentPage - 1) * stockItemPerPage + 1, filteredStockItems.length)"></span> sampai 
                            <span x-text="Math.min(stockItemCurrentPage * stockItemPerPage, filteredStockItems.length)"></span> 
                            dari <span x-text="filteredStockItems.length"></span> item
                        </div>
                    </div>
                    <div class="flex items-center bg-gray-50 rounded-lg px-3 py-1 border w-48">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z" />
                        </svg>
                        <input type="text" x-model="stockItemSearch" class="flex-1 bg-transparent border-none outline-none text-sm" placeholder="Cari item..." />
                    </div>
                </div>
                <div class="overflow-x-auto">
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
                            <template x-for="item in paginatedStockItems" :key="item.id">
                                <tr>
                                    <td class="p-2">
                                        <img :src="item.gambar.startsWith('data:') ? item.gambar : '{{ asset('') }}' + item.gambar" class="w-8 h-8 rounded object-cover" alt="item">
                                    </td>
                                    <td class="p-2" x-text="item.nama"></td>
                                    <td class="p-2 font-mono text-xs" x-text="item.sku"></td>
                                    <td class="p-2" x-text="Number(item.tersedia).toLocaleString('id-ID')"></td>
                                    <td class="p-2">Rp<span x-text="Number(item.harga).toLocaleString('id-ID')"></span></td>
                                    <td class="p-2">
                                        <input type="number" 
                                               x-model="item.selectedQuantity" 
                                               @input="updateItemSubtotal(item)"
                                               class="w-16 rounded border border-gray-300 px-2 py-1 text-sm" 
                                               min="0" 
                                               placeholder="0">
                                    </td>
                                    <td class="p-2 font-semibold">
                                        <span x-show="item.selectedQuantity > 0">Rp<span x-text="(Number(item.harga) * Number(item.selectedQuantity || 0)).toLocaleString('id-ID')"></span></span>
                                        <span x-show="item.selectedQuantity <= 0" class="text-gray-400">-</span>
                                    </td>
                                    <td class="p-2">
                                        <button type="button" 
                                                @click="addItemToCart(item)" 
                                                :disabled="!item.selectedQuantity || item.selectedQuantity <= 0"
                                                class="px-3 py-1 rounded text-xs font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                                                :class="item.selectedQuantity > 0 ? 'bg-[#28C328] text-white hover:bg-[#22a322]' : 'bg-gray-300 text-gray-500'">
                                            Tambah
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination untuk Stock Items -->
                <div x-show="filteredStockItems.length > stockItemPerPage" class="flex justify-center mt-4">
                    <nav class="flex items-center space-x-2">
                        <button @click="prevStockPage()" :disabled="stockItemCurrentPage === 1" class="w-8 h-8 flex items-center justify-center rounded-full border border-gray-300 text-gray-500 hover:bg-gray-100 disabled:opacity-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        </button>
                        
                        <!-- First page -->
                        <template x-if="stockItemTotalPages > 0">
                            <button @click="goToStockPage(1)" :class="{'bg-[#28C328] text-white': stockItemCurrentPage === 1, 'bg-white text-gray-700': stockItemCurrentPage !== 1 }" class="w-8 h-8 flex items-center justify-center rounded-full border border-gray-300 font-semibold">
                                <span>1</span>
                            </button>
                        </template>
                        
                        <!-- Ellipsis before current page -->
                        <template x-if="stockItemCurrentPage > 3">
                            <span class="w-8 h-8 flex items-center justify-center text-gray-500">...</span>
                        </template>
                        
                        <!-- Pages around current page -->
                        <template x-for="page in getVisibleStockPages()" :key="page">
                            <template x-if="page !== 1 && page !== stockItemTotalPages">
                                <button @click="goToStockPage(page)" :class="{'bg-[#28C328] text-white': stockItemCurrentPage === page, 'bg-white text-gray-700': stockItemCurrentPage !== page }" class="w-8 h-8 flex items-center justify-center rounded-full border border-gray-300 font-semibold">
                                    <span x-text="page"></span>
                                </button>
                            </template>
                        </template>
                        
                        <!-- Ellipsis after current page -->
                        <template x-if="stockItemCurrentPage < stockItemTotalPages - 2">
                            <span class="w-8 h-8 flex items-center justify-center text-gray-500">...</span>
                        </template>
                        
                        <!-- Last page -->
                        <template x-if="stockItemTotalPages > 1">
                            <button @click="goToStockPage(stockItemTotalPages)" :class="{'bg-[#28C328] text-white': stockItemCurrentPage === stockItemTotalPages, 'bg-white text-gray-700': stockItemCurrentPage !== stockItemTotalPages }" class="w-8 h-8 flex items-center justify-center rounded-full border border-gray-300 font-semibold">
                                <span x-text="stockItemTotalPages"></span>
                            </button>
                        </template>
                        
                        <button @click="nextStockPage()" :disabled="stockItemCurrentPage === stockItemTotalPages" class="w-8 h-8 flex items-center justify-center rounded-full border border-gray-300 text-gray-500 hover:bg-gray-100 disabled:opacity-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Keranjang Pesanan -->
            <div class="bg-green-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-green-800 mb-3">Keranjang Pesanan:</h3>
                <div x-show="cartItems.length === 0" class="text-center text-gray-500 py-4">
                    Belum ada item yang dipilih
                </div>
                <div x-show="cartItems.length > 0" class="space-y-2">
                    <template x-for="(cartItem, idx) in cartItems" :key="cartItem.id">
                        <div class="flex items-center justify-between bg-white rounded-lg p-3 border border-green-200">
                            <div class="flex items-center gap-3">
                                <img :src="cartItem.gambar.startsWith('data:') ? cartItem.gambar : '{{ asset('') }}' + cartItem.gambar" class="w-10 h-10 rounded object-cover" alt="item">
                                <div>
                                    <div class="font-semibold text-gray-800" x-text="cartItem.nama"></div>
                                    <div class="text-xs text-gray-500">SKU: <span x-text="cartItem.sku"></span></div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-600">
                                    <span x-text="cartItem.selectedQuantity"></span> x Rp<span x-text="Number(cartItem.harga).toLocaleString('id-ID')"></span>
                                </div>
                                <div class="font-semibold text-green-800">
                                    Rp<span x-text="(Number(cartItem.harga) * Number(cartItem.selectedQuantity)).toLocaleString('id-ID')"></span>
                                </div>
                            </div>
                            <button @click="removeFromCart(idx)" class="text-red-500 hover:text-red-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </template>
                    <!-- Total Quantity -->
                    <div class="bg-green-100 rounded-lg p-3 border border-green-300 mt-3">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-green-800">Total Quantity:</span>
                            <span class="font-bold text-green-900 text-lg" x-text="cartItems.reduce((sum, item) => sum + (Number(item.selectedQuantity) || 0), 0)"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Catatan Section -->
            <div class="bg-blue-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-blue-800 mb-3">Catatan</h3>
                <textarea x-model="addNotes" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm resize-none" rows="3" placeholder="Tambahkan catatan untuk transaksi ini (opsional)"></textarea>
            </div>

            <!-- Input Ongkir dan Ekspedisi -->
            <div class="bg-orange-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-orange-800 mb-3">Pengaturan Ongkir</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold mb-2 text-sm">Nama Ekspedisi</label>
                        <input type="text" x-model="addNamaEkspedisi" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Contoh: JNE, SiCepat, J&T">
                    </div>
                    <div>
                        <label class="block font-semibold mb-2 text-sm">Biaya Ongkir</label>
                        <input type="number" x-model="addOngkir" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="0" min="0">
                    </div>
                </div>
                <div x-show="addOngkir > 0" class="mt-2 text-sm">
                    <span class="text-orange-700">Ongkir: </span>
                    <span class="font-semibold text-orange-800">Rp <span x-text="Number(addOngkir).toLocaleString('id-ID')"></span></span>
                    <span x-show="addNamaEkspedisi" class="text-orange-700 ml-2">via <span class="font-semibold" x-text="addNamaEkspedisi"></span></span>
                </div>
            </div>

            <!-- Total dan Tombol -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="flex justify-between items-center mb-2">
                    <span class="font-semibold text-gray-700">Subtotal:</span>
                    <span class="font-semibold text-gray-800">Rp<span x-text="cartSubtotal.toLocaleString('id-ID')"></span></span>
                </div>
                <div x-show="addDiskonNilai > 0" class="flex justify-between items-center mb-2">
                    <span class="text-gray-600">Diskon Reguler:</span>
                    <span class="text-red-600">
                        <span x-show="addDiskonTipe === 'rupiah'">-Rp<span x-text="Number(addDiskonNilai).toLocaleString('id-ID')"></span></span>
                        <span x-show="addDiskonTipe === 'persen'">-<span x-text="addDiskonNilai"></span>%</span>
                    </span>
                </div>
                <div x-show="addDiskonBallNilai > 0" class="flex justify-between items-center mb-2">
                    <span class="text-gray-600">Diskon Ball:</span>
                    <span class="text-red-600">
                        <span x-show="addDiskonBallTipe === 'rupiah'">-Rp<span x-text="Number(addDiskonBallNilai).toLocaleString('id-ID')"></span></span>
                        <span x-show="addDiskonBallTipe === 'persen'">-<span x-text="addDiskonBallNilai"></span>%</span>
                    </span>
                </div>
                <div x-show="addOngkir > 0" class="flex justify-between items-center mb-2">
                    <span class="text-gray-600">Ongkir:</span>
                    <span class="text-green-600">+Rp<span x-text="Number(addOngkir).toLocaleString('id-ID')"></span></span>
                </div>
                <div class="flex justify-between items-center mb-4 pt-2 border-t border-gray-200">
                    <span class="text-lg font-bold text-gray-800">Total:</span>
                    <span class="text-xl font-bold text-[#28C328]">Rp<span x-text="cartTotal.toLocaleString('id-ID')"></span></span>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex flex-col md:flex-row gap-4">
                <button type="button" @click="backToStep1()" class="flex-1 rounded-lg bg-gray-200 text-gray-700 font-semibold py-3 hover:bg-gray-300 transition">Kembali</button>
                <button type="button" @click="submitAddForm()" :disabled="cartItems.length === 0" class="flex-1 rounded-lg bg-[#28C328] text-white font-semibold py-3 hover:bg-[#22a322] transition disabled:opacity-50 disabled:cursor-not-allowed">Simpan Transaksi</button>
            </div>
        </div>
    </div>
    <!-- Modal Detail Sales -->
    <div x-show="showDetailModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" x-cloak @click.self="showDetailModal = false">
        <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-5xl mx-4 relative overflow-y-auto max-h-[80vh]" @click.stop>
            <button @click="showDetailModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
            <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between bg-[#BAFFBA] rounded-t-2xl px-8 py-4">
                    <span class="font-bold text-lg">Detail Transaksi</span>
                </div>
                    <div class="w-full mt-6 bg-white rounded-b-2xl px-4">                        
                        <!-- Informasi Umum -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div class="space-y-3">
                                <div>
                                    <span class="text-xs text-gray-500">Nama Pemesan:</span>
                                    <span class="font-semibold block" x-text="detailSaleData.nama_pemesan"></span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500">ID Pesanan:</span>
                                    <span class="font-semibold block" x-text="detailSaleData.id_pesanan"></span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500">Nama Sales:</span>
                                    <span class="font-semibold block" x-text="detailSaleData.nama_sales || '-' "></span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500">Jenis Transaksi:</span>
                                    <span class="font-semibold block" :class="detailSaleData.jenis_transaksi === 'Transfer' ? 'text-blue-600' : 'text-green-600'" x-text="detailSaleData.jenis_transaksi"></span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500">Status:</span>
                                    <span class="font-semibold block" x-text="detailSaleData.status"></span>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <span class="text-xs text-gray-500">Periode:</span>
                                    <span class="font-semibold block" x-text="detailSaleData.periode"></span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500">Total Quantity:</span>
                                    <span class="font-semibold block text-blue-600" x-text="getTotalQuantityText(detailSaleData)"></span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500">Diskon Reguler:</span>
                                    <span class="font-semibold block" :class="detailSaleData.total_diskon > 0 ? 'text-red-600' : 'text-gray-400'" x-text="formatDiskon(detailSaleData)"></span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500">Diskon Ball:</span>
                                    <span class="font-semibold block" :class="detailSaleData.total_diskon_ball > 0 ? 'text-red-600' : 'text-gray-400'" x-text="formatDiskonBall(detailSaleData)"></span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500">Ongkir:</span>
                                    <span class="font-semibold block" :class="detailSaleData.ongkir > 0 ? 'text-green-600' : 'text-gray-400'" x-text="formatOngkir(detailSaleData)"></span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500">Total Harga:</span>
                                    <span class="font-bold block text-lg text-[#28C328]" x-text="'Rp ' + Number(detailSaleData.total_harga || 0).toLocaleString('id-ID')"></span>
                                </div>
                            </div>
                        </div>

                        <!-- CATATAN -->
                        <div class="mb-6">
                            <div class="font-bold mb-2" style="background:#BAFFBA; padding:6px; border-radius:8px;">CATATAN</div>
                            <div class="relative flex flex-col gap-2 mt-2 ml-2">
                                <span class="absolute left-1.5 top-3 bottom-3 w-0.5 bg-gray-200 z-0"></span>
                                <div class="flex items-start gap-2 relative z-10">
                                    <span class="w-2.5 h-2.5 rounded-full mt-0.5 inline-block bg-[#28C328]"></span>
                                    <div class="flex flex-col gap-0">
                                        <span class="text-xs text-gray-500 leading-tight">Pemesanan</span>
                                        <span class="text-xs text-gray-400 leading-tight" x-text="detailSaleData.periode || detailSaleData.tanggal || '-'"/>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2 relative z-10">
                                    <span class="w-2.5 h-2.5 rounded-full mt-0.5 inline-block bg-[#28C328]"></span>
                                    <div class="flex flex-col gap-0">
                                        <span class="text-xs text-gray-500 leading-tight">Penerbitan Invoice</span>
                                        <span class="text-xs text-gray-400 leading-tight" x-text="detailSaleData.periode || detailSaleData.tanggal || '-'"/>
                                    </div>
                                </div>
                                <!-- Sedang Diproses -->
                                <div class="flex items-start gap-2 relative z-10">
                                    <span class="w-2.5 h-2.5 rounded-full mt-0.5 inline-block"
                                          :class="(detailSaleData.status && detailSaleData.status.includes('Dalam Proses')) || detailSaleData.status === 'Selesai' ? 'bg-[#28C328]' : 'bg-gray-400'"></span>
                                    <div class="flex flex-col gap-0">
                                        <span class="text-xs text-gray-500 leading-tight">Sedang Diproses</span>
                                        <span class="text-xs text-gray-400 leading-tight" x-text="detailSaleData.periode || detailSaleData.tanggal || '-'"/>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2 relative z-10">
                                    <span class="w-2.5 h-2.5 rounded-full mt-0.5 inline-block" :class="(detailSaleData.status === 'Selesai' || detailSaleData.status === 'Dalam Proses-Sudah Dibayar') ? 'bg-[#28C328]' : 'bg-gray-400'"></span>
                                    <div class="flex flex-col gap-0">
                                        <span class="text-xs font-bold text-black leading-tight">Pembayaran</span>
                                        <span class="text-xs text-gray-400 leading-tight" x-text="detailSaleData.periode || detailSaleData.tanggal || '-'"/>
                                    </div>
                                </div>
                                <!-- Selesai -->
                                <div class="flex items-start gap-2 relative z-10">
                                    <span class="w-2.5 h-2.5 rounded-full mt-0.5 inline-block" :class="detailSaleData.status === 'Selesai' ? 'bg-[#28C328]' : 'bg-gray-400'"></span>
                                    <div class="flex flex-col gap-0">
                                        <span class="text-xs text-gray-500 leading-tight">Selesai</span>
                                        <span class="text-xs text-gray-400 leading-tight" x-text="detailSaleData.periode || detailSaleData.tanggal || '-'"/>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Daftar Item yang Dibeli -->
                        <div class="mb-6">
                            <div class="font-semibold mb-3 text-gray-800">Daftar Item yang Dibeli:</div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <template x-if="detailSaleData.items && detailSaleData.items.length > 0">
                                    <div class="space-y-3">
                                        <template x-for="(item, idx) in detailSaleData.items" :key="idx">
                                            <div class="flex items-center justify-between bg-white rounded-lg p-3 border border-gray-200">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 bg-[#28C328] rounded-full flex items-center justify-center text-white font-bold text-sm">
                                                        <span x-text="idx + 1"></span>
                                                    </div>
                                                    <div>
                                                        <div class="font-semibold text-gray-800" x-text="item.nama"></div>
                                                        <div class="text-xs text-gray-500">SKU: <span x-text="item.sku"></span></div>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-sm text-gray-600">
                                                        <span x-text="item.pivot?.quantity || item.quantity || 0"></span> x Rp<span x-text="Number(item.pivot?.harga || item.harga || 0).toLocaleString('id-ID')"></span>
                                                    </div>
                                                    <div class="font-semibold text-[#28C328]">
                                                        Rp<span x-text="Number(item.pivot?.subtotal || (item.pivot?.harga || item.harga || 0) * (item.pivot?.quantity || item.quantity || 0)).toLocaleString('id-ID')"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="!detailSaleData.items || detailSaleData.items.length === 0">
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
                                    <span class="block" x-text="(getAdminAddressLines()[0] || 'Gerbang Kuning Gudang Bumbu, Jalan Ceuri no 51 Kampung Sindang Asih,')"></span>
                                        <span class="block" x-text="(getAdminAddressLines()[1] || 'Katapang, Pamentasan, Kabupaten Bandung,')"></span>
                                    </div>
                                    <div class="text-xs text-gray-400">Telp: <span x-text="getAdminIdentity().phone || '{{ $adminIdentity['telepon'] }}'"></span> | Email: <span x-text="getAdminIdentity().email || '{{ $adminIdentity['email'] }}'"></span></div>
                    </div>
                    <div class="ml-auto text-right">
                        <div class="text-lg font-bold text-gray-700">INVOICE</div>
                                    <div class="text-xs text-gray-500">No. Invoice: <span x-text="getInvoiceNo(detailSaleData)"></span></div>
                                    <div class="text-xs text-gray-500">ID Pesanan: <span x-text="detailSaleData.id_pesanan"></span></div>
                        <div class="text-xs text-gray-500">Tanggal: <span x-text="detailSaleData.periode"></span></div>
                                    <div class="mt-2">
                                        <span :class="invoiceStatusClass(detailSaleData.status)" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold" x-text="detailSaleData.status"></span>
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
                                            <div class="font-bold text-[#28C328] text-lg" x-text="detailSaleData.nama_pemesan"></div>
                                        </div>
                                        <div>
                                            <span class="text-xs text-gray-500 font-medium">ID Pesanan:</span>
                                            <div class="text-sm text-gray-700" x-text="detailSaleData.id_pesanan"></div>
                                        </div>
                                        <div>
                                            <span class="text-xs text-gray-500 font-medium">No. Telepon:</span>
                                            <div class="text-sm text-gray-700" x-text="detailSaleData.telepon || '-'"></div>
                                        </div>
                                        <div>
                                            <span class="text-xs text-gray-500 font-medium">Alamat:</span>
                                            <div class="text-sm text-gray-700" x-text="detailSaleData.alamat || '-'"></div>
                                        </div>
                                        <div x-show="detailSaleData.notes || detailSaleData.nama_ekspedisi">
                                            <span class="text-xs text-gray-500 font-medium">Catatan:</span>
                                            <div class="text-sm text-gray-700">
                                                <span x-text="detailSaleData.notes || ''"></span>
                                                <span x-show="detailSaleData.nama_ekspedisi && detailSaleData.notes" class="ml-2">| Ekspedisi: <span x-text="detailSaleData.nama_ekspedisi"></span></span>
                                                <span x-show="detailSaleData.nama_ekspedisi && !detailSaleData.notes">Ekspedisi: <span x-text="detailSaleData.nama_ekspedisi"></span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabel Transaksi (match PDF) -->
                            <div class="overflow-x-auto mb-4 px-2">
                                <template x-if="detailSaleData.items && detailSaleData.items.length > 0">
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
                                            <template x-for="(item, iidx) in detailSaleData.items" :key="iidx">
                                                <tr class="even:bg-gray-50">
                                                    <td class="py-2 px-4 border-b" x-text="item.nama"></td>
                                                    <td class="py-2 px-4 border-b text-left font-mono" x-text="'Rp'+Number(item.pivot?.harga || item.harga || 0).toLocaleString('id-ID')"></td>
                                                    <td class="py-2 px-4 border-b text-left font-mono" x-text="item.pivot?.quantity || item.quantity || 0"></td>
                                                    <td class="py-2 px-4 border-b text-left font-bold font-mono" x-text="'Rp'+Number(itemTotalAfterAllDiskon(item, detailSaleData)).toLocaleString('id-ID')"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-gray-50">
                                                <td class="py-2 px-4 border-t font-semibold text-gray-700" colspan="2">Total Quantity:</td>
                                                <td class="py-2 px-4 border-t text-left font-bold font-mono" x-text="detailSaleData.items ? detailSaleData.items.reduce((sum, item) => sum + (Number(item.pivot?.quantity || item.quantity || 0)), 0) : 0"></td>
                                                <td class="py-2 px-4 border-t"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </template>
                                <template x-if="!detailSaleData.items || detailSaleData.items.length === 0">
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
                                            <td class="py-2 px-4 border-b" x-text="detailSaleData.nama_pesanan"></td>
                                                <td class="py-2 px-4 border-b text-left font-mono" x-text="'Rp'+Number(detailSaleData.harga_barang || 0).toLocaleString('id-ID')"></td>
                                                <td class="py-2 px-4 border-b text-left font-mono" x-text="detailSaleData.quantity || 0"></td>
                                                <td class="py-2 px-4 border-b text-left font-bold font-mono" x-text="'Rp'+((Number(detailSaleData.harga_barang || 0)*Number(detailSaleData.quantity || 0)) - Number(saleTotalDiskon(detailSaleData))).toLocaleString('id-ID')"></td>
                                                </tr>
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50">
                                <td class="py-2 px-4 border-t font-semibold text-gray-700" colspan="2">Total Quantity:</td>
                                <td class="py-2 px-4 border-t text-left font-bold font-mono" x-text="detailSaleData.quantity || 0"></td>
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
                                            <span x-text="'Rp'+Number(saleItemsSubtotal(detailSaleData)).toLocaleString('id-ID')"></span>
                                        </div>
                                        <div class="flex justify-between text-sm text-gray-600">
                                            <span>Total Diskon Reguler</span>
                                            <span x-text="saleTotalDiskon(detailSaleData) > 0 ? ('Rp'+Number(saleTotalDiskon(detailSaleData)).toLocaleString('id-ID')) : '-' "></span>
                                        </div>
                                        <div class="flex justify-between text-sm text-gray-600">
                                            <span>Total Diskon Ball</span>
                                            <span x-text="saleTotalDiskonBall(detailSaleData) > 0 ? ('Rp'+Number(saleTotalDiskonBall(detailSaleData)).toLocaleString('id-ID')) : '-' "></span>
                                        </div>
                                        <div class="flex justify-between text-sm text-gray-600">
                                            <span>Total Ongkir</span>
                                            <span x-text="formatOngkirWithExpedition(detailSaleData)"></span>
                                        </div>
                                        <div class="flex justify-between text-lg font-bold text-[#28C328] border-t mt-2 pt-2">
                                            <span>Total Bayar</span>
                                            <span x-text="'Rp'+(Number(detailSaleData.total_harga || 0)).toLocaleString('id-ID')"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500">* Invoice ini sah tanpa tanda tangan dan dicetak otomatis oleh sistem GAFI.</div>
                                <div class="text-xs text-gray-600 mt-1">Pembayaran ke:</div>
                                <div class="text-xs text-gray-600" x-show="getAdminIdentity().bank && getAdminIdentity().account" x-text="getAdminIdentity().bank + ' - ' + getAdminIdentity().account"></div>
                                <button class="rounded-lg bg-[#28C328] text-white font-semibold px-6 py-2 text-sm mt-3" @click="exportInvoicePDF">Export PDF</button>
                                </div>
                                </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Edit Data -->
    <div x-show="showEditModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" x-cloak @click.self="showEditModal = false; resetEditForm()">
        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-2xl mx-2 relative overflow-y-auto max-h-[70vh]" @click.stop>
            <button @click="showEditModal = false; resetEditForm()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
            <form class="grid grid-cols-1 md:grid-cols-2 gap-4" @submit.prevent="submitEditForm()">
                <div x-show="editErrorMsg" class="col-span-1 md:col-span-2 text-red-500 text-sm mb-2" x-text="editErrorMsg"></div>
                <div>
                    <label class="block font-semibold mb-2">Nama Pemesan</label>
                    <input type="text" x-model="editNamaPemesan" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400" placeholder="Masukan Nama Pemesan">
                </div>
                <div>
                    <label class="block font-semibold mb-2">ID Pesanan</label>
                    <input type="text" x-model="editIdPesanan" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400" placeholder="Masukan ID Pesanan">
                </div>
                <div class="hidden"></div>
                <div class="hidden"></div>
                <div class="hidden"></div>
                <div>
                    <label class="block font-semibold mb-2">Status</label>
                    <select x-model="editStatus" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700">
                        <option value="">Pilih Status</option>
                        <option value="Selesai">Selesai</option>
                        <option value="Dalam Proses-Sudah Dibayar">Dalam Proses-Sudah Dibayar</option>
                        <option value="Dalam Proses-Belum Dibayar">Dalam Proses-Belum Dibayar</option>
                        <option value="Belum approval">Belum approval</option>
                        <option value="Dibatalkan">Dibatalkan</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold mb-2">Periode (Tanggal)</label>
                    <input type="date" x-model="editPeriode" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700">
                </div>
                <div>
                    <label class="block font-semibold mb-2">Jenis Transaksi</label>
                    <select x-model="editJenisTransaksi" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700">
                        <option value="">Pilih Jenis Transaksi</option>
                        <option value="Transfer">Transfer</option>
                        <option value="Tunai">Tunai</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold mb-2">Nomor Telepon</label>
                    <input type="text" x-model="editTelepon" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400" placeholder="Masukan Nomor Telepon">
                </div>
                <div>
                    <label class="block font-semibold mb-2">Alamat</label>
                    <textarea x-model="editAlamat" rows="3" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400 resize-none" placeholder="Masukan Alamat Lengkap"></textarea>
                </div>
                <!-- Tombol bawah -->
                <div class="col-span-2 flex flex-col md:flex-row gap-2 mt-2">
                    <button type="submit" class="w-full rounded-lg bg-[#28C328] text-white font-semibold py-3 text-lg hover:bg-[#22a322] transition">Simpan</button>
                </div>
                <div class="col-span-2 flex flex-col md:flex-row gap-2">
                    <button type="reset" @click.prevent="resetEditForm()" class="w-full rounded-lg bg-gray-200 text-gray-500 font-semibold py-3 text-lg">Reset</button>
                    <button type="button" @click="resetEditForm(); showEditModal = false" class="w-full rounded-lg bg-gray-200 text-gray-500 font-semibold py-3 text-lg">Batal</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Modal Konfirmasi Hapus Sales -->
    <div x-show="showDeleteModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" x-cloak @click.self="showDeleteModal = false">
        <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md mx-4" @click.stop>
            <div class="flex flex-col items-center gap-4">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900">Hapus Sales</h2>
                <p class="text-gray-600 text-center">
                    Apakah Anda yakin ingin menghapus sales <span class="font-semibold" x-text="deleteSaleName"></span>?
                </p>
                <p class="text-sm text-gray-500 text-center">
                    Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="flex gap-3 w-full mt-4">
                    <button @click="showDeleteModal = false" class="flex-1 rounded-lg bg-gray-200 text-gray-700 font-semibold py-3 hover:bg-gray-300 transition">
                        Batal
                    </button>
                    <button @click="confirmDelete()" class="flex-1 rounded-lg bg-red-600 text-white font-semibold py-3 hover:bg-red-700 transition">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
function salesTable() {
    return {
        sales: [], // Data sales dari backend
        search: '',
        periode: '',
        status: '',
        currentPage: 1,
        perPage: 10,
        showAddModal: false,
        openActionMenuIndex: null,
        sortKey: '',
        sortAsc: true,
        sortCount: 0,
        adminIdentityCache: null, // Cache untuk data identitas admin
        customerNames: [], // Daftar nama pemesan unik dari sales
        filteredCustomerNames: [], // Filtered customer names untuk autocomplete
        filteredOrderIds: [], // Filtered order IDs untuk autocomplete
        sortBy(key) {
            if (this.sortKey !== key) {
                this.sortKey = key;
                this.sortAsc = true;
                this.sortCount = 1;
            } else if (this.sortAsc) {
                this.sortAsc = false;
                this.sortCount = 2;
            } else {
                this.sortKey = '';
                this.sortAsc = true;
                this.sortCount = 0;
            }
        },
        get filteredSales() {
            const search = this.search.toLowerCase();
            let filtered = this.sales.filter(sale =>
                Object.values(sale).some(val => String(val).toLowerCase().includes(search))
            ).filter(sale =>
                (this.status === '' || sale.status === this.status)
            );
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
                if (['jumlah_total','quantity'].includes(this.sortKey)) {
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
            // Ambil tahun unik dari data
            return [...new Set(this.sales.map(s => s.periode))];
        },
        get statuses() {
            return ['Selesai', 'Dalam Proses-Sudah Dibayar', 'Dalam Proses-Belum Dibayar', 'Belum approval', 'Dibatalkan'];
        },
        get filteredStockItems() {
            const search = this.stockItemSearch.toLowerCase();
            if (!search) return this.availableStockItems;
            return this.availableStockItems.filter(item => 
                item.nama.toLowerCase().includes(search) || 
                item.sku.toLowerCase().includes(search)
            );
        },
        get paginatedStockItems() {
            const start = (this.stockItemCurrentPage - 1) * this.stockItemPerPage;
            return this.filteredStockItems.slice(start, start + this.stockItemPerPage);
        },
        get stockItemTotalPages() {
            return Math.max(1, Math.ceil(this.filteredStockItems.length / this.stockItemPerPage));
        },
        getVisibleStockPages() {
            const total = this.stockItemTotalPages;
            const current = this.stockItemCurrentPage;
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
        statusClass(status) {
            if (status === 'Selesai') return 'bg-[#28C328] text-white';
            if (status === 'Dalam Proses-Sudah Dibayar') return 'bg-[#b6e388] text-[#28C328]';
            if (status === 'Dalam Proses-Belum Dibayar') return 'bg-[#ffa726] text-white';
            if (status === 'Belum approval') return 'bg-[#b6e388] text-[#28C328]';
            if (status === 'Dibatalkan') return 'bg-[#ff5c5c] text-white';
            return '';
        },
        exportExcel() {
            // Implementasi export Excel
            const excelData = [];
            let totalQuantity = 0;

            this.sortedSales.forEach(sale => {
                if (sale && sale.items && Array.isArray(sale.items) && sale.items.length > 0) {
                    sale.items.forEach(item => {
                        const qty = Number(item.pivot?.quantity || item.quantity || 0);
                        const harga = Number(item.pivot?.harga || item.harga || 0);
                        const subtotal = Number(item.pivot?.subtotal || (harga * qty)) || 0;
                        const diskonRegulerItem = this.itemProratedDiskon(item, sale) || 0;
                        const diskonBallItem = this.itemProratedDiskonBall(item, sale) || 0;
                        const totalItem = Math.max(0, subtotal - diskonRegulerItem - diskonBallItem);

                        totalQuantity += qty;

                        excelData.push({
                            'Nama Pemesan': sale.nama_pemesan || '',
                            'ID Pesanan': sale.id_pesanan || '',
                            'Jenis Transaksi': sale.jenis_transaksi || '',
                            'Nama Item': item.nama || item.nama_item || '-',
                            'Quantity': `${qty} unit`,
                            'Harga Satuan': 'Rp' + Number(harga).toLocaleString('id-ID'),
                            'Diskon Reguler Item': diskonRegulerItem > 0 ? 'Rp' + Number(diskonRegulerItem).toLocaleString('id-ID') : '-',
                            'Diskon Ball Item': diskonBallItem > 0 ? 'Rp' + Number(diskonBallItem).toLocaleString('id-ID') : '-',
                            'Subtotal Item': 'Rp' + Number(totalItem).toLocaleString('id-ID'),
                            'Status': sale.status || ''
                        });
                    });
                } else {
                    const qty = Number(sale?.total_quantity || sale?.quantity || 0);
                    const harga = Number(sale?.harga_barang || 0);
                    const subtotal = qty * harga;
                    const diskonRegulerItem = Number(sale?.total_diskon) || 0;
                    const diskonBallItem = Number(sale?.total_diskon_ball) || 0;
                    const totalItem = Math.max(0, subtotal - diskonRegulerItem - diskonBallItem);

                    if (qty > 0) {
                        totalQuantity += qty;
                    }

                    excelData.push({
                        'Nama Pemesan': sale?.nama_pemesan || '',
                        'ID Pesanan': sale?.id_pesanan || '',
                        'Jenis Transaksi': sale?.jenis_transaksi || '',
                        'Nama Item': sale?.nama_pesanan || sale?.nama_item || '-',
                        'Quantity': qty ? `${qty} unit` : '-',
                        'Harga Satuan': harga ? 'Rp' + Number(harga).toLocaleString('id-ID') : '-',
                        'Diskon Reguler Item': diskonRegulerItem > 0 ? 'Rp' + Number(diskonRegulerItem).toLocaleString('id-ID') : '-',
                        'Diskon Ball Item': diskonBallItem > 0 ? 'Rp' + Number(diskonBallItem).toLocaleString('id-ID') : '-',
                        'Subtotal Item': totalItem ? 'Rp' + Number(totalItem).toLocaleString('id-ID') : '-',
                        'Status': sale?.status || ''
                    });
                }
            });
            
            // Tambahkan row total
            excelData.push({
                'Nama Pemesan': '',
                'ID Pesanan': '',
                'Jenis Transaksi': '',
                'Nama Item': '',
                'Quantity': `TOTAL: ${totalQuantity} unit`,
                'Harga Satuan': '',
                'Diskon Reguler Item': '',
                'Diskon Ball Item': '',
                'Subtotal Item': '',
                'Status': ''
            });
            
            const worksheet = XLSX.utils.json_to_sheet(excelData);
            // Set column widths - kolom Total Quantity dibuat lebih lebar untuk menampung daftar item
            worksheet['!cols'] = [ 
                { wch: 20 },  // Nama Pemesan
                { wch: 18 },  // ID Pesanan
                { wch: 18 },  // Jenis Transaksi
                { wch: 40 },  // Nama Item
                { wch: 15 },  // Quantity
                { wch: 18 },  // Harga Satuan
                { wch: 18 },  // Diskon Reguler Item
                { wch: 18 },  // Diskon Ball Item
                { wch: 18 },  // Subtotal Item
                { wch: 20 }   // Status
            ];
            
            // Set wrap text untuk kolom Nama Item agar daftar panjang tetap terlihat
            const range = XLSX.utils.decode_range(worksheet['!ref']);
            for (let row = 1; row <= range.e.r; row++) {
                const cellAddress = XLSX.utils.encode_cell({ r: row, c: 3 }); // Kolom D (index 3)
                if (!worksheet[cellAddress]) continue;
                worksheet[cellAddress].s = {
                    alignment: { wrapText: true, vertical: 'top' }
                };
            }
            
            worksheet['!freeze'] = { xSplit: 0, ySplit: 1 };
            worksheet['!autofilter'] = { ref: 'A1:J1' };
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, 'Sales Data');
            XLSX.writeFile(workbook, 'sales_data.xlsx');
        },
        exportPDF() {
            // Implementasi export PDF
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            const tableColumn = ['Nama Pemesan', 'ID Pesanan', 'Jenis Transaksi', 'Total Quantity', 'Diskon Reguler', 'Diskon Ball', 'Ongkir', 'Total Harga', 'Status'];
            const tableRows = this.sortedSales.map(sale => [
                sale.nama_pemesan || '',
                sale.id_pesanan || '',
                sale.jenis_transaksi || '',
                this.getTotalQuantityText(sale),
                this.formatDiskon(sale),
                this.formatDiskonBall(sale),
                this.formatOngkirWithExpedition(sale),
                'Rp'+this.getSaleTotal(sale).toLocaleString('id-ID'),
                sale.status || ''
            ]);
            
            // Hitung total quantity
            let totalQuantity = 0;
            this.sortedSales.forEach(sale => {
                if (sale && sale.items && Array.isArray(sale.items) && sale.items.length > 0) {
                    totalQuantity += sale.items.reduce((total, item) => total + (Number(item.pivot?.quantity || item.quantity || 0)), 0);
                } else if (sale && sale.total_quantity && Number(sale.total_quantity) > 0) {
                    totalQuantity += Number(sale.total_quantity);
                } else if (sale && sale.quantity && Number(sale.quantity) > 0) {
                    totalQuantity += Number(sale.quantity);
                }
            });
            
            // Tambahkan row total
            tableRows.push([
                '',
                '',
                '',
                `TOTAL: ${totalQuantity} unit`,
                '',
                '',
                '',
                '',
                ''
            ]);
            
            doc.autoTable({
                head: [tableColumn],
                body: tableRows,
                startY: 20,
                styles: { fontSize: 8, cellPadding: 2 },
                headStyles: { fillColor: [40, 195, 40], textColor: 255 },
                footStyles: { fontStyle: 'bold' }
            });
            doc.save('sales_data.pdf');
        },
        // Modal/aksi
        showAddModal: false,
        showAddStep2Modal: false,
        addNamaPemesan: '',
        addIdPesanan: '',
        addStatus: '',
        addPeriode: '',
        addJenisTransaksi: '',
        addTelepon: '',
        addAlamat: '',
        addNamaSales: '',
        addDiskonTipe: 'rupiah',
        addDiskonNilai: 0,
        addDiskonBallTipe: 'rupiah',
        addDiskonBallNilai: 0,
        addNamaEkspedisi: '',
        addOngkir: 0,
        addErrorMsg: '',
        cartItems: [],
        availableStockItems: [],
        stockItemSearch: '',
        stockItemCurrentPage: 1,
        stockItemPerPage: 10,
        addNotes: '',
        showDetailModal: false,
        detailSaleData: {},
        showInvoiceModal: false,
        showDeleteModal: false,
        deleteIndex: null,
        deleteSaleName: '',
        resetAddForm() {
            this.addNamaPemesan = '';
            this.addIdPemesan = '';
            this.addStatus = '';
            this.addPeriode = '';
            this.addJenisTransaksi = '';
            this.addTelepon = '';
            this.addAlamat = '';
            this.addNamaSales = '';
            this.addDiskonTipe = 'rupiah';
            this.addDiskonNilai = 0;
            this.addDiskonBallTipe = 'rupiah';
            this.addDiskonBallNilai = 0;
            this.addNamaEkspedisi = '';
            this.addOngkir = 0;
            this.addErrorMsg = '';
            this.cartItems = [];
            this.stockItemSearch = '';
            this.addNotes = '';
        },
        submitAddForm() {
            // Validasi frontend (opsional, bisa tetap ada)
            if (!this.addNamaPemesan) { this.addErrorMsg = 'Field Nama Pemesan wajib diisi.'; return; }
            if (!this.addIdPesanan) { this.addErrorMsg = 'Field ID Pesanan wajib diisi.'; return; }
            if (!this.addStatus) { this.addErrorMsg = 'Field Status wajib diisi.'; return; }
            if (!this.addPeriode) { this.addErrorMsg = 'Field Periode wajib diisi.'; return; }
            if (!this.addJenisTransaksi) { this.addErrorMsg = 'Field Jenis Transaksi wajib diisi.'; return; }

            // Validasi telepon dan alamat
            if (!this.addTelepon) { this.addErrorMsg = 'Field Nomor Telepon wajib diisi.'; return; }
            if (!this.addAlamat) { this.addErrorMsg = 'Field Alamat wajib diisi.'; return; }

            // Validasi cart items
            if (this.cartItems.length === 0) {
                this.addErrorMsg = 'Pilih minimal satu item untuk dibeli.';
                return;
            }

            // Tidak perlu set nilai default untuk Tunai

            // Siapkan payload untuk backend
            const payload = {
                nama_pemesan: this.addNamaPemesan,
                id_pesanan: this.addIdPesanan,
                nama_sales: this.addNamaSales || null,
                status: this.addStatus,
                periode: this.addPeriode,
                jenis_transaksi: this.addJenisTransaksi,
                telepon: this.addTelepon,
                alamat: this.addAlamat,
                diskon_tipe: this.addDiskonTipe || null,
                diskon_nilai: Number(this.addDiskonNilai) || 0,
                diskon_ball_tipe: this.addDiskonBallTipe || null,
                diskon_ball_nilai: Number(this.addDiskonBallNilai) || 0,
                nama_ekspedisi: this.addNamaEkspedisi || null,
                ongkir: Number(this.addOngkir) || 0,
                notes: this.addNotes || '',
                items: this.cartItems.map(item => ({
                    id: item.id,
                    selectedQuantity: Number(item.selectedQuantity) || 0,
                    harga: Number(item.harga) || 0,
                }))
            };

            // Log data yang akan dikirim
            console.log('Payload yang akan dikirim:', payload);
            console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]').content);

            // Kirim ke backend
            fetch('/admin/sales', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(payload)
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => { 
                        console.error('Backend error:', err);
                        throw err; 
                    });
                }
                return res.json();
            })
            .then((data) => {
                console.log('Success response:', data);
                this.fetchSales();
                this.resetAddForm();
                this.closeAddModal();
            })
            .catch((error) => {
                console.error('Frontend error:', error);
                this.addErrorMsg = 'Gagal menyimpan transaksi: ' + (error.message || 'Periksa inputan Anda.');
            });
        },
        detailSale(sale) {
            this.detailSaleData = {...sale};
            this.showDetailModal = true;
        },
        editSale(sale, idx) {
            this.editIndex = idx;
            this.editNamaPemesan = sale.nama_pemesan;
            this.editIdPesanan = sale.id_pesanan;
            this.editNamaPesanan = sale.nama_pesanan;
            this.editHargaBarang = sale.harga_barang;
            this.editQuantity = sale.quantity;
            this.editStatus = sale.status;
            this.editPeriode = sale.periode;
            this.editJenisTransaksi = sale.jenis_transaksi;
            this.editTelepon = sale.telepon || '';
            this.editAlamat = sale.alamat || '';
            this.editErrorMsg = '';
            this.showEditModal = true;
        },
        showEditModal: false,
        editIndex: null,
        editNamaPemesan: '',
        editIdPesanan: '',
        editNamaPesanan: '',
        editHargaBarang: '',
        editQuantity: '',
        editStatus: '',
        editPeriode: '',
        editJenisTransaksi: '',
        editTelepon: '',
        editAlamat: '',
        editErrorMsg: '',
        resetEditForm() {
            this.editNamaPemesan = '';
            this.editIdPesanan = '';
            this.editNamaPesanan = '';
            this.editHargaBarang = '';
            this.editQuantity = '';
            this.editStatus = '';
            this.editPeriode = '';
            this.editJenisTransaksi = '';
            this.editTelepon = '';
            this.editAlamat = '';
            this.editErrorMsg = '';
            this.editIndex = null;
        },
        async submitEditForm() {
            // Validasi minimal - hanya nama pemesan yang wajib
            if (!this.editNamaPemesan) { this.editErrorMsg = 'Field Nama Pemesan wajib diisi.'; return; }
            if (!this.editIdPesanan) { this.editErrorMsg = 'Field ID Pesanan wajib diisi.'; return; }
            if (!this.editStatus) { this.editErrorMsg = 'Field Status wajib diisi.'; return; }
            if (!this.editPeriode) { this.editErrorMsg = 'Field Periode wajib diisi.'; return; }
            if (!this.editJenisTransaksi) { this.editErrorMsg = 'Field Jenis Transaksi wajib diisi.'; return; }
            if (!this.editTelepon) { this.editErrorMsg = 'Field Nomor Telepon wajib diisi.'; return; }
            if (!this.editAlamat) { this.editErrorMsg = 'Field Alamat wajib diisi.'; return; }
            const payload = {
                nama_pemesan: this.editNamaPemesan,
                id_pesanan: this.editIdPesanan,
                status: this.editStatus,
                periode: this.editPeriode,
                jenis_transaksi: this.editJenisTransaksi,
                telepon: this.editTelepon,
                alamat: this.editAlamat
            };
            const id = this.sales[this.editIndex].id;
            fetch(`/admin/sales/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(payload)
            })
            .then(res => {
                if (!res.ok) throw new Error('Gagal update data');
                return res.json();
            })
            .then(() => {
                this.fetchSales();
                this.showEditModal = false;
            })
            .catch(() => { this.editErrorMsg = 'Gagal update data'; });
        },
        async exportInvoicePDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({ unit: 'pt', format: 'a4' });
            const pageWidth = doc.internal.pageSize.getWidth();
            const pageHeight = doc.internal.pageSize.getHeight();
            const margin = 40;
            const headerHeight = 120;
            const adminIdent = this.getAdminIdentity();
            const detail = this.detailSaleData || {};
            const fallbackAddress = 'Gerbang Kuning Gudang Bumbu, Jalan Ceuri no 51 Kampung Sindang Asih, Katapang, Pamentasan, Kabupaten Bandung, Jawa Barat 40921';
            const address = adminIdent.address || fallbackAddress;
            const paymentBank = adminIdent.bank || 'BCA';
            const paymentAccount = adminIdent.account || '1234567890';
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
                    const harga = Number(item.pivot?.harga || item.harga || 0);
                    const qty = Number(item.pivot?.quantity || item.quantity || 0);
                    const subtotal = Number(item.pivot?.subtotal || harga * qty) || 0;
                    totalQuantity += qty;
                    return [
                        String(item.nama || ''),
                        formatCurrency(harga),
                        String(qty),
                        formatCurrency(subtotal)
                    ];
                })
                : (() => {
                    const harga = Number(detail.harga_barang || 0);
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
                styles: { fontSize: 9, cellPadding: 6, valign: 'middle' },
                headStyles: { fillColor: [186, 255, 186], textColor: 60, fontStyle: 'bold' },
                footStyles: { fillColor: [240, 240, 240], textColor: 60, fontStyle: 'bold' },
                columnStyles: {
                    1: { halign: 'right' },
                    2: { halign: 'center' },
                    3: { halign: 'right' }
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
        
        // Admin identity helpers (using database API)
        getAdminIdentity() {
            // Return cached data if available
            if (this.adminIdentityCache) {
                return this.adminIdentityCache;
            }
            
            // Default values
            const defaultIdentity = {
                phone: '(021) 12345678',
                address: 'Gerbang Kuning Gudang Bumbu, Jalan Ceuri no 51 Kampung Sindang Asih, Katapang, Pamentasan, Kabupaten Bandung, Jawa Barat 40921',
                email: 'info@gafi.co.id',
                bank: 'BCA',
                account: '1234567890'
            };
            
            // Fetch from API asynchronously
            fetch('/admin/identity', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (!data.error) {
                    this.adminIdentityCache = {
                        phone: data.telepon || defaultIdentity.phone,
                        address: data.alamat || defaultIdentity.address,
                        email: data.email || defaultIdentity.email,
                        bank: data.bank || defaultIdentity.bank,
                        account: data.no_rekening || defaultIdentity.account
                    };
                } else {
                    this.adminIdentityCache = defaultIdentity;
                }
            })
            .catch(error => {
                console.error('Error loading admin identity:', error);
                this.adminIdentityCache = defaultIdentity;
            });
            
            // Return default values while loading
            return defaultIdentity;
        },
        getAdminAddressLines() {
            const addr = (this.getAdminIdentity().address || 'Gerbang Kuning Gudang Bumbu, Jalan Ceuri no 51 Kampung Sindang Asih, Katapang, Pamentasan, Kabupaten Bandung, Jawa Barat 40921');
            // Split into ~3 lines for layout consistency
            const parts = String(addr).split(',').map(s => s.trim());
            return [
                parts.slice(0, 3).join(', '),
                parts.slice(3, 6).join(', '),
                parts.slice(6).join(', ')
            ];
        },

        // Fungsi untuk modal 2 tahap
        nextToStep2() {
            // Validasi form tahap 1
            if (!this.addNamaPemesan) { this.addErrorMsg = 'Field Nama Pemesan wajib diisi.'; return; }
            if (!this.addIdPesanan) { this.addErrorMsg = 'Field ID Pesanan wajib diisi.'; return; }
            if (!this.addStatus) { this.addErrorMsg = 'Field Status wajib diisi.'; return; }
            if (!this.addPeriode) { this.addErrorMsg = 'Field Periode wajib diisi.'; return; }
            if (!this.addJenisTransaksi) { this.addErrorMsg = 'Field Jenis Transaksi wajib diisi.'; return; }
            // Validasi telepon dan alamat
            if (!this.addTelepon) { this.addErrorMsg = 'Field Nomor Telepon wajib diisi.'; return; }
            if (!this.addAlamat) { this.addErrorMsg = 'Field Alamat wajib diisi.'; return; }
            
            // Load data stok dari localStorage
            this.loadStockItems();
            
            // Pindah ke modal tahap 2
            this.showAddModal = false;
            this.showAddStep2Modal = true;
            this.addErrorMsg = '';
            // Reset pagination stock items
            this.stockItemCurrentPage = 1;
        },
        
        // Fungsi untuk mempersiapkan autocomplete saat modal dibuka
        prepareAutocomplete() {
            // Update customer names jika belum ada
            if (this.customerNames.length === 0) {
                this.updateCustomerNames();
            }
            // Inisialisasi filtered lists dengan 10 data pertama
            this.filteredCustomerNames = this.customerNames.slice(0, 10);
            this.filteredOrderIds = this.customerNames.slice(0, 10);
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

        loadStockItems() {
            // Ambil data stok dari backend (bukan localStorage)
            fetch('/admin/stock-items', {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(stockItems => {
                this.availableStockItems = stockItems.map(item => ({
                    ...item,
                    harga: Number(item.harga) || 0,
                    tersedia: Number(item.tersedia) || 0,
                    selectedQuantity: 0
                }));
            })
            .catch(() => { this.availableStockItems = []; });
        },

        updateItemSubtotal(item) {
            // Update subtotal otomatis saat quantity berubah
            if (item.selectedQuantity < 0) {
                item.selectedQuantity = 0;
            }
        },

        addItemToCart(item) {
            if (!item.selectedQuantity || item.selectedQuantity <= 0) return;
            
            // Cek apakah item sudah ada di cart
            const existingCartItem = this.cartItems.find(cartItem => cartItem.id === item.id);
            
            const requestedQty = Number(item.selectedQuantity) || 0;
            
            if (existingCartItem) {
                // Update quantity jika sudah ada
                existingCartItem.selectedQuantity = Number(existingCartItem.selectedQuantity) + requestedQty;
            } else {
                // Tambah item baru ke cart (sama seperti di client - tanpa validasi stok)
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
        
        // Pagination functions untuk stock items
        prevStockPage() { 
            if (this.stockItemCurrentPage > 1) this.stockItemCurrentPage--; 
        },
        nextStockPage() { 
            if (this.stockItemCurrentPage < this.stockItemTotalPages) this.stockItemCurrentPage++; 
        },
        goToStockPage(page) { 
            this.stockItemCurrentPage = page; 
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

        calculateDiscountBall(subtotal, diskonReguler) {
            if (this.addDiskonBallNilai <= 0) return 0;
            
            if (this.addDiskonBallTipe === 'rupiah') {
                return Math.min(this.addDiskonBallNilai, subtotal);
            } else if (this.addDiskonBallTipe === 'persen') {
                return (subtotal * this.addDiskonBallNilai) / 100;
            }
            return 0;
        },

        hitungDiskon() {
            // Fungsi ini dipanggil saat tombol "Hitung Diskon" diklik
            // Bisa ditambahkan logika tambahan jika diperlukan
        },

        hitungDiskonBall() {
            // Fungsi ini dipanggil saat tombol "Hitung Diskon Ball" diklik
            // Bisa ditambahkan logika tambahan jika diperlukan
        },

        get cartSubtotal() {
            return this.cartItems.reduce((total, item) => {
                return total + (Number(item.harga) * Number(item.selectedQuantity));
            }, 0);
        },

        get cartTotal() {
            const subtotal = Number(this.cartSubtotal) || 0;
            const diskonReguler = Number(this.calculateDiscount(subtotal)) || 0;
            const diskonBall = Number(this.calculateDiscountBall(subtotal, diskonReguler)) || 0;
            const ongkir = Number(this.addOngkir) || 0;
            return Math.max(0, subtotal - diskonReguler - diskonBall + ongkir);
        },

        // Helper functions untuk tampilan data
        getTotalQuantityText(sale) {
            if (sale && sale.items && Array.isArray(sale.items) && sale.items.length > 0) {
                const totalItems = sale.items.length;
                const totalQuantity = sale.items.reduce((total, item) => total + (Number(item.pivot?.quantity || item.quantity || 0)), 0);
                return `${totalItems} item - ${totalQuantity} unit`;
            } else if (sale && sale.total_quantity && Number(sale.total_quantity) > 0) {
                return `${sale.total_quantity} unit`;
            } else if (sale && sale.quantity && Number(sale.quantity) > 0) {
                return `${sale.quantity} unit`;
            }
            return '0 unit';
        },
        
        // Fungsi untuk mendapatkan total quantity dengan daftar item (untuk export Excel)
        getTotalQuantityWithItems(sale) {
            if (sale && sale.items && Array.isArray(sale.items) && sale.items.length > 0) {
                const totalItems = sale.items.length;
                const totalQuantity = sale.items.reduce((total, item) => total + (Number(item.pivot?.quantity || item.quantity || 0)), 0);
                
                // Buat daftar item secara vertikal (setiap item di baris baru)
                const itemsList = sale.items.map(item => {
                    const itemName = item.nama || item.nama_item || 'Unknown';
                    const qty = Number(item.pivot?.quantity || item.quantity || 0);
                    return `- ${itemName} (qty: ${qty})`;
                }).join('\n');
                
                return `${totalItems} item - ${totalQuantity} unit\nItem:\n${itemsList}`;
            } else if (sale && sale.total_quantity && Number(sale.total_quantity) > 0) {
                // Untuk data lama yang tidak punya items array
                const itemName = sale.nama_pesanan || sale.nama_item || 'Unknown';
                return `${sale.total_quantity} unit\nItem:\n- ${itemName} (qty: ${sale.total_quantity})`;
            } else if (sale && sale.quantity && Number(sale.quantity) > 0) {
                const itemName = sale.nama_pesanan || sale.nama_item || 'Unknown';
                return `${sale.quantity} unit\nItem:\n- ${itemName} (qty: ${sale.quantity})`;
            }
            return '0 unit';
        },

        formatDiskon(sale) {
            if (sale && sale.total_diskon && Number(sale.total_diskon) > 0) {
                if (sale.diskon_tipe === 'persen') {
                    return `${sale.diskon_nilai}%`;
                } else {
                    return `Rp ${Number(sale.total_diskon).toLocaleString('id-ID')}`;
                }
            }
            return '-';
        },

        formatDiskonBall(sale) {
            if (sale && sale.total_diskon_ball && Number(sale.total_diskon_ball) > 0) {
                if (sale.diskon_ball_tipe === 'persen') {
                    return `${sale.diskon_ball_nilai}%`;
                } else {
                    return `Rp ${Number(sale.total_diskon_ball).toLocaleString('id-ID')}`;
                }
            }
            return '-';
        },

        // Teks gabungan total diskon (reguler + ball)
        formatTotalDiskonCombined(sale) {
            const total = (Number(this.saleTotalDiskon(sale)) || 0) + (Number(this.saleTotalDiskonBall(sale)) || 0);
            return 'Rp ' + Number(total).toLocaleString('id-ID');
        },

        formatOngkir(sale) {
            if (sale && sale.ongkir && Number(sale.ongkir) > 0) {
                let text = `Rp ${Number(sale.ongkir).toLocaleString('id-ID')}`;
                if (sale.nama_ekspedisi) {
                    text += ` (${sale.nama_ekspedisi})`;
                }
                return text;
            }
            return '-';
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

        // Fungsi untuk handle perubahan jenis transaksi (tidak diperlukan lagi)

        getSaleTotal(sale) {
            if (sale && sale.total_harga && Number(sale.total_harga) > 0) {
                return Number(sale.total_harga);
            }
            
            if (sale && sale.items && Array.isArray(sale.items) && sale.items.length > 0) {
                const itemsTotal = sale.items.reduce((total, item) => {
                    const harga = Number(item.pivot?.harga || item.harga || 0);
                    const quantity = Number(item.pivot?.quantity || item.quantity || 0);
                    const subtotal = Number(item.pivot?.subtotal || (harga * quantity)) || 0;
                    return total + subtotal;
                }, 0);
                const diskon = Number(sale.total_diskon) || 0;
                return Math.max(0, itemsTotal - diskon);
            }
            
            // Fallback untuk data lama
            const singleTotal = (Number(sale.harga_barang) || 0) * (Number(sale.quantity) || 0);
            const diskon = Number(sale.total_diskon) || 0;
            const computed = singleTotal - diskon;
            return computed > 0 ? computed : singleTotal;
        },

        // Helpers untuk invoice multi-item dan diskon
        saleItemsSubtotal(sale) {
            if (sale && sale.items && Array.isArray(sale.items) && sale.items.length > 0) {
                return sale.items.reduce((total, item) => {
                    const harga = Number(item.pivot?.harga || item.harga || 0);
                    const qty = Number(item.pivot?.quantity || item.quantity || 0);
                    const subtotal = Number(item.pivot?.subtotal || (harga * qty)) || 0;
                    return total + subtotal;
                }, 0);
            }
            
            // Fallback untuk data lama
            return (Number(sale.harga_barang) || 0) * (Number(sale.quantity) || 0);
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

        saleTotalOngkir(sale) {
            return Number(sale && sale.ongkir) || 0;
        },

        itemProratedDiskon(item, sale) {
            const subtotalItems = this.saleItemsSubtotal(sale);
            const totalDiskon = this.saleTotalDiskon(sale);
            const harga = Number(item.pivot?.harga || item.harga || 0);
            const qty = Number(item.pivot?.quantity || item.quantity || 0);
            const subtotal = Number(item.pivot?.subtotal || (harga * qty)) || 0;
            
            if (subtotalItems <= 0 || totalDiskon <= 0) return 0;
            return Math.round((subtotal / subtotalItems) * totalDiskon);
        },

        itemTotalAfterDiskon(item, sale) {
            const harga = Number(item.pivot?.harga || item.harga || 0);
            const qty = Number(item.pivot?.quantity || item.quantity || 0);
            const subtotal = Number(item.pivot?.subtotal || (harga * qty)) || 0;
            return subtotal - this.itemProratedDiskon(item, sale);
        },

        itemProratedDiskonBall(item, sale) {
            const subtotalItems = this.saleItemsSubtotal(sale);
            const totalDiskonBall = this.saleTotalDiskonBall(sale);
            const harga = Number(item.pivot?.harga || item.harga || 0);
            const qty = Number(item.pivot?.quantity || item.quantity || 0);
            const subtotal = Number(item.pivot?.subtotal || (harga * qty)) || 0;
            
            if (subtotalItems <= 0 || totalDiskonBall <= 0) return 0;
            
            // Diskon ball dihitung dari subtotal asli (bukan setelah diskon reguler)
            const proratedDiskon = (subtotal / subtotalItems) * totalDiskonBall;
            
            // Untuk item terakhir, pastikan total diskon ball tidak melebihi yang tersisa
            const itemIndex = sale.items.findIndex(i => i.id === item.id);
            if (itemIndex === sale.items.length - 1) {
                // Hitung total diskon ball yang sudah digunakan oleh item sebelumnya
                let totalDiskonBallUsed = 0;
                for (let i = 0; i < sale.items.length - 1; i++) {
                    const prevItem = sale.items[i];
                    const prevHarga = Number(prevItem.pivot?.harga || prevItem.harga || 0);
                    const prevQty = Number(prevItem.pivot?.quantity || prevItem.quantity || 0);
                    const prevSubtotal = Number(prevItem.pivot?.subtotal || (prevHarga * prevQty)) || 0;
                    const prevProrated = Math.round((prevSubtotal / subtotalItems) * totalDiskonBall);
                    totalDiskonBallUsed += prevProrated;
                }
                
                // Item terakhir mendapat sisa diskon ball
                return totalDiskonBall - totalDiskonBallUsed;
            }
            
            return Math.round(proratedDiskon);
        },

        itemTotalAfterAllDiskon(item, sale) {
            const harga = Number(item.pivot?.harga || item.harga || 0);
            const qty = Number(item.pivot?.quantity || item.quantity || 0);
            const subtotal = Number(item.pivot?.subtotal || (harga * qty)) || 0;
            const diskonReguler = this.itemProratedDiskon(item, sale);
            const diskonBall = this.itemProratedDiskonBall(item, sale);
            return subtotal - diskonReguler - diskonBall;
        },

        invoiceStatusClass(status) {
            if (status === 'Selesai' || status === 'Dalam Proses-Sudah Dibayar') return 'bg-[#eafbe6] text-[#28C328]';
            if (status === 'Dalam Proses-Belum Dibayar' || status === 'Belum approval') return 'bg-orange-100 text-orange-600';
            if (status === 'Dibatalkan') return 'bg-red-100 text-red-600';
            return 'bg-gray-100 text-gray-600';
        },
        // Helpers: Invoice number and due date
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
        getDueDate(sale, days = 7) {
            try {
                const base = sale && sale.periode ? new Date(sale.periode) : new Date();
                base.setDate(base.getDate() + Number(days));
                return base.toLocaleDateString('id-ID');
            } catch (_) {
                return '';
            }
        },
        // Kurangi stok pada localStorage berdasarkan cart
        decrementStockForCart() {
            // Tidak perlu lagi, stok dikurangi di backend
        },
        // --- FETCH DATA SALES DARI BACKEND ---
        fetchSales() {
            fetch('/admin/sales', {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => { 
                this.sales = data;
                // Update customer names list untuk autocomplete
                this.updateCustomerNames();
            })
            .catch(() => { this.sales = []; });
        },
        
        // Update customer names dari sales data
        updateCustomerNames() {
            // Ambil semua kombinasi nama pemesan dan ID pesanan (bisa duplikat)
            this.customerNames = this.sales.map(sale => ({
                nama_pemesan: sale.nama_pemesan || '',
                id_pesanan: sale.id_pesanan || '',
                telepon: sale.telepon || '',
                alamat: sale.alamat || '',
                nama_sales: sale.nama_sales || '',
                jenis_transaksi: sale.jenis_transaksi || ''
            })).filter(item => item.nama_pemesan && item.id_pesanan);
        },
        
        // Filter customer names berdasarkan input
        filterCustomerNames() {
            const search = (this.addNamaPemesan || '').toLowerCase();
            if (!search) {
                this.filteredCustomerNames = this.customerNames.slice(0, 10); // Limit 10 untuk performa
            } else {
                this.filteredCustomerNames = this.customerNames
                    .filter(customer => 
                        customer.nama_pemesan.toLowerCase().includes(search) ||
                        customer.id_pesanan.toLowerCase().includes(search)
                    )
                    .slice(0, 10);
            }
        },
        
        // Filter order IDs berdasarkan input
        filterOrderIds() {
            const search = (this.addIdPesanan || '').toLowerCase();
            if (!search) {
                this.filteredOrderIds = this.customerNames.slice(0, 10); // Limit 10 untuk performa
            } else {
                this.filteredOrderIds = this.customerNames
                    .filter(order => 
                        order.id_pesanan.toLowerCase().includes(search) ||
                        order.nama_pemesan.toLowerCase().includes(search)
                    )
                    .slice(0, 10);
            }
        },
        
        // Select customer dari dropdown
        selectCustomer(customer) {
            this.addNamaPemesan = customer.nama_pemesan;
            this.addIdPesanan = customer.id_pesanan;
            if (customer.telepon) this.addTelepon = customer.telepon;
            if (customer.alamat) this.addAlamat = customer.alamat;
            if (customer.nama_sales) this.addNamaSales = customer.nama_sales;
            if (customer.jenis_transaksi) this.addJenisTransaksi = customer.jenis_transaksi;
        },
        
        // Select order dari dropdown
        selectOrder(order) {
            this.addIdPesanan = order.id_pesanan;
            this.addNamaPemesan = order.nama_pemesan;
            if (order.telepon) this.addTelepon = order.telepon;
            if (order.alamat) this.addAlamat = order.alamat;
            if (order.nama_sales) this.addNamaSales = order.nama_sales;
            if (order.jenis_transaksi) this.addJenisTransaksi = order.jenis_transaksi;
        },
        // --- HAPUS DATA SALES ---
        confirmDelete() {
            const id = this.sales[this.deleteIndex].id;
            fetch(`/admin/sales/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('Gagal hapus data');
                return res.json();
            })
            .then(() => {
                this.fetchSales();
                this.showDeleteModal = false;
            })
            .catch(() => { alert('Gagal hapus data'); });
        },
        // --- INISIALISASI ---
        init() {
            this.fetchSales();
            this.loadAdminIdentity();
            
            // Watch for search changes to reset pagination
            this.$watch('stockItemSearch', () => {
                this.stockItemCurrentPage = 1;
            });
            
            // Initialize filtered lists
            this.filteredCustomerNames = [];
            this.filteredOrderIds = [];
        },
        
        // Load admin identity data
        loadAdminIdentity() {
            fetch('/admin/identity', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (!data.error) {
                    this.adminIdentityCache = {
                        phone: data.telepon || '(021) 12345678',
                        address: data.alamat || 'Gerbang Kuning Gudang Bumbu, Jalan Ceuri no 51 Kampung Sindang Asih, Katapang, Pamentasan, Kabupaten Bandung, Jawa Barat 40921',
                        email: data.email || 'info@gafi.co.id',
                        bank: data.bank || 'BCA',
                        account: data.no_rekening || '1234567890'
                    };
                }
            })
            .catch(error => {
                console.error('Error loading admin identity:', error);
            });
        },
        deleteSale(sale, idx) {
            this.deleteIndex = idx;
            this.deleteSaleName = sale.nama_pemesan + ' (' + sale.id_pesanan + ')';
            this.showDeleteModal = true;
        },
        dateFilter: '',
        customStartDate: '',
        customEndDate: '',
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
                case 'today': return 'Menampilkan transaksi hari ini';
                case 'week': return 'Menampilkan transaksi minggu ini';
                case 'month': return 'Menampilkan transaksi bulan ini';
                case 'year': return 'Menampilkan transaksi tahun ini';
                case 'custom':
                    if (this.customStartDate && this.customEndDate) {
                        const start = new Date(this.customStartDate).toLocaleDateString('id-ID');
                        const end = new Date(this.customEndDate).toLocaleDateString('id-ID');
                        return `Menampilkan transaksi dari ${start} sampai ${end}`;
                    }
                    return 'Menampilkan transaksi berdasarkan rentang tanggal custom';
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
                    if (!this.customStartDate || !this.customEndDate) return sales;
                    startDate = new Date(this.customStartDate);
                    endDate = new Date(this.customEndDate);
                    endDate.setHours(23, 59, 59, 999);
                    break;
                default: return sales;
            }
            return sales.filter(sale => {
                const saleDate = sale.periode ? new Date(sale.periode) : null;
                return saleDate && saleDate >= startDate && saleDate <= endDate;
            });
        },
    }
}
</script>


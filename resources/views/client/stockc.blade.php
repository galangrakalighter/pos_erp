@extends('layouts.client')

@section('content')
<style>
    [x-cloak] { display: none !important; }
</style>
<div class="bg-white rounded-xl shadow p-8" x-data="stockTable()" x-init="initRealtime()">
    <h1 class="text-2xl font-bold text-[#28C328] mb-6">Stock Management</h1>
    <div class="mb-6">
        <div class="flex flex-wrap items-center gap-2 justify-between">
            <div class="flex flex-1 gap-2 items-center">
                <!-- Search kiri -->
                <div class="w-64">
                    <div class="flex items-center border border-gray-300 rounded-lg px-4 py-1 bg-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#28C328] mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" /></svg>
                        <input type="text" placeholder="Cari Item" x-model="search" @input.debounce.500ms="onSearchChange()" class="flex-1 bg-transparent border-none outline-none text-gray-400 text-sm font-medium placeholder-gray-400 h-6">
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
                        <div @click="status = ''; open = false; onSearchChange()" :class="{'bg-[#eafbe6] text-[#28C328]': status === ''}" class="px-4 py-2 cursor-pointer hover:bg-[#eafbe6] rounded-2xl">Status</div>
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
                {{-- <button class="rounded-lg bg-[#28C328] px-4 py-2 text-white text-sm font-semibold flex items-center gap-2 hover:bg-[#22a322] transition" @click="showAddModal = true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Tambahkan Item
                </button> --}}
                <div class="relative">
                    <button class="rounded-lg bg-[#28C328] px-4 py-2 text-white text-sm font-semibold flex items-center gap-2 hover:bg-[#22a322] transition" @click="showAddDropdown = !showAddDropdown">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Tambahkan Item
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="showAddDropdown" @click.away="showAddDropdown = false" class="absolute right-0 mt-2 w-44 bg-white rounded-xl shadow-lg border border-gray-100 z-10">
                        <button @click="showAddModal = true; showAddDropdown = false" class="block w-full text-left px-4 py-2 hover:bg-[#eafbe6] rounded-t-xl">Input Manual</button>
                        <button @click="showImportModal = true; showAddDropdown = false" class="block w-full text-left px-4 py-2 hover:bg-[#eafbe6] rounded-b-xl">Import Data</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Loading removed for cleaner UX -->

        <div x-show="error" class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-red-700" x-text="error"></span>
            </div>
        </div>

        <!-- Filter status indicator -->
        <div x-show="dateFilter" class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-center gap-2 text-sm text-blue-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 0 0-.293.707V17l-4 4v-6.586a1 1 0 0 0-.293-.707L3.293 7.207A1 1 0 0 1 3 6.5V4z" /></svg>
                <span x-text="getFilterStatusText()"></span>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto mt-4" x-show="!loading && !error">
            <table class="min-w-full text-sm text-center">
            <thead>
                <tr class="bg-[#28C328] text-white">
                        <th class="p-3 cursor-pointer select-none rounded-tl-xl" @click="sortBy('nama')">
                            <div class="flex items-center gap-1 justify-center">
                                <span>Nama Item</span>
                                <span class="flex flex-col">
                                    <svg :class="{'opacity-100': sortKey==='nama' && sortAsc, 'opacity-50': !(sortKey==='nama' && sortAsc)}" class="w-3 h-3" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                    <svg :class="{'opacity-100': sortKey==='nama' && !sortAsc, 'opacity-50': !(sortKey==='nama' && !sortAsc)}" class="w-3 h-3 -mt-1" fill="white" viewBox="0 0 20 20"><path d="M10 14l4-4H6l4 4z"/></svg>
                                </span>
                        </div>
                    </th>
                        <th class="p-3 cursor-pointer select-none" @click="sortBy('sku')">
                            <div class="flex items-center gap-1 justify-center">
                                <span>SKU</span>
                                <span class="flex flex-col">
                                    <svg :class="{'opacity-100': sortKey==='sku' && sortAsc, 'opacity-50': !(sortKey==='sku' && sortAsc)}" class="w-3 h-3" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                    <svg :class="{'opacity-100': sortKey==='sku' && !sortAsc, 'opacity-50': !(sortKey==='sku' && !sortAsc)}" class="w-3 h-3 -mt-1" fill="white" viewBox="0 0 20 20"><path d="M10 14l4-4H6l4 4z"/></svg>
                                </span>
                        </div>
                    </th>
                        <th class="p-3 cursor-pointer select-none" @click="sortBy('kategori')">
                            <div class="flex items-center gap-1 justify-center">
                                <span>Kategori</span>
                                <span class="flex flex-col">
                                    <svg :class="{'opacity-100': sortKey==='kategori' && sortAsc, 'opacity-50': !(sortKey==='kategori' && sortAsc)}" class="w-3 h-3" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                    <svg :class="{'opacity-100': sortKey==='kategori' && !sortAsc, 'opacity-50': !(sortKey==='kategori' && !sortAsc)}" class="w-3 h-3 -mt-1" fill="white" viewBox="0 0 20 20"><path d="M10 14l4-4H6l4 4z"/></svg>
                                </span>
                        </div>
                    </th>
                        <th class="p-3 cursor-pointer select-none" @click="sortBy('tersedia')">
                            <div class="flex items-center gap-1 justify-center">
                                <span>Tersedia</span>
                                <span class="flex flex-col">
                                    <svg :class="{'opacity-100': sortKey==='tersedia' && sortAsc, 'opacity-50': !(sortKey==='tersedia' && sortAsc)}" class="w-3 h-3" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                    <svg :class="{'opacity-100': sortKey==='tersedia' && !sortAsc, 'opacity-50': !(sortKey==='tersedia' && !sortAsc)}" class="w-3 h-3 -mt-1" fill="white" viewBox="0 0 20 20"><path d="M10 14l4-4H6l4 4z"/></svg>
                                </span>
                        </div>
                    </th>
                        <th class="p-3 cursor-pointer select-none" @click="sortBy('harga')">
                            <div class="flex items-center gap-1 justify-center">
                                <span>Harga</span>
                                <span class="flex flex-col">
                                    <svg :class="{'opacity-100': sortKey==='harga' && sortAsc, 'opacity-50': !(sortKey==='harga' && sortAsc)}" class="w-3 h-3" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                    <svg :class="{'opacity-100': sortKey==='harga' && !sortAsc, 'opacity-50': !(sortKey==='harga' && !sortAsc)}" class="w-3 h-3 -mt-1" fill="white" viewBox="0 0 20 20"><path d="M10 14l4-4H6l4 4z"/></svg>
                                </span>
                        </div>
                    </th>
                        <th class="p-3 cursor-pointer select-none" @click="sortBy('diperbaharui')">
                            <div class="flex items-center gap-1 justify-center">
                                <span>Diperbaharui</span>
                                <span class="flex flex-col">
                                    <svg :class="{'opacity-100': sortKey==='diperbaharui' && sortAsc, 'opacity-50': !(sortKey==='diperbaharui' && sortAsc)}" class="w-3 h-3" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                    <svg :class="{'opacity-100': sortKey==='diperbaharui' && !sortAsc, 'opacity-50': !(sortKey==='diperbaharui' && !sortAsc)}" class="w-3 h-3 -mt-1" fill="white" viewBox="0 0 20 20"><path d="M10 14l4-4H6l4 4z"/></svg>
                                </span>
                        </div>
                    </th>
                        <th class="p-3 rounded-tr-xl">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100 text-center align-middle">
                    <template x-for="(item, idx) in paginatedItems" :key="item.id">
                <tr>
                            <td class="p-3 align-middle" x-text="item.nama"></td>
                            <td class="p-3 align-middle" x-text="item.sku"></td>
                            <td class="p-3 align-middle" x-text="item.kategori || 'Umum'"></td>
                            <td class="p-3 align-middle">
                                <span :class="Number(item.tersedia) <= 10 ? 'text-red-600 font-semibold' : 'text-gray-700'" x-text="formatQty(item.tersedia)"></span>
                            </td>
                            <td class="p-3 align-middle">Rp<span x-text="Number(item.harga).toLocaleString('id-ID')"></span></td>
                            <td class="p-3 align-middle" x-text="new Date(item.diperbaharui).toLocaleDateString('id-ID')"></td>
                            <td class="p-3 align-middle">
                                <div class="relative">
                                    <button @click="openActionMenuIndex = openActionMenuIndex === idx ? null : idx" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="6" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="18" r="1.5"/></svg>
                                    </button>
                                    <div x-show="openActionMenuIndex === idx" x-transition class="absolute right-0 mt-2 w-32 bg-white rounded-xl shadow-lg border border-gray-100 z-10">
                                        <button @click="detailItem(item); openActionMenuIndex = null" class="block w-full text-left px-4 py-2 hover:bg-[#eafbe6] rounded-t-xl">Detail</button>
                                        <!-- Edit button - only for client items (Umum) -->
                                        <button x-show="item.source === 'client' && item.kategori !== 'GAFI'" @click="editItem(item, idx); openActionMenuIndex = null" class="block w-full text-left px-4 py-2 hover:bg-[#eafbe6]">Edit</button>
                                        <!-- Split button - only for client items (GAFI) -->
                                        <button x-show="item.source === 'client' && item.kategori === 'GAFI'" @click="openSplitModal(item); openActionMenuIndex = null" class="block w-full text-left px-4 py-2 hover:bg-[#eafbe6] text-blue-600">
                                            Split Item
                                        </button>
                                        <!-- Delete button - only for client items (Umum) -->
                                        <button x-show="item.source === 'client' && item.kategori !== 'GAFI'" @click="deleteItem(item, idx); openActionMenuIndex = null" class="block w-full text-left px-4 py-2 hover:bg-[#ffeaea] text-red-600 rounded-b-xl">Hapus</button>
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

    <!-- Modal Tambah Item -->
    <div x-show="showAddModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" @click.away="showSuggestions = false">
        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-2xl mx-2 relative overflow-y-auto max-h-[70vh]">
            <button @click="showAddModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
            <form class="grid grid-cols-1 md:grid-cols-2 gap-4" @submit.prevent="submitAddForm()">
                <div x-show="addErrorMsg" :class="addErrorMsg.startsWith('Info:') ? 'text-blue-600' : 'text-red-500'" class="col-span-1 md:col-span-2 text-sm mb-2" x-text="addErrorMsg"></div>
                <div>
                    <label class="block font-semibold mb-2">Nama Item</label>
                    <div class="relative">
                        <input type="text" x-model="addNama" @input="onNamaInput" @focus="showSuggestions = true" @blur="setTimeout(() => showSuggestions = false, 200)" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400" placeholder="Masukan Nama Item">
                        <!-- Suggestions dropdown -->
                        <div x-show="showSuggestions && filteredSuggestions.length > 0" x-transition class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-40 overflow-y-auto">
                            <template x-for="suggestion in filteredSuggestions" :key="suggestion.id">
                                <div @click="selectSuggestion(suggestion)" class="px-4 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0">
                                    <div class="font-semibold" x-text="suggestion.nama"></div>
                                    <div class="text-xs text-gray-500" x-text="suggestion.sku + ' - ' + suggestion.kategori"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold mb-2">SKU</label>
                    <input type="text" x-model="addSku" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400 bg-gray-50" placeholder="Auto-generated" readonly>
                </div>
                                    <div>
                        <label class="block font-semibold mb-2">Kategori</label>
                        <input type="text" value="Umum" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 bg-gray-50" readonly>
                        <div class="text-xs text-gray-500 mt-1">Client hanya dapat menambahkan item dengan kategori Umum</div>
                    </div>
                <div>
                    <label class="block font-semibold mb-2">Tersedia</label>
                    <input type="number" x-model="addTersedia" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400" placeholder="Masukan Jumlah Tersedia">
                </div>
                                    <div>
                        <label class="block font-semibold mb-2">Harga</label>
                        <input type="number" x-model="addHarga" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400" placeholder="Kosongkan untuk pakai harga yang sudah ada">
                        <div class="text-xs text-gray-500 mt-1">
                            • Kosongkan: Stok akan ditambahkan ke item yang sudah ada<br>
                            • Isi harga berbeda: Item baru akan dibuat dengan harga tersebut
                        </div>
                    </div>
                <!-- Tombol bawah -->
                <div class="col-span-2 flex flex-col md:flex-row gap-2 mt-2">
                    <button type="submit" class="w-full rounded-lg bg-[#28C328] text-white font-semibold py-3 text-lg hover:bg-[#22a322] transition">Simpan</button>
                </div>
                <div class="col-span-2 flex flex-col md:flex-row gap-2">
                    <button type="reset" @click.prevent="resetAddForm()" class="w-full rounded-lg bg-gray-200 text-gray-500 font-semibold py-3 text-lg">Reset</button>
                    <button type="button" @click="resetAddForm(); showAddModal = false" class="w-full rounded-lg bg-gray-200 text-gray-500 font-semibold py-3 text-lg">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Detail Item -->
    <div x-show="showDetailModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl relative transform transition-all max-h-[90vh] flex flex-col overflow-hidden">
            <!-- Compact Header -->
            <div class="bg-[#28C328] rounded-t-2xl p-4 text-white relative">
                <button @click="showDetailModal = false" class="absolute top-3 right-3 w-7 h-7 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-full flex items-center justify-center text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
            </button>
                
                <div class="text-center">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                </div>
                    
                    <h2 class="text-xl font-bold mb-2" x-text="detailItemData.nama"></h2>
                    <div class="inline-flex items-center px-2 py-1 bg-white bg-opacity-20 rounded-full text-xs">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        <span class="font-medium" x-text="detailItemData.sku"></span>
            </div>
        </div>
    </div>
            
            <!-- Clean Content -->
            <div class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <!-- Simple Category Badge -->
                <div class="flex justify-center mb-6">
                    <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium" 
                          :class="detailItemData.kategori === 'GAFI' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-gray-100 text-gray-800 border border-gray-200'">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        <span x-text="detailItemData.kategori || 'Umum'"></span>
                    </span>
                </div>
                
                <!-- Clean Grid Layout -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Stock Information -->
                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">📦 Informasi Stok</h3>
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-14 0a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V8"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600 mb-2" x-text="Number(detailItemData.tersedia).toLocaleString('id-ID')"></div>
                            <div class="text-sm text-gray-600 mb-3">Stok Tersedia</div>
                            <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium" 
                                 :class="Number(detailItemData.tersedia) <= 10 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'">
                                <span x-text="Number(detailItemData.tersedia) <= 10 ? '⚠️ Stok Rendah' : '✅ Stok Aman'"></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Price Information -->
                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">💰 Informasi Harga</h3>
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600 mb-2">Rp<span x-text="Number(detailItemData.harga).toLocaleString('id-ID')"></span></div>
                            <div class="text-sm text-gray-600">Harga Satuan</div>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Details -->
                <div class="mt-6 bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Detail Tambahan
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                            <div class="text-xs text-gray-500 mb-1">SKU</div>
                            <div class="font-mono text-sm font-medium text-gray-800" x-text="detailItemData.sku"></div>
                        </div>
                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                            <div class="text-xs text-gray-500 mb-1">Terakhir Diperbarui</div>
                            <div class="text-sm font-medium text-gray-800" x-text="new Date(detailItemData.diperbaharui).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })"></div>
                        </div>
                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                            <div class="text-xs text-gray-500 mb-1">ID Item</div>
                            <div class="font-mono text-sm font-medium text-gray-800" x-text="detailItemData.id"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Clean Footer -->
            <div class="bg-white rounded-b-2xl px-6 py-4 border-t border-gray-200 shrink-0">
                <div class="flex gap-3">
                    <!-- Edit button - only for client items -->
                    <button x-show="detailItemData.source === 'client' && detailItemData.kategori !== 'GAFI'"
                            @click="editItem(detailItemData, items.findIndex(i => i.id === detailItemData.id)); showDetailModal = false" 
                            class="flex-1 bg-[#28C328] hover:bg-[#22a322] text-white font-semibold py-3 px-4 rounded-xl transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Item
                    </button>
                    <!-- Split button - only for client items (GAFI) -->
                    <button x-show="detailItemData.source === 'client' && detailItemData.kategori === 'GAFI'"
                            @click="openSplitModal(detailItemData); showDetailModal = false" 
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        Split Item
                    </button>
                    <button @click="showDetailModal = false" 
                            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 px-4 rounded-xl transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Item -->
    <div x-show="showEditModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-2xl mx-2 relative overflow-y-auto max-h-[70vh]">
            <button @click="showEditModal = false; resetEditForm()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
            <form class="grid grid-cols-1 md:grid-cols-2 gap-4" @submit.prevent="submitEditForm()">
                <div x-show="editErrorMsg" class="col-span-1 md:col-span-2 text-red-500 text-sm mb-2" x-text="editErrorMsg"></div>
                    <div>
                        <label class="block font-semibold mb-2">Nama Item</label>
                        <input type="text" x-model="editNama" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400" placeholder="Masukan Nama Item">
                    </div>
                    <div>
                        <label class="block font-semibold mb-2">SKU</label>
                    <input type="text" x-model="editSku" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400 bg-gray-50" readonly>
                    </div>
                    <div>
                    <label class="block font-semibold mb-2">Kategori</label>
                    <select x-model="editKategori" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700" :disabled="editKategori === 'GAFI'">
                        <option value="">Pilih Kategori</option>
                        <option value="Umum">Umum</option>
                        <option value="GAFI" :disabled="true">GAFI (Hanya Admin)</option>
                    </select>
                    <div x-show="editKategori === 'GAFI'" class="text-xs text-red-500 mt-1">Item GAFI tidak dapat diedit oleh mitra</div>
                    </div>
                    <div>
                        <label class="block font-semibold mb-2">Tersedia</label>
                    <input type="number" x-model="editTersedia" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400" placeholder="Masukan Jumlah Tersedia">
                    </div>
                    <div>
                        <label class="block font-semibold mb-2">Harga</label>
                    <input type="number" x-model="editHarga" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400" placeholder="Masukan Harga">
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

    <!-- Modal Split Item -->
    <div x-show="showSplitModal" x-cloak x-transition class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-2xl mx-2 relative overflow-y-auto max-h-[80vh]">
            <button @click="showSplitModal = false; resetSplitForm()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
            
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Split Item</h2>
                <p class="text-gray-600">Pilih item induk dan tentukan jumlah yang akan di-split</p>
            </div>

            <form @submit.prevent="submitSplitForm()" class="space-y-6">
                <!-- Error Message -->
                <div x-show="splitErrorMsg" class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-600 text-sm" x-text="splitErrorMsg"></div>

                <!-- Item Information -->
                <div class="bg-gray-50 rounded-xl p-4">
                    <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Informasi Item yang akan di-Split
                    </h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Nama:</span>
                            <span class="font-semibold text-gray-800 ml-2" x-text="splitItemData.nama"></span>
                        </div>
                        <div>
                            <span class="text-gray-600">SKU:</span>
                            <span class="font-mono text-gray-800 ml-2" x-text="splitItemData.sku"></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Kategori:</span>
                            <span class="font-semibold text-blue-600 ml-2" x-text="splitItemData.kategori"></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Stok Tersedia:</span>
                            <span class="font-semibold text-gray-800 ml-2" x-text="Number(splitItemData.tersedia).toLocaleString('id-ID') + ' pcs'"></span>
                        </div>
                    </div>
                </div>

                <!-- Split Configuration -->
                <div class="space-y-4">
                    <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Konfigurasi Split
                    </h3>

                    <!-- Input Gramasi -->
                    <div>
                        <label class="block font-semibold mb-2 text-gray-700">Gramasi Item Baru <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="number" x-model="splitGrams" @input="onSplitGramsInput()" step="1" min="1" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="500" required>
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">gram</div>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">Masukkan gramasi yang diinginkan untuk item baru</div>
                    </div>

                    <!-- Input Jumlah Split -->
                    <div>
                        <label class="block font-semibold mb-2 text-gray-700">Jumlah yang akan di-Split <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="number" x-model="splitQuantity" @input="onSplitQuantityInput()" step="1" min="1" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="2" required>
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">item</div>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">Minimal 1 item. Pengurangan stok induk mengikuti rumus: jumlah × (gramasi/1000 pcs)</div>
                    </div>

                    <!-- Input Harga Item Baru (opsional) -->
                    <div>
                        <label class="block font-semibold mb-2 text-gray-700">Harga Item Baru (opsional)</label>
                        <div class="relative">
                            <input type="number" x-model="splitPrice" step="1" min="0" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Kosongkan untuk pakai harga induk">
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">Rp</div>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">Jika dikosongkan, harga akan mengikuti harga item induk.</div>
                    </div>

                    <!-- Preview Konversi -->
                    <div x-show="splitQuantity && splitGrams" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="font-semibold text-blue-800 mb-2 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Preview Split
                        </h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Jumlah yang akan di-Split:</span>
                                <span class="font-semibold text-blue-800 ml-2" x-text="splitQuantity + ' pcs'"></span>
                            </div>
                            <div>
                                <span class="text-gray-600">Gramasi Item Baru:</span>
                                <span class="font-semibold text-blue-800 ml-2" x-text="splitGrams + 'g'"></span>
                            </div>
                            <div>
                                <span class="text-gray-600">Nama Item Baru:</span>
                                <span class="font-semibold text-blue-800 ml-2" x-text="splitItemData.nama + ' ' + splitGrams + 'g'"></span>
                            </div>
                            <div>
                                <span class="text-gray-600">Stok Induk Setelah Split:</span>
                                <span class="font-semibold text-blue-800 ml-2" x-text="formatQty(Number(splitItemData.tersedia) - (Number(splitQuantity) * (Number(splitGrams) / 1000))) + ' pcs'"></span>
                            </div>
                            <div class="col-span-2">
                                <span class="text-gray-600">Hasil Split:</span>
                                <span class="font-semibold text-blue-800 ml-2" x-text="'Akan dibuat ' + splitQuantity + ' item baru masing-masing ' + splitGrams + 'g'"></span>
                            </div>
                            <div class="col-span-2">
                                <span class="text-gray-600">Konversi Default:</span>
                                <span class="font-semibold text-blue-800 ml-2" x-text="'1 pcs = 1000g (1kg)'"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Split Item
                    </button>
                    <button type="button" @click="showSplitModal = false; resetSplitForm()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 px-4 rounded-xl transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Sukses -->
    <div x-show="showSuccessModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md mx-4 relative transform transition-all">
            <div class="text-center">
                <!-- Success Icon -->
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full mb-4" :class="successType === 'delete' ? 'bg-red-100' : 'bg-green-100'">
                    <template x-if="successType === 'delete'">
                        <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </template>
                    <template x-if="successType !== 'delete'">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </template>
                </div>
                
                <!-- Title -->
                <h3 class="text-xl font-bold text-gray-900 mb-2" x-text="successType === 'create' ? '✨ Item Baru Berhasil Ditambahkan!' : (successType === 'edit' ? '📝 Item Berhasil Diedit!' : (successType === 'delete' ? '🗑️ Item Berhasil Dihapus!' : '🔄 Item Berhasil Diperbarui!'))"></h3>
                
                <!-- Content for Create -->
                <template x-if="successType === 'create'">
                    <div class="text-gray-600 space-y-2">
                        <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                            <p class="font-semibold text-green-800 mb-2" x-text="successData.nama"></p>
                            <div class="text-sm space-y-1">
                                <div class="flex justify-between">
                                    <span>SKU:</span>
                                    <span class="font-mono text-gray-700" x-text="successData.sku"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Kategori:</span>
                                    <span class="font-medium" x-text="successData.kategori"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Stok:</span>
                                    <span class="font-bold text-green-600" x-text="Number(successData.stock).toLocaleString('id-ID')"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Harga:</span>
                                    <span class="font-bold text-green-600">Rp<span x-text="Number(successData.price).toLocaleString('id-ID')"></span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                
                <!-- Content for Update -->
                <template x-if="successType === 'update'">
                    <div class="text-gray-600 space-y-2">
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <p class="font-semibold text-blue-800 mb-2" x-text="successData.nama"></p>
                            <div class="text-sm space-y-2">
                                <div class="bg-white rounded p-2 border border-blue-100">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600">Stok Lama:</span>
                                        <span class="text-gray-700" x-text="Number(successData.oldStock).toLocaleString('id-ID')"></span>
                                    </div>
                                    <div class="flex justify-between items-center text-green-600">
                                        <span>+ Ditambahkan:</span>
                                        <span class="font-semibold" x-text="Number(successData.addedStock).toLocaleString('id-ID')"></span>
                                    </div>
                                    <hr class="my-1 border-blue-200">
                                    <div class="flex justify-between items-center font-bold">
                                        <span class="text-blue-800">Stok Baru:</span>
                                        <span class="text-blue-800" x-text="Number(successData.newStock).toLocaleString('id-ID')"></span>
                                    </div>
                                </div>
                                <template x-if="successData.priceUpdated">
                                    <div class="bg-yellow-50 rounded p-2 border border-yellow-200">
                                        <div class="text-xs text-yellow-700 mb-1">💰 Harga diperbarui:</div>
                                        <div class="flex justify-between items-center text-sm">
                                            <span class="line-through text-gray-500">Rp<span x-text="Number(successData.oldPrice).toLocaleString('id-ID')"></span></span>
                                            <span class="font-bold text-yellow-700">Rp<span x-text="Number(successData.newPrice).toLocaleString('id-ID')"></span></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
                
                <!-- Content for Edit -->
                <template x-if="successType === 'edit'">
                    <div class="text-gray-600 space-y-2">
                        <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                            <p class="font-semibold text-purple-800 mb-2" x-text="successData.nama"></p>
                            <div class="text-sm space-y-1">
                                <div class="flex justify-between">
                                    <span>SKU:</span>
                                    <span class="font-mono text-gray-700" x-text="successData.sku"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Kategori:</span>
                                    <span class="font-medium" x-text="successData.kategori"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Stok:</span>
                                    <span class="font-bold text-purple-600" x-text="Number(successData.stock).toLocaleString('id-ID')"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Harga:</span>
                                    <span class="font-bold text-purple-600">Rp<span x-text="Number(successData.price).toLocaleString('id-ID')"></span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                
                <!-- Content for Delete -->
                <template x-if="successType === 'delete'">
                    <div class="text-gray-600 space-y-2">
                        <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                            <p class="font-semibold text-red-800 mb-2">Item yang dihapus:</p>
                            <div class="bg-white rounded p-3 border border-red-100 text-sm space-y-1">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Nama:</span>
                                    <span class="font-semibold text-red-700" x-text="successData.nama"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">SKU:</span>
                                    <span class="font-mono text-gray-700" x-text="successData.sku"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Kategori:</span>
                                    <span class="font-medium text-gray-700" x-text="successData.kategori"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Stok:</span>
                                    <span class="text-gray-700" x-text="Number(successData.stock).toLocaleString('id-ID')"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Harga:</span>
                                    <span class="text-gray-700">Rp<span x-text="Number(successData.price).toLocaleString('id-ID')"></span></span>
                                </div>
                            </div>
                            <p class="text-xs text-red-600 mt-2 text-center">⚠️ Data telah dihapus secara permanen</p>
                        </div>
                    </div>
                </template>

                <!-- Content for Split -->
                <template x-if="successType === 'split'">
                    <div class="text-gray-600 space-y-2">
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <p class="font-semibold text-blue-800 mb-2">✨ Item Berhasil di-Split!</p>
                            <div class="bg-white rounded p-3 border border-blue-100 text-sm space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Item Baru:</span>
                                    <span class="font-semibold text-blue-700" x-text="successData.nama"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Dari Induk:</span>
                                    <span class="font-medium text-gray-700" x-text="successData.parentName"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Jumlah yang di-Split:</span>
                                    <span class="font-semibold text-blue-600" x-text="successData.quantity + ' pcs'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Gramasi Item Baru:</span>
                                    <span class="font-semibold text-blue-600" x-text="successData.grams"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Item Baru Dibuat:</span>
                                    <span class="font-semibold text-blue-600" x-text="successData.itemsCreated + ' item'"></span>
                                </div>
                                <hr class="my-2 border-blue-200">
                                <div class="bg-blue-50 rounded p-2 border border-blue-100">
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-gray-600">Stok Induk Sebelum:</span>
                                        <span class="text-gray-700" x-text="Number(successData.oldStock).toLocaleString('id-ID') + ' pcs'"></span>
                                    </div>
                                    <div class="flex justify-between items-center text-red-600 text-xs">
                                        <span>- Dikurangi:</span>
                                        <span class="font-semibold" x-text="Number(successData.deductedPieces).toFixed(2) + ' pcs'"></span>
                                    </div>
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-gray-600">Harga Item Baru:</span>
                                        <span class="text-gray-700">Rp<span x-text="Number(successData.priceUsed).toLocaleString('id-ID')"></span></span>
                                    </div>
                                    <hr class="my-1 border-blue-200">
                                    <div class="flex justify-between items-center font-bold text-blue-800 text-xs">
                                        <span>Stok Induk Sekarang:</span>
                                        <span x-text="Number(successData.newStock).toFixed(2) + ' pcs'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                
                <!-- Close Button -->
                <button @click="showSuccessModal = false" class="mt-6 w-full font-semibold py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2" :class="successType === 'delete' ? 'bg-red-600 hover:bg-red-700 text-white' : (successType === 'split' ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-green-600 hover:bg-green-700 text-white')">
                    <template x-if="successType === 'delete'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </template>
                    <template x-if="successType === 'split'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </template>
                    <template x-if="successType !== 'delete' && successType !== 'split'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </template>
                    <span x-text="successType === 'delete' ? 'Tutup' : 'Selesai'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Delete Item -->
    <div x-show="showDeleteModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" x-cloak>
        <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md mx-4">
            <div class="text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-4">Hapus Item</h2>
                <p class="text-gray-600 mb-4">Apakah Anda yakin ingin menghapus item berikut?</p>
                <div class="bg-gray-50 rounded-lg p-4 mb-4 text-left text-sm">
                    <div><span class="font-semibold">Nama:</span> <span x-text="deleteItemData?.nama"></span></div>
                    <div><span class="font-semibold">SKU:</span> <span x-text="deleteItemData?.sku"></span></div>
                    <div><span class="font-semibold">Stok:</span> <span x-text="deleteItemData?.tersedia"></span></div>
                    <div><span class="font-semibold">Harga:</span> Rp<span x-text="Number(deleteItemData?.harga).toLocaleString('id-ID')"></span></div>
                </div>
                <p class="text-xs text-red-600 mb-6">Tindakan ini tidak dapat dibatalkan!</p>
                <div class="flex gap-3">
                    <button @click="showDeleteModal = false; deleteItemData = null; deleteItemIndex = null" class="flex-1 rounded-lg bg-gray-200 text-gray-700 font-semibold py-3 hover:bg-gray-300 transition">Batal</button>
                    <button @click="confirmDeleteItem()" class="flex-1 rounded-lg bg-red-600 text-white font-semibold py-3 hover:bg-red-700 transition">Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <div x-show="showImportModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" x-cloak @click.self="showImportModal = false">
        <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md mx-4 relative" @click.stop>
            <button @click="showImportModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
            
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold text-[#28C328]">Import Data Stok</h2>
                <p class="text-gray-600 text-sm">Upload file CSV atau Excel untuk menambah banyak item sekaligus.</p>
            </div>

            <form @submit.prevent="submitImportForm">
                <div class="mb-4">
                    <input type="file" accept=".csv, .xlsx, .xls" @change="onImportFileChange($event)" class="block w-full text-sm text-gray-700 border border-gray-300 rounded-lg p-2" />
                </div>

                <div class="mb-4">
                    <a href="{{ asset('public/excel/template_partner.xlsx') }}" download class="flex items-center justify-center gap-2 w-full py-2 px-4 border border-[#28C328] text-[#28C328] rounded-lg text-xs font-semibold hover:bg-[#f0fff0] transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="Status 4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download Template Excel
                    </a>
                </div>

                <div class="mb-4 text-xs text-gray-500 bg-gray-50 rounded p-3 border border-gray-100">
                    <div class="font-semibold text-gray-700 mb-1">Format file yang didukung:</div>
                    <div class="mb-1">Kolom wajib: <span class="font-mono text-[10px] bg-white px-1 border border-gray-200">nama, sku, lokasi, tersedia, harga, diperbaharui</span></div>
                    <div class="text-gray-400 italic italic text-[10px]">Gunakan template di atas untuk menghindari error format.</div>
                </div>

                <div class="flex gap-2 mt-4">
                    <button type="submit" :disabled="!importFile" class="flex-1 rounded-lg bg-[#28C328] text-white font-semibold py-2 hover:bg-[#22a322] transition disabled:opacity-50 disabled:cursor-not-allowed">Import</button>
                    <button type="button" @click="showImportModal = false" class="flex-1 rounded-lg bg-gray-200 text-gray-700 font-semibold py-2 hover:bg-gray-300 transition">Batal</button>
                </div>

                <div x-show="importErrorMsg" class="text-red-500 text-xs mt-2" x-text="importErrorMsg"></div>
                <div x-show="importSuccessMsg" class="text-green-600 text-xs mt-2" x-text="importSuccessMsg"></div>
            </form>
        </div>
    </div>
</div>
@endsection

<script>
function stockTable() {
    return {
        items: [],
        loading: false,
        error: null,
        search: '',
        periode: '',
        // New date filter state
        dateFilter: '',
        customStartDate: '',
        customEndDate: '',
        status: '',
        kategori: '',
        currentPage: 1,
        perPage: 10,
        showAddModal: false,
        showAddDropdown: false,
        openActionMenuIndex: null,
        sortKey: '',
        sortAsc: true,
        sortCount: 0,
        // Add form state
        addNama: '',
        addSku: '',
        addKategori: '',
        addTersedia: '',
        addHarga: '',
        addErrorMsg: '',
        showSuggestions: false,
        // track suggestion selected
        selectedExistingItem: null,
        // Detail/Edit state
        showDetailModal: false,
        detailItemData: {},
        showEditModal: false,
        editIndex: null,
        editNama: '',
        editSku: '',
        editKategori: '',
        editTersedia: '',
        editHarga: '',
        editErrorMsg: '',
        // Success modal state
        showSuccessModal: false,
        successType: '',
        successData: {},
        
        // Split modal state
        showSplitModal: false,
        splitItemData: {},
        splitQuantity: '',
        splitGrams: '',
        splitPrice: '',
        splitErrorMsg: '',

        // Delete item state
        deleteItemData: null,
        deleteItemIndex: null,
        showDeleteModal: false,

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

        get filteredItems() {
            let filtered = this.items;
            // Apply date filter to 'diperbaharui'
            if (this.dateFilter) {
                filtered = this.applyDateFilterToItems(filtered);
            }
            return filtered;
        },

        get sortedItems() {
            if (!this.sortKey) return this.filteredItems;
            return this.filteredItems.slice().sort((a, b) => {
                    let valA = a[this.sortKey];
                    let valB = b[this.sortKey];
                if (['tersedia','harga'].includes(this.sortKey)) {
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
        showImportModal: false,
        importFile: null,
        importErrorMsg: '',
        importSuccessMsg: '',
        onImportFileChange(e) {
            this.importFile = e.target.files[0] || null;
            this.importErrorMsg = '';
            this.importSuccessMsg = '';
        },

        async submitImportForm() {
            if (!this.importFile) {
                this.importErrorMsg = 'Pilih file terlebih dahulu.';
                return;
            }
            this.importErrorMsg = '';
            this.importSuccessMsg = '';
            const formData = new FormData();
            formData.append('file', this.importFile);
            try {
                const res = await fetch('/client/stock-items/import', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: formData
                });
                const result = await res.json();
                if (!res.ok) {
                    this.importErrorMsg = result.message || 'Gagal import data.';
                    if (result.errors && result.errors.length > 0) {
                        this.importErrorMsg += ' Errors: ' + result.errors.join(', ');
                    }
                    return;
                }
                
                // Tampilkan hasil import yang detail
                let successMsg = result.message || 'Import berhasil!';
                if (result.imported > 0 || result.updated > 0) {
                    successMsg = `Import selesai! ${result.imported || 0} item baru ditambahkan, ${result.updated || 0} item diperbarui.`;
                }
                if (result.errors && result.errors.length > 0) {
                    successMsg += ` Ada ${result.errors.length} error: ` + result.errors.join(', ');
                }
                
                this.importSuccessMsg = successMsg;
                this.showImportModal = false;
                this.importFile = null;
                window.location.reload();
                await this.fetchItems();
            } catch (e) {
                this.importErrorMsg = 'Gagal terhubung ke server.';
            }
        },

        get paginatedItems() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.sortedItems.slice(start, start + this.perPage);
        },

        get totalPages() {
            return Math.max(1, Math.ceil(this.sortedItems.length / this.perPage));
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
            return [...new Set(this.items.map(i => i.diperbaharui ? i.diperbaharui.split('-')[0] : ''))];
        },

        get statuses() {
            return [...new Set(this.items.map(i => i.kategori).filter(Boolean))];
        },

        // Watch for search changes to reload data
        async onSearchChange() {
            await this.loadItems();
        },



        // Auto-complete functionality
        get filteredSuggestions() {
            if (!this.addNama || this.addNama.length < 2) return [];
            const query = this.addNama.toLowerCase();
            return this.items.filter(item => 
                item.nama.toLowerCase().includes(query)
            ).slice(0, 5);
        },

        onNamaInput() {
            if (this.addNama && this.addNama.length >= 2) {
                this.showSuggestions = true;
            } else {
                this.showSuggestions = false;
            }
            // reset selection jika user ubah nama
            this.selectedExistingItem = null;
            this.generateSku();
        },

        selectSuggestion(suggestion) {
            this.addNama = suggestion.nama;
            // Don't change kategori - client items are always "Umum"
            this.addHarga = ''; // Reset harga to allow new price input
            this.addTersedia = ''; // Reset tersedia for new stock
            // gunakan SKU existing agar tambah stok ke item yang sama
            this.addSku = suggestion.sku || this.addSku;
            this.selectedExistingItem = suggestion;
            this.showSuggestions = false;
            
            // Show info about existing item in error message area (but as info)
            this.addErrorMsg = `Info: Item "${suggestion.nama}" sudah ada dengan stok ${suggestion.tersedia} dan harga Rp${Number(suggestion.harga).toLocaleString('id-ID')}. 
            
Jika Anda ingin:
• Menambah stok ke item yang sama: Kosongkan field harga untuk menggunakan harga yang sudah ada
• Membuat item baru dengan harga berbeda: Isi field harga dengan harga yang berbeda`;
        },

        generateSku() {
            if (!this.addNama) {
                this.addSku = '';
                return;
            }
            
            // Generate SKU based on nama - client items always use "Umum" category
            const prefix = 'UM'; // Client items are always "Umum"
            const words = this.addNama.split(' ').filter(word => word.length > 0);
            const initials = words.map(word => word.charAt(0).toUpperCase()).join('');
            const timestamp = Date.now().toString().slice(-4);
            this.addSku = `${prefix}-${initials}-${timestamp}`;
        },

        exportExcel() {
            const excelData = this.sortedItems.map(item => ({
                'Nama Item': item.nama,
                'SKU': item.sku,
                'Kategori': item.kategori || 'Umum',
                'Tersedia': Number(item.tersedia).toLocaleString('id-ID'),
                'Harga': `Rp ${Number(item.harga).toLocaleString('id-ID')}`,
                'Diperbaharui': new Date(item.diperbaharui).toLocaleDateString('id-ID')
            }));

            const worksheet = XLSX.utils.json_to_sheet(excelData);
            worksheet['!cols'] = [
                { wch: 25 }, { wch: 18 }, { wch: 15 }, { wch: 12 }, 
                { wch: 18 }, { wch: 15 }
            ];
            worksheet['!freeze'] = { xSplit: 0, ySplit: 1 };
            worksheet['!autofilter'] = { ref: "A1:F1" };

            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, "Stock Data");
            XLSX.writeFile(workbook, "client_stock_data.xlsx");
        },

        exportPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            const tableColumn = ["Nama Item", "SKU", "Kategori", "Tersedia", "Harga", "Diperbaharui"];
            const tableRows = this.sortedItems.map(item => [
                item.nama,
                item.sku,
                item.kategori || 'Umum',
                Number(item.tersedia).toLocaleString('id-ID'),
                `Rp ${Number(item.harga).toLocaleString('id-ID')}`,
                new Date(item.diperbaharui).toLocaleDateString('id-ID')
            ]);

            doc.autoTable({
                head: [tableColumn],
                body: tableRows,
                startY: 20,
                styles: { fontSize: 8, cellPadding: 2 },
                headStyles: { fillColor: [40, 195, 40], textColor: 255 }
            });
            doc.save("client_stock_data.pdf");
        },

        // Helpers
        formatQty(val) {
            const num = Number(val || 0);
            return Number.isInteger(num) ? String(num) : num.toLocaleString('id-ID', { maximumFractionDigits: 2, minimumFractionDigits: 0 });
        },

        // Load data from API
        async loadItems() {
            this.loading = true;
            this.error = null;
            
            try {
                const params = new URLSearchParams();
                if (this.search) params.append('search', this.search);
                if (this.kategori) params.append('kategori', this.kategori);
                if (this.dateFilter) params.append('dateFilter', this.dateFilter);
                if (this.dateFilter === 'custom' && this.customStartDate && this.customEndDate) {
                    params.append('startDate', this.customStartDate);
                    params.append('endDate', this.customEndDate);
                }
                
                const response = await fetch(`/client/stock-items?${params}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                
                if (!response.ok) {
                    throw new Error('Failed to load items');
                }
                
                const result = await response.json();
                this.items = result.data || [];
            } catch (error) {
                console.error('Error loading items:', error);
                this.error = 'Failed to load items: ' + error.message;
            } finally {
                this.loading = false;
            }
        },

        // Initialize data loading
        initRealtime() {
            this.loadItems();
        },

        // Date filter helpers
        applyDateFilter() {
            this.currentPage = 1;
            this.loadItems();
        },
        clearDateFilter() {
            this.dateFilter = '';
            this.customStartDate = '';
            this.customEndDate = '';
            this.currentPage = 1;
            this.loadItems();
        },
        getFilterStatusText() {
            switch (this.dateFilter) {
                case 'today': return 'Menampilkan data stok yang diperbarui hari ini';
                case 'week': return 'Menampilkan data stok minggu ini';
                case 'month': return 'Menampilkan data stok bulan ini';
                case 'year': return 'Menampilkan data stok tahun ini';
                case 'custom':
                    if (this.customStartDate && this.customEndDate) {
                        const start = new Date(this.customStartDate).toLocaleDateString('id-ID');
                        const end = new Date(this.customEndDate).toLocaleDateString('id-ID');
                        return `Menampilkan data stok dari ${start} sampai ${end}`;
                    }
                    return 'Menampilkan data stok berdasarkan rentang tanggal custom';
                default: return '';
            }
        },
        applyDateFilterToItems(items) {
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
            return items.filter(i => {
                const d = new Date(i.diperbaharui);
                return d >= startDate && d <= endDate;
            });
        },

        // Add form methods
        resetAddForm() {
            this.addNama = '';
            this.addSku = '';
            this.addKategori = '';
            this.addTersedia = '';
            this.addHarga = '';
            this.addErrorMsg = '';
            this.showSuggestions = false;
            this.successType = '';
            this.successData = {};
        },

        async submitAddForm() {
            if (!this.addNama) { this.addErrorMsg = 'Field Nama Item wajib diisi.'; return; }
            if (!this.addTersedia || Number(this.addTersedia) < 0) { this.addErrorMsg = 'Field Tersedia wajib diisi dan harus positif.'; return; }

            // Check if this is an existing item (when user selects from suggestions)
            const existingItems = this.items.filter(item => item.nama.toLowerCase() === this.addNama.toLowerCase());
            
            // Validate harga based on whether it's a new item or existing item
            if (existingItems.length > 0) {
                // For existing items, check if user wants to create new item or add to existing
                if (this.addHarga && this.addHarga !== '' && Number(this.addHarga) > 0) {
                    // User provided a price, check if it's different from existing items
                    const samePriceItem = existingItems.find(item => Number(item.harga) === Number(this.addHarga));
                    if (samePriceItem) {
                        // Same price found, will add to existing item
                        this.addErrorMsg = `Info: Item "${this.addNama}" dengan harga Rp${Number(this.addHarga).toLocaleString('id-ID')} sudah ada. Stok akan ditambahkan ke item yang sama.`;
                    } else {
                        // Different price, will create new item
                        this.addErrorMsg = `Info: Item "${this.addNama}" dengan harga berbeda akan dibuat sebagai item baru.`;
                    }
                } else {
                    // No price provided, will add to existing item with same name
                    this.addErrorMsg = `Info: Stok akan ditambahkan ke item "${this.addNama}" yang sudah ada.`;
                    // bila user memilih suggestion, pakai SKU existing agar backend tepat menambah stok
                    if (this.selectedExistingItem && this.selectedExistingItem.sku) {
                        this.addSku = this.selectedExistingItem.sku;
                    }
                }
                
                // Validate harga if provided
                if (this.addHarga && this.addHarga !== '' && Number(this.addHarga) <= 0) {
                    this.addErrorMsg = 'Jika mengisi harga, harus positif.';
                    return;
                }
            } else {
                // For new items, harga is required
                if (!this.addHarga || this.addHarga === '' || Number(this.addHarga) <= 0) {
                    this.addErrorMsg = 'Field Harga wajib diisi dan harus positif untuk item baru.';
                    return;
                }
            }
            
            // Force kategori to be "Umum" for client items
            this.addKategori = 'Umum';

            this.addErrorMsg = '';
            
            try {
                const formData = new FormData();
                formData.append('nama', this.addNama);
                formData.append('sku', this.addSku);
                formData.append('kategori', this.addKategori);
                formData.append('tersedia', Number(this.addTersedia));
                
                // Only send harga if it's provided and valid
                if (this.addHarga && this.addHarga !== '' && Number(this.addHarga) > 0) {
                    formData.append('harga', Number(this.addHarga));
                }

                const response = await fetch('/client/stock-items', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                let result;
                const ct = response.headers.get('content-type') || '';
                if (ct.includes('application/json')) {
                    result = await response.json();
                } else {
                    // fallback jika server mengembalikan HTML (mis. redirect/419)
                    const text = await response.text();
                    throw new Error('Server mengembalikan respons non-JSON. Coba muat ulang halaman dan pastikan sesi masih aktif.');
                }

                if (!response.ok) {
                    throw new Error(result.error || 'Failed to save item');
                }

                // Reload items
                await this.loadItems();

                // Set success data based on action
                if (result.action === 'update') {
                    this.successType = 'update';
                    this.successData = {
                        nama: this.addNama,
                        oldStock: result.oldStock,
                        addedStock: result.addedStock,
                        newStock: result.newStock,
                        oldPrice: result.data.harga,
                        newPrice: result.data.harga,
                        priceUpdated: false
                    };
                } else {
                    this.successType = 'create';
                    this.successData = {
                        nama: this.addNama,
                        stock: Number(this.addTersedia),
                        price: this.addHarga && this.addHarga !== '' ? Number(this.addHarga) : 0,
                        sku: this.addSku,
                        kategori: this.addKategori
                    };
                }
            
            this.resetAddForm();
            this.showAddModal = false;
            this.showSuccessModal = true;

            } catch (error) {
                console.error('Error saving item:', error);
                this.addErrorMsg = error.message;
            }
        },

        // Detail/Edit methods
        detailItem(item) {
            this.detailItemData = {...item};
            this.showDetailModal = true;
        },

        editItem(item, idx) {
            // Check if item is from admin (read-only)
            if (item.source === 'admin') {
                alert('❌ Item dari admin tidak dapat diedit oleh mitra. Hanya admin pusat yang dapat mengedit item ini.');
                return;
            }
            
            // Check if item is GAFI category
            if (item.kategori === 'GAFI') {
                alert('❌ Item GAFI tidak dapat diedit oleh mitra. Hanya admin pusat yang dapat mengedit item GAFI.');
                return;
            }
            
            this.editIndex = idx;
            this.editNama = item.nama;
            this.editSku = item.sku;
            this.editKategori = item.kategori || '';
            this.editTersedia = item.tersedia;
            this.editHarga = item.harga;
            this.editErrorMsg = '';
            this.showEditModal = true;
        },

        resetEditForm() {
            this.editNama = '';
            this.editSku = '';
            this.editKategori = '';
            this.editTersedia = '';
            this.editHarga = '';
            this.editErrorMsg = '';
            this.editIndex = null;
        },

        async submitEditForm() {
            if (!this.editNama) { this.editErrorMsg = 'Field Nama Item wajib diisi.'; return; }
            if (!this.editKategori) { this.editErrorMsg = 'Field Kategori wajib diisi.'; return; }
            if (this.editKategori === 'GAFI') { this.editErrorMsg = 'Item GAFI tidak dapat diedit oleh mitra.'; return; }
            if (!this.editTersedia || Number(this.editTersedia) < 0) { this.editErrorMsg = 'Field Tersedia wajib diisi dan harus positif.'; return; }
            if (!this.editHarga || Number(this.editHarga) <= 0) { this.editErrorMsg = 'Field Harga wajib diisi dan harus positif.'; return; }
            
            // Additional check to prevent editing admin items
            const originalItem = this.items[this.editIndex];
            if (originalItem.source === 'admin') {
                this.editErrorMsg = 'Item dari admin tidak dapat diedit oleh mitra.';
                return;
            }

            this.editErrorMsg = '';
            
            try {
                const formData = new FormData();
                formData.append('_method', 'PUT');
                formData.append('nama', this.editNama);
                formData.append('sku', this.editSku);
                formData.append('kategori', this.editKategori);
                formData.append('tersedia', Number(this.editTersedia));
                formData.append('harga', Number(this.editHarga));

                const response = await fetch(`/client/stock-items/${this.items[this.editIndex].id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: formData
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.error || 'Failed to update item');
                }

                // Reload items
                await this.loadItems();
            
            // Store success data for edit
            this.successType = 'edit';
            this.successData = {
                    nama: this.editNama,
                    stock: Number(this.editTersedia),
                    price: Number(this.editHarga),
                    sku: this.editSku,
                    kategori: this.editKategori
            };
            
            this.resetEditForm();
            this.showEditModal = false;
            this.showSuccessModal = true;

            } catch (error) {
                console.error('Error updating item:', error);
                this.editErrorMsg = error.message;
            }
        },

        async deleteItem(item, idx) {
            // Check if item is from admin (read-only)
            if (item.source === 'admin') {
                alert('❌ Item dari admin tidak dapat dihapus oleh mitra. Hanya admin pusat yang dapat menghapus item ini.');
                return;
            }
            
            // Check if item is GAFI category
            if (item.kategori === 'GAFI') {
                alert('❌ Item GAFI tidak dapat dihapus oleh mitra. Hanya admin pusat yang dapat menghapus item GAFI.');
                return;
            }
            
            // Show custom modal
            this.deleteItemData = item;
            this.deleteItemIndex = idx;
            this.showDeleteModal = true;
        },

        async confirmDeleteItem() {
            if (!this.deleteItemData) return;
            const item = this.deleteItemData;
            try {
                const formData = new FormData();
                formData.append('_method', 'DELETE');
                const response = await fetch(`/client/stock-items/${item.id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') || ''
                    },
                    body: formData
                });
                const result = await response.json();
                if (!response.ok) {
                    throw new Error(result.error || 'Failed to delete item');
                }
                await this.loadItems();
                this.successType = 'delete';
                this.successData = {
                    nama: item.nama,
                    sku: item.sku,
                    stock: item.tersedia,
                    price: item.harga,
                    kategori: item.kategori
                };
                this.showSuccessModal = true;
            } catch (error) {
                alert('Error deleting item: ' + error.message);
            } finally {
                this.showDeleteModal = false;
                this.deleteItemData = null;
                this.deleteItemIndex = null;
            }
        },

        // Split Item methods
        openSplitModal(item) {
            // Check if item is from client and is GAFI category
            if (item.source !== 'client' || item.kategori !== 'GAFI') {
                alert('❌ Split item hanya dapat dilakukan pada item GAFI milik Anda.');
                return;
            }
            this.splitItemData = {...item};
            this.splitQuantity = '';
            this.splitGrams = '';
            this.splitErrorMsg = '';
            this.showSplitModal = true;
        },



        onSplitQuantityInput() {
            this.splitErrorMsg = '';
            
            // Quantity is number of new items to create (integer >= 1)
            if (!this.splitQuantity || this.splitQuantity < 1) {
                this.splitErrorMsg = 'Jumlah minimal adalah 1 item';
                return;
            }
            
            // If grams not set yet, nothing else to validate
            if (!this.splitGrams || this.splitGrams < 1) return;
            
            // Compute max items we can split based on available stock and grams
            const gramsPerPiece = 1000; // 1 pcs = 1000g
            const deductionPerItemInPieces = Number(this.splitGrams) / gramsPerPiece; // e.g. 750g -> 0.75 pcs
            const maxItems = Math.floor(Number(this.splitItemData.tersedia) / deductionPerItemInPieces);
            
            if (this.splitQuantity > maxItems) {
                this.splitErrorMsg = `Maksimal dapat membuat ${maxItems} item berdasarkan stok induk dan gramasi saat ini`;
                this.splitQuantity = maxItems > 0 ? maxItems : 1;
            }
        },

        onSplitGramsInput() {
            this.splitErrorMsg = '';
            
            // Validate minimum
            if (this.splitGrams < 1) {
                this.splitErrorMsg = 'Gramasi minimal adalah 1 gram';
                return;
            }
            
            // Re-validate max items if quantity is already set
            if (!this.splitQuantity || this.splitQuantity < 1) return;
            const gramsPerPiece = 1000;
            const deductionPerItemInPieces = Number(this.splitGrams) / gramsPerPiece;
            const maxItems = Math.floor(Number(this.splitItemData.tersedia) / deductionPerItemInPieces);
            if (this.splitQuantity > maxItems) {
                this.splitErrorMsg = `Maksimal dapat membuat ${maxItems} item berdasarkan stok induk dan gramasi saat ini`;
                this.splitQuantity = maxItems > 0 ? maxItems : 1;
            }
        },

        resetSplitForm() {
            this.splitItemData = {};
            this.splitQuantity = '';
            this.splitGrams = '';
            this.splitErrorMsg = '';
        },

        async submitSplitForm() {
            // Validation
            if (!this.splitQuantity || this.splitQuantity < 0.1) {
                this.splitErrorMsg = 'Jumlah split minimal 0.1 pcs';
                return;
            }
            
            if (!this.splitGrams || this.splitGrams < 1) {
                this.splitErrorMsg = 'Gramasi minimal adalah 1 gram';
                return;
            }
            
            if (this.splitQuantity > Number(this.splitItemData.tersedia)) {
                this.splitErrorMsg = `Jumlah split tidak boleh melebihi stok induk (${Number(this.splitItemData.tersedia).toFixed(2)} pcs)`;
                return;
            }

            this.splitErrorMsg = '';
            
            try {
                const formData = new FormData();
                formData.append('splitQuantity', Number(this.splitQuantity));
                formData.append('splitGrams', Number(this.splitGrams));
                if (this.splitPrice) {
                    formData.append('splitPrice', Number(this.splitPrice));
                }

                const response = await fetch(`/client/stock-items/${this.splitItemData.id}/split`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: formData
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.error || 'Failed to split item');
                }

                // Reload items
                await this.loadItems();

            // Show success message
            this.successType = 'split';
            this.successData = {
                nama: `${this.splitItemData.nama} ${this.splitGrams}g`,
                parentName: this.splitItemData.nama,
                quantity: Number(this.splitQuantity),
                grams: this.splitGrams + 'g',
                oldStock: Number(this.splitItemData.tersedia),
                    newStock: result.data.splitDetails.newParentStock,
                itemsCreated: Number(this.splitQuantity),
                    deductedPieces: result.data.splitDetails.deduction,
                priceUsed: this.splitPrice && Number(this.splitPrice) > 0 ? Number(this.splitPrice) : Number(this.splitItemData.harga)
            };

            // Close modal and show success
            this.showSplitModal = false;
            this.showSuccessModal = true;

            } catch (error) {
                console.error('Error splitting item:', error);
                this.splitErrorMsg = error.message;
            }
        },

        generateSplitSku(parentSku, grams) {
            return `${parentSku}-${grams}g`;
        }
    }
}
</script>

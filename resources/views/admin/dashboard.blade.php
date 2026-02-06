@extends('layouts.admin')

@section('content')
<div class="bg-white rounded-xl shadow p-8" x-data="stockTable()">
    <h1 class="text-2xl font-bold text-[#28C328] mb-6">Stock Management</h1>
    <div class="mb-6">
        <!-- Seluruh filter bar, tabel, pagination, dan modal di sini -->
        <div class="flex flex-wrap items-center gap-2 justify-between">
            <div class="flex flex-1 gap-2">
                <!-- Search bar kiri -->
                <div class="w-22">
                    <div class="flex items-center border border-gray-300 rounded-lg px-4 py-1 bg-white">
                        <img src="{{ asset('icons/search.png') }}" alt="Search" class="w-5 h-5 object-contain mr-2" />
                        <input
                            type="text"
                            placeholder="Cari Transaksi"
                            x-model="search"
                            class="flex-1 bg-transparent border-none outline-none text-gray-400 text-sm font-medium placeholder-gray-400 h-6"
                        >
                    </div>
                </div>
                <!-- Filter tanggal -->
                <div class="flex gap-2">
                    <select x-model="dateFilter" @change="applyDateFilter()" class="rounded-lg border border-gray-300 px-3 py-2 text-gray-700 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-[#28C328] focus:border-transparent">
                        <option value="">Semua Tanggal</option>
                        <option value="today">Hari Ini</option>
                        <option value="week">Minggu Ini</option>
                        <option value="month">Bulan Ini</option>
                        <option value="year">Tahun Ini</option>
                        <option value="custom">Custom Range</option>
                    </select>
                    
                    <!-- Custom date range inputs (muncul saat custom dipilih) -->
                    <div x-show="dateFilter === 'custom'" class="flex gap-2 items-center">
                        <input 
                            type="date" 
                            x-model="customStartDate" 
                            @change="applyDateFilter()"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-gray-700 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-[#28C328] focus:border-transparent"
                            placeholder="Tanggal Mulai"
                        >
                        <span class="text-gray-500">sampai</span>
                        <input 
                            type="date" 
                            x-model="customEndDate" 
                            @change="applyDateFilter()"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-gray-700 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-[#28C328] focus:border-transparent"
                            placeholder="Tanggal Akhir"
                        >
                    </div>
                    
                    <!-- Clear filter button (muncul saat ada filter aktif) -->
                    <button 
                        x-show="dateFilter" 
                        @click="clearDateFilter()" 
                        class="px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition text-sm flex items-center gap-1"
                        title="Hapus Filter Tanggal"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Clear
                    </button>
                </div>

            </div>
            <div class="flex gap-2 ml-auto">
                <!-- Tombol Excel & PDF & Tambahkan Item -->
                <button class="rounded-lg bg-[#28C328] px-4 py-2 text-white text-sm font-semibold flex items-center gap-2 hover:bg-[#22a322] transition" @click="exportExcel">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 2h8v4H8z" /></svg>
                    Excel
                </button>
                <button class="rounded-lg bg-[#28C328] px-4 py-2 text-white text-sm font-semibold flex items-center gap-2 hover:bg-[#22a322] transition" @click="exportPDF">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20h9" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    PDF
                </button>
                <div class="relative">
                    <button class="rounded-lg bg-[#28C328] px-4 py-2 text-white text-sm font-semibold flex items-center gap-2 hover:bg-[#22a322] transition" @click="showAddDropdown = !showAddDropdown">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Tambahkan Item
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="showAddDropdown" @click.away="showAddDropdown = false" class="absolute right-0 mt-2 w-44 bg-white rounded-xl shadow-lg border border-gray-100 z-10">
                        <button @click="showModal = true; showAddDropdown = false" class="block w-full text-left px-4 py-2 hover:bg-[#eafbe6] rounded-t-xl">Input Manual</button>
                        <button @click="showImportModal = true; showAddDropdown = false" class="block w-full text-left px-4 py-2 hover:bg-[#eafbe6] rounded-b-xl">Import Data</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Table -->
        <div class="overflow-x-auto mt-4">
            <table class="min-w-full text-sm text-center">
            <thead>
                <tr class="bg-[#28C328] text-white">
                        <th class="p-3 rounded-tl-xl"></th>
                        <th class="p-3 cursor-pointer select-none" @click="sortBy('nama')">
                            <div class="flex items-center gap-1 justify-center">
                                <span>Nama Item</span>
                                <span class="flex flex-col">
                                    <svg :class="{'opacity-100': sortKey==='nama' && sortOrder==='asc', 'opacity-50': !(sortKey==='nama' && sortOrder==='asc')}" class="w-3 h-3" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                    <svg :class="{'opacity-100': sortKey==='nama' && sortOrder==='desc', 'opacity-50': !(sortKey==='nama' && sortOrder==='desc')}" class="w-3 h-3 -mt-1" fill="white" viewBox="0 0 20 20"><path d="M10 14l4-4H6l4 4z"/></svg>
                                </span>
                        </div>
                    </th>
                        <th class="p-3 cursor-pointer select-none" @click="sortBy('sku')">
                            <div class="flex items-center gap-1 justify-center">
                                <span>SKU</span>
                                <span class="flex flex-col">
                                    <svg :class="{'opacity-100': sortKey==='sku' && sortOrder==='asc', 'opacity-50': !(sortKey==='sku' && sortOrder==='asc')}" class="w-3 h-3" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                    <svg :class="{'opacity-100': sortKey==='sku' && sortOrder==='desc', 'opacity-50': !(sortKey==='sku' && sortOrder==='desc')}" class="w-3 h-3 -mt-1" fill="white" viewBox="0 0 20 20"><path d="M10 14l4-4H6l4 4z"/></svg>
                                </span>
                        </div>
                    </th>

                        <th class="p-3 cursor-pointer select-none" @click="sortBy('lokasi')">
                            <div class="flex items-center gap-1 justify-center">
                                <span>Lokasi</span>
                                <span class="flex flex-col">
                                    <svg :class="{'opacity-100': sortKey==='lokasi' && sortOrder==='asc', 'opacity-50': !(sortKey==='lokasi' && sortOrder==='asc')}" class="w-3 h-3" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                    <svg :class="{'opacity-100': sortKey==='lokasi' && sortOrder==='desc', 'opacity-50': !(sortKey==='lokasi' && sortOrder==='desc')}" class="w-3 h-3 -mt-1" fill="white" viewBox="0 0 20 20"><path d="M10 14l4-4H6l4 4z"/></svg>
                                </span>
                        </div>
                    </th>
                        <th class="p-3 cursor-pointer select-none" @click="sortBy('tersedia')">
                            <div class="flex items-center gap-1 justify-center">
                                <span>Tersedia</span>
                                <span class="flex flex-col">
                                    <svg :class="{'opacity-100': sortKey==='tersedia' && sortOrder==='asc', 'opacity-50': !(sortKey==='tersedia' && sortOrder==='asc')}" class="w-3 h-3" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                    <svg :class="{'opacity-100': sortKey==='tersedia' && sortOrder==='desc', 'opacity-50': !(sortKey==='tersedia' && sortOrder==='desc')}" class="w-3 h-3 -mt-1" fill="white" viewBox="0 0 20 20"><path d="M10 14l4-4H6l4 4z"/></svg>
                                </span>
                        </div>
                    </th>

                        <th class="p-3 cursor-pointer select-none" @click="sortBy('harga')">
                            <div class="flex items-center gap-1 justify-center">
                                <span>Harga</span>
                                <span class="flex flex-col">
                                    <svg :class="{'opacity-100': sortKey==='harga' && sortOrder==='asc', 'opacity-50': !(sortKey==='harga' && sortOrder==='asc')}" class="w-3 h-3" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                    <svg :class="{'opacity-100': sortKey==='harga' && sortOrder==='desc', 'opacity-50': !(sortKey==='harga' && sortOrder==='desc')}" class="w-3 h-3 -mt-1" fill="white" viewBox="0 0 20 20"><path d="M10 14l4-4H6l4 4z"/></svg>
                                </span>
                        </div>
                    </th>
                        <th class="p-3 cursor-pointer select-none" @click="sortBy('diperbaharui')">
                            <div class="flex items-center gap-1 justify-center">
                                <span>Diperbaharui</span>
                                <span class="flex flex-col">
                                    <svg :class="{'opacity-100': sortKey==='diperbaharui' && sortOrder==='asc', 'opacity-50': !(sortKey==='diperbaharui' && sortOrder==='asc')}" class="w-3 h-3" fill="white" viewBox="0 0 20 20"><path d="M10 6l-4 4h8l-4-4z"/></svg>
                                    <svg :class="{'opacity-100': sortKey==='diperbaharui' && sortOrder==='desc', 'opacity-50': !(sortKey==='diperbaharui' && sortOrder==='desc')}" class="w-3 h-3 -mt-1" fill="white" viewBox="0 0 20 20"><path d="M10 14l4-4H6l4 4z"/></svg>
                                </span>
                        </div>
                    </th>
                    <th class="rounded-tr-xl">
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100 text-center align-middle">
                    <template x-for="(item, idx) in paginatedItems" :key="item.id">
                <tr>
                    <td class="p-3 align-middle">
                                <template x-if="getImageSrc(item.gambar)">
                                    <img :src="getImageSrc(item.gambar)" class="w-10 h-10 rounded object-cover mx-auto" alt="item">
                                </template>
                                <template x-if="!getImageSrc(item.gambar)">
                                    <div class="w-10 h-10 rounded bg-gray-200 flex items-center justify-center mx-auto">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </template>
                    </td>
                            <td class="p-3 align-middle" x-text="item.nama"></td>
                            <td class="p-3 align-middle" x-text="item.sku"></td>
                            <td class="p-3 align-middle" x-text="item.lokasi"></td>
                            <td class="p-3 align-middle" x-text="Number(item.tersedia).toLocaleString('id-ID')"></td>
                            <td class="p-3 align-middle">Rp<span x-text="Number(item.harga).toLocaleString('id-ID')"></span></td>
                            <td class="p-3 align-middle" x-text="new Date(item.diperbaharui).toLocaleDateString('id-ID')"></td>
                            <td class="p-3 align-middle">
                                <div class="relative">
                                    <button @click="openActionMenuIndex = openActionMenuIndex === idx ? null : idx" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="6" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="18" r="1.5"/></svg>
                                    </button>
                                    <div
                                        x-show="openActionMenuIndex === idx"
                                        x-transition
                                        x-ref="actionMenu"
                                        :class="dropdownDirection === 'up' ? 'origin-bottom bottom-full mb-2' : 'origin-top mt-2'"
                                        class="absolute right-0 w-32 bg-white rounded-xl shadow-lg border border-gray-100 z-10"
                                        x-init="$watch('openActionMenuIndex', value => { if(value === idx) { $nextTick(() => { adjustDropdownDirection($refs.actionMenu, $el); }); } })"
                                    >
                                        <button @click="detailItem(item); openActionMenuIndex = null" class="block w-full text-left px-4 py-2 hover:bg-[#eafbe6] rounded-t-xl">Detail</button>
                                        <button @click="editItem(item, idx); openActionMenuIndex = null" class="block w-full text-left px-4 py-2 hover:bg-[#eafbe6]">Edit</button>
                                        <button @click="deleteItem(item, idx); openActionMenuIndex = null" class="block w-full text-left px-4 py-2 hover:bg-[#ffeaea] text-red-600 rounded-b-xl">Hapus</button>
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
                <span class="font-semibold" x-text="'(' + filteredItems.length + ' dari ' + items.length + ' item)'"></span>
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
    <!-- Modal Tambah Item -->
    <div x-show="showModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.15);" x-cloak @click.self="closeModal()">
        <div class="relative bg-white rounded-2xl shadow-xl p-8 w-full max-w-3xl mx-4 overflow-y-auto max-h-screen" style="min-width:340px;" @click.stop>
            <button @click="resetForm(); closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
            <form class="grid grid-cols-1 md:grid-cols-2 gap-8" @submit.prevent="submitForm()">
                <div x-show="errorMsg" class="col-span-1 md:col-span-2 text-red-500 text-sm mb-2" x-text="errorMsg"></div>
                <!-- Kiri -->
                <div class="flex flex-col gap-4">
                    <div>
                        <label class="block font-semibold mb-2">Upload Foto Item</label>
                        <div class="border border-gray-300 rounded-lg flex items-center justify-center h-32 bg-gray-50">
                            <input type="file" class="hidden" id="uploadFoto" @change="handleFoto($event)" accept="image/*">
                            <label for="uploadFoto" class="flex flex-col items-center justify-center cursor-pointer w-full h-full">
                                <template x-if="fotoPreview">
                                    <img :src="fotoPreview" class="w-16 h-16 rounded object-cover mb-2" alt="foto preview">
                                </template>
                                <template x-if="!fotoPreview">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11l4 4 4-4" />
                                    </svg>
                                </template>
                                <span class="text-gray-400 text-sm">Upload Foto Item</span>
                            </label>
                        </div>
                    </div>
                    <div class="relative">
                        <label class="block font-semibold mb-2">Nama Item</label>
                        <input type="text" x-model="nama" @focus="showNamaSuggestion = true" @input="onNamaInput(); showNamaSuggestion = true" @blur="setTimeout(() => showNamaSuggestion = false, 150)" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400" placeholder="Masukkan nama item (contoh: Nasi Goreng)">
                        <div x-show="showNamaSuggestion" class="absolute left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg z-20 max-h-48 overflow-y-auto mt-1">
                            <template x-for="item in filteredNamaSuggestions" :key="item">
                                <div @mousedown.prevent="selectNamaSuggestion(item)" class="px-4 py-2 cursor-pointer hover:bg-[#eafbe6]" x-text="item"></div>
                            </template>
                            <template x-if="filteredNamaSuggestions.length === 0">
                                <div class="px-4 py-2 text-gray-400">Tidak ada nama item</div>
                            </template>
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold mb-2">Jumlah Tersedia</label>
                        <input type="number" x-model="unit" min="0" @input="if (unit < 0) unit = 0" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400" placeholder="Masukkan jumlah tersedia (contoh: 50)">
                    </div>
                </div>
                <!-- Kanan -->
                <div class="flex flex-col gap-4">
                    <div>
                        <label class="block font-semibold mb-2">SKU Item</label>
                        <input type="text" x-model="sku" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400" placeholder="Masukkan kode SKU (contoh: NG001)">
                    </div>
                    <div>
                        <label class="block font-semibold mb-2">Harga Item</label>
                        <input type="number" x-model="harga" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400" placeholder="Masukkan harga (contoh: 15000)">
                    </div>
                    <div>
                        <label class="block font-semibold mb-2">Lokasi Item</label>
                        <input type="text" x-model="lokasi" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400" placeholder="Masukkan lokasi penyimpanan (contoh: Rak A1)">
                    </div>

                </div>
                <!-- Tombol bawah -->
                <div class="col-span-1 md:col-span-2 flex flex-col md:flex-row gap-4 mt-6">
                    <button type="submit" class="w-full rounded-lg bg-[#28C328] text-white font-semibold py-3 text-lg hover:bg-[#22a322] transition">Simpan</button>
                </div>
                <div class="col-span-1 md:col-span-2 flex flex-col md:flex-row gap-4">
                    <button type="reset" @click.prevent="resetForm()" class="w-full rounded-lg bg-gray-200 text-gray-500 font-semibold py-3 text-lg">Reset</button>
                    <button type="button" @click="resetForm(); closeModal()" class="w-full rounded-lg bg-gray-200 text-gray-500 font-semibold py-3 text-lg">Batal</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Modal Detail Item -->
    <div x-show="showDetailModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" x-cloak @click.self="showDetailModal = false">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl mx-4 relative flex flex-col max-h-[90vh]" @click.stop>
            <!-- Header dengan gradient -->
            <div class="bg-gradient-to-r from-[#28C328] to-[#22a322] p-6 text-white flex-shrink-0">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center">
                            <template x-if="getImageSrc(detailItemData.gambar)">
                                <img :src="getImageSrc(detailItemData.gambar)" class="w-12 h-12 rounded-full object-cover" alt="item">
                            </template>
                            <template x-if="!getImageSrc(detailItemData.gambar)">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </template>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold" x-text="detailItemData.nama"></h2>
                            <p class="text-white/80 text-sm" x-text="'SKU: ' + detailItemData.sku"></p>
                        </div>
                    </div>
                    <button @click="showDetailModal = false" class="text-white/80 hover:text-white transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Content dengan scroll -->
            <div class="flex-1 overflow-y-auto p-6">
                <div class="space-y-6">
                    <!-- Informasi Utama -->
                    <div class="space-y-3">
                        <!-- Informasi Item & Statistik dalam satu baris -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="h-32">
                                <h3 class="text-sm font-bold text-gray-800 mb-2 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#28C328]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Informasi Item
                                </h3>
                                <div class="bg-gray-50 rounded-lg p-3 h-24 flex flex-col justify-center space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 text-xs">Nama:</span>
                                        <span class="font-semibold text-gray-800 text-sm" x-text="detailItemData.nama"></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 text-xs">SKU:</span>
                                        <span class="font-mono bg-gray-200 px-2 py-1 rounded text-xs" x-text="detailItemData.sku"></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 text-xs">Lokasi:</span>
                                        <span class="font-semibold text-gray-800 text-sm" x-text="detailItemData.lokasi"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="h-32">
                                <h3 class="text-sm font-bold text-gray-800 mb-2 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#28C328]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    Statistik Stock
                                </h3>
                                <div class="bg-blue-50 rounded-lg p-3 h-24 flex flex-col justify-center items-center">
                                    <div class="text-xl font-bold text-blue-600" x-text="Number(detailItemData.tersedia).toLocaleString('id-ID')"></div>
                                    <div class="text-xs text-blue-600 font-medium">Jumlah Tersedia</div>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Harga & Timeline dalam satu baris -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="h-32">
                                <h3 class="text-sm font-bold text-gray-800 mb-2 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#28C328]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                    </svg>
                                    Informasi Harga
                                </h3>
                                <div class="bg-green-50 rounded-lg p-3 h-24 flex flex-col justify-between">
                                    <div class="text-center">
                                        <div class="text-xl font-bold text-green-600" x-text="'Rp ' + Number(detailItemData.harga).toLocaleString('id-ID')"></div>
                                        <div class="text-xs text-green-600 font-medium">Harga Satuan</div>
                                    </div>
                                    <div class="pt-2 border-t border-green-200">
                                        <div class="flex justify-between items-center">
                                            <span class="text-green-700 font-medium text-xs">Total Nilai:</span>
                                            <span class="font-bold text-green-800 text-sm" x-text="'Rp ' + (Number(detailItemData.harga) * Number(detailItemData.tersedia)).toLocaleString('id-ID')"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="h-32">
                                <h3 class="text-sm font-bold text-gray-800 mb-2 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#28C328]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Timeline Update
                                </h3>
                                <div class="bg-gray-50 rounded-lg p-3 h-24 flex flex-col justify-center space-y-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-5 h-5 bg-[#28C328] rounded-full flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-800 text-xs">Item Dibuat</div>
                                            <div class="text-xs text-gray-500" x-text="new Date(detailItemData.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) + ' ' + new Date(detailItemData.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })"></div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-800 text-xs">Terakhir Update</div>
                                            <div class="text-xs text-gray-500" x-text="(detailItemData.updated_at ? new Date(detailItemData.updated_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) + ' ' + new Date(detailItemData.updated_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : new Date(detailItemData.diperbaharui).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) + ' 00:00')"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Button -->
                        <div class="flex justify-center">
                            <button @click="editItem(detailItemData, 0); showDetailModal = false" class="w-auto bg-[#28C328] text-white px-4 py-2 rounded-lg font-semibold hover:bg-[#22a322] transition flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit Item
                            </button>
                        </div>
                    </div>

                    <!-- History Section -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#28C328]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            History Lengkap
                        </h3>
                        <div class="bg-gray-50 rounded-xl p-4 max-h-64 overflow-y-auto">
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-xs">
                                    <thead class="sticky top-0">
                                        <tr class="bg-[#28C328] text-white">
                                            <th class="p-2 text-left">Tanggal</th>
                                            <th class="p-2 text-left">Aksi</th>
                                            <th class="p-2 text-left">Perubahan</th>
                                            <th class="p-2 text-left">User</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template x-for="history in getItemHistory(detailItemData.id)" :key="history.id">
                                            <tr>
                                                <td class="p-2" x-text="formatHistoryDate(history.timestamp)"></td>
                                                <td class="p-2">
                                                    <span class="px-2 py-1 rounded-full text-xs font-semibold" 
                                                          :class="getActionClass(history.action)" 
                                                          x-text="history.action"></span>
                                                </td>
                                                <td class="p-2">
                                                    <div class="space-y-1">
                                                        <template x-for="(change, key) in history.changes" :key="key">
                                                            <div class="text-xs">
                                                                <template x-if="typeof change === 'object' && change.dari !== undefined">
                                                                    <div>
                                                                        <span class="font-semibold" x-text="key + ': '"></span>
                                                                        <span class="text-red-600" x-text="change.dari"></span>
                                                                        <span class="mx-1">→</span>
                                                                        <span class="text-green-600" x-text="change.ke"></span>
                                                                    </div>
                                                                </template>
                                                                <template x-if="typeof change === 'object' && change.stok_lama !== undefined">
                                                                    <div>
                                                                        <template x-if="change.stok_baru > 0">
                                                                            <div>
                                                                                <span class="font-semibold">Ditambahkan: </span>
                                                                                <span class="text-green-600">+<span x-text="change.stok_baru"></span></span>
                                                                                <span class="text-gray-500"> (dari <span x-text="change.stok_lama"></span> menjadi <span x-text="change.stok_total"></span>)</span>
                                                                            </div>
                                                                        </template>
                                                                        <template x-if="change.stok_baru < 0">
                                                                            <div>
                                                                                <span class="font-semibold">Dikurangi: </span>
                                                                                <span class="text-red-600"><span x-text="change.stok_baru"></span></span>
                                                                                <span class="text-gray-500"> (dari <span x-text="change.stok_lama"></span> menjadi <span x-text="change.stok_total"></span>)</span>
                                                                            </div>
                                                                        </template>
                                                                        <template x-if="change.stok_baru === 0">
                                                                            <div>
                                                                                <span class="font-semibold">Tidak ada perubahan: </span>
                                                                                <span class="text-gray-600">0</span>
                                                                                <span class="text-gray-500"> (tetap <span x-text="change.stok_total"></span>)</span>
                                                                            </div>
                                                                        </template>
                                                                    </div>
                                                                </template>
                                                                <template x-if="typeof change === 'string'">
                                                                    <div x-text="change"></div>
                                                                </template>
                                                                <template x-if="typeof change === 'object' && change.catatan">
                                                                    <div class="text-gray-500 italic" x-text="change.catatan"></div>
                                                                </template>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </td>
                                                <td class="p-2" x-text="history.user"></td>
                                            </tr>
                                        </template>
                                        <template x-if="getItemHistory(detailItemData.id).length === 0">
                                            <tr>
                                                <td colspan="4" class="p-4 text-center text-gray-500">
                                                    Belum ada history untuk item ini
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Edit Item -->
    <div x-show="showEditModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" x-cloak @click.self="showEditModal = false">
        <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-3xl mx-4 relative" @click.stop>
            <button @click="showEditModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
            <form class="grid grid-cols-1 md:grid-cols-2 gap-8" @submit.prevent="submitEditForm()">
                <div x-show="editErrorMsg" class="col-span-1 md:col-span-2 text-red-500 text-sm mb-2" x-text="editErrorMsg"></div>
                <!-- Kiri -->
                <div class="flex flex-col gap-4">
                    <div>
                        <label class="block font-semibold mb-2">Upload Foto Item</label>
                        <div class="border border-gray-300 rounded-lg flex items-center justify-center h-32 bg-gray-50">
                            <input type="file" class="hidden" id="editUploadFoto" @change="handleEditFoto($event)">
                            <label for="editUploadFoto" class="flex flex-col items-center justify-center cursor-pointer w-full h-full">
                                <template x-if="editFotoPreview">
                                    <img :src="editFotoPreview" class="w-16 h-16 rounded object-cover mb-2" alt="foto preview">
                                </template>
                                <template x-if="!editFotoPreview && getImageSrc(detailItemData.gambar)">
                                    <img :src="getImageSrc(detailItemData.gambar)" class="w-16 h-16 rounded object-cover mb-2" alt="foto current">
                                </template>
                                <template x-if="!editFotoPreview && !getImageSrc(detailItemData.gambar)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11l4 4 4-4" />
                                    </svg>
                                </template>
                                <span class="text-gray-400 text-sm">Upload Foto</span>
                            </label>
                        </div>
                    </div>
                    <div class="relative">
                        <label class="block font-semibold mb-2">Nama Item</label>
                        <input type="text" x-model="editNama" @focus="showEditNamaSuggestion = true" @input="showEditNamaSuggestion = true" @blur="setTimeout(() => showEditNamaSuggestion = false, 150)" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400" placeholder="Masukkan Nama Item">
                        <div x-show="showEditNamaSuggestion" class="absolute left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg z-20 max-h-48 overflow-y-auto mt-1">
                            <template x-for="item in filteredEditNamaSuggestions" :key="item">
                                <div @mousedown.prevent="editNama = item; showEditNamaSuggestion = false" class="px-4 py-2 cursor-pointer hover:bg-[#eafbe6]" x-text="item"></div>
                            </template>
                            <template x-if="filteredEditNamaSuggestions.length === 0">
                                <div class="px-4 py-2 text-gray-400">Tidak ada nama item</div>
                            </template>
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold mb-2">SKU</label>
                        <input type="text" x-model="editSku" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400">
                    </div>
                </div>
                <!-- Kanan -->
                <div class="flex flex-col gap-4">
                    <div>
                        <label class="block font-semibold mb-2">Lokasi</label>
                        <input type="text" x-model="editLokasi" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400">
                    </div>
                    <div>
                        <label class="block font-semibold mb-2">Tersedia</label>
                        <input type="number" x-model="editTersedia" min="0" @input="if (editTersedia < 0) editTersedia = 0" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400">
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">Harga</label>
                        <input type="number" x-model="editHarga" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-700 placeholder-gray-400">
                    </div>
                </div>
                <!-- Tombol bawah -->
                <div class="col-span-1 md:col-span-2 flex flex-col md:flex-row gap-4 mt-6">
                    <button type="submit" class="w-full rounded-lg bg-[#28C328] text-white font-semibold py-3 text-lg hover:bg-[#22a322] transition">Simpan</button>
                </div>
                <div class="col-span-1 md:col-span-2 flex flex-col md:flex-row gap-4">
                    <button type="reset" @click.prevent="resetEditForm()" class="w-full rounded-lg bg-gray-200 text-gray-500 font-semibold py-3 text-lg">Reset</button>
                    <button type="button" @click="resetEditForm(); showEditModal = false" class="w-full rounded-lg bg-gray-200 text-gray-500 font-semibold py-3 text-lg">Batal</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Konfirmasi Merge Item -->
    <div x-show="showConfirmModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" x-cloak @click.self="showConfirmModal = false; confirmData = null">
        <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-2xl mx-4 relative" @click.stop>
            <button @click="showConfirmModal = false; confirmData = null" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Item Sudah Ada</h3>
                <p class="text-gray-600">Item dengan nama "<span class="font-semibold" x-text="confirmData?.newData?.nama"></span>" sudah ada dalam sistem.</p>
            </div>
            
            <div class="bg-gray-50 rounded-xl p-4 mb-6">
                <h4 class="font-semibold text-gray-800 mb-3">Detail Item yang Ada:</h4>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Nama:</span>
                        <span class="font-semibold" x-text="confirmData?.existingItem?.nama"></span>
                    </div>
                    <div>
                        <span class="text-gray-600">SKU:</span>
                        <span class="font-semibold" x-text="confirmData?.existingItem?.sku"></span>
                    </div>
                    <div>
                        <span class="text-gray-600">Stok Saat Ini:</span>
                        <span class="font-semibold" x-text="confirmData?.existingItem?.tersedia"></span>
                    </div>
                    <div>
                        <span class="text-gray-600">Harga:</span>
                        <span class="font-semibold">Rp<span x-text="Number(confirmData?.existingItem?.harga).toLocaleString('id-ID')"></span></span>
                    </div>
                </div>
            </div>
            
            <div class="bg-blue-50 rounded-xl p-4 mb-6">
                <h4 class="font-semibold text-blue-800 mb-3">Aksi yang Akan Dilakukan:</h4>
                <div class="text-sm text-blue-700 space-y-1">
                    <p>• Stok baru (<span x-text="confirmData?.newData?.tersedia"></span>) akan <strong>ditambahkan</strong> ke stok yang ada</p>
                    <p>• Total stok menjadi: <strong><span x-text="Number(confirmData?.existingItem?.tersedia || 0) + Number(confirmData?.newData?.tersedia || 0)"></span></strong></p>
                    <template x-if="confirmData?.existingItem?.sku !== confirmData?.newData?.sku">
                        <p>• SKU akan diubah dari <span class="font-semibold text-red-600" x-text="confirmData?.existingItem?.sku"></span> menjadi <span class="font-semibold text-green-600" x-text="confirmData?.newData?.sku"></span></p>
                    </template>
                    <template x-if="confirmData?.existingItem?.lokasi !== confirmData?.newData?.lokasi">
                        <p>• Lokasi akan diubah dari <span class="font-semibold text-red-600" x-text="confirmData?.existingItem?.lokasi"></span> menjadi <span class="font-semibold text-green-600" x-text="confirmData?.newData?.lokasi"></span></p>
                    </template>
                    <template x-if="Number(confirmData?.existingItem?.harga) !== Number(confirmData?.newData?.harga)">
                        <p>• Harga akan diubah dari <span class="font-semibold text-red-600">Rp<span x-text="Number(confirmData?.existingItem?.harga).toLocaleString('id-ID')"></span></span> menjadi <span class="font-semibold text-green-600">Rp<span x-text="Number(confirmData?.newData?.harga).toLocaleString('id-ID')"></span></span></p>
                    </template>
                    <template x-if="confirmData?.existingItem?.foto !== confirmData?.newData?.foto">
                        <p>• Gambar akan diubah:</p>
                        <div class="flex gap-2 items-center">
                            <template x-if="getImageSrc(confirmData?.existingItem?.foto)">
                                <img :src="getImageSrc(confirmData?.existingItem?.foto)" class="w-10 h-10 rounded object-cover border-2 border-red-300" alt="Gambar lama">
                            </template>
                            <template x-if="!getImageSrc(confirmData?.existingItem?.foto)">
                                <div class="w-10 h-10 rounded bg-gray-200 flex items-center justify-center border-2 border-red-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </template>
                            <span class="mx-1">→</span>
                            <template x-if="getImageSrc(confirmData?.newData?.foto)">
                                <img :src="getImageSrc(confirmData?.newData?.foto)" class="w-10 h-10 rounded object-cover border-2 border-green-300" alt="Gambar baru">
                            </template>
                            <template x-if="!getImageSrc(confirmData?.newData?.foto)">
                                <div class="w-10 h-10 rounded bg-gray-200 flex items-center justify-center border-2 border-green-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
            
            <div class="flex gap-4">
                <button @click="showConfirmModal = false; confirmData = null" class="flex-1 rounded-lg bg-gray-200 text-gray-700 font-semibold py-3 hover:bg-gray-300 transition">
                    Batal
                </button>
                <button @click="mergeItems()" class="flex-1 rounded-lg bg-[#28C328] text-white font-semibold py-3 hover:bg-[#22a322] transition">
                    Gabungkan Stok
                </button>
            </div>
        </div>
    </div>
    <!-- Modal Konfirmasi Hapus -->
    <div x-show="showDeleteModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" x-cloak @click.self="showDeleteModal = false">
        <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md mx-4 relative" @click.stop>
            <button @click="showDeleteModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Konfirmasi Hapus</h3>
                <p class="text-gray-600">Apakah Anda yakin ingin menghapus item <span class="font-semibold" x-text="deleteItemData?.nama"></span>?</p>
            </div>
            <div class="flex gap-4">
                <button @click="showDeleteModal = false" class="flex-1 rounded-lg bg-gray-200 text-gray-700 font-semibold py-3 hover:bg-gray-300 transition">
                    Batal
                </button>
                <button @click="confirmDeleteItem()" class="flex-1 rounded-lg bg-red-600 text-white font-semibold py-3 hover:bg-red-700 transition">
                    Hapus
                </button>
            </div>
        </div>
    </div>
    <!-- Modal Import Data -->
    <div x-show="showImportModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" x-cloak @click.self="showImportModal = false">
        <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md mx-4 relative" @click.stop>
            <button @click="showImportModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
            
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold text-[#28C328]">Import Data Stok</h2>
                <p class="text-gray-600 text-sm">Upload file CSV atau Excel untuk menambah banyak item sekaligus.</p>
            </div>

            <form @submit.prevent="submitImportForm">
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-gray-500 mb-1 ml-1">Pilih File</label>
                    <input type="file" accept=".csv, .xlsx, .xls" @change="onImportFileChange($event)" class="block w-full text-sm text-gray-700 border border-gray-300 rounded-lg p-2 focus:ring-1 focus:ring-[#28C328] outline-none" />
                </div>

                <div class="mb-5">
                    <a href="{{ asset('public/excel/template_admin.xlsx') }}" download class="flex items-center justify-center gap-2 w-full py-2 px-4 border border-dashed border-[#28C328] text-[#28C328] rounded-lg text-xs font-bold hover:bg-green-50 transition-all group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform group-hover:translate-y-0.5 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="Status 4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        UNDUH TEMPLATE EXCEL
                    </a>
                </div>

                <div class="mb-4 text-[11px] text-gray-500 bg-gray-50 border border-gray-100 rounded-xl p-3">
                    <div class="font-bold text-gray-700 mb-2 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Panduan Format:
                    </div>
                    <div class="space-y-1">
                        <p>• Kolom wajib: <span class="font-mono text-red-500">nama, sku, lokasi, tersedia, harga, diperbaharui</span></p>
                        <p>• Pastikan header (baris pertama) sama persis dengan template.</p>
                    </div>
                </div>

                <div class="flex gap-2 mt-6">
                    <button type="submit" :disabled="!importFile" class="flex-1 rounded-lg bg-[#28C328] text-white font-bold py-2.5 hover:bg-[#22a322] shadow-md shadow-green-100 transition disabled:opacity-50 disabled:cursor-not-allowed uppercase text-sm">Import</button>
                    <button type="button" @click="showImportModal = false" class="flex-1 rounded-lg bg-gray-100 text-gray-600 font-bold py-2.5 hover:bg-gray-200 transition uppercase text-sm">Batal</button>
                </div>

                <div x-show="importErrorMsg" class="text-red-500 text-xs mt-3 text-center font-medium" x-text="importErrorMsg"></div>
                <div x-show="importSuccessMsg" class="text-green-600 text-xs mt-3 text-center font-medium" x-text="importSuccessMsg"></div>
            </form>
        </div>
    </div>
</div>
@endsection

<script>
function stockTable() {
    return {
        items: [],
        itemHistory: {}, // history nanti bisa dari DB juga jika diinginkan,
        search: '',
        dateFilter: '',
        customStartDate: '',
        customEndDate: '',
        currentPage: 1,
        perPage: 10,
        // Modal state
        showModal: false,
        showConfirmModal: false,
        confirmData: null,
        foto: null,
        fotoPreview: '',
        nama: '',
        unit: '',
        sku: '',
        harga: '',
        lokasi: '',
        errorMsg: '',
        async init() {
            await this.fetchItems();
        },
        closeModal() { this.showModal = false; this.resetForm(); this.errorMsg = ''; },
        resetForm() {
            this.foto = null;
            this.fotoPreview = '';
            this.nama = '';
            this.unit = '';
            this.sku = '';
            this.harga = '';
            this.lokasi = '';
            this.errorMsg = '';
        },
        handleFoto(event) {
            const file = event.target.files[0];
            this.foto = file;
            if (file) {
                const reader = new FileReader();
                reader.onload = e => { this.fotoPreview = e.target.result; };
                reader.readAsDataURL(file);
            } else {
                this.fotoPreview = '';
            }
        },
        async fetchItems() {
            const res = await fetch('/admin/stock-items', { headers: { 'Accept': 'application/json' } });
            if (res.ok) {
                this.items = await res.json();
            }
        },
        async submitForm() {
            // Validasi wajib isi (foto opsional)
            if (!this.nama || this.nama.trim() === '') {
                this.errorMsg = 'Field Nama Item wajib diisi.';
                return;
            }
            if (!this.unit || Number(this.unit) <= 0) {
                this.errorMsg = 'Field Jumlah Tersedia wajib diisi dan harus lebih dari 0.';
                return;
            }
            if (!this.sku || this.sku.trim() === '') {
                this.errorMsg = 'Field SKU Item wajib diisi.';
                return;
            }
            if (!this.harga || Number(this.harga) <= 0) {
                this.errorMsg = 'Field Harga Item wajib diisi dan harus lebih dari 0.';
                return;
            }
            if (!this.lokasi || this.lokasi.trim() === '') {
                this.errorMsg = 'Field Lokasi Item wajib diisi.';
                return;
            }

            // Cek apakah nama item sudah ada
            const existingItem = this.items.find(item => item.nama.toLowerCase() === this.nama.trim().toLowerCase());
            
            if (existingItem) {
                // Tampilkan konfirmasi untuk merge
                this.confirmData = {
                    existingItem: { ...existingItem, foto: existingItem.gambar },
                    newData: {
                        nama: this.nama.trim(),
                        sku: this.sku.trim(),
                        lokasi: this.lokasi.trim(),
                        tersedia: Number(this.unit) || 0,
                        harga: Number(this.harga) || 0,
                        foto: this.fotoPreview || ''
                    }
                };
                this.showConfirmModal = true;
                return;
            }

            // Jika nama unik, buat item baru di server
            await this.createNewItem();
        },

        async createNewItem() {
            try {
                const res = await fetch('/admin/stock-items', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                    },
                    body: JSON.stringify({
                nama: this.nama.trim(),
                sku: this.sku.trim(),
                lokasi: this.lokasi.trim(),
                tersedia: Number(this.unit) || 0,
                harga: Number(this.harga) || 0,
                        foto: this.fotoPreview || ''
                    })
                });
                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    this.errorMsg = err.message || 'Gagal menyimpan item.';
                    return;
                }
                const saved = await res.json();
                this.items.unshift(saved);
            this.resetForm();
            this.closeModal();
            } catch (e) {
                this.errorMsg = 'Gagal terhubung ke server.';
            }
        },

        async mergeItems() {
            const existingItem = this.confirmData.existingItem;
            const newData = this.confirmData.newData;
            
            try {
                const res = await fetch(`/admin/stock-items/${existingItem.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                    },
                    body: JSON.stringify({
                sku: newData.sku,
                lokasi: newData.lokasi,
                        harga: Number(newData.harga) || existingItem.harga,
                        tersedia: (Number(existingItem.tersedia) || 0) + (Number(newData.tersedia) || 0),
                        foto: newData.foto || ''
                    })
                });
                if (!res.ok) {
                    alert('Gagal menggabungkan item.');
                    return;
                }
                const updated = await res.json();
                const idx = this.items.findIndex(i => i.id === existingItem.id);
                if (idx !== -1) this.items.splice(idx, 1, updated);
            this.showConfirmModal = false;
            this.confirmData = null;
            this.resetForm();
            this.closeModal();
            } catch (e) {
                alert('Gagal terhubung ke server.');
            }
        },

        async addItemHistory(itemId, action, changes) {
            // History sekarang dicatat di server oleh controller; fungsi ini tidak digunakan lagi
        },
        sortKey: '',
        sortOrder: '', // '', 'asc', 'desc'
        sortCount: 0,
        sortBy(key) {
            if (this.sortKey !== key) {
                this.sortKey = key;
                this.sortOrder = 'asc';
                this.sortCount = 1;
            } else if (this.sortOrder === 'asc') {
                this.sortOrder = 'desc';
                this.sortCount = 2;
            } else if (this.sortOrder === 'desc') {
                this.sortKey = '';
                this.sortOrder = '';
                this.sortCount = 0;
            }
        },
        
        // Date filter functions
        applyDateFilter() {
            // Reset to first page when filter changes
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
                case 'today':
                    return 'Menampilkan item yang diperbaharui hari ini';
                case 'week':
                    return 'Menampilkan item yang diperbaharui minggu ini';
                case 'month':
                    return 'Menampilkan item yang diperbaharui bulan ini';
                case 'year':
                    return 'Menampilkan item yang diperbaharui tahun ini';
                case 'custom':
                    if (this.customStartDate && this.customEndDate) {
                        const start = new Date(this.customStartDate).toLocaleDateString('id-ID');
                        const end = new Date(this.customEndDate).toLocaleDateString('id-ID');
                        return `Menampilkan item yang diperbaharui dari ${start} sampai ${end}`;
                    }
                    return 'Menampilkan item berdasarkan rentang tanggal custom';
                default:
                    return '';
            }
        },
        
        applyDateFilterToItems(items) {
            if (!this.dateFilter) return items;
            
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
                    if (!this.customStartDate || !this.customEndDate) return items;
                    startDate = new Date(this.customStartDate);
                    endDate = new Date(this.customEndDate);
                    endDate.setHours(23, 59, 59, 999);
                    break;
                default:
                    return items;
            }
            
            return items.filter(item => {
                const itemDate = new Date(item.diperbaharui);
                return itemDate >= startDate && itemDate <= endDate;
            });
        },
        
        get filteredItems() {
            const search = this.search.toLowerCase();
            let items = this.items.filter(item => {
                const matchSearch = this.search === '' || Object.values(item).some(val =>
                    String(val).toLowerCase().includes(search)
                );
                return matchSearch;
            });
            
            // Apply date filter
            if (this.dateFilter) {
                items = this.applyDateFilterToItems(items);
            }
            
            // Sorting
            if (this.sortKey && this.sortOrder) {
                items = [...items].sort((a, b) => {
                    let valA = a[this.sortKey];
                    let valB = b[this.sortKey];
                    // Angka
                    if (["tersedia","harga"].includes(this.sortKey)) {
                        valA = Number(valA);
                        valB = Number(valB);
                    }
                    // Tanggal
                    if (this.sortKey === 'diperbaharui') {
                        valA = new Date(valA);
                        valB = new Date(valB);
                    }
                    if (valA < valB) return this.sortOrder === 'asc' ? -1 : 1;
                    if (valA > valB) return this.sortOrder === 'asc' ? 1 : -1;
                    return 0;
                });
            }
            return items;
        },
        get paginatedItems() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredItems.slice(start, start + this.perPage);
        },
        get totalPages() {
            return Math.max(1, Math.ceil(this.filteredItems.length / this.perPage));
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

        exportExcel() {
            // Gunakan data yang sudah difilter (dan terurut) saja
            const excelData = this.filteredItems.map(item => ({
                'Nama Item': item.nama,
                'SKU': item.sku,
                'Lokasi': item.lokasi,
                'Tersedia': Number(item.tersedia).toLocaleString('id-ID'),
                'Harga': `Rp ${Number(item.harga).toLocaleString('id-ID')}`,
                'Diperbaharui': new Date(item.diperbaharui).toLocaleDateString('id-ID')
            }));

            const worksheet = XLSX.utils.json_to_sheet(excelData);

            // Set lebar kolom
            worksheet['!cols'] = [
                { wch: 25 }, { wch: 18 }, { wch: 20 },
                { wch: 12 }, { wch: 18 }, { wch: 15 }
            ];

            // Freeze header row
            worksheet['!freeze'] = { xSplit: 0, ySplit: 1 };

            // Auto filter
            worksheet['!autofilter'] = { ref: "A1:F1" };

            // Styling header dan zebra striping (hanya didukung di Google Sheets atau SheetJS Pro)
            const range = XLSX.utils.decode_range(worksheet['!ref']);
            for (let C = range.s.c; C <= range.e.c; ++C) {
                const cell = worksheet[XLSX.utils.encode_cell({ r: 0, c: C })];
                if (cell) {
                    cell.s = {
                        font: { bold: true, color: { rgb: "FFFFFF" } },
                        fill: { fgColor: { rgb: "28C328" } },
                        alignment: { horizontal: "center", vertical: "center" }
                    };
                }
            }
            for (let R = 1; R <= range.e.r; ++R) {
                for (let C = range.s.c; C <= range.e.c; ++C) {
                    const cell = worksheet[XLSX.utils.encode_cell({ r: R, c: C })];
                    if (cell) {
                        cell.s = {
                            alignment: { horizontal: "center", vertical: "center" },
                            fill: (R % 2 === 0) ? { fgColor: { rgb: "F8F9FA" } } : undefined
                        };
                    }
                }
            }

            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, "Stock Data");
            XLSX.writeFile(workbook, "stock_data.xlsx");
        },
        exportPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            const tableColumn = ["Nama Item", "SKU", "Lokasi", "Tersedia", "Harga", "Diperbaharui"];
            // Hanya data hasil filter yang diekspor
            const tableRows = this.filteredItems.map(item => [
                item.nama,
                item.sku,
                item.lokasi,
                Number(item.tersedia).toLocaleString('id-ID'),
                `Rp ${Number(item.harga).toLocaleString('id-ID')}`,
                new Date(item.diperbaharui).toLocaleDateString('id-ID')
            ]);

            doc.autoTable({
                head: [tableColumn],
                body: tableRows,
                startY: 20,
                styles: {
                    fontSize: 8,
                    cellPadding: 2
                },
                headStyles: {
                    fillColor: [40, 195, 40],
                    textColor: 255
                }
            });
            doc.save("stock_data.pdf");
        },
        formatElapsedTime(dateString) {
            if (!dateString) return '-';
            const now = new Date();
            const then = new Date(dateString);
            const diff = Math.floor((now - then) / 1000);
            if (diff < 60) return 'baru saja';
            if (diff < 3600) return Math.floor(diff/60) + ' menit yang lalu';
            if (diff < 86400) return Math.floor(diff/3600) + ' jam yang lalu';
            if (diff < 2592000) return Math.floor(diff/86400) + ' hari yang lalu';
            if (diff < 31536000) return Math.floor(diff/2592000) + ' bulan yang lalu';
            return Math.floor(diff/31536000) + ' tahun yang lalu';
        },
        openActionMenuIndex: null,
        showDetailModal: false,
        detailItemData: {},
        showEditModal: false,
        editItemId: null, // Simpan ID item, bukan index
        editFoto: null,
        editFotoPreview: '',
        editNama: '',
        editSku: '',
        editLokasi: '',
        editTersedia: '',

        editHarga: '',
        editErrorMsg: '',
        async detailItem(item) {
            this.detailItemData = {...item};
            // fetch history dari server
            try {
                const res = await fetch(`/admin/stock-items/${item.id}/history`, { headers: { 'Accept': 'application/json' } });
                if (res.ok) {
                    this.itemHistory[item.id] = await res.json();
                } else {
                    this.itemHistory[item.id] = [];
                }
            } catch { this.itemHistory[item.id] = []; }
            this.showDetailModal = true;
        },
        editItem(item, idx) {
            // Simpan ID item langsung, bukan index (untuk menghindari masalah saat search/filter)
            this.editItemId = item.id;
            this.editNama = item.nama;
            this.editSku = item.sku;
            this.editLokasi = item.lokasi;
            this.editTersedia = item.tersedia;
            this.editHarga = item.harga;
            this.editFotoPreview = '';
            this.detailItemData = {...item};
            this.showEditModal = true;
            this.editErrorMsg = '';
        },
        closeEditModal() { this.showEditModal = false; this.resetEditForm(); this.editErrorMsg = ''; },
        resetEditForm() {
            this.editFoto = null;
            this.editFotoPreview = '';
            this.editNama = '';
            this.editSku = '';
            this.editLokasi = '';
            this.editTersedia = '';

            this.editHarga = '';
            this.editErrorMsg = '';
            this.editItemId = null; // Reset ID juga
        },
        handleEditFoto(event) {
            const file = event.target.files[0];
            this.editFoto = file;
            if (file) {
                const reader = new FileReader();
                reader.onload = e => { this.editFotoPreview = e.target.result; };
                reader.readAsDataURL(file);
            }
        },
        async submitEditForm() {
            if (!this.editNama) { this.editErrorMsg = 'Field Nama Item wajib diisi.'; return; }
            if (!this.editSku) { this.editErrorMsg = 'Field SKU wajib diisi.'; return; }
            if (!this.editLokasi) { this.editErrorMsg = 'Field Lokasi wajib diisi.'; return; }
            if (!this.editTersedia) { this.editErrorMsg = 'Field Tersedia wajib diisi.'; return; }
            if (!this.editHarga) { this.editErrorMsg = 'Field Harga wajib diisi.'; return; }
            
            // Validasi ID item
            if (!this.editItemId) {
                this.editErrorMsg = 'ID item tidak valid. Silakan tutup dan buka kembali form edit.';
                return;
            }
            
            // Cari item berdasarkan ID (bukan index) untuk memastikan item yang benar
            const originalItem = this.items.find(item => item.id === this.editItemId);
            if (!originalItem) {
                this.editErrorMsg = 'Item tidak ditemukan. Silakan refresh halaman.';
                return;
            }
            
            try {
                const payload = {
                nama: this.editNama,
                sku: this.editSku,
                lokasi: this.editLokasi,
                tersedia: Number(this.editTersedia) || 0,
                harga: Number(this.editHarga) || 0,
                };
                if (this.editFotoPreview && this.editFotoPreview.startsWith('data:image')) {
                    payload.foto = this.editFotoPreview;
                }
                const res = await fetch(`/admin/stock-items/${this.editItemId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                    },
                    body: JSON.stringify(payload)
                });
                if (!res.ok) {
                    const errorData = await res.json().catch(() => ({}));
                    this.editErrorMsg = errorData.message || 'Gagal menyimpan perubahan.';
                    return;
                }
                const updated = await res.json();
                
                // Update item di array berdasarkan ID, bukan index
                const itemIndex = this.items.findIndex(item => item.id === this.editItemId);
                if (itemIndex !== -1) {
                    this.items[itemIndex] = updated;
                } else {
                    // Jika tidak ditemukan, reload items
                    await this.loadItems();
                }
                
            this.resetEditForm();
            this.showEditModal = false;
            } catch (e) {
                console.error('Error updating item:', e);
                this.editErrorMsg = 'Gagal terhubung ke server: ' + e.message;
            }
        },
        async deleteItem(item, idx) {
            this.deleteItemData = item;
            this.deleteItemIndex = idx;
            this.showDeleteModal = true;
        },
        async confirmDeleteItem() {
            const item = this.deleteItemData;
            const idx = this.deleteItemIndex;
            try {
                this.deletingIndex = idx;
                const res = await fetch(`/admin/stock-items/${item.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                    }
                });
                this.deletingIndex = null;
                this.showDeleteModal = false;
                this.deleteItemData = null;
                this.deleteItemIndex = null;
                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    alert('Gagal menghapus item: ' + (err.message || 'Unknown error'));
                    return;
                }
                await this.fetchItems();
                // Optional: tampilkan toast sukses
            } catch (e) {
                this.deletingIndex = null;
                this.showDeleteModal = false;
                this.deleteItemData = null;
                this.deleteItemIndex = null;
                alert('Gagal terhubung ke server.');
            }
        },
        exportItemDetail() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            // Header
            doc.setFontSize(20);
            doc.setTextColor(40, 195, 40);
            doc.text('Detail Item - GAFI', 20, 20);
            
            // Item Image (if available)
            if (this.detailItemData.gambar && this.detailItemData.gambar !== 'images/gambar.png') {
                try {
                    doc.addImage(this.detailItemData.gambar, 'JPEG', 20, 30, 30, 30);
                } catch(e) {
                    // If image fails, continue without it
                }
            }
            
            // Item Information
            doc.setFontSize(12);
            doc.setTextColor(60);
            let y = 40;
            
            doc.text('Nama Item:', 20, y);
            doc.text(this.detailItemData.nama || '', 60, y);
            y += 8;
            
            doc.text('SKU:', 20, y);
            doc.text(this.detailItemData.sku || '', 60, y);
            y += 8;
            
            doc.text('Lokasi:', 20, y);
            doc.text(this.detailItemData.lokasi || '', 60, y);
            y += 8;
            
            doc.text('Kondisi:', 20, y);
            doc.text(this.detailItemData.kondisi || 'Baru', 60, y);
            y += 8;
            
            doc.text('Tersedia:', 20, y);
            doc.text(Number(this.detailItemData.tersedia).toLocaleString('id-ID'), 60, y);
            y += 8;
            
            doc.text('Harga Satuan:', 20, y);
            doc.text('Rp ' + Number(this.detailItemData.harga).toLocaleString('id-ID'), 60, y);
            y += 8;
            
            doc.text('Total Nilai Stock:', 20, y);
            doc.text('Rp ' + (Number(this.detailItemData.harga) * Number(this.detailItemData.tersedia)).toLocaleString('id-ID'), 60, y);
            y += 12;
            
            doc.text('Dibuat:', 20, y);
            doc.text(new Date(this.detailItemData.created_at).toLocaleDateString('id-ID'), 60, y);
            y += 8;
            
            doc.text('Diperbaharui:', 20, y);
            doc.text(new Date(this.detailItemData.diperbaharui).toLocaleDateString('id-ID'), 60, y);
            
            // Footer
            doc.setFontSize(10);
            doc.setTextColor(120);
            doc.text('Dicetak pada: ' + new Date().toLocaleDateString('id-ID') + ' ' + new Date().toLocaleTimeString('id-ID'), 20, 280);
            
            doc.save('detail_item_' + (this.detailItemData.sku || 'gafi') + '.pdf');
        },

        // Helper functions untuk history
        getItemHistory(itemId) {
            return this.itemHistory[itemId] || [];
        },

        // Helper function untuk gambar
        getImageSrc(gambar) {
            if (!gambar || gambar === 'images/gambar.png') {
                return null; // Tidak ada gambar default
            }
            if (gambar.startsWith('data:') || gambar.startsWith('http')) {
                return gambar;
            }
            return '{{ asset('') }}' + gambar;
        },

        formatHistoryDate(timestamp) {
            const date = new Date(timestamp);
            return date.toLocaleDateString('id-ID', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        getActionClass(action) {
            switch(action) {
                case 'Item Baru Dibuat':
                    return 'bg-green-100 text-green-800';
                case 'Item Diperbaharui':
                    return 'bg-blue-100 text-blue-800';
                case 'Stok Ditambahkan':
                    return 'bg-yellow-100 text-yellow-800';
                case 'Item Dihapus':
                    return 'bg-red-100 text-red-800';
                default:
                    return 'bg-gray-100 text-gray-800';
            }
        },
        dropdownDirection: 'down',
        adjustDropdownDirection(el, parentEl) {
            if (!el) return;
            const rect = el.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.top;
            const spaceAbove = rect.top;
            // Deteksi posisi menu
            if (spaceBelow < 150 && spaceAbove > spaceBelow) {
                this.dropdownDirection = 'up';
            } else {
                this.dropdownDirection = 'down';
            }
            // Scroll otomatis jika menu terpotong
            const menuHeight = rect.height;
            let scrollOffset = 0;
            if (this.dropdownDirection === 'down' && spaceBelow < menuHeight) {
                scrollOffset = menuHeight - spaceBelow + 16; // 16px margin
                window.scrollBy({ top: scrollOffset, behavior: 'smooth' });
            } else if (this.dropdownDirection === 'up' && spaceAbove < menuHeight) {
                scrollOffset = menuHeight - spaceAbove + 16;
                window.scrollBy({ top: -scrollOffset, behavior: 'smooth' });
            }
        },
        showNamaSuggestion: false,
        showEditNamaSuggestion: false,
        get filteredNamaSuggestions() {
            const q = this.nama.toLowerCase();
            return this.items
                .map(i => i.nama)
                .filter((v, i, arr) => arr.indexOf(v) === i)
                .filter(nama => !q || nama.toLowerCase().includes(q));
        },
        get filteredEditNamaSuggestions() {
            const q = this.editNama.toLowerCase();
            return this.items
                .map(i => i.nama)
                .filter((v, i, arr) => arr.indexOf(v) === i)
                .filter(nama => !q || nama.toLowerCase().includes(q));
        },
        // Prefill data SKU/Harga/Lokasi jika nama cocok dengan item yang sudah ada
        assetBase: '{{ asset('') }}',
        onNamaInput() {
            const namaLower = (this.nama || '').trim().toLowerCase();
            if (!namaLower) return;
            const match = this.items.find(i => (i.nama || '').toLowerCase() === namaLower);
            if (match) {
                this.sku = match.sku || '';
                this.harga = Number(match.harga || 0) || '';
                this.lokasi = match.lokasi || '';
                if (!this.fotoPreview && match.gambar && match.gambar !== 'images/gambar.png') {
                    this.fotoPreview = (String(match.gambar).startsWith('http') || String(match.gambar).startsWith('data:'))
                        ? match.gambar
                        : (this.assetBase + match.gambar);
                } else if (!this.fotoPreview) {
                    this.fotoPreview = ''; // Tidak ada gambar default
                }
            }
        },
        selectNamaSuggestion(itemName) {
            this.nama = itemName;
            this.showNamaSuggestion = false;
            this.onNamaInput();
        },
        showAddDropdown: false,
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
                const res = await fetch('/admin/stock-items/import', {
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
                await this.fetchItems();
            } catch (e) {
                this.importErrorMsg = 'Gagal terhubung ke server.';
            }
        },
    }
}
</script>

<style>[x-cloak] { display: none !important; }</style>
@extends('layouts.admin')

@section('content')
<div class="bg-white rounded-xl shadow p-8" x-data="adminOverview()" x-init="initChart(); loadOverviewData(); startAutoRefresh();">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-4">
        <h1 class="text-2xl font-bold text-[#28C328]">Admin Overview</h1>
        <div class="flex items-center gap-3 text-sm">
            <div class="w-px h-5 bg-gray-200"></div>
            <label class="text-gray-600">Periode</label>
            <div class="relative">
                <select class="rounded border border-gray-300 px-3 pr-8 py-1 min-w-[7rem]" style="appearance:none;-webkit-appearance:none;-moz-appearance:none;background-image:none;" x-model="selectedPeriod" @change="onPeriodChange()">
                    <option value="week">Minggu</option>
                    <option value="month">Bulan</option>
                    <option value="year">Tahun</option>
                </select>
                <svg class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
            <div class="w-px h-5 bg-gray-200"></div>
            <label class="text-gray-600">Admin Low Stock Threshold</label>
            <input type="number" min="1" class="w-20 rounded border border-gray-300 px-2 py-1" x-model.number="lowStockThreshold" @input="onThresholdInput">
            <div class="w-px h-5 bg-gray-200"></div>
            <label class="text-gray-600">Client Low Stock Threshold</label>
            <input type="number" min="1" class="w-20 rounded border border-gray-300 px-2 py-1" x-model.number="clientLowStockThreshold" @input="onClientThresholdInput">
            <div class="w-px h-5 bg-gray-200"></div>
            <label class="text-gray-600">Target Omzet</label>
            <div class="relative">
                <span class="absolute left-2 top-1.5 text-gray-400 text-xs">Rp</span>
                <input type="text" inputmode="numeric" class="w-40 rounded border border-gray-300 pl-6 pr-2 py-1" x-model="targetRevenueInput" @input="onTargetInput">
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
        <div class="rounded-xl border p-4 bg-gray-50">
            <div class="text-xs text-gray-500">Total Client</div>
            <div class="text-2xl font-bold" x-text="dbKpi.totalClients || 0"></div>
        </div>
        <div class="rounded-xl border p-4 bg-gray-50">
            <div class="text-xs text-gray-500">Total Item Stok</div>
            <div class="text-2xl font-bold" x-text="dbKpi.totalItems || 0"></div>
        </div>
        <div class="rounded-xl border p-4 bg-gray-50">
            <div class="text-xs text-gray-500">Item Low Stock</div>
            <div class="text-2xl font-bold text-orange-600" x-text="dbKpi.lowStockCount || 0"></div>
        </div>
        <div class="rounded-xl border p-4 bg-gray-50">
            <div class="text-xs text-gray-500">Transaksi (Rentang)</div>
            <div class="text-2xl font-bold" x-text="dbKpi.txInRange || 0"></div>
        </div>
        <div class="rounded-xl border p-4 bg-gray-50">
            <div class="text-xs text-gray-500">Omzet (Rentang)</div>
            <div class="text-2xl font-bold text-[#28C328]">Rp<span x-text="Number(dbKpi.revenueInRange || 0).toLocaleString('id-ID')"></span></div>
        </div>
        <div class="rounded-xl border p-4 bg-gray-50">
            <div class="text-xs text-gray-500">Belum Dibayar</div>
            <div class="text-2xl font-bold text-red-600" x-text="dbKpi.unpaidCount || 0"></div>
        </div>
    </div>

    <!-- Sales Chart -->
    <div class="rounded-xl border p-4 mb-6 bg-gradient-to-b from-white to-gray-50">
        <div class="flex items-center justify-between mb-2">
            <div class="font-semibold">Omzet Berdasarkan Periode</div>
            <div class="text-xs text-gray-500">
                <span x-show="dbRevenueData && dbRevenueData.length > 0">
                    Sumber: Data Transaksi Real per Hari/Bulan
                </span>
                <span x-show="!dbRevenueData || dbRevenueData.length === 0">
                    Sumber: Total Omzet KPI (Rp <span x-text="Number(dbKpi.revenueInRange || 0).toLocaleString('id-ID')"></span>)
                </span>
            </div>
        </div>
        <!-- Debug Info untuk Development -->
        <div class="text-xs text-gray-400 mt-1" x-show="dbKpi.debug">
            Debug: Sales: Rp<span x-text="Number(dbKpi.debug?.salesOmzet || 0).toLocaleString('id-ID')"></span> | 
            Client: Rp<span x-text="Number(dbKpi.debug?.clientOmzet || 0).toLocaleString('id-ID')"></span> | 
            Total: Rp<span x-text="Number(dbKpi.debug?.totalOmzet || 0).toLocaleString('id-ID')"></span>
        </div>
        <div class="relative h-64">
            <canvas id="salesChart" class="w-full h-full"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Pending Payments -->
        <div class="rounded-xl border p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="font-semibold">Pending Payments</div>
                <a href="{{ route('admin.sales') }}" class="text-xs text-[#28C328]">Lihat semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600">
                            <th class="p-2 text-left">ID</th>
                            <th class="p-2 text-left">Nama</th>
                            <th class="p-2 text-left">Status</th>
                            <th class="p-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="s in dbPendingPayments" :key="s.id">
                            <tr class="border-b">
                                <td class="p-2" x-text="s.id_pesanan"></td>
                                <td class="p-2" x-text="s.nama_pemesan"></td>
                                <td class="p-2">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold" :class="statusBadge(s.status)" x-text="s.status"></span>
                                </td>
                                <td class="p-2 text-right">Rp<span x-text="Number(s.total_harga || 0).toLocaleString('id-ID')"></span></td>
                            </tr>
                        </template>
                        <template x-if="dbPendingPayments.length === 0">
                            <tr><td colspan="4" class="p-3 text-center text-gray-400">Tidak ada transaksi pending</td></tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Low Stock -->
        <div class="rounded-xl border p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="font-semibold">Low Stock</div>
                <a href="{{ route('admin.dashboard') }}" class="text-xs text-[#28C328]">Kelola stok</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600">
                            <th class="p-2 text-left">Item</th>
                            <th class="p-2 text-left">SKU</th>
                            <th class="p-2 text-right">Tersedia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="i in dbLowStockItems" :key="i.id">
                            <tr class="border-b">
                                <td class="p-2" x-text="i.nama"></td>
                                <td class="p-2" x-text="i.sku"></td>
                                <td class="p-2 text-right" x-text="Number(i.tersedia).toLocaleString('id-ID')"></span></td>
                            </tr>
                        </template>
                        <template x-if="dbLowStockItems.length === 0">
                            <tr><td colspan="3" class="p-3 text-center text-gray-400">Semua stok aman</td></tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Aktivitas Terakhir -->
        <div class="rounded-xl border p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="font-semibold">Aktivitas Terakhir</div>
                <a href="{{ route('admin.sales') }}" class="text-xs text-[#28C328]">Lihat riwayat</a>
            </div>
            <div class="space-y-3 max-h-72 overflow-y-auto">
                <template x-for="a in dbRecentActivities" :key="a.id">
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full" :class="a.type==='sale' ? 'bg-[#28C328]' : (a.type==='stock' ? 'bg-blue-500' : 'bg-gray-400')"></span>
                            <span x-text="a.text"></span>
                        </div>
                        <span class="text-xs text-gray-500" x-text="a.time"></span>
                    </div>
                </template>
                <template x-if="dbRecentActivities.length === 0">
                    <div class="text-center text-gray-400 text-sm">Belum ada aktivitas</div>
                </template>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('admin.client') }}" class="rounded-xl border p-4 hover:shadow transition">
            <div class="font-semibold">Tambah/kelola Client</div>
            <div class="text-sm text-gray-500">Manajemen data pelanggan</div>
        </a>
        <a href="{{ route('admin.dashboard') }}" class="rounded-xl border p-4 hover:shadow transition">
            <div class="font-semibold">Tambah/kelola Stok</div>
            <div class="text-sm text-gray-500">Manajemen persediaan</div>
        </a>
        <a href="{{ route('admin.sales') }}" class="rounded-xl border p-4 hover:shadow transition">
            <div class="font-semibold">Buat Transaksi</div>
            <div class="text-sm text-gray-500">Catat penjualan baru</div>
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

<script>
function adminOverview() {
    return {
        // Backend data - HANYA ini yang digunakan
        dbPendingPayments: [],
        dbRecentActivities: [],
        dbKpi: {},
        dbLowStockItems: [],
        dbRevenueData: [],
        dbLowStockNotif: [],
        
        // Settings dari localStorage - HANYA untuk konfigurasi
        lowStockThreshold: Number(localStorage.getItem('gafi_overview_lowStockThreshold') || 10),
        clientLowStockThreshold: Number(localStorage.getItem('gafi_overview_clientLowStockThreshold') || 10),
        selectedPeriod: localStorage.getItem('gafi_overview_selectedPeriod') || 'month',
        targetRevenue: Number(localStorage.getItem('gafi_overview_targetRevenue') || 0),
        targetRevenueInput: (localStorage.getItem('gafi_overview_targetRevenue') || '0'),
        
        // Chart instance
        chart: null,
        chartInitialized: false,
        
        // Load data dari database
        loadOverviewData() {
            fetch(`/admin/overview-data?threshold=${this.lowStockThreshold}&clientThreshold=${this.clientLowStockThreshold}&period=${this.selectedPeriod}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                this.dbPendingPayments = data.pendingPayments || [];
                this.dbRecentActivities = data.recentActivities || [];
                this.dbKpi = data.kpi || {};
                this.dbLowStockItems = data.lowStockItems || [];
                this.dbRevenueData = data.revenueData || [];
                this.dbLowStockNotif = data.lowStockNotif || [];
                // Update chart setelah data tersedia, pastikan chart sudah siap
                if (this.chartInitialized && this.chart) {
                    this.updateChart();
                }
            })
            .catch(error => {
                this.dbRevenueData = [];
                this.dbKpi = {};
                if (this.chartInitialized && this.chart) {
                    this.updateChart();
                }
            });
        },
        
        onPeriodChange() {
            localStorage.setItem('gafi_overview_selectedPeriod', this.selectedPeriod);
            this.loadOverviewData(); // Reload data saat periode berubah
        },
        
        makeGradient(ctx) {
            const g = ctx.createLinearGradient(0, 0, 0, 260);
            g.addColorStop(0, 'rgba(40,195,40,0.25)');
            g.addColorStop(1, 'rgba(40,195,40,0.02)');
            return g;
        },
        
        sanitizeNumeric(text) {
            const cleaned = String(text || '').replace(/[^0-9]/g, '');
            return cleaned ? Number(cleaned) : 0;
        },
        
        onTargetInput(e) {
            const raw = e?.target?.value ?? this.targetRevenueInput;
            const num = this.sanitizeNumeric(raw);
            this.targetRevenue = num;
            localStorage.setItem('gafi_overview_targetRevenue', num);
            this.targetRevenueInput = num.toLocaleString('id-ID');
            if (this.chartInitialized && this.chart) {
                this.updateChart();
            }
        },
        
        onThresholdInput(e) {
            const num = Number(e?.target?.value ?? this.lowStockThreshold) || 0;
            this.lowStockThreshold = num;
            localStorage.setItem('gafi_overview_lowStockThreshold', num);
            this.loadOverviewData(); // Reload data saat threshold berubah
        },
        
        onClientThresholdInput(e) {
            const num = Number(e?.target?.value ?? this.clientLowStockThreshold) || 0;
            this.clientLowStockThreshold = num;
            localStorage.setItem('gafi_overview_clientLowStockThreshold', num);
            this.loadOverviewData(); // Reload data saat threshold berubah
        },
        
        startAutoRefresh() {
            // Auto refresh setiap 30 detik
            setInterval(() => {
                this.loadOverviewData();
            }, 30000);
        },
        
        initChart() {
            const canvas = document.getElementById('salesChart');
            const ctx = canvas?.getContext('2d');
            if (!ctx || !window.Chart) {
                return;
            }
            const gradient = this.makeGradient(ctx);
            this.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Omzet',
                        data: [],
                        borderColor: 'rgb(40,195,40)',
                        backgroundColor: gradient,
                        borderWidth: 2,
                        tension: 0.35,
                        fill: true,
                        pointRadius: 2.5,
                        pointHoverRadius: 4,
                        pointBackgroundColor: 'rgb(40,195,40)'
                    }, {
                        label: 'Target',
                        data: [],
                        borderColor: 'rgba(239,68,68,0.7)',
                        borderDash: [6,6],
                        pointRadius: 0,
                        fill: false,
                        tension: 0
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: {
                        legend: { display: true },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => ctx.dataset.label + ': Rp ' + Number(ctx.parsed.y).toLocaleString('id-ID')
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: '#6b7280', font: { size: 11 } }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#eef2f7' },
                            ticks: {
                                color: '#6b7280',
                                callback: (v) => 'Rp ' + Number(v).toLocaleString('id-ID'),
                                font: { size: 11 }
                            }
                        }
                    }
                }
            });
            this.chartInitialized = true;
            // Setelah chart siap, update chart jika sudah ada data
            if (this.dbRevenueData && this.dbRevenueData.length > 0) {
                this.updateChart();
            }
        },
        
        // Method sederhana untuk update chart
        updateChart() {
            if (!this.chartInitialized || !this.chart) {
                return;
            }
            try {
                // Ambil data dari backend
                const revenueData = this.dbRevenueData || [];
                if (revenueData.length > 0) {
                    // Extract labels dan values
                    const labels = revenueData.map(item => item.label || '');
                    const values = revenueData.map(item => Number(item.value) || 0);
                    
                    // Update chart data
                    this.chart.data.labels = labels;
                    this.chart.data.datasets[0].data = values;
                    
                    // Update target line
                    const targetVal = Number(this.targetRevenue) || 0;
                    this.chart.data.datasets[1].data = labels.map(() => targetVal);
                    
                    // Update chart
                    this.chart.update('none');
                } else {
                    // Show empty chart
                    this.chart.data.labels = [];
                    this.chart.data.datasets[0].data = [];
                    this.chart.data.datasets[1].data = [];
                    this.chart.update('none');
                }
            } catch (error) {
                // Optional: handle error silently
            }
        },
        
        statusBadge(st) {
            if (st === 'Selesai' || st === 'Dalam Proses-Sudah Dibayar') return 'bg-green-100 text-green-700';
            if (st === 'Dalam Proses-Belum Dibayar' || st === 'Belum approval') return 'bg-orange-100 text-orange-700';
            if (st === 'Dibatalkan') return 'bg-red-100 text-red-700';
            return 'bg-gray-100 text-gray-600';
        }
    }
}
</script>

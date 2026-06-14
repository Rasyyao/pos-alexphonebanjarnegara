@extends('layouts.app')
@section('title', 'Laporan Stok')

@section('content')
    <div class="space-y-6">

        {{-- Title and Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold" style="color:var(--ink)">Laporan Stok</h2>
                <p class="text-xs mt-0.5" style="color:var(--ink-mute)">Informasi inventaris unit HP, aksesoris ready, dan
                    nilai aset stok barang</p>
            </div>

            {{-- Export Buttons --}}
            @if (auth()->user()->role->value === 'superadmin')
                <div class="flex items-center gap-2">
                    {{-- <a href="{{ route('reports.stock.opname') }}"
               target="_blank"
               class="text-xs h-9 px-4 font-semibold rounded-lg transition-all flex items-center gap-1.5 border shadow-sm"
               style="background:#FDF4FF;color:#7E22CE;border-color:#E9D5FF"
               onmouseenter="this.style.background='#F3E8FF'" onmouseleave="this.style.background='#FDF4FF'">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                Cetak Stock Opname
            </a> --}}
                    <a href="{{ route('reports.pdf', 'stock') }}" target="_blank"
                        class="text-xs h-9 px-4 font-semibold rounded-lg transition-all flex items-center gap-1.5 border shadow-sm"
                        style="background:#EFF6FF;color:#1D4ED8;border-color:#BFDBFE"
                        onmouseenter="this.style.background='#DBEAFE'" onmouseleave="this.style.background='#EFF6FF'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        Export Stock Opname PDF
                    </a>
                    <a href="{{ route('reports.export', 'stock') }}"
                        class="text-xs h-9 px-4 font-semibold rounded-lg transition-all flex items-center gap-1.5 border shadow-sm"
                        style="background:#F0FDF4;color:var(--success);border-color:#BBF7D0"
                        onmouseenter="this.style.background='#DCFCE7'" onmouseleave="this.style.background='#F0FDF4'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export Stock Opname Excel
                    </a>
                </div>
            @endif
        </div>

        {{-- Stock Metrics --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl border p-5 shadow-sm" style="border-color:var(--line)">
                <div class="text-[10px] font-bold uppercase tracking-widest font-mono mb-1.5" style="color:var(--ink-mute)">
                    Total Unit HP</div>
                <div class="text-2xl font-bold font-mono tabular-nums" style="color:var(--ink)">{{ $units->total() }} unit
                </div>
                <p class="text-[11px] mt-1" style="color:var(--ink-mute)">Unit handphone dalam stok saat ini</p>
            </div>

            <div class="bg-white rounded-xl border p-5 shadow-sm" style="border-color:var(--line)">
                <div class="text-[10px] font-bold uppercase tracking-widest font-mono mb-1.5" style="color:var(--ink-mute)">
                    Nilai Aset Stok (HP)</div>
                <div class="text-2xl font-bold font-mono tabular-nums" style="color:var(--accent)">Rp
                    {{ number_format($assetValue, 0, ',', '.') }}</div>
                <p class="text-[11px] mt-1" style="color:var(--ink-mute)">Berdasarkan harga modal beli ready unit</p>
            </div>

            <div class="bg-white rounded-xl border p-5 shadow-sm" style="border-color:var(--line)">
                <div class="text-[10px] font-bold uppercase tracking-widest font-mono mb-1.5" style="color:var(--ink-mute)">
                    Total Stok Aksesoris</div>
                <div class="text-2xl font-bold font-mono tabular-nums" style="color:var(--ink)">{{ $totalStockQty }} pcs
                </div>
                <p class="text-[11px] mt-1" style="color:var(--ink-mute)">Keseluruhan unit aksesoris terdaftar</p>
            </div>
        </div>

        {{-- Statistics & Analytics Dashboard --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Doughnut Chart Panel --}}
            <div class="lg:col-span-5 bg-white rounded-xl border p-5 shadow-sm flex flex-col justify-between"
                style="border-color:var(--line)">
                <div>
                    <div class="flex items-center justify-between border-b pb-3 mb-4" style="border-color:var(--line)">
                        <h3 class="text-sm font-semibold" style="color:var(--ink)">Analisis Proporsi Stok</h3>
                        <div class="flex items-center gap-1 p-0.5 bg-gray-100 rounded-lg text-[10px] font-medium">
                            <button type="button" onclick="switchChartTab('brand')" id="tab-brand"
                                class="px-2.5 py-1 rounded bg-white text-blue-600 font-semibold shadow-sm transition-all">Brand</button>
                            <button type="button" onclick="switchChartTab('type')" id="tab-type"
                                class="px-2.5 py-1 rounded text-gray-500 hover:text-gray-900 transition-all">Kondisi</button>
                            <button type="button" onclick="switchChartTab('status')" id="tab-status"
                                class="px-2.5 py-1 rounded text-gray-500 hover:text-gray-900 transition-all">Status</button>
                        </div>
                    </div>

                    {{-- Chart Wrapper --}}
                    <div class="relative flex items-center justify-center my-6" style="height: 220px;">
                        <canvas id="stockDoughnutChart"></canvas>
                        <div class="absolute flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-3xl font-bold font-mono tracking-tight" id="chart-center-qty"
                                style="color:var(--ink)">0</span>
                            <span class="text-[9px] uppercase tracking-wider font-semibold font-mono"
                                style="color:var(--ink-mute)">Unit Total</span>
                        </div>
                    </div>
                </div>

                <div id="chart-legend-container"
                    class="grid grid-cols-2 gap-x-4 gap-y-2 text-[10px] font-mono border-t pt-4"
                    style="border-color:var(--line)">
                    {{-- Legend items injected via JS --}}
                </div>
            </div>

            {{-- Dynamic Expanded Breakdown Panel --}}
            <div class="lg:col-span-7 bg-white rounded-xl border shadow-sm flex flex-col justify-between overflow-hidden"
                style="border-color:var(--line)">
                <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color:var(--line)">
                    <div>
                        <h3 class="text-sm font-semibold" id="breakdown-title" style="color:var(--ink)">Rincian Distribusi
                            Brand</h3>
                        <p class="text-[11px] mt-0.5" style="color:var(--ink-mute)">Pangsa stok dan persentase kepemilikan
                            barang ready</p>
                    </div>
                    <span
                        class="text-[10px] font-mono px-2 py-0.5 bg-blue-50 text-blue-700 rounded-full font-bold uppercase">Aset
                        Real-Time</span>
                </div>

                <div class="flex-1 overflow-y-auto" style="max-height: 260px;" id="breakdown-list-container">
                    {{-- Breakdown list table injected via JS --}}
                </div>

                <div class="px-5 py-3.5 bg-gray-50 border-t flex justify-between items-center text-[11px]"
                    style="border-color:var(--line)">
                    <span class="font-medium" style="color:var(--ink-mute)">Total Terhitung:</span>
                    <span class="font-bold font-mono text-sm" id="breakdown-total" style="color:var(--ink)">0 unit</span>
                </div>
            </div>
        </div>

        {{-- HP Stock Table --}}
        <div class="bg-white rounded-xl border overflow-hidden shadow-sm" style="border-color:var(--line)">
            <div class="px-5 py-4 border-b flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                style="border-color:var(--line)">
                <div>
                    <h3 class="text-sm font-semibold" style="color:var(--ink)">Daftar Unit HP</h3>
                    <p class="text-[11px] mt-0.5" style="color:var(--ink-mute)">Detail inventaris dan spesifikasi unit</p>
                </div>

                {{-- Status Filter Buttons inside view for quick filtering --}}
                <div class="flex items-center gap-1.5 p-1 bg-[#F3F4F6] rounded-lg text-xs" style="width:fit-content">
                    <a href="?status="
                        class="px-2.5 py-1 rounded-md transition-colors {{ !request('status') ? 'bg-white font-semibold text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">Semua</a>
                    <a href="?status=ready"
                        class="px-2.5 py-1 rounded-md transition-colors {{ request('status') === 'ready' ? 'bg-white font-semibold text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">Ready</a>
                    <a href="?status=returned"
                        class="px-2.5 py-1 rounded-md transition-colors {{ request('status') === 'returned' ? 'bg-white font-semibold text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">Retur</a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr style="background:var(--bg-soft); border-bottom:1px solid var(--line)">
                            <th class="text-left px-5 py-3 font-bold uppercase tracking-wider font-mono"
                                style="color:var(--ink-mute)">Unit / Model</th>
                            <th class="text-left px-5 py-3 font-bold uppercase tracking-wider font-mono"
                                style="color:var(--ink-mute)">Detail Spek</th>
                            <th class="text-left px-5 py-3 font-bold uppercase tracking-wider font-mono"
                                style="color:var(--ink-mute)">IMEI / SN</th>
                            <th class="text-right px-5 py-3 font-bold uppercase tracking-wider font-mono"
                                style="color:var(--ink-mute)">Harga Beli (Modal)</th>
                            <th class="text-center px-5 py-3 font-bold uppercase tracking-wider font-mono"
                                style="color:var(--ink-mute)">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($units as $unit)
                            <tr style="border-bottom:1px solid var(--line)">
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-sm" style="color:var(--ink)">
                                        {{ $unit->model->brand->name ?? '—' }} {{ $unit->model->name ?? '—' }}</div>
                                    <div class="flex items-center gap-2 mt-1">
                                        {{-- Type badge --}}
                                        @if ($unit->unit_type->value === 'baru')
                                            <span
                                                class="px-1.5 py-0.5 rounded text-[9px] font-bold font-mono bg-blue-50 text-blue-700 uppercase">Baru</span>
                                        @else
                                            <span
                                                class="px-1.5 py-0.5 rounded text-[9px] font-bold font-mono bg-amber-50 text-amber-700 uppercase">Second</span>
                                        @endif

                                        {{-- Grade badge --}}
                                        <span
                                            class="px-1.5 py-0.5 rounded text-[9px] font-bold font-mono bg-purple-50 text-purple-700 uppercase">Grade
                                            {{ $unit->grade ?: ($unit->unit_type->value === 'baru' ? 'A' : 'Second') }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 font-mono" style="color:var(--ink-soft)">
                                    <div>{{ $unit->ram }} / {{ $unit->rom }}</div>
                                    <div class="text-[10px] mt-0.5" style="color:var(--ink-mute)">{{ $unit->color }}
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 font-mono text-[10px]" style="color:var(--ink-soft)">
                                    <div>IMEI: {{ $unit->imei ?: '—' }}</div>
                                    <div class="mt-0.5" style="color:var(--ink-mute)">SN:
                                        {{ $unit->serial_number ?: '—' }}</div>
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono font-bold tabular-nums"
                                    style="color:var(--ink)">
                                    Rp {{ number_format($unit->purchase_price, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    @if ($unit->status->value === 'ready')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                                            style="background:#F0FDF4;color:var(--success)">Ready</span>
                                    @elseif($unit->status->value === 'sold')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                                            style="background:var(--bg-soft);color:var(--ink-mute)">Terjual</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                                            style="background:#FFF5F5;color:var(--warn)">Retur</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center text-sm" style="color:var(--ink-mute)">
                                    Tidak ada unit yang terdaftar dengan filter ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($units->total() > 0)
                <div class="px-5 py-3 flex items-center justify-between"
                    style="border-top:1px solid var(--line);background:var(--bg-soft)">
                    <span class="text-xs font-mono" style="color:var(--ink-mute)">{{ $units->total() }} unit</span>
                    {{ $units->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

        {{-- Accessories Table --}}
        <div class="bg-white rounded-xl border overflow-hidden shadow-sm" style="border-color:var(--line)">
            <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color:var(--line)">
                <div>
                    <h3 class="text-sm font-semibold" style="color:var(--ink)">Daftar Stok Aksesoris</h3>
                    <p class="text-[11px] mt-0.5" style="color:var(--ink-mute)">Inventaris aksesoris toko dengan profit
                        margin</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr style="background:var(--bg-soft); border-bottom:1px solid var(--line)">
                            <th class="text-left px-5 py-3 font-bold uppercase tracking-wider font-mono"
                                style="color:var(--ink-mute)">Aksesoris / Kategori</th>
                            <th class="text-center px-5 py-3 font-bold uppercase tracking-wider font-mono"
                                style="color:var(--ink-mute)">Jumlah Stok</th>
                            <th class="text-right px-5 py-3 font-bold uppercase tracking-wider font-mono"
                                style="color:var(--ink-mute)">Harga Beli (Modal)</th>
                            <th class="text-right px-5 py-3 font-bold uppercase tracking-wider font-mono"
                                style="color:var(--ink-mute)">Harga Jual</th>
                            <th class="text-right px-5 py-3 font-bold uppercase tracking-wider font-mono"
                                style="color:var(--ink-mute)">Margin Profit</th>
                            <th class="text-center px-5 py-3 font-bold uppercase tracking-wider font-mono"
                                style="color:var(--ink-mute)">Status Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accessories as $accessory)
                            @php $margin = $accessory->selling_price - $accessory->purchase_price; @endphp
                            <tr style="border-bottom:1px solid var(--line)">
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-sm" style="color:var(--ink)">{{ $accessory->name }}</div>
                                    <div class="text-[10px] font-mono mt-0.5" style="color:var(--ink-mute)">
                                        {{ $accessory->category ?: 'Tanpa Kategori' }}</div>
                                </td>
                                <td class="px-5 py-3.5 text-center font-mono font-bold tabular-nums"
                                    style="color:var(--ink)">{{ $accessory->stock_qty }} pcs</td>
                                <td class="px-5 py-3.5 text-right font-mono tabular-nums" style="color:var(--ink-soft)">Rp
                                    {{ number_format($accessory->purchase_price, 0, ',', '.') }}</td>
                                <td class="px-5 py-3.5 text-right font-mono font-semibold tabular-nums"
                                    style="color:var(--ink)">Rp
                                    {{ number_format($accessory->selling_price, 0, ',', '.') }}</td>
                                <td class="px-5 py-3.5 text-right font-mono font-bold tabular-nums"
                                    style="color:var(--success)">Rp {{ number_format($margin, 0, ',', '.') }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    @if ($accessory->stock_qty <= 5)
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-red-50 text-red-600">Stok
                                            Menipis</span>
                                    @else
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-green-50 text-green-600">Aman</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm" style="color:var(--ink-mute)">
                                    Belum ada data aksesoris ready</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($accessories->total() > 0)
                <div class="px-5 py-3 flex items-center justify-between"
                    style="border-top:1px solid var(--line);background:var(--bg-soft)">
                    <span class="text-xs font-mono" style="color:var(--ink-mute)">{{ $accessories->total() }}
                        aksesoris</span>
                    {{ $accessories->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

    </div>

    {{-- Chart.js and Custom Script for Advanced Analytics --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentChart = null;
            let activeTab = 'brand';

            const brandData = {
                labels: {!! json_encode(array_column($brandDist, 'brand_name')) !!},
                values: {!! json_encode(array_column($brandDist, 'count')) !!},
                colors: ['#3B82F6', '#10B981', '#6366F1', '#8B5CF6', '#F59E0B', '#EC4899', '#EF4444', '#14B8A6',
                    '#22C55E', '#06B6D4'
                ],
                labelName: 'Brand HP'
            };

            const typeData = {
                labels: {!! json_encode(array_map(fn($t) => $t === 'baru' ? 'Baru' : 'Second', array_column($typeDist, 'unit_type'))) !!},
                values: {!! json_encode(array_column($typeDist, 'count')) !!},
                colors: ['#3B82F6', '#F59E0B'],
                labelName: 'Kondisi HP'
            };

            const statusData = {
                labels: {!! json_encode(
                    array_map(
                        fn($s) => $s === 'ready' ? 'Ready' : 'Retur',
                        array_column($statusDist, 'status'),
                    ),
                ) !!},
                values: {!! json_encode(array_column($statusDist, 'count')) !!},
                colors: ['#10B981', '#EF4444'],
                labelName: 'Status Unit'
            };

            function renderChart(type) {
                let activeData = brandData;
                if (type === 'type') activeData = typeData;
                if (type === 'status') activeData = statusData;

                const totalQty = activeData.values.reduce((a, b) => a + b, 0);
                document.getElementById('chart-center-qty').innerText = totalQty;

                // Render Chart
                const canvasEl = document.getElementById('stockDoughnutChart');
                if (!canvasEl) return;
                const ctx = canvasEl.getContext('2d');

                if (currentChart) {
                    currentChart.destroy();
                }

                currentChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: activeData.labels,
                        datasets: [{
                            data: activeData.values,
                            backgroundColor: activeData.colors,
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#1F2937',
                                padding: 10,
                                bodyFont: {
                                    size: 11,
                                    family: 'monospace'
                                },
                                titleFont: {
                                    size: 12,
                                    weight: 'bold'
                                },
                                callbacks: {
                                    label: function(context) {
                                        const val = context.raw;
                                        const pct = totalQty > 0 ? ((val / totalQty) * 100).toFixed(1) :
                                            0;
                                        return ` ${val} unit (${pct}%)`;
                                    }
                                }
                            }
                        },
                        cutout: '72%'
                    }
                });

                // Render custom premium HTML Legend
                const legendContainer = document.getElementById('chart-legend-container');
                if (legendContainer) {
                    legendContainer.innerHTML = '';
                    activeData.labels.forEach((label, idx) => {
                        const val = activeData.values[idx];
                        const pct = totalQty > 0 ? ((val / totalQty) * 100).toFixed(1) : 0;
                        const color = activeData.colors[idx % activeData.colors.length];

                        const legendItem = document.createElement('div');
                        legendItem.className =
                            'flex items-center gap-1.5 hover:translate-x-1 transition-all duration-150 cursor-pointer';
                        legendItem.innerHTML = `
                        <span class="w-2 h-2 rounded-full shrink-0" style="background:${color}"></span>
                        <span class="truncate font-semibold text-gray-700" title="${label}">${label}</span>
                        <span class="ml-auto font-mono font-bold text-gray-900">${val} <span class="text-[9px] font-normal text-gray-500">(${pct}%)</span></span>
                    `;
                        legendContainer.appendChild(legendItem);
                    });
                }

                // Render Breakdown List
                const breakdownTitle = document.getElementById('breakdown-title');
                const breakdownListContainer = document.getElementById('breakdown-list-container');
                const breakdownTotal = document.getElementById('breakdown-total');

                if (breakdownTotal) breakdownTotal.innerText = `${totalQty} unit`;

                if (breakdownTitle) {
                    if (type === 'brand') {
                        breakdownTitle.innerText = 'Rincian Distribusi Brand HP';
                    } else if (type === 'type') {
                        breakdownTitle.innerText = 'Rincian Distribusi Kondisi HP';
                    } else {
                        breakdownTitle.innerText = 'Rincian Distribusi Status HP';
                    }
                }

                if (breakdownListContainer) {
                    let tableHtml = `
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="bg-gray-50 border-b font-mono font-bold uppercase tracking-wider text-gray-500 text-left" style="border-color:var(--line)">
                                <th class="px-5 py-2.5">Kategori / Parameter</th>
                                <th class="px-5 py-2.5 text-center">Jumlah unit</th>
                                <th class="px-5 py-2.5 text-right">Rasio Stok</th>
                                <th class="px-5 py-2.5 text-right">Visualisasi Rasio</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="border-color:var(--line)">
                `;

                    if (activeData.labels.length === 0) {
                        tableHtml += `
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center text-gray-400">Tidak ada data terhitung</td>
                        </tr>
                    `;
                    } else {
                        activeData.labels.forEach((label, idx) => {
                            const val = activeData.values[idx];
                            const pct = totalQty > 0 ? ((val / totalQty) * 100).toFixed(1) : 0;
                            const color = activeData.colors[idx % activeData.colors.length];

                            tableHtml += `
                            <tr class="hover:bg-gray-50 transition-colors" style="border-color:var(--line)">
                                <td class="px-5 py-3 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full shrink-0" style="background:${color}"></span>
                                    <span class="font-bold text-gray-800">${label}</span>
                                </td>
                                <td class="px-5 py-3 text-center font-mono font-semibold text-gray-900">${val} unit</td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-blue-600">${pct}%</td>
                                <td class="px-5 py-3 text-right">
                                    <div class="w-24 bg-gray-100 h-2 rounded-full overflow-hidden ml-auto">
                                        <div class="h-full rounded-full transition-all duration-500" style="width:${pct}%; background:${color}"></div>
                                    </div>
                                </td>
                            </tr>
                        `;
                        });
                    }

                    tableHtml += `</tbody></table>`;
                    breakdownListContainer.innerHTML = tableHtml;
                }
            }

            window.switchChartTab = function(type) {
                ['brand', 'type', 'status'].forEach(t => {
                    const btn = document.getElementById(`tab-${t}`);
                    if (btn) {
                        if (t === type) {
                            btn.className =
                                "px-2.5 py-1 rounded bg-white text-blue-600 font-semibold shadow-sm transition-all";
                        } else {
                            btn.className =
                                "px-2.5 py-1 rounded text-gray-500 hover:text-gray-900 transition-all";
                        }
                    }
                });
                activeTab = type;
                renderChart(type);
            };

            // Initial render
            renderChart('brand');
        });
    </script>
@endsection

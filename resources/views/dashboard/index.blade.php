@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@php
    $brandData = [];
    foreach ($brandDist as $b) {
        $brandData[] = [
            'label' => $b['brand_name'] ?? 'Lainnya',
            'count' => (int) $b['count']
        ];
    }

    $typeData = [];
    $typeTranslations = ['baru' => 'Baru', 'second' => 'Second'];
    foreach ($typeDist as $t) {
        $typeData[] = [
            'label' => $typeTranslations[$t['unit_type']] ?? ucfirst($t['unit_type']),
            'count' => (int) $t['count']
        ];
    }

    $statusData = [];
    $statusTranslations = ['ready' => 'Tersedia', 'sold' => 'Terjual', 'pending' => 'Verifikasi'];
    foreach ($statusDist as $s) {
        $statusData[] = [
            'label' => $statusTranslations[$s['status']] ?? ucfirst($s['status']),
            'count' => (int) $s['count']
        ];
    }
@endphp
<div class="space-y-6">

    {{-- ── Greeting ── --}}
    <div>
        <h2 class="text-xl font-bold" style="color:var(--ink)">Halo, {{ auth()->user()->name }}!</h2>
        <p class="text-xs mt-0.5" style="color:var(--ink-mute)">Berikut adalah ringkasan performa toko, transaksi, dan stok barang hari ini.</p>
    </div>

    {{-- ── KPI Row ── --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- Stok HP --}}
        <div class="bg-white rounded-xl border p-5 card-lift" style="border-color:var(--line)">
            <div class="flex items-start justify-between mb-3">
                <div class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Stok HP</div>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:rgba(37,99,235,.1)">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="color:var(--accent)">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-semibold leading-none mb-1 font-mono tabular-nums" style="color:var(--ink)">{{ $stockCounts['total'] }}</div>
            <div class="text-xs flex gap-2" style="color:var(--ink-mute)">
                <span class="inline-flex items-center gap-1 font-medium" style="color:var(--accent)">
                    <span class="w-1.5 h-1.5 rounded-full bg-current inline-block"></span>{{ $stockCounts['baru'] }} Baru
                </span>
                <span class="inline-flex items-center gap-1 font-medium" style="color:#B45309">
                    <span class="w-1.5 h-1.5 rounded-full bg-current inline-block"></span>{{ $stockCounts['second'] }} Second
                </span>
            </div>
        </div>

        {{-- Total Omset --}}
        <div class="bg-white rounded-xl border p-5 card-lift" style="border-color:var(--line)">
            <div class="flex items-start justify-between mb-3">
                <div class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Total Omset</div>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:rgba(37,99,235,.1)">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="color:var(--accent)"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                </div>
            </div>
            <div class="text-2xl font-semibold leading-none mb-1 font-mono tabular-nums" style="color:var(--ink)">
                {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}
            </div>
            <div class="text-xs" style="color:var(--ink-mute)">Semua transaksi approved</div>
        </div>

        {{-- Total Laba --}}
        <div class="bg-white rounded-xl border p-5 card-lift" style="border-color:var(--line)">
            <div class="flex items-start justify-between mb-3">
                <div class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Total Laba</div>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:rgba(16,128,107,.1)">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="color:var(--success)"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                </div>
            </div>
            <div class="text-2xl font-semibold leading-none mb-1 font-mono tabular-nums" style="color:var(--success)">
                {{ number_format($totalProfit ?? 0, 0, ',', '.') }}
            </div>
            <div class="text-xs" style="color:var(--ink-mute)">Laba bersih terkumpul</div>
        </div>

        {{-- Total Aksesoris --}}
        <div class="bg-white rounded-xl border p-5 card-lift" style="border-color:var(--line)">
            <div class="flex items-start justify-between mb-3">
                <div class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Total Aksesoris</div>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:rgba(122,138,168,.12)">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="color:var(--ink-soft)"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                </div>
            </div>
            <div class="text-2xl font-semibold leading-none mb-1 font-mono tabular-nums" style="color:var(--ink)">
                {{ number_format($totalAccessories ?? 0, 0, ',', '.') }}
            </div>
            <div class="text-xs" style="color:var(--ink-mute)">Stok aksesoris aktif</div>
        </div>

    </div>

    {{-- ── Ringkasan Finansial Toko ── --}}
    <div>
        <!-- <div class="mb-3">
            <div class="text-sm font-semibold" style="color:var(--ink)">Ringkasan Finansial Toko</div>
            <div class="text-xs mt-0.5" style="color:var(--ink-mute)">Pantauan akumulasi omset dan laba bersih berkala real-time.</div>
        </div> -->
        <!-- <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- Hari Ini --}}
            <div class="bg-white rounded-xl border p-5 card-lift relative overflow-hidden" style="border-color:var(--line)">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold uppercase tracking-widest font-mono" style="color:var(--accent)">
                        Hari Ini
                    </span>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(37,99,235,.08)">
                        <svg class="w-4 h-4" fill="none" viewBox1="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="color:var(--accent)"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                </div>
                <div class="space-y-3">
                    <div>
                        <div class="text-[10px] font-medium uppercase tracking-widest font-mono mb-0.5" style="color:var(--ink-mute)">Total Omset</div>
                        <div class="text-2xl font-bold font-mono tabular-nums leading-none" style="color:var(--ink)">Rp {{ number_format($todayStats['revenue'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] font-medium uppercase tracking-widest font-mono mb-0.5" style="color:var(--success)">Laba Bersih</div>
                        <div class="text-lg font-bold font-mono tabular-nums leading-none" style="color:var(--success)">+ Rp {{ number_format($todayStats['profit'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t flex items-center gap-1 text-[11px] font-mono" style="border-color:var(--line);color:var(--ink-mute)">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    {{ $todayStats['count'] ?? 0 }} transaksi hari ini
                </div>
            </div>

            {{-- Minggu Ini --}}
            <div class="bg-white rounded-xl border p-5 card-lift relative overflow-hidden" style="border-color:var(--line)">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold uppercase tracking-widest font-mono" style="color:var(--accent)">
                        Minggu Ini
                    </span>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(37,99,235,.08)">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="color:var(--accent)"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                </div>
                <div class="space-y-3">
                    <div>
                        <div class="text-[10px] font-medium uppercase tracking-widest font-mono mb-0.5" style="color:var(--ink-mute)">Total Omset</div>
                        <div class="text-2xl font-bold font-mono tabular-nums leading-none" style="color:var(--ink)">Rp {{ number_format($weekStats['revenue'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] font-medium uppercase tracking-widest font-mono mb-0.5" style="color:var(--success)">Laba Bersih</div>
                        <div class="text-lg font-bold font-mono tabular-nums leading-none" style="color:var(--success)">+ Rp {{ number_format($weekStats['profit'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t flex items-center gap-1 text-[11px] font-mono" style="border-color:var(--line);color:var(--ink-mute)">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    {{ $weekStats['count'] ?? 0 }} transaksi minggu ini
                </div>
            </div>

            {{-- Bulan Ini --}}
            <div class="bg-white rounded-xl border p-5 card-lift relative overflow-hidden" style="border-color:var(--line)">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold uppercase tracking-widest font-mono" style="color:var(--accent)">
                        Bulan Ini
                    </span>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(37,99,235,.08)">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="color:var(--accent)"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                </div>
                <div class="space-y-3">
                    <div>
                        <div class="text-[10px] font-medium uppercase tracking-widest font-mono mb-0.5" style="color:var(--ink-mute)">Total Omset</div>
                        <div class="text-2xl font-bold font-mono tabular-nums leading-none" style="color:var(--ink)">Rp {{ number_format($monthStats['revenue'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] font-medium uppercase tracking-widest font-mono mb-0.5" style="color:var(--success)">Laba Bersih</div>
                        <div class="text-lg font-bold font-mono tabular-nums leading-none" style="color:var(--success)">+ Rp {{ number_format($monthStats['profit'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t flex items-center gap-1 text-[11px] font-mono" style="border-color:var(--line);color:var(--ink-mute)">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    {{ $monthStats['count'] ?? 0 }} transaksi bulan ini
                </div>
            </div>

        </div> -->
    </div>

    {{-- ── Chart + Analisis Proporsi Stok (3:2 Grid Ratio) ── --}}
    <div class="grid lg:grid-cols-5 gap-5">

        {{-- Tren Pendapatan dual-line chart (3/5 columns) --}}
        <div class="lg:col-span-3 bg-white rounded-xl border p-5 flex flex-col" style="border-color:var(--line)">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <div class="text-sm font-semibold" style="color:var(--ink)">Tren Pendapatan (7 Hari Terakhir)</div>
                    <div class="text-xs font-mono mt-0.5" style="color:var(--ink-mute)">
                        Visualisasi naik-turun omset kotor berbanding profit bersih toko.
                    </div>
                </div>
                <div class="flex items-center gap-4 text-[11px] font-mono flex-shrink-0">
                    <span class="flex items-center gap-1.5" style="color:var(--accent)">
                        <span class="w-6 h-0.5 inline-block rounded" style="background:var(--accent)"></span>Omset Kotor
                    </span>
                    <span class="flex items-center gap-1.5" style="color:var(--success)">
                        <span class="w-6 h-0.5 inline-block rounded" style="background:var(--success)"></span>Laba Bersih
                    </span>
                    <a href="{{ route('reports.finance') }}" class="text-xs font-medium hover:underline ml-2" style="color:var(--accent)">Lihat laporan →</a>
                </div>
            </div>

            @php
                $days = collect(range(6,0))->map(fn($i) => now()->subDays($i)->toDateString());
                $chartLabels = [];
                $chartRevenue = [];
                $chartProfit  = [];
                foreach($days as $day) {
                    $row = $weeklyRevenue->firstWhere('date', $day);
                    $chartLabels[]  = \Carbon\Carbon::parse($day)->isoFormat('ddd, D/M');
                    $chartRevenue[] = $row?->total  ?? 0;
                    $chartProfit[]  = $row?->profit ?? 0;
                }
            @endphp
            <div class="relative w-full flex-1" style="min-height:280px">
                <canvas id="trendChart"></canvas>
            </div>
            <script>
            document.addEventListener("DOMContentLoaded", function() {
                const ctx = document.getElementById('trendChart').getContext('2d');

                const gradBlue = ctx.createLinearGradient(0, 0, 0, 280);
                gradBlue.addColorStop(0, 'rgba(37,99,235,0.15)');
                gradBlue.addColorStop(1, 'rgba(37,99,235,0.00)');

                const gradGreen = ctx.createLinearGradient(0, 0, 0, 280);
                gradGreen.addColorStop(0, 'rgba(16,128,107,0.12)');
                gradGreen.addColorStop(1, 'rgba(16,128,107,0.00)');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($chartLabels) !!},
                        datasets: [
                            {
                                label: 'Omset Kotor',
                                data: {!! json_encode($chartRevenue) !!},
                                borderColor: '#2563EB',
                                borderWidth: 2,
                                backgroundColor: gradBlue,
                                fill: true,
                                tension: 0.35,
                                pointBackgroundColor: '#2563EB',
                                pointBorderColor: '#FFFFFF',
                                pointBorderWidth: 1.5,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            },
                            {
                                label: 'Laba Bersih',
                                data: {!! json_encode($chartProfit) !!},
                                borderColor: '#10806B',
                                borderWidth: 2,
                                backgroundColor: gradGreen,
                                fill: true,
                                tension: 0.35,
                                pointBackgroundColor: '#10806B',
                                pointBorderColor: '#FFFFFF',
                                pointBorderWidth: 1.5,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0A2540',
                                titleColor: '#FFFFFF',
                                bodyColor: 'rgba(255,255,255,0.75)',
                                padding: 12,
                                cornerRadius: 10,
                                displayColors: true,
                                callbacks: {
                                    label: function(ctx) {
                                        return ' ' + ctx.dataset.label + ': Rp ' + Number(ctx.raw).toLocaleString('id-ID');
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: '#7A8AA8', font: { family: '"Satoshi", sans-serif', size: 11 } }
                            },
                            y: {
                                grid: { color: '#E4E9F2', drawTicks: false },
                                ticks: {
                                    color: '#7A8AA8',
                                    font: { family: '"Satoshi", sans-serif', size: 11 },
                                    callback: function(v) {
                                        if (v >= 1000000) return 'Rp ' + (v / 1000000).toFixed(1) + 'jt';
                                        if (v >= 1000)    return 'Rp ' + (v / 1000) + 'rb';
                                        return 'Rp ' + v;
                                    }
                                }
                            }
                        }
                    }
                });
            });
            </script>

            {{-- Chart summary footer --}}
            <div class="grid grid-cols-3 border-t mt-4 pt-4 divide-x" style="border-color:var(--line)">
                <div class="pr-4">
                    <span class="text-[10px] font-medium uppercase tracking-widest font-mono block mb-1" style="color:var(--ink-mute)">Total Omzet</span>
                    <span class="text-sm font-bold font-mono" style="color:var(--ink)">
                        Rp {{ number_format($weeklyRevenue->sum('total'), 0, ',', '.') }}
                    </span>
                </div>
                <div class="px-4">
                    <span class="text-[10px] font-medium uppercase tracking-widest font-mono block mb-1" style="color:var(--success)">Total Laba</span>
                    <span class="text-sm font-bold font-mono" style="color:var(--success)">
                        Rp {{ number_format($weeklyRevenue->sum('profit'), 0, ',', '.') }}
                    </span>
                </div>
                <div class="pl-4">
                    <span class="text-[10px] font-medium uppercase tracking-widest font-mono block mb-1" style="color:var(--accent)">Traffic Penjualan</span>
                    <span class="text-sm font-bold font-mono" style="color:var(--accent)">
                        {{ $weeklyRevenue->sum('count') }} Transaksi
                    </span>
                </div>
            </div>
        </div>

        {{-- Analisis Proporsi Stok (2/5 columns for perfect 3:2 ratio) --}}
        <div class="lg:col-span-2 bg-white rounded-xl border p-5 flex flex-col shadow-sm" style="border-color:var(--line)">
            <div class="flex items-center justify-between border-b pb-4 mb-4" style="border-color:var(--line)">
                <div>
                    <h3 class="text-sm font-semibold" style="color:var(--ink)">Analisis Proporsi Stok</h3>
                    <p class="text-[11px] mt-0.5" style="color:var(--ink-mute)">Proporsi stok handphone.</p>
                </div>
                
                {{-- Tabs Segmented Controls --}}
                <div class="flex items-center gap-0.5 bg-gray-100 p-0.5 rounded-lg text-xs font-semibold flex-shrink-0" style="height: 32px;">
                    <button type="button" id="tab-brand" onclick="switchStockTab('brand')" 
                            class="px-2.5 rounded-md transition-all text-[10px] h-6 flex items-center justify-center bg-white text-blue-600 font-bold shadow-sm">
                        Brand
                    </button>
                    <button type="button" id="tab-type" onclick="switchStockTab('type')" 
                            class="px-2.5 rounded-md transition-all text-[10px] h-6 flex items-center justify-center text-gray-500 hover:text-gray-800">
                        Kondisi
                    </button>
                    <button type="button" id="tab-status" onclick="switchStockTab('status')" 
                            class="px-2.5 rounded-md transition-all text-[10px] h-6 flex items-center justify-center text-gray-500 hover:text-gray-800">
                        Status
                    </button>
                </div>
            </div>

            <div class="flex flex-col items-center flex-1 justify-center">
                {{-- Doughnut Container --}}
                <div class="relative flex items-center justify-center mb-4" style="width:200px; height:200px;">
                    <canvas id="stockPropChart"></canvas>
                    {{-- Absolute Center text --}}
                    <div class="absolute flex flex-col items-center justify-center text-center">
                        <span id="stock-prop-total" class="text-2xl font-extrabold font-mono tracking-tight" style="color:var(--ink)">0</span>
                        <span class="text-[8px] uppercase tracking-widest font-bold mt-0.5" style="color:var(--ink-mute)">Unit Total</span>
                    </div>
                </div>

                {{-- Custom Legend Container --}}
                <div class="w-full border-t pt-3 mt-1" style="border-color:var(--line)">
                    <div id="stock-prop-legend" class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-[11px] max-w-md mx-auto">
                        {{-- Rendered dynamically by JS --}}
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        const stockPalette = [
            '#2563EB', // Brand active blue
            '#10806B', // Clean success emerald
            '#8B5CF6', // Purple/Violet
            '#F59E0B', // Amber/Orange
            '#EC4899', // Pink
            '#14B8A6', // Teal
            '#F43F5E', // Rose/Red
            '#6366F1', // Indigo
            '#84CC16', // Lime
            '#64748B'  // Slate/Gray
        ];

        const stockPropData = {
            brand: {!! json_encode($brandData) !!},
            type: {!! json_encode($typeData) !!},
            status: {!! json_encode($statusData) !!}
        };

        let stockPropChart = null;

        function switchStockTab(tab) {
            // 1. Update buttons styles
            ['brand', 'type', 'status'].forEach(t => {
                const btn = document.getElementById('tab-' + t);
                if (btn) {
                    if (t === tab) {
                        btn.className = 'px-2.5 rounded-md transition-all text-[10px] h-6 flex items-center justify-center bg-white text-blue-600 font-bold shadow-sm';
                    } else {
                        btn.className = 'px-2.5 rounded-md transition-all text-[10px] h-6 flex items-center justify-center text-gray-500 hover:text-gray-800';
                    }
                }
            });

            // 2. Load dataset
            const dataset = stockPropData[tab];
            
            // Calculate total
            const total = dataset.reduce((sum, item) => sum + item.count, 0);
            document.getElementById('stock-prop-total').innerText = total;

            const labels = dataset.map(item => item.label);
            const counts = dataset.map(item => item.count);
            const colors = dataset.map((_, i) => stockPalette[i % stockPalette.length]);

            // 3. Render chart
            const ctx = document.getElementById('stockPropChart').getContext('2d');
            if (stockPropChart) {
                stockPropChart.destroy();
            }

            stockPropChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: counts,
                        backgroundColor: colors,
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%', // Sleek thin doughnut
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0A2540',
                            titleColor: '#FFFFFF',
                            bodyColor: 'rgba(255,255,255,0.8)',
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(ctx) {
                                    const val = ctx.raw;
                                    const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                    return ' ' + ctx.label + ': ' + val + ' unit (' + pct + '%)';
                                }
                            }
                        }
                    }
                }
            });

            // 4. Render legend list
            const legendContainer = document.getElementById('stock-prop-legend');
            legendContainer.innerHTML = '';
            
            if (dataset.length === 0) {
                legendContainer.innerHTML = `<div class="col-span-2 text-center text-gray-400 py-4">Tidak ada data stok</div>`;
                return;
            }

            dataset.forEach((item, idx) => {
                const color = colors[idx];
                const pct = total > 0 ? ((item.count / total) * 100).toFixed(1) : 0;
                
                const colEl = document.createElement('div');
                colEl.className = 'flex items-center justify-between py-1 px-1.5 hover:bg-gray-50 rounded transition-colors';
                colEl.innerHTML = `
                    <div class="flex items-center gap-1.5 truncate mr-1">
                        <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:${color}"></span>
                        <span class="font-medium text-gray-600 truncate text-[10px]">${item.label}</span>
                    </div>
                    <div class="font-mono text-gray-950 font-bold flex-shrink-0 text-[10px]">
                        ${item.count} <span class="text-[9px] text-gray-400 font-normal">(${pct}%)</span>
                    </div>
                `;
                legendContainer.appendChild(colEl);
            });
        }

        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(() => {
                switchStockTab('brand');
            }, 100);
        });
    </script>

    {{-- ── Daftar Stok HP Terbaru ── --}}
    <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
        <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:var(--line);background:var(--bg-soft)">
            <div>
                <div class="text-sm font-semibold" style="color:var(--ink)">Daftar Stok HP Terbaru</div>
                <div class="text-xs mt-0.5" style="color:var(--ink-mute)">Menampilkan 5 unit handphone terbaru yang tersedia beserta kondisi barang.</div>
            </div>
            <a href="{{ route('units.index') }}" class="text-xs font-medium hover:underline" style="color:var(--accent)">Lihat semua →</a>
        </div>

        @if($readyUnits->isEmpty())
        <div class="px-5 py-10 text-center">
            <div class="w-12 h-12 rounded-xl mx-auto mb-3 flex items-center justify-center" style="background:var(--bg-soft)">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="color:var(--ink-mute)"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" /></svg>
            </div>
            <p class="text-sm font-medium mb-1" style="color:var(--ink)">Belum ada unit ready</p>
            <a href="{{ route('units.create') }}" class="text-xs" style="color:var(--accent)">Tambah unit pertama →</a>
        </div>
        @else
        <table class="w-full">
            <thead>
                <tr style="background:var(--bg-soft);border-bottom:1px solid var(--line)">
                    <th class="text-left px-5 py-3 text-[11px] font-semibold uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Merek &amp; Nama Handphone</th>
                    <th class="text-left px-5 py-3 text-[11px] font-semibold uppercase tracking-widest font-mono hidden md:table-cell" style="color:var(--ink-mute)">Detail Fisik</th>
                    <th class="text-left px-5 py-3 text-[11px] font-semibold uppercase tracking-widest font-mono hidden lg:table-cell" style="color:var(--ink-mute)">Harga Modal</th>
                    <th class="text-right px-5 py-3 text-[11px] font-semibold uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Kondisi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($readyUnits as $unit)
                <tr style="border-bottom:1px solid var(--line)"
                    onmouseenter="this.style.background='var(--bg-soft)'" onmouseleave="this.style.background=''">
                    <td class="px-5 py-3.5">
                        <div class="text-sm font-semibold" style="color:var(--ink)">
                            {{ $unit->model->name ?? '—' }}
                        </div>
                        <div class="text-[11px] font-medium mt-0.5 uppercase tracking-wide" style="color:var(--accent)">
                            {{ $unit->model->brand->name ?? '—' }}
                        </div>
                    </td>
                    <td class="px-5 py-3.5 hidden md:table-cell">
                        <div class="text-xs font-mono" style="color:var(--ink-soft)">
                            <span>Warna: <span style="color:var(--ink)">{{ $unit->color }}</span></span>
                        </div>
                        <div class="text-xs font-mono mt-0.5" style="color:var(--ink-soft)">
                            RAM/ROM: <span style="color:var(--ink)">{{ $unit->ram }}/{{ $unit->rom }}</span>
                        </div>
                        <div class="text-xs font-mono mt-0.5" style="color:var(--ink-soft)">
                            Kategori: <span class="font-semibold" style="{{ $unit->unit_type->value === 'baru' ? 'color:var(--accent)' : 'color:#B45309' }}">{{ ucfirst($unit->unit_type->value) }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 hidden lg:table-cell">
                        <span class="text-sm font-mono tabular-nums font-semibold" style="color:var(--ink)">
                            Rp {{ number_format($unit->purchase_price, 0, ',', '.') }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        @if($unit->grade)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider" style="{{ $unit->grade === 'A' ? 'background:var(--ink);color:#fff' : 'background:#92400E;color:#fff' }}">
                            Grade {{ $unit->grade }}
                        </span>
                        @else
                            @if($unit->unit_type->value === 'baru')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider" style="background:var(--ink);color:#fff">
                                Grade A
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider" style="background:#92400E;color:#fff">
                                Second
                            </span>
                            @endif
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection

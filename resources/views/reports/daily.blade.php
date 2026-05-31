@extends('layouts.app')
@section('title', 'Laporan Penjualan')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div>
        <h2 class="text-xl font-bold" style="color:var(--ink)">Laporan Penjualan</h2>
        <p class="text-xs mt-0.5" style="color:var(--ink-mute)">Rekap transaksi penjualan, rekap metode pembayaran, dan analisis proporsi penerimaan</p>
    </div>

    {{-- Filter + Export bar --}}
    <div class="bg-white rounded-xl border p-4 shadow-sm" style="border-color:var(--line)">
        <form method="GET" class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold" style="color:var(--ink-soft)">Tanggal Laporan:</span>
                <div class="flex items-center gap-2 px-3 rounded-lg border bg-[#F8FAFC]"
                    style="border-color:var(--line); height: 36px;">
                    <input type="date" name="date" value="{{ $date }}"
                        class="text-xs focus:outline-none bg-transparent"
                        style="border:none!important;outline:none!important;box-shadow:none!important;padding:0!important;background:transparent;color:var(--ink);width:140px;" />
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0 self-end md:self-auto">
                <button type="submit"
                    class="text-xs h-9 px-4 font-semibold rounded-lg transition-colors flex items-center gap-1.5 shadow-sm"
                    style="background:var(--accent);color:#fff"
                    onmouseenter="this.style.filter='brightness(0.95)'" onmouseleave="this.style.filter='none'">
                    Tampilkan Laporan
                </button>
                @if(auth()->user()->role->value === 'superadmin')
                <a href="{{ route('reports.export', ['type' => 'sales', 'date' => $date]) }}"
                    class="text-xs h-9 px-4 font-semibold rounded-lg transition-all flex items-center gap-1.5 border shadow-sm"
                    style="background:#F0FDF4;color:var(--success);border-color:#BBF7D0"
                    onmouseenter="this.style.background='#DCFCE7'" onmouseleave="this.style.background='#F0FDF4'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export Harian (.xlsx)
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">

        {{-- Total Omzet --}}
        <div class="bg-white rounded-xl border p-4 shadow-sm" style="border-color:var(--line)">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center mb-3" style="background:rgba(59,130,246,0.08)">
                <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </div>
            <p class="text-[10px] font-bold uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Total Omzet</p>
            <p class="text-lg font-bold font-mono tabular-nums mt-0.5" style="color:var(--ink)">
                Rp {{ number_format($total_revenue, 0, ',', '.') }}
            </p>
        </div>

        {{-- Laba Kotor --}}
        @if(auth()->user()->role->value === 'superadmin')
        <div class="bg-white rounded-xl border p-4 shadow-sm" style="border-color:var(--line)">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center mb-3" style="background:rgba(16,128,107,0.08)">
                <svg class="w-4 h-4" style="color:var(--success)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-[10px] font-bold uppercase tracking-widest font-mono" style="color:var(--success)">Laba Kotor</p>
            <p class="text-lg font-bold font-mono tabular-nums mt-0.5" style="color:var(--success)">
                Rp {{ number_format($total_profit, 0, ',', '.') }}
            </p>
        </div>
        @else
        <div class="bg-white rounded-xl border p-4 shadow-sm relative overflow-hidden" style="border-color:var(--line)">
            <div class="absolute inset-0 bg-white/80 backdrop-blur-[2px] flex flex-col items-center justify-center text-center p-2">
                <svg class="w-5 h-5 mb-1 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span class="text-[10px] font-semibold" style="color:var(--ink-soft)">Laba Kotor</span>
                <span class="text-[8px] uppercase tracking-wider font-mono font-bold mt-0.5" style="color:var(--accent)">Superadmin Only</span>
            </div>
        </div>
        @endif

        {{-- Penerimaan Cash --}}
        <div class="bg-white rounded-xl border p-4 shadow-sm" style="border-color:var(--line)">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center mb-3 bg-emerald-50">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z" />
                </svg>
            </div>
            <p class="text-[10px] font-bold uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Penerimaan Cash</p>
            <p class="text-lg font-bold font-mono tabular-nums mt-0.5" style="color:var(--ink)">
                Rp {{ number_format($total_cash, 0, ',', '.') }}
            </p>
        </div>

        {{-- Penerimaan Transfer --}}
        <div class="bg-white rounded-xl border p-4 shadow-sm" style="border-color:var(--line)">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center mb-3" style="background:rgba(37,99,235,0.08)">
                <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
            </div>
            <p class="text-[10px] font-bold uppercase tracking-widest font-mono" style="color:var(--accent)">Penerimaan Transfer</p>
            <p class="text-lg font-bold font-mono tabular-nums mt-0.5" style="color:var(--accent)">
                Rp {{ number_format($total_transfer, 0, ',', '.') }}
            </p>
        </div>

        {{-- Piutang --}}
        <div class="bg-white rounded-xl border p-4 shadow-sm" style="border-color:var(--line)">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center mb-3 bg-amber-50">
                <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <p class="text-[10px] font-bold uppercase tracking-widest font-mono" style="color:var(--warn)">Piutang (Hutang)</p>
            <p class="text-lg font-bold font-mono tabular-nums mt-0.5" style="color:var(--warn)">
                Rp {{ number_format($total_debt, 0, ',', '.') }}
            </p>
        </div>

    </div>

    {{-- Main Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Log Penjualan (2/3) --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border overflow-hidden shadow-sm" style="border-color:var(--line)">
                <div class="px-5 py-4 border-b" style="border-color:var(--line)">
                    <h3 class="text-sm font-semibold" style="color:var(--ink)">Log Penjualan Terdaftar</h3>
                    <p class="text-[11px] mt-0.5" style="color:var(--ink-mute)">Semua invoice penjualan yang disetujui pada tanggal yang dipilih</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr style="background:var(--bg-soft);border-bottom:1px solid var(--line)">
                                <th class="text-left px-5 py-2.5 font-bold uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Invoice</th>
                                <th class="text-left px-5 py-2.5 font-bold uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Kasir</th>
                                <th class="text-right px-5 py-2.5 font-bold uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Total Belanja</th>
                                <th class="text-left px-5 py-2.5 font-bold uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Metode Pembayaran</th>
                                @if(auth()->user()->role->value === 'superadmin')
                                <th class="text-right px-5 py-2.5 font-bold uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Laba Kotor</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sales as $sale)
                            <tr class="hover:bg-gray-50/60 transition-colors" style="border-bottom:1px solid var(--line)">
                                <td class="px-5 py-2.5 font-mono font-bold">
                                    <a href="{{ route('sales.show', $sale) }}" class="hover:underline" style="color:var(--accent)">
                                        {{ $sale->invoice_number }}
                                    </a>
                                </td>
                                <td class="px-5 py-2.5" style="color:var(--ink-soft)">{{ $sale->creator->name ?? '—' }}</td>
                                <td class="px-5 py-2.5 text-right font-mono font-bold tabular-nums" style="color:var(--ink)">
                                    Rp {{ number_format($sale->total_price, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-2.5">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($sale->payments as $payment)
                                            @php
                                                $methodVal = $payment->method->value ?? $payment->method;
                                                $badgeStyle = match($methodVal) {
                                                    'cash'     => 'background:#F0FDF4;color:var(--success);border:1px solid #BBF7D0',
                                                    'transfer' => 'background:#EFF6FF;color:var(--accent);border:1px solid #BFDBFE',
                                                    'utang'    => 'background:#FFF7ED;color:var(--warn);border:1px solid #FED7AA',
                                                    default    => 'background:var(--bg-soft);color:var(--ink-soft)'
                                                };
                                            @endphp
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold capitalize font-mono" style="{{ $badgeStyle }}">
                                                {{ $methodVal }}: {{ number_format($payment->amount, 0, ',', '.') }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                @if(auth()->user()->role->value === 'superadmin')
                                <td class="px-5 py-2.5 text-right font-mono font-bold tabular-nums" style="color:var(--success)">
                                    Rp {{ number_format($sale->profit, 0, ',', '.') }}
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-5 py-14 text-center text-xs" style="color:var(--ink-mute)">
                                    Tidak ada transaksi penjualan terdaftar pada tanggal ini
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Analisis Metode Penerimaan (1/3) --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl border overflow-hidden shadow-sm h-full" style="border-color:var(--line)">
                <div class="px-5 py-4 border-b" style="border-color:var(--line)">
                    <h3 class="text-sm font-semibold" style="color:var(--ink)">Analisis Metode Penerimaan</h3>
                    <p class="text-[11px] mt-0.5" style="color:var(--ink-mute)">Proporsi cash, transfer, dan piutang penjualan harian</p>
                </div>
                <div class="p-5 flex flex-col items-center">
                    @if(count($sales) > 0 && ($total_cash > 0 || $total_transfer > 0 || $total_debt > 0))
                        <div class="w-full relative flex items-center justify-center" style="height:220px">
                            <canvas id="paymentMethodChart"></canvas>
                        </div>
                        <div class="w-full mt-5 space-y-2">
                            <div class="flex items-center justify-between text-xs font-medium">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full" style="background:#10806B"></span>
                                    <span style="color:var(--ink-soft)">Cash</span>
                                </div>
                                <span class="font-mono" style="color:var(--ink)">{{ $total_revenue > 0 ? number_format(($total_cash / $total_revenue) * 100, 1) : 0 }}%</span>
                            </div>
                            <div class="flex items-center justify-between text-xs font-medium">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full" style="background:#2563EB"></span>
                                    <span style="color:var(--ink-soft)">Transfer</span>
                                </div>
                                <span class="font-mono" style="color:var(--ink)">{{ $total_revenue > 0 ? number_format(($total_transfer / $total_revenue) * 100, 1) : 0 }}%</span>
                            </div>
                            <div class="flex items-center justify-between text-xs font-medium">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full" style="background:#D97706"></span>
                                    <span style="color:var(--ink-soft)">Piutang (Hutang)</span>
                                </div>
                                <span class="font-mono" style="color:var(--ink)">{{ $total_revenue > 0 ? number_format(($total_debt / $total_revenue) * 100, 1) : 0 }}%</span>
                            </div>
                        </div>

                        {{-- Transaction count --}}
                        <div class="w-full mt-4 pt-4 border-t" style="border-color:var(--line)">
                            <div class="flex items-center justify-between text-xs">
                                <span style="color:var(--ink-soft)">Total Transaksi</span>
                                <span class="font-mono font-bold" style="color:var(--ink)">{{ count($sales) }} transaksi</span>
                            </div>
                            <div class="flex items-center justify-between text-xs mt-1.5">
                                <span style="color:var(--ink-soft)">Rata-rata per Transaksi</span>
                                <span class="font-mono font-bold" style="color:var(--ink)">
                                    Rp {{ count($sales) > 0 ? number_format($total_revenue / count($sales), 0, ',', '.') : 0 }}
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="py-16 text-center flex flex-col items-center justify-center text-xs" style="color:var(--ink-mute)">
                            <svg class="w-8 h-8 mb-2 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.003 9.003 0 1020.945 13H11V3.055z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                            </svg>
                            Belum ada rekap metode pembayaran
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

</div>

@if(count($sales) > 0 && ($total_cash > 0 || $total_transfer > 0 || $total_debt > 0))
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('paymentMethodChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Cash', 'Transfer', 'Piutang'],
            datasets: [{
                data: [{{ $total_cash }}, {{ $total_transfer }}, {{ $total_debt }}],
                backgroundColor: ['#10806B', '#2563EB', '#D97706'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label + ': ';
                            label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.parsed);
                            return label;
                        }
                    }
                }
            },
            cutout: '75%'
        }
    });
});
</script>
@endif

<style>
    input[type="date"] {
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
        background: transparent !important;
        padding: 0 !important;
        -webkit-appearance: none;
        appearance: none;
    }
</style>
@endsection

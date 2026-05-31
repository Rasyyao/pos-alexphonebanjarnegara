@extends('layouts.app')
@section('title', 'Kelola Piutang')

@section('content')
<div class="space-y-6" x-data="{
    showPayModal: false,
    activeDebt: null,
    invoiceNumber: '',
    outstanding: 0,
    paymentType: 'full',
    installmentAmount: 0,
    
    openPayModal(debt) {
        this.activeDebt = debt;
        this.invoiceNumber = debt.sale ? debt.sale.invoice_number : '';
        this.outstanding = parseFloat(debt.amount) - parseFloat(debt.paid_amount);
        this.paymentType = 'full';
        this.installmentAmount = this.outstanding;
        this.showPayModal = true;
    },
    
    closePayModal() {
        this.showPayModal = false;
        this.activeDebt = null;
    },
    
    get remainingBalance() {
        let pay = this.paymentType === 'full' ? this.outstanding : parseFloat(this.installmentAmount || 0);
        return Math.max(0, this.outstanding - pay);
    }
}">

    {{-- Title and Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold" style="color:var(--ink)">Daftar Piutang Pelanggan</h2>
            <p class="text-xs mt-0.5" style="color:var(--ink-mute)">Pantau tagihan jatuh tempo, cicilan pembayaran, dan penerimaan pelunasan piutang counter</p>
        </div>
    </div>

    {{-- 3-Card Premium Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Piutang Aktif --}}
        <div class="bg-white rounded-xl border p-5 shadow-sm" style="border-color:var(--line)">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-widest font-mono" style="color:var(--warn)">Piutang Aktif (Belum Lunas)</span>
                    <div class="text-2xl font-bold font-mono tracking-tight tabular-nums mt-1" style="color:var(--warn)">
                        Rp {{ number_format($unpaidSum, 0, ',', '.') }}
                    </div>
                </div>
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-orange-50 text-orange-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Piutang Terbayar --}}
        <div class="bg-white rounded-xl border p-5 shadow-sm" style="border-color:var(--line)">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-widest font-mono" style="color:var(--success)">Total Piutang Terbayar</span>
                    <div class="text-2xl font-bold font-mono tracking-tight tabular-nums mt-1" style="color:var(--success)">
                        Rp {{ number_format($paidSum, 0, ',', '.') }}
                    </div>
                </div>
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-teal-50 text-teal-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Jumlah Debitur --}}
        <div class="bg-white rounded-xl border p-5 shadow-sm" style="border-color:var(--line)">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-widest font-mono" style="color:var(--ink-soft)">Jumlah Debitur Aktif</span>
                    <div class="text-2xl font-bold font-mono tracking-tight mt-1" style="color:var(--ink)">
                        {{ $debitorsCount }} Orang
                    </div>
                </div>
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-blue-50 text-blue-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter and Content Card --}}
    <div class="bg-white rounded-xl border overflow-hidden shadow-sm" style="border-color:var(--line)">
        
        {{-- Filtering Presets --}}
        <div class="px-5 py-3 flex gap-2 border-b bg-gray-50/50" style="border-color:var(--line)">
            @foreach([
                'active'  => 'Piutang Aktif',
                'paid'    => 'Lunas',
                'all'     => 'Semua Transaksi'
            ] as $val => $label)
                <a href="{{ route('debts.index', ['status' => $val]) }}"
                   class="px-4 py-1.5 rounded-full text-xs font-semibold transition-all border shadow-sm"
                   style="
                     background: {{ $statusFilter === $val ? 'var(--accent)' : '#fff' }};
                     color: {{ $statusFilter === $val ? '#fff' : 'var(--ink-soft)' }};
                     border-color: {{ $statusFilter === $val ? 'var(--accent)' : 'var(--line)' }};
                   ">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr style="background:var(--bg-soft); border-bottom:1px solid var(--line)">
                        <th class="text-left px-5 py-3 font-bold uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Invoice</th>
                        <th class="text-left px-5 py-3 font-bold uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Kasir</th>
                        <th class="text-right px-5 py-3 font-bold uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Total Tagihan</th>
                        <th class="text-right px-5 py-3 font-bold uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Sudah Dibayar</th>
                        <th class="text-right px-5 py-3 font-bold uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Kurang (Sisa)</th>
                        <th class="text-left px-5 py-3 font-bold uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Jatuh Tempo</th>
                        <th class="text-center px-5 py-3 font-bold uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Status</th>
                        <th class="text-right px-5 py-3 font-bold uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color:var(--line)">
                    @forelse($debts as $debt)
                    <tr class="transition-colors hover:bg-gray-50/50">
                        <td class="px-5 py-3.5 font-mono font-bold">
                            <a href="{{ route('sales.show', $debt->sale_id) }}" class="hover:underline" style="color:var(--accent)">
                                {{ $debt->sale->invoice_number ?? '—' }}
                            </a>
                        </td>
                        <td class="px-5 py-3.5" style="color:var(--ink-soft)">{{ $debt->sale->creator->name ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-right font-mono font-bold tabular-nums" style="color:var(--ink)">
                            Rp {{ number_format($debt->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5 text-right font-mono font-bold tabular-nums" style="color:var(--success)">
                            Rp {{ number_format($debt->paid_amount, 0, ',', '.') }}
                        </td>
                        @php $remaining = $debt->amount - $debt->paid_amount; @endphp
                        <td class="px-5 py-3.5 text-right font-mono font-bold tabular-nums" style="{{ $remaining > 0 ? 'color:var(--warn)' : 'color:var(--ink-mute)' }}">
                            Rp {{ number_format($remaining, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5 font-mono" style="color:var(--ink-soft)">
                            {{ $debt->due_date ? $debt->due_date->format('d/m/Y') : '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @php
                                $badgeStyle = match($debt->status) {
                                    'unpaid' => 'background:#FFF5F5;color:var(--warn);border:1px solid #FECACA',
                                    'partial' => 'background:#FFFBEB;color:#D97706;border:1px solid #FDE68A',
                                    'paid' => 'background:#F0FDF4;color:var(--success);border:1px solid #BBF7D0',
                                    default => 'background:var(--bg-soft);color:var(--ink-soft)'
                                };
                                $statusLabel = match($debt->status) {
                                    'unpaid' => 'Belum Dibayar',
                                    'partial' => 'Cicilan',
                                    'paid' => 'Lunas',
                                    default => $debt->status
                                };
                            @endphp
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono uppercase" style="{{ $badgeStyle }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            @if($debt->status !== 'paid')
                            <button @click="openPayModal({{ json_encode($debt) }})"
                                    class="text-xs px-3 py-1 font-semibold rounded-lg transition-colors border shadow-sm"
                                    style="background:var(--accent);color:#fff;border-color:var(--accent)"
                                    onmouseenter="this.style.filter='brightness(0.95)'" onmouseleave="this.style.filter='none'">
                                Catat Bayar
                            </button>
                            @else
                            <span class="text-xs font-semibold font-mono" style="color:var(--ink-mute)">Lunas</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-14 text-center text-xs" style="color:var(--ink-mute)">Tidak ada data piutang pelanggan terdaftar</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination footer bar --}}
        <div class="px-5 py-3 flex items-center justify-between" style="border-top:1px solid var(--line);background:var(--bg-soft)">
            <span class="text-xs font-mono" style="color:var(--ink-mute)">
                Menampilkan {{ $debts->firstItem() ?? 0 }}–{{ $debts->lastItem() ?? 0 }} dari {{ $debts->total() }} transaksi piutang
            </span>
            {{ $debts->links() }}
        </div>

    </div>

    {{-- ── DEBT REPAYMENT MODAL (Alpine.js) ── --}}
    <div x-show="showPayModal"
         class="fixed inset-0 z-[200]"
         x-cloak
         x-transition>
        {{-- Backdrop --}}
        <div class="fixed inset-0" style="background:rgba(0,0,0,0.5)" @click="closePayModal()"></div>
        {{-- Content wrapper --}}
        <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto">

        <div class="bg-white rounded-2xl max-w-md w-full border overflow-hidden shadow-2xl relative"
             style="border-color:var(--line)"
             @click.stop>
             
            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b flex items-center justify-between" style="border-color:var(--line)">
                <div>
                    <h3 class="font-bold text-sm" style="color:var(--ink)">Catat Pembayaran Piutang</h3>
                    <p class="text-[10px] mt-0.5" style="color:var(--ink-mute)">Invoice: <span class="font-mono font-bold" x-text="invoiceNumber"></span></p>
                </div>
                <button @click="closePayModal()" style="color:var(--ink-mute)" class="hover:text-gray-800 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            {{-- Repayment Form --}}
            <form :action="activeDebt ? `/debts/${activeDebt.id}/pay` : '#'" method="POST" class="p-6 space-y-4">
                @csrf
                
                {{-- Quick Debt Info Card --}}
                <div class="bg-[#F8FAFC] border rounded-xl p-4 divide-y font-mono text-xs" style="border-color:var(--line)">
                    <div class="pb-2.5 flex items-center justify-between">
                        <span style="color:var(--ink-soft)">Total Piutang Awal:</span>
                        <span class="font-bold text-slate-800" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(activeDebt ? activeDebt.amount : 0)"></span>
                    </div>
                    <div class="py-2.5 flex items-center justify-between">
                        <span style="color:var(--ink-soft)">Sisa Piutang Saat Ini:</span>
                        <span class="font-bold text-slate-800" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(outstanding)"></span>
                    </div>
                </div>

                {{-- Payment Type Selection --}}
                <div>
                    <label class="field-label">Pilih Jenis Pembayaran</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex flex-col items-center justify-center p-3 border rounded-xl cursor-pointer hover:bg-gray-50/50 transition-colors"
                               :style="paymentType === 'full' ? 'border-color:var(--accent);background:rgba(37,99,235,0.02)' : 'border-color:var(--line)']">
                            <input type="radio" name="type" value="full" x-model="paymentType" class="sr-only" />
                            <span class="text-xs font-bold" :style="paymentType === 'full' ? 'color:var(--accent)' : 'color:var(--ink-soft)'">Bayar Lunas</span>
                            <span class="text-[9px] mt-0.5" style="color:var(--ink-mute)">Bayar semua sisa utang</span>
                        </label>
                        <label class="flex flex-col items-center justify-center p-3 border rounded-xl cursor-pointer hover:bg-gray-50/50 transition-colors"
                               :style="paymentType === 'partial' ? 'border-color:var(--accent);background:rgba(37,99,235,0.02)' : 'border-color:var(--line)']">
                            <input type="radio" name="type" value="partial" x-model="paymentType" class="sr-only" />
                            <span class="text-xs font-bold" :style="paymentType === 'partial' ? 'color:var(--accent)' : 'color:var(--ink-soft)'">Cicil Sebagian</span>
                            <span class="text-[9px] mt-0.5" style="color:var(--ink-mute)">Bayar cicilan sebagian</span>
                        </label>
                    </div>
                </div>

                {{-- Installment Custom Amount Input --}}
                <div x-show="paymentType === 'partial'" x-transition>
                    <label class="field-label">Jumlah Pembayaran Cicilan</label>
                    <div class="money-wrap">
                        <span class="rp-prefix">Rp</span>
                        <input type="number" name="amount" x-model="installmentAmount" class="field-input" min="1" :max="outstanding" placeholder="Contoh: 200000" />
                    </div>
                    <span class="text-[10px] mt-1 block" style="color:var(--ink-mute)">Masukkan jumlah cicilan yang disetorkan. Sisa utang: <span class="font-bold font-mono text-[11px]" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(outstanding)"></span></span>
                </div>

                {{-- Live Calculation Breakdown --}}
                <div class="border-t pt-4 font-mono text-xs space-y-2">
                    <div class="flex items-center justify-between">
                        <span style="color:var(--ink-soft)">Jumlah yang Dibayarkan:</span>
                        <span class="font-bold text-slate-800" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(paymentType === 'full' ? outstanding : (installmentAmount || 0))"></span>
                    </div>
                    <div class="flex items-center justify-between font-sans font-bold" style="color:var(--success)">
                        <span style="color:var(--ink-soft)">Sisa Piutang Setelah Bayar:</span>
                        <span class="text-sm font-mono" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(remainingBalance)"></span>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="pt-4 flex items-center justify-end gap-3 border-t" style="border-color:var(--line)">
                    <button type="button" @click="closePayModal()" class="btn-secondary" style="height:38px;padding:0 16px;font-size:13px">
                        Batalkan
                    </button>
                    <button type="submit" class="btn-primary" style="height:38px;padding:0 20px;font-size:13px">
                        Simpan Pembayaran
                    </button>
                </div>
            </form>

        </div>
        </div>{{-- /content wrapper --}}
    </div>

</div>
@endsection

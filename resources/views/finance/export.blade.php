@extends('layouts.app')
@section('title', 'Export Data')

@section('content')
<div class="max-w-2xl">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('finance.index') }}" class="flex items-center justify-center w-8 h-8 rounded-lg transition-colors"
           style="background:var(--bg-soft);color:var(--ink-mute)"
           onmouseenter="this.style.background='var(--line)'" onmouseleave="this.style.background='var(--bg-soft)'">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h2 class="text-lg font-semibold leading-none" style="color:var(--ink)">Export Data</h2>
            <p class="text-xs mt-1" style="color:var(--ink-mute)">Unduh laporan dalam format Excel (.xlsx)</p>
        </div>
    </div>

    <div class="space-y-4">

        {{-- Export Stok --}}
        <div class="bg-white rounded-xl border p-5 flex items-center justify-between" style="border-color:var(--line)">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background:#F0FDF4;color:var(--success)">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-sm font-semibold" style="color:var(--ink)">Stok HP</div>
                    <div class="text-xs mt-0.5" style="color:var(--ink-mute)">Semua unit — ID, brand, model, tipe, spesifikasi, harga, status</div>
                </div>
            </div>
            <a href="{{ route('reports.export', 'stock') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors"
               style="background:#F0FDF4;color:var(--success)"
               onmouseenter="this.style.background='#DCFCE7'" onmouseleave="this.style.background='#F0FDF4'">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download .xlsx
            </a>
        </div>

        {{-- Export Keuangan --}}
        <div class="bg-white rounded-xl border p-5 flex items-center justify-between" style="border-color:var(--line)">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background:#EFF6FF;color:var(--accent)">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-sm font-semibold" style="color:var(--ink)">Ringkasan Keuangan</div>
                    <div class="text-xs mt-0.5" style="color:var(--ink-mute)">Total omzet, laba, modal, utang, nilai aset stok</div>
                </div>
            </div>
            <a href="{{ route('reports.export', 'finance') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors"
               style="background:#EFF6FF;color:var(--accent)"
               onmouseenter="this.style.background='#DBEAFE'" onmouseleave="this.style.background='#EFF6FF'">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download .xlsx
            </a>
        </div>

        {{-- Export Pengeluaran --}}
        <div class="bg-white rounded-xl border p-5 flex items-center justify-between" style="border-color:var(--line)">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background:#FFF5F5;color:var(--warn)">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <div>
                    <div class="text-sm font-semibold" style="color:var(--ink)">Pengeluaran</div>
                    <div class="text-xs mt-0.5" style="color:var(--ink-mute)">Semua pengeluaran — tanggal, keterangan, kategori, jumlah, catatan</div>
                </div>
            </div>
            <a href="{{ route('reports.export', 'expenses') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors"
               style="background:#FFF5F5;color:var(--warn)"
               onmouseenter="this.style.background='#FEE2E2'" onmouseleave="this.style.background='#FFF5F5'">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download .xlsx
            </a>
        </div>

        {{-- Export Penjualan by date --}}
        <div class="bg-white rounded-xl border p-5" style="border-color:var(--line)">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background:#FFFBEB;color:#B45309">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-sm font-semibold" style="color:var(--ink)">Penjualan Harian</div>
                    <div class="text-xs mt-0.5" style="color:var(--ink-mute)">Invoice, tanggal, total, laba, metode pembayaran, status</div>
                </div>
            </div>
            <form action="{{ route('reports.export', 'sales') }}" method="GET" class="flex items-end gap-3">
                <div class="flex-1">
                    <label class="field-label">Pilih Tanggal</label>
                    <input type="date" name="date" value="{{ today()->toDateString() }}" required class="field-input" />
                </div>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex-shrink-0"
                        style="background:#FFFBEB;color:#B45309;height:44px"
                        onmouseenter="this.style.background='#FEF3C7'" onmouseleave="this.style.background='#FFFBEB'">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download .xlsx
                </button>
            </form>
        </div>

    </div>
</div>
@endsection

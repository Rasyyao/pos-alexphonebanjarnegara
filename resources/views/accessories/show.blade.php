@extends('layouts.app')
@section('title', $accessory->name . ' — Detail Aksesoris')

@section('content')
<div class="w-full">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('accessories.index') }}" class="flex items-center justify-center w-8 h-8 rounded-lg transition-colors"
               style="background:var(--bg-soft);color:var(--ink-mute)"
               onmouseenter="this.style.background='var(--line)'" onmouseleave="this.style.background='var(--bg-soft)'">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h2 class="text-lg font-semibold leading-none" style="color:var(--ink)">{{ $accessory->name }}</h2>
                <p class="text-xs mt-1" style="color:var(--ink-mute)">{{ $accessory->category ?? 'Tanpa kategori' }}</p>
            </div>
        </div>
        <a href="{{ route('accessories.edit', $accessory) }}" class="btn-primary" style="height:36px;padding:0 16px;font-size:13px">
            Edit Aksesoris
        </a>
    </div>

    <div class="grid lg:grid-cols-3 gap-5">

        {{-- Left: main details (col-span-2) --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Informasi Produk --}}
            <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                <div class="px-5 py-3.5 flex items-center gap-2" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="color:var(--ink-mute)">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Informasi Produk</span>
                </div>
                <div class="divide-y">
                    <div class="flex items-center px-5 py-3" style="border-color:var(--line)">
                        <span class="w-36 text-xs font-medium flex-shrink-0" style="color:var(--ink-mute)">Nama Aksesoris</span>
                        <span class="text-sm font-medium" style="color:var(--ink)">{{ $accessory->name }}</span>
                    </div>
                    <div class="flex items-center px-5 py-3" style="border-color:var(--line)">
                        <span class="w-36 text-xs font-medium flex-shrink-0" style="color:var(--ink-mute)">Kategori</span>
                        <span class="text-sm font-medium" style="color:var(--ink)">{{ $accessory->category ?? '—' }}</span>
                    </div>
                    <div class="flex items-center px-5 py-3" style="border-color:var(--line)">
                        <span class="w-36 text-xs font-medium flex-shrink-0" style="color:var(--ink-mute)">Stok</span>
                        <span class="text-sm font-semibold font-mono tabular-nums"
                              style="color:{{ $accessory->stock_qty <= 3 ? 'var(--warn)' : 'var(--ink)' }}">
                            {{ $accessory->stock_qty }}
                            @if($accessory->stock_qty <= 3 && $accessory->stock_qty > 0)
                                <span class="ml-1.5 text-[11px] font-medium" style="color:var(--warn)">— Stok menipis</span>
                            @elseif($accessory->stock_qty === 0)
                                <span class="ml-1.5 text-[11px] font-medium" style="color:var(--warn)">— Habis</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center px-5 py-3" style="border-color:var(--line)">
                        <span class="w-36 text-xs font-medium flex-shrink-0" style="color:var(--ink-mute)">Tanggal Input</span>
                        <span class="text-sm font-mono" style="color:var(--ink)">{{ $accessory->created_at->format('d M Y, H:i') }}</span>
                    </div>
                </div>
            </div>

            {{-- Harga --}}
            <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                <div class="px-5 py-3.5 flex items-center gap-2" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="color:var(--ink-mute)">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Harga</span>
                </div>
                <div class="divide-y">
                    <div class="flex items-center px-5 py-3" style="border-color:var(--line)">
                        <span class="w-36 text-xs font-medium flex-shrink-0" style="color:var(--ink-mute)">Harga Beli</span>
                        <span class="text-sm font-mono tabular-nums" style="color:var(--ink-soft)">Rp {{ number_format($accessory->purchase_price, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center px-5 py-3" style="border-color:var(--line)">
                        <span class="w-36 text-xs font-medium flex-shrink-0" style="color:var(--ink-mute)">Harga Jual</span>
                        <span class="text-sm font-semibold font-mono tabular-nums" style="color:var(--ink)">Rp {{ number_format($accessory->selling_price, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Right sidebar: margin + actions --}}
        <div class="space-y-5">

            {{-- Estimasi Margin --}}
            @php
                $margin = $accessory->selling_price - $accessory->purchase_price;
                $marginPct = $accessory->selling_price > 0 ? round(($margin / $accessory->selling_price) * 100) : 0;
                $marginColor = $margin >= 0 ? 'var(--success)' : 'var(--warn)';
                $barWidth = max(0, min(100, $marginPct));
            @endphp
            <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                <div class="px-5 py-3.5" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                    <span class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Estimasi Margin</span>
                </div>
                <div class="p-5">
                    <div class="text-3xl font-semibold font-mono tabular-nums" style="color:{{ $marginColor }}">
                        Rp {{ number_format($margin, 0, ',', '.') }}
                    </div>
                    <div class="text-xs mt-1" style="color:{{ $marginColor }}">
                        {{ $margin >= 0 ? 'Untung' : 'Rugi' }} {{ abs($marginPct) }}% dari harga jual
                    </div>
                    <div class="mt-4 h-1.5 rounded-full overflow-hidden" style="background:var(--bg-soft)">
                        <div class="h-full rounded-full" style="width:{{ $barWidth }}%;background:{{ $marginColor }}"></div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="space-y-2.5">
                <a href="{{ route('accessories.edit', $accessory) }}" class="btn-primary w-full" style="height:44px;font-size:14px">
                    Edit Aksesoris
                </a>
                <a href="{{ route('accessories.index') }}" class="btn-secondary w-full" style="height:44px;font-size:14px">
                    Kembali ke Daftar
                </a>
            </div>

        </div>

    </div>
</div>
@endsection

@extends('layouts.app')
@section('title', ($unit->model->brand->name ?? '') . ' ' . ($unit->model->name ?? '') . ' — Detail Unit')

@section('content')
<div class="w-full">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('units.index') }}" class="flex items-center justify-center w-8 h-8 rounded-lg transition-colors"
               style="background:var(--bg-soft);color:var(--ink-mute)"
               onmouseenter="this.style.background='var(--line)'" onmouseleave="this.style.background='var(--bg-soft)'">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h2 class="text-lg font-semibold leading-none" style="color:var(--ink)">
                    {{ $unit->model->brand->name ?? '—' }} {{ $unit->model->name ?? '—' }}
                </h2>
                <p class="text-xs mt-1 font-mono" style="color:var(--ink-mute)">
                    {{ $unit->ram }}/{{ $unit->rom }} · {{ $unit->color }}
                </p>
            </div>
        </div>
        @if($unit->status->value !== 'sold')
        <a href="{{ route('units.edit', $unit) }}" class="btn-primary" style="height:36px;padding:0 16px;font-size:13px">
            Edit Unit
        </a>
        @endif
    </div>

    <div class="grid lg:grid-cols-3 gap-5">

        {{-- Left: main details (col-span-2) --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Spesifikasi --}}
            <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                <div class="px-5 py-3.5" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                    <span class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Spesifikasi</span>
                </div>
                <div class="divide-y" style="--tw-divide-opacity:1">
                    @foreach([
                        ['Brand', $unit->model->brand->name ?? '—'],
                        ['Model', $unit->model->name ?? '—'],
                        ['Tipe', ucfirst($unit->unit_type->value)],
                        ['Grade', $unit->grade ? 'Grade ' . $unit->grade : '—'],
                        ['Warna', $unit->color ?: '—'],
                        ['RAM', $unit->ram ?: '—'],
                        ['ROM / Storage', $unit->rom ?: '—'],
                    ] as [$label, $value])
                    <div class="flex items-center px-5 py-3" style="border-color:var(--line)">
                        <span class="w-36 text-xs font-medium flex-shrink-0" style="color:var(--ink-mute)">{{ $label }}</span>
                        <span class="text-sm font-medium" style="color:var(--ink)">{{ $value }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Harga & Pembelian --}}
            <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                <div class="px-5 py-3.5" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                    <span class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Harga & Pembelian</span>
                </div>
                <div class="divide-y">
                    <div class="flex items-center px-5 py-3" style="border-color:var(--line)">
                        <span class="w-36 text-xs font-medium flex-shrink-0" style="color:var(--ink-mute)">Harga Beli</span>
                        <span class="text-sm font-semibold font-mono tabular-nums" style="color:var(--ink)">Rp {{ number_format($unit->purchase_price, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center px-5 py-3" style="border-color:var(--line)">
                        <span class="w-36 text-xs font-medium flex-shrink-0" style="color:var(--ink-mute)">Harga Jual</span>
                        @if($unit->selling_price)
                            <span class="text-sm font-semibold font-mono tabular-nums" style="color:var(--success)">Rp {{ number_format($unit->selling_price, 0, ',', '.') }}</span>
                        @else
                            <span class="text-sm font-mono" style="color:var(--ink-mute)">Belum diset</span>
                        @endif
                    </div>
                    <div class="flex items-center px-5 py-3" style="border-color:var(--line)">
                        <span class="w-36 text-xs font-medium flex-shrink-0" style="color:var(--ink-mute)">Tanggal Beli</span>
                        <span class="text-sm font-mono" style="color:var(--ink)">{{ $unit->purchase_date->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- Identitas --}}
            <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                <div class="px-5 py-3.5" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                    <span class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Identitas</span>
                </div>
                <div class="divide-y">
                    <div class="flex items-center px-5 py-3" style="border-color:var(--line)">
                        <span class="w-36 text-xs font-medium flex-shrink-0" style="color:var(--ink-mute)">Status</span>
                        <div class="flex items-center gap-2">
                            @if($unit->status->value === 'ready')
                                <span class="w-2 h-2 rounded-full inline-block" style="background:var(--success)"></span>
                                <span class="text-sm font-semibold" style="color:var(--success)">Ready — Tersedia</span>
                            @elseif($unit->status->value === 'sold')
                                <span class="w-2 h-2 rounded-full inline-block" style="background:var(--ink-mute)"></span>
                                <span class="text-sm font-semibold" style="color:var(--ink-mute)">Terjual</span>
                                @if($unit->saleItem)
                                    <span class="text-xs" style="color:var(--ink-mute)">
                                        (melalui
                                        <a href="{{ route('sales.show', $unit->saleItem->sale) }}"
                                           class="font-medium hover:underline" style="color:var(--accent)">
                                            {{ $unit->saleItem->sale->invoice_number ?? '—' }}
                                        </a>)
                                    </span>
                                @endif
                            @else
                                <span class="w-2 h-2 rounded-full inline-block" style="background:var(--warn)"></span>
                                <span class="text-sm font-semibold" style="color:var(--warn)">Retur</span>
                            @endif
                        </div>
                    </div>
                    @foreach([
                        ['IMEI', $unit->imei ?: '—'],
                        ['Serial Number', $unit->serial_number ?: '—'],
                        ['Ditambahkan oleh', $unit->creator->name ?? '—'],
                        ['Tanggal Input', $unit->created_at->format('d M Y, H:i')],
                    ] as [$label, $value])
                    <div class="flex items-center px-5 py-3" style="border-color:var(--line)">
                        <span class="w-36 text-xs font-medium flex-shrink-0" style="color:var(--ink-mute)">{{ $label }}</span>
                        <span class="text-sm font-mono" style="color:var(--ink)">{{ $value }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Catatan --}}
            @if($unit->notes)
            <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                <div class="px-5 py-3.5" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                    <span class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Catatan</span>
                </div>
                <div class="px-5 py-4 text-sm leading-relaxed" style="color:var(--ink-soft)">
                    {{ $unit->notes }}
                </div>
            </div>
            @endif

        </div>

        {{-- Right sidebar: photo + status + harga --}}
        <div class="space-y-4">

            {{-- Photo --}}
            <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                <div class="px-5 py-3.5" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                    <span class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Foto Unit</span>
                </div>
                <div class="p-5 space-y-3">
                    @php
                        $photos = collect([$unit->photo_path, $unit->photo_path_2, $unit->photo_path_3])->filter();
                    @endphp

                    @if($photos->isNotEmpty())
                        {{-- Main Display --}}
                        <div class="w-full rounded-xl overflow-hidden bg-black flex items-center justify-center" style="height:220px">
                            <img id="main-photo-display" src="{{ Storage::url($photos->first()) }}" alt="Foto unit"
                                 class="w-full h-full object-contain" />
                        </div>

                        {{-- Thumbnails --}}
                        @if($photos->count() > 1)
                        <div class="grid grid-cols-3 gap-2">
                            @foreach($photos as $index => $path)
                            <button type="button" onclick="swapMainPhoto('{{ Storage::url($path) }}', this)" 
                                    class="aspect-square rounded-lg border-2 overflow-hidden transition-all" 
                                    style="border-color: {{ $index === 0 ? 'var(--accent)' : 'var(--line)' }}">
                                <img src="{{ Storage::url($path) }}" alt="" class="w-full h-full object-cover" />
                            </button>
                            @endforeach
                        </div>
                        @endif
                    @else
                        <div class="flex flex-col items-center justify-center gap-2 rounded-xl" style="min-height:140px;background:var(--bg-soft)">
                            <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1" style="color:var(--line)">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-xs" style="color:var(--ink-mute)">Tidak ada foto</span>
                        </div>
                    @endif
                </div>
            </div>



            {{-- Estimasi Margin (otomatis dari harga beli & jual) --}}
            <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                <div class="px-5 py-3.5" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                    <span class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Estimasi Margin</span>
                </div>
                @php
                    $buy    = (float) $unit->purchase_price;
                    $sell   = (float) ($unit->selling_price ?? 0);
                    $margin = $sell - $buy;
                    $pct    = $sell > 0 ? round(($margin / $sell) * 100) : 0;
                    $barW   = max(0, min(100, abs($pct)));
                    $hasPrice = $sell > 0;
                @endphp
                <div class="p-5">
                    <div class="text-2xl font-semibold font-mono tabular-nums"
                         style="color:{{ $hasPrice ? ($margin >= 0 ? 'var(--success)' : 'var(--warn)') : 'var(--ink-mute)' }}">
                        Rp {{ $hasPrice ? number_format($margin, 0, ',', '.') : '0' }}
                    </div>
                    <div class="text-xs mt-1"
                         style="color:{{ $hasPrice ? ($margin >= 0 ? 'var(--success)' : 'var(--warn)') : 'var(--ink-mute)' }}">
                        @if($hasPrice)
                            {{ $margin >= 0 ? 'Untung' : 'Rugi' }} {{ abs($pct) }}% dari harga jual
                        @else
                            Harga jual belum diset
                        @endif
                    </div>
                    <div class="mt-3 h-1.5 rounded-full overflow-hidden" style="background:var(--bg-soft)">
                        <div class="h-full rounded-full" style="width:{{ $barW }}%;background:{{ $hasPrice ? ($margin >= 0 ? 'var(--success)' : 'var(--warn)') : 'var(--bg-soft)' }}"></div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<script>
function swapMainPhoto(src, btn) {
    document.getElementById('main-photo-display').src = src;
    btn.parentNode.querySelectorAll('button').forEach(b => {
        b.style.borderColor = 'var(--line)';
    });
    btn.style.borderColor = 'var(--accent)';
}
</script>
@endsection

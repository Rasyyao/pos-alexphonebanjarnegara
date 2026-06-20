@extends('layouts.app')
@section('title', ($unit->model->brand->name ?? '') . ' ' . ($unit->model->name ?? '') . ' — Detail Unit')

@section('content')
    <div class="w-full">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('units.index') }}"
                    class="flex items-center justify-center w-8 h-8 rounded-lg transition-colors"
                    style="background:var(--bg-soft);color:var(--ink-mute)" onmouseenter="this.style.background='var(--line)'"
                    onmouseleave="this.style.background='var(--bg-soft)'">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
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
            @if ($unit->status->value !== 'sold')
                <a href="{{ route('units.edit', $unit) }}" class="btn-primary"
                    style="height:36px;padding:0 16px;font-size:13px">
                    Edit Unit
                </a>
            @endif
        </div>

        <div class="grid gap-5">

            {{-- Left: main details (col-span-2) --}}
            <div class="space-y-4">

                {{-- Spesifikasi --}}
                <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                    <div class="px-5 py-3.5" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                        <span class="text-[11px] font-medium uppercase tracking-widest font-mono"
                            style="color:var(--ink-mute)">Spesifikasi</span>
                    </div>
                    <div class="divide-y" style="--tw-divide-opacity:1">
                        @foreach ([['Brand', $unit->model->brand->name ?? '—'], ['Model', $unit->model->name ?? '—'], ['Tipe', ucfirst($unit->unit_type->value)], ['Grade', $unit->grade ? 'Grade ' . $unit->grade : '—'], ['Warna', $unit->color ?: '—'], ['RAM', $unit->ram ?: '—'], ['ROM / Storage', $unit->rom ?: '—']] as [$label, $value])
                            <div class="flex items-center px-5 py-3" style="border-color:var(--line)">
                                <span class="w-36 text-xs font-medium flex-shrink-0"
                                    style="color:var(--ink-mute)">{{ $label }}</span>
                                <span class="text-sm font-medium" style="color:var(--ink)">{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Identitas --}}
                <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                    <div class="px-5 py-3.5" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                        <span class="text-[11px] font-medium uppercase tracking-widest font-mono"
                            style="color:var(--ink-mute)">Identitas</span>
                    </div>
                    <div class="divide-y">
                        <div class="flex items-center px-5 py-3" style="border-color:var(--line)">
                            <span class="w-36 text-xs font-medium flex-shrink-0" style="color:var(--ink-mute)">Status</span>
                            <div class="flex items-center gap-2">
                                @if ($unit->status->value === 'ready')
                                    <span class="w-2 h-2 rounded-full inline-block"
                                        style="background:var(--success)"></span>
                                    <span class="text-sm font-semibold" style="color:var(--success)">Ready — Tersedia</span>
                                @elseif($unit->status->value === 'sold')
                                    <span class="w-2 h-2 rounded-full inline-block"
                                        style="background:var(--ink-mute)"></span>
                                    <span class="text-sm font-semibold" style="color:var(--ink-mute)">Terjual</span>
                                    @if ($unit->saleItem)
                                        <span class="text-xs" style="color:var(--ink-mute)">
                                            (melalui
                                            <a href="{{ route('sales.show', $unit->saleItem->sale) }}"
                                                class="font-medium hover:underline" style="color:var(--accent)">
                                                {{ $unit->saleItem->sale->invoice_number ?? '—' }}
                                            </a>)
                                        </span>
                                    @endif
                                @elseif($unit->status->value === 'pending')
                                    <span class="w-2 h-2 rounded-full inline-block" style="background:#F59E0B"></span>
                                    <span class="text-sm font-semibold" style="color:#B45309">Menunggu Verifikasi</span>
                                @else
                                    <span class="w-2 h-2 rounded-full inline-block" style="background:var(--warn)"></span>
                                    <span class="text-sm font-semibold" style="color:var(--warn)">Retur</span>
                                @endif
                            </div>
                        </div>
                        @foreach ([['IMEI', $unit->imei ?: '—'], ['Serial Number', $unit->serial_number ?: '—'], ['Ditambahkan oleh', $unit->creator->name ?? '—'], ['Tanggal Input', $unit->created_at->format('d M Y, H:i')]] as [$label, $value])
                            <div class="flex items-center px-5 py-3" style="border-color:var(--line)">
                                <span class="w-36 text-xs font-medium flex-shrink-0"
                                    style="color:var(--ink-mute)">{{ $label }}</span>
                                <span class="text-sm font-mono" style="color:var(--ink)">{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Catatan --}}
                @if ($unit->notes)
                    <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                        <div class="px-5 py-3.5" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                            <span class="text-[11px] font-medium uppercase tracking-widest font-mono"
                                style="color:var(--ink-mute)">Catatan</span>
                        </div>
                        <div class="px-5 py-4 text-sm leading-relaxed" style="color:var(--ink-soft)">
                            {{ $unit->notes }}
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    @include('components.money-format')
    <script>
        (function() {
            function rawInt(id) {
                return parseInt((document.getElementById(id)?.value || '').replace(/[^0-9]/g, ''), 10) || 0;
            }

            function calcShowMargin() {
                var buy = rawInt('show-est-modal');
                var sell = rawInt('show-est-jual');
                var margin = sell - buy;
                var amtEl = document.getElementById('show-margin-amount');
                var pctEl = document.getElementById('show-margin-pct');
                var bar = document.getElementById('show-margin-bar');
                if (buy > 0 && sell > 0) {
                    var pct = Math.round((margin / sell) * 100);
                    var color = margin >= 0 ? 'var(--success)' : 'var(--warn)';
                    amtEl.textContent = 'Rp ' + margin.toLocaleString('id-ID');
                    amtEl.style.color = color;
                    pctEl.textContent = (margin >= 0 ? 'Untung ' : 'Rugi ') + Math.abs(pct) + '% dari harga jual';
                    pctEl.style.color = color;
                    bar.style.width = Math.max(0, Math.min(100, Math.abs(pct))) + '%';
                    bar.style.background = color;
                } else {
                    amtEl.textContent = 'Rp 0';
                    amtEl.style.color = 'var(--ink-mute)';
                    pctEl.textContent = 'Isi harga jual estimasi untuk lihat margin';
                    pctEl.style.color = 'var(--ink-mute)';
                    bar.style.width = '0%';
                }
            }
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('show-est-modal')?.addEventListener('input', calcShowMargin);
                document.getElementById('show-est-jual')?.addEventListener('input', calcShowMargin);
            });
        })();
    </script>
@endsection

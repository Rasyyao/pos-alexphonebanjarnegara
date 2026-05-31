@extends('layouts.app')
@section('title', 'Tambah Aksesoris')

@section('content')
<div class="w-full">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('accessories.index') }}" class="flex items-center justify-center w-8 h-8 rounded-lg transition-colors"
           style="background:var(--bg-soft);color:var(--ink-mute)"
           onmouseenter="this.style.background='var(--line)'" onmouseleave="this.style.background='var(--bg-soft)'">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h2 class="text-lg font-semibold leading-none" style="color:var(--ink)">Tambah Aksesoris</h2>
            <p class="text-xs mt-1" style="color:var(--ink-mute)">Tambahkan item aksesoris baru ke daftar stok</p>
        </div>
    </div>

    <form method="POST" action="{{ route('accessories.store') }}" id="acc-form">
        @csrf

        <div class="grid lg:grid-cols-3 gap-5">

            {{-- Left: product info --}}
            <div class="lg:col-span-2 space-y-5">
                <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                    <div class="px-5 py-3.5 flex items-center gap-2" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="color:var(--ink-mute)">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Informasi Produk</span>
                    </div>
                    <div class="p-5 space-y-4">
                        <div>
                            <label class="field-label">Nama Aksesoris <span style="color:var(--warn)">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required placeholder="mis. Casing Silicone iPhone 15"
                                   class="field-input @error('name') error @enderror" autofocus />
                            @error('name')<p class="field-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="field-label">Kategori</label>
                                <input type="text" name="category" value="{{ old('category') }}" placeholder="mis. Case, Charger, TWS" list="cat-suggest"
                                       class="field-input" />
                                <datalist id="cat-suggest">
                                    <option value="Case"></option>
                                    <option value="Charger"></option>
                                    <option value="Kabel"></option>
                                    <option value="TWS"></option>
                                    <option value="Anti Gores"></option>
                                    <option value="Powerbank"></option>
                                </datalist>
                            </div>
                            <div>
                                <label class="field-label">Stok Awal <span style="color:var(--warn)">*</span></label>
                                <input type="number" name="stock_qty" value="{{ old('stock_qty', 0) }}" required min="0"
                                       class="field-input @error('stock_qty') error @enderror" />
                                @error('stock_qty')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                    <div class="px-5 py-3.5 flex items-center gap-2" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="color:var(--ink-mute)">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Harga</span>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="field-label">Harga Beli <span style="color:var(--warn)">*</span></label>
                                <div class="money-wrap">
                                    <span class="rp-prefix">Rp</span>
                                    <input type="text" name="purchase_price" id="acc-buy" value="{{ old('purchase_price') }}" required
                                           class="field-input money-input @error('purchase_price') error @enderror"
                                           placeholder="0" inputmode="numeric" oninput="calcMargin()" onblur="calcMargin()" />
                                </div>
                                @error('purchase_price')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="field-label">Harga Jual <span style="color:var(--warn)">*</span></label>
                                <div class="money-wrap">
                                    <span class="rp-prefix">Rp</span>
                                    <input type="text" name="selling_price" id="acc-sell" value="{{ old('selling_price') }}" required
                                           class="field-input money-input @error('selling_price') error @enderror"
                                           placeholder="0" inputmode="numeric" oninput="calcMargin()" onblur="calcMargin()" />
                                </div>
                                @error('selling_price')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: live margin + actions --}}
            <div class="space-y-5">
                <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                    <div class="px-5 py-3.5" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                        <span class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Estimasi Margin</span>
                    </div>
                    <div class="p-5">
                        <div class="text-3xl font-semibold font-mono tabular-nums" id="margin-amount" style="color:var(--ink-mute)">Rp 0</div>
                        <div class="text-xs mt-1" id="margin-pct" style="color:var(--ink-mute)">Laba per unit terjual</div>
                        <div class="mt-4 h-1.5 rounded-full overflow-hidden" style="background:var(--bg-soft)">
                            <div id="margin-bar" class="h-full rounded-full transition-all duration-300" style="width:0%;background:var(--success)"></div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border p-4 space-y-2.5" style="border-color:var(--line)">
                    <button type="submit" class="btn-primary w-full" style="height:44px;font-size:14px">Simpan Aksesoris</button>
                    <a href="{{ route('accessories.index') }}" class="btn-secondary w-full" style="height:44px;font-size:14px">Batal</a>
                </div>
            </div>

        </div>
    </form>
</div>

@include('components.money-format')
<script>
function rawNum(id) {
    var el = document.getElementById(id);
    return el ? parseInt((el.value || '').replace(/[^0-9]/g, ''), 10) || 0 : 0;
}
function calcMargin() {
    var buy = rawNum('acc-buy'), sell = rawNum('acc-sell');
    var margin = sell - buy;
    var amtEl = document.getElementById('margin-amount');
    var pctEl = document.getElementById('margin-pct');
    var bar   = document.getElementById('margin-bar');
    amtEl.textContent = 'Rp ' + margin.toLocaleString('id-ID');
    if (sell > 0 && buy > 0) {
        var pct = Math.round((margin / sell) * 100);
        var color = margin >= 0 ? 'var(--success)' : 'var(--warn)';
        amtEl.style.color = color;
        bar.style.background = color;
        bar.style.width = Math.max(0, Math.min(100, pct)) + '%';
        pctEl.textContent = (margin >= 0 ? 'Untung ' : 'Rugi ') + Math.abs(pct) + '% dari harga jual';
        pctEl.style.color = color;
    } else {
        amtEl.style.color = 'var(--ink-mute)';
        pctEl.textContent = 'Laba per unit terjual';
        pctEl.style.color = 'var(--ink-mute)';
        bar.style.width = '0%';
    }
}
document.addEventListener('DOMContentLoaded', calcMargin);
</script>
@endsection

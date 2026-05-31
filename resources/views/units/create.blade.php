@extends('layouts.app')
@section('title', 'Tambah Unit HP')

@section('content')
<div class="w-full">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('units.index') }}" class="flex items-center justify-center w-8 h-8 rounded-lg transition-colors"
           style="background:var(--bg-soft);color:var(--ink-mute)"
           onmouseenter="this.style.background='var(--line)'" onmouseleave="this.style.background='var(--bg-soft)'">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h2 class="text-lg font-semibold leading-none" style="color:var(--ink)">Tambah Unit HP</h2>
            <p class="text-xs mt-1" style="color:var(--ink-mute)">Isi detail unit yang akan ditambahkan ke stok</p>
        </div>
    </div>

    <form method="POST" action="{{ route('units.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="grid lg:grid-cols-3 gap-5">

            {{-- Left column: main fields --}}
            <div class="lg:col-span-2 space-y-4">

                {{-- Identitas Unit --}}
                <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                    <div class="px-5 py-3.5" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                        <span class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Identitas Unit</span>
                    </div>
                    <div class="p-5 space-y-4">
                        {{-- Brand & Model separate --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="field-label">Brand <span style="color:var(--warn)">*</span></label>
                                <input type="text" name="brand_name" value="{{ old('brand_name') }}" required
                                       list="brands-list" autocomplete="off"
                                       class="field-input @error('brand_name') error @enderror"
                                       placeholder="mis. Samsung, Apple, Xiaomi" />
                                <datalist id="brands-list">
                                    @foreach($brands as $b)
                                        <option value="{{ $b->name }}">
                                    @endforeach
                                </datalist>
                                @error('brand_name')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="field-label">Model <span style="color:var(--warn)">*</span></label>
                                <input type="text" name="model_name" value="{{ old('model_name') }}" required
                                       class="field-input @error('model_name') error @enderror"
                                       placeholder="mis. Galaxy S24, iPhone 15 Pro" />
                                @error('model_name')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="field-label">Tipe <span style="color:var(--warn)">*</span></label>
                                <select name="unit_type" required class="field-input">
                                    <option value="baru" {{ old('unit_type', 'baru') === 'baru' ? 'selected' : '' }}>Baru</option>
                                    <option value="second" {{ old('unit_type') === 'second' ? 'selected' : '' }}>Second</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">Grade</label>
                                <select name="grade" class="field-input @error('grade') error @enderror">
                                    <option value="">Pilih Grade (Optional)</option>
                                    <option value="A" {{ old('grade') === 'A' ? 'selected' : '' }}>Grade A</option>
                                    <option value="B" {{ old('grade') === 'B' ? 'selected' : '' }}>Grade B</option>
                                    <option value="C" {{ old('grade') === 'C' ? 'selected' : '' }}>Grade C</option>
                                    <option value="D" {{ old('grade') === 'D' ? 'selected' : '' }}>Grade D</option>
                                    <option value="E" {{ old('grade') === 'E' ? 'selected' : '' }}>Grade E</option>
                                </select>
                                @error('grade')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="field-label">Warna</label>
                                <input type="text" name="color" value="{{ old('color') }}" placeholder="mis. Midnight Black" class="field-input" />
                            </div>
                            <div>
                                <label class="field-label">RAM</label>
                                <input type="text" name="ram" value="{{ old('ram') }}" placeholder="mis. 8GB" class="field-input" />
                            </div>
                            <div>
                                <label class="field-label">ROM / Storage</label>
                                <input type="text" name="rom" value="{{ old('rom') }}" placeholder="mis. 256GB" class="field-input" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Harga & Identitas --}}
                <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                    <div class="px-5 py-3.5" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                        <span class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Harga & Pembelian</span>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="field-label">Harga Beli <span style="color:var(--warn)">*</span></label>
                                <div class="money-wrap">
                                    <span class="rp-prefix">Rp</span>
                                    <input type="text" name="purchase_price" value="{{ old('purchase_price') }}" required
                                           class="field-input money-input @error('purchase_price') error @enderror"
                                           placeholder="0" inputmode="numeric" />
                                </div>
                                @error('purchase_price')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="field-label">Harga Jual</label>
                                <div class="money-wrap">
                                    <span class="rp-prefix">Rp</span>
                                    <input type="text" name="selling_price" value="{{ old('selling_price') }}"
                                           class="field-input money-input @error('selling_price') error @enderror"
                                           placeholder="0" inputmode="numeric" />
                                </div>
                                @error('selling_price')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="field-label">Tanggal Beli <span style="color:var(--warn)">*</span></label>
                                <input type="date" name="purchase_date" value="{{ old('purchase_date', today()->toDateString()) }}" required
                                       class="field-input @error('purchase_date') error @enderror" />
                                @error('purchase_date')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="field-label">IMEI</label>
                                <input type="text" name="imei" value="{{ old('imei') }}" maxlength="20"
                                       class="field-input @error('imei') error @enderror" placeholder="15 digit IMEI" />
                                @error('imei')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="field-label">Serial Number</label>
                                <input type="text" name="serial_number" value="{{ old('serial_number') }}" class="field-input" />
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right column: photo + notes + submit --}}
            <div class="space-y-4">

                {{-- Foto --}}
                <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                    <div class="px-5 py-3.5" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                        <span class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Foto Unit (Maks. 3)</span>
                    </div>
                    <div class="p-5 space-y-4">
                        
                        {{-- Grid of 3 uploads --}}
                        <div class="grid grid-cols-3 gap-3">
                            
                            {{-- Foto 1 --}}
                            <div>
                                <label id="photo-label" for="photo-input"
                                       class="flex flex-col items-center justify-center gap-1.5 rounded-xl border-2 border-dashed cursor-pointer transition-colors overflow-hidden relative"
                                       style="border-color:var(--line);aspect-ratio:1/1;background:var(--bg-soft)"
                                       onmouseenter="this.style.borderColor='var(--accent)'" onmouseleave="this.style.borderColor='var(--line)'">
                                    <div id="photo-preview" class="hidden w-full h-full absolute inset-0">
                                        <img id="photo-img" src="" alt="" class="w-full h-full object-cover" />
                                    </div>
                                    <div id="photo-placeholder" class="flex flex-col items-center text-center p-2">
                                        <svg class="w-5 h-5 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:var(--ink-mute)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="text-[9px]" style="color:var(--ink-mute)">Foto 1</span>
                                    </div>
                                </label>
                                <input type="file" id="photo-input" name="photo" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="previewPhoto(this, 'photo-img', 'photo-preview', 'photo-placeholder')" />
                                @error('photo')<p class="field-error text-[10px] mt-1">{{ $message }}</p>@enderror
                            </div>

                            {{-- Foto 2 --}}
                            <div>
                                <label id="photo-label-2" for="photo-input-2"
                                       class="flex flex-col items-center justify-center gap-1.5 rounded-xl border-2 border-dashed cursor-pointer transition-colors overflow-hidden relative"
                                       style="border-color:var(--line);aspect-ratio:1/1;background:var(--bg-soft)"
                                       onmouseenter="this.style.borderColor='var(--accent)'" onmouseleave="this.style.borderColor='var(--line)'">
                                    <div id="photo-preview-2" class="hidden w-full h-full absolute inset-0">
                                        <img id="photo-img-2" src="" alt="" class="w-full h-full object-cover" />
                                    </div>
                                    <div id="photo-placeholder-2" class="flex flex-col items-center text-center p-2">
                                        <svg class="w-5 h-5 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:var(--ink-mute)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="text-[9px]" style="color:var(--ink-mute)">Foto 2</span>
                                    </div>
                                </label>
                                <input type="file" id="photo-input-2" name="photo_2" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="previewPhoto(this, 'photo-img-2', 'photo-preview-2', 'photo-placeholder-2')" />
                                @error('photo_2')<p class="field-error text-[10px] mt-1">{{ $message }}</p>@enderror
                            </div>

                            {{-- Foto 3 --}}
                            <div>
                                <label id="photo-label-3" for="photo-input-3"
                                       class="flex flex-col items-center justify-center gap-1.5 rounded-xl border-2 border-dashed cursor-pointer transition-colors overflow-hidden relative"
                                       style="border-color:var(--line);aspect-ratio:1/1;background:var(--bg-soft)"
                                       onmouseenter="this.style.borderColor='var(--accent)'" onmouseleave="this.style.borderColor='var(--line)'">
                                    <div id="photo-preview-3" class="hidden w-full h-full absolute inset-0">
                                        <img id="photo-img-3" src="" alt="" class="w-full h-full object-cover" />
                                    </div>
                                    <div id="photo-placeholder-3" class="flex flex-col items-center text-center p-2">
                                        <svg class="w-5 h-5 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:var(--ink-mute)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="text-[9px]" style="color:var(--ink-mute)">Foto 3</span>
                                    </div>
                                </label>
                                <input type="file" id="photo-input-3" name="photo_3" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="previewPhoto(this, 'photo-img-3', 'photo-preview-3', 'photo-placeholder-3')" />
                                @error('photo_3')<p class="field-error text-[10px] mt-1">{{ $message }}</p>@enderror
                            </div>

                        </div>
                        <div class="text-[10px] text-center" style="color:var(--ink-mute)">Format didukung: JPG, PNG, WebP. Maks 2MB per file.</div>
                    </div>
                </div>

                {{-- Catatan --}}
                <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                    <div class="px-5 py-3.5" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                        <span class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Catatan</span>
                    </div>
                    <div class="p-5">
                        <textarea name="notes" rows="4" class="field-input" placeholder="Kondisi, kelengkapan, aksesori bawaan, dll...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                {{-- Estimasi Margin --}}
                <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                    <div class="px-5 py-3.5" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                        <span class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Estimasi Margin</span>
                    </div>
                    <div class="p-5">
                        <div class="text-2xl font-semibold font-mono tabular-nums" id="create-margin-amount" style="color:var(--ink-mute)">Rp 0</div>
                        <div class="text-xs mt-1" id="create-margin-pct" style="color:var(--ink-mute)">Isi harga beli &amp; harga jual</div>
                        <div class="mt-3 h-1.5 rounded-full overflow-hidden" style="background:var(--bg-soft)">
                            <div id="create-margin-bar" class="h-full rounded-full transition-all duration-300" style="width:0%;background:var(--success)"></div>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn-primary w-full" style="height:44px;font-size:14px">
                    Simpan Unit
                </button>
                <a href="{{ route('units.index') }}" class="btn-secondary w-full" style="height:44px;font-size:14px">
                    Batal
                </a>
            </div>

        </div>
    </form>
</div>

@include('components.money-format')
<script>
function previewPhoto(input, imgId, previewId, placeholderId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById(imgId).src = e.target.result;
            document.getElementById(previewId).classList.remove('hidden');
            document.getElementById(placeholderId).classList.add('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function calcCreateMargin() {
    const rawVal = id => parseInt((document.querySelector(`[name="${id}"]`)?.value || '').replace(/[^0-9]/g, ''), 10) || 0;
    const buy    = rawVal('purchase_price');
    const sell   = rawVal('selling_price');
    const margin = sell - buy;
    const amtEl  = document.getElementById('create-margin-amount');
    const pctEl  = document.getElementById('create-margin-pct');
    const bar    = document.getElementById('create-margin-bar');

    if (buy > 0 && sell > 0) {
        const pct   = Math.round((margin / sell) * 100);
        const color = margin >= 0 ? 'var(--success)' : 'var(--warn)';
        amtEl.textContent  = 'Rp ' + margin.toLocaleString('id-ID');
        amtEl.style.color  = color;
        pctEl.textContent  = (margin >= 0 ? 'Untung ' : 'Rugi ') + Math.abs(pct) + '% dari harga jual';
        pctEl.style.color  = color;
        bar.style.width    = Math.max(0, Math.min(100, Math.abs(pct))) + '%';
        bar.style.background = color;
    } else {
        amtEl.textContent = 'Rp 0';
        amtEl.style.color = 'var(--ink-mute)';
        pctEl.textContent = 'Isi harga beli & harga jual';
        pctEl.style.color = 'var(--ink-mute)';
        bar.style.width   = '0%';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[name="purchase_price"], [name="selling_price"]')
        .forEach(el => el.addEventListener('input', calcCreateMargin));
});
</script>
@endsection

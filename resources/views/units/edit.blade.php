@extends('layouts.app')
@section('title', 'Edit Unit HP')

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
                <h2 class="text-lg font-semibold leading-none" style="color:var(--ink)">Edit Unit HP</h2>
                <p class="text-xs mt-1 font-mono" style="color:var(--ink-mute)">
                    {{ $unit->model->brand->name ?? '—' }} {{ $unit->model->name ?? '—' }} · {{ $unit->ram }}/{{ $unit->rom }}
                </p>
            </div>
        </div>
        @if($unit->status->value !== 'sold')
        <form method="POST" action="{{ route('units.destroy', $unit) }}" onsubmit="return confirm('Hapus unit ini?')">
            @csrf @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-sm font-medium transition-colors"
                    style="background:#FFF5F5;color:var(--warn);border:1px solid #FEE2E2"
                    onmouseenter="this.style.background='#FEE2E2'" onmouseleave="this.style.background='#FFF5F5'">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Hapus Unit
            </button>
        </form>
        @endif
    </div>

    <form method="POST" action="{{ route('units.update', $unit) }}" enctype="multipart/form-data">
        @csrf @method('PUT')

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
                                <input type="text" name="brand_name" required
                                       value="{{ old('brand_name', $unit->model->brand->name ?? '') }}"
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
                                <input type="text" name="model_name" required
                                       value="{{ old('model_name', $unit->model->name ?? '') }}"
                                       class="field-input @error('model_name') error @enderror"
                                       placeholder="mis. Galaxy S24, iPhone 15 Pro" />
                                @error('model_name')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="field-label">Tipe</label>
                                <select name="unit_type" class="field-input">
                                    <option value="baru"   {{ old('unit_type', $unit->unit_type->value) === 'baru' ? 'selected' : '' }}>Baru</option>
                                    <option value="second" {{ old('unit_type', $unit->unit_type->value) === 'second' ? 'selected' : '' }}>Second</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">Grade</label>
                                <select name="grade" class="field-input @error('grade') error @enderror">
                                    <option value="">Pilih Grade (Optional)</option>
                                    <option value="A" {{ old('grade', $unit->grade) === 'A' ? 'selected' : '' }}>Grade A</option>
                                    <option value="B" {{ old('grade', $unit->grade) === 'B' ? 'selected' : '' }}>Grade B</option>
                                    <option value="C" {{ old('grade', $unit->grade) === 'C' ? 'selected' : '' }}>Grade C</option>
                                    <option value="D" {{ old('grade', $unit->grade) === 'D' ? 'selected' : '' }}>Grade D</option>
                                    <option value="E" {{ old('grade', $unit->grade) === 'E' ? 'selected' : '' }}>Grade E</option>
                                </select>
                                @error('grade')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="field-label">Status</label>
                                <select name="status" class="field-input">
                                    <option value="ready"    {{ old('status', $unit->status->value) === 'ready' ? 'selected' : '' }}>Ready</option>
                                    <option value="sold"     {{ old('status', $unit->status->value) === 'sold' ? 'selected' : '' }}>Terjual</option>
                                    <option value="returned" {{ old('status', $unit->status->value) === 'returned' ? 'selected' : '' }}>Retur</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">Warna</label>
                                <input type="text" name="color" value="{{ old('color', $unit->color) }}" class="field-input" placeholder="mis. Midnight Black" />
                            </div>
                            <div>
                                <label class="field-label">RAM</label>
                                <input type="text" name="ram" value="{{ old('ram', $unit->ram) }}" class="field-input" placeholder="mis. 8GB" />
                            </div>
                            <div>
                                <label class="field-label">ROM / Storage</label>
                                <input type="text" name="rom" value="{{ old('rom', $unit->rom) }}" class="field-input" placeholder="mis. 256GB" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Harga & Pembelian --}}
                <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                    <div class="px-5 py-3.5" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                        <span class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Harga & Pembelian</span>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="field-label">Harga Beli</label>
                                <div class="money-wrap">
                                    <span class="rp-prefix">Rp</span>
                                    <input type="text" name="purchase_price" value="{{ old('purchase_price', $unit->purchase_price) }}" required
                                           class="field-input money-input" placeholder="0" inputmode="numeric" />
                                </div>
                            </div>
                            <div>
                                <label class="field-label">Harga Jual</label>
                                <div class="money-wrap">
                                    <span class="rp-prefix">Rp</span>
                                    <input type="text" name="selling_price" value="{{ old('selling_price', $unit->selling_price) }}"
                                           class="field-input money-input" placeholder="0" inputmode="numeric" />
                                </div>
                            </div>
                            <div>
                                <label class="field-label">Tanggal Beli</label>
                                <input type="date" name="purchase_date" value="{{ old('purchase_date', $unit->purchase_date->toDateString()) }}" required class="field-input" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="field-label">IMEI</label>
                                <input type="text" name="imei" value="{{ old('imei', $unit->imei) }}" maxlength="20" class="field-input" />
                            </div>
                            <div>
                                <label class="field-label">Serial Number</label>
                                <input type="text" name="serial_number" value="{{ old('serial_number', $unit->serial_number) }}" class="field-input" />
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
                                    @if($unit->photo_path)
                                        <div id="photo-preview" class="w-full h-full absolute inset-0">
                                            <img id="photo-img" src="{{ Storage::url($unit->photo_path) }}" alt="" class="w-full h-full object-cover" />
                                        </div>
                                        <div id="photo-placeholder" class="hidden flex flex-col items-center text-center p-2">
                                            <svg class="w-5 h-5 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:var(--ink-mute)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span class="text-[9px]" style="color:var(--ink-mute)">Foto 1</span>
                                        </div>
                                    @else
                                        <div id="photo-preview" class="hidden w-full h-full absolute inset-0">
                                            <img id="photo-img" src="" alt="" class="w-full h-full object-cover" />
                                        </div>
                                        <div id="photo-placeholder" class="flex flex-col items-center text-center p-2">
                                            <svg class="w-5 h-5 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:var(--ink-mute)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span class="text-[9px]" style="color:var(--ink-mute)">Foto 1</span>
                                        </div>
                                    @endif
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
                                    @if($unit->photo_path_2)
                                        <div id="photo-preview-2" class="w-full h-full absolute inset-0">
                                            <img id="photo-img-2" src="{{ Storage::url($unit->photo_path_2) }}" alt="" class="w-full h-full object-cover" />
                                        </div>
                                        <div id="photo-placeholder-2" class="hidden flex flex-col items-center text-center p-2">
                                            <svg class="w-5 h-5 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:var(--ink-mute)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span class="text-[9px]" style="color:var(--ink-mute)">Foto 2</span>
                                        </div>
                                    @else
                                        <div id="photo-preview-2" class="hidden w-full h-full absolute inset-0">
                                            <img id="photo-img-2" src="" alt="" class="w-full h-full object-cover" />
                                        </div>
                                        <div id="photo-placeholder-2" class="flex flex-col items-center text-center p-2">
                                            <svg class="w-5 h-5 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:var(--ink-mute)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span class="text-[9px]" style="color:var(--ink-mute)">Foto 2</span>
                                        </div>
                                    @endif
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
                                    @if($unit->photo_path_3)
                                        <div id="photo-preview-3" class="w-full h-full absolute inset-0">
                                            <img id="photo-img-3" src="{{ Storage::url($unit->photo_path_3) }}" alt="" class="w-full h-full object-cover" />
                                        </div>
                                        <div id="photo-placeholder-3" class="hidden flex flex-col items-center text-center p-2">
                                            <svg class="w-5 h-5 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:var(--ink-mute)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span class="text-[9px]" style="color:var(--ink-mute)">Foto 3</span>
                                        </div>
                                    @else
                                        <div id="photo-preview-3" class="hidden w-full h-full absolute inset-0">
                                            <img id="photo-img-3" src="" alt="" class="w-full h-full object-cover" />
                                        </div>
                                        <div id="photo-placeholder-3" class="flex flex-col items-center text-center p-2">
                                            <svg class="w-5 h-5 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:var(--ink-mute)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span class="text-[9px]" style="color:var(--ink-mute)">Foto 3</span>
                                        </div>
                                    @endif
                                </label>
                                <input type="file" id="photo-input-3" name="photo_3" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="previewPhoto(this, 'photo-img-3', 'photo-preview-3', 'photo-placeholder-3')" />
                                @error('photo_3')<p class="field-error text-[10px] mt-1">{{ $message }}</p>@enderror
                            </div>

                        </div>
                        <div class="text-[10px] text-center mt-2" style="color:var(--ink-mute)">Pilih file baru untuk mengganti foto yang sudah ada.</div>
                    </div>
                </div>

                {{-- Catatan --}}
                <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                    <div class="px-5 py-3.5" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                        <span class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Catatan</span>
                    </div>
                    <div class="p-5">
                        <textarea name="notes" rows="4" class="field-input" placeholder="Kondisi, kelengkapan, dll...">{{ old('notes', $unit->notes) }}</textarea>
                    </div>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn-primary w-full" style="height:44px;font-size:14px">
                    Simpan Perubahan
                </button>
                <a href="{{ route('units.show', $unit) }}" class="btn-secondary w-full" style="height:44px;font-size:14px">
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
</script>
@endsection

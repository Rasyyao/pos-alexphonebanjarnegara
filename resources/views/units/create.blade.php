@extends('layouts.app')
@section('title', 'Tambah Unit HP')

@section('content')
    <div class="w-full">

        {{-- Header --}}
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('units.index') }}" class="flex items-center justify-center w-8 h-8 rounded-lg transition-colors"
                style="background:var(--bg-soft);color:var(--ink-mute)" onmouseenter="this.style.background='var(--line)'"
                onmouseleave="this.style.background='var(--bg-soft)'">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h2 class="text-lg font-semibold leading-none" style="color:var(--ink)">Tambah Unit HP</h2>
                <p class="text-xs mt-1" style="color:var(--ink-mute)">Isi detail unit yang akan ditambahkan ke stok</p>
            </div>
        </div>

        <form method="POST" action="{{ route('units.store') }}">
            @csrf

            <div class="grid lg:grid-cols-3 gap-5">

                {{-- Left column: main fields --}}
                <div class="lg:col-span-2 space-y-4">

                    {{-- Identitas Unit --}}
                    <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                        <div class="px-5 py-3.5" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                            <span class="text-[11px] font-medium uppercase tracking-widest font-mono"
                                style="color:var(--ink-mute)">Identitas Unit</span>
                        </div>
                        <div class="p-5 space-y-4">
                            {{-- Brand & Model separate --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="field-label">Brand <span style="color:var(--warn)">*</span></label>
                                    <select name="brand_name" required
                                        class="field-input @error('brand_name') error @enderror">
                                        <option value="">Pilih Brand</option>
                                        @foreach ($brands as $b)
                                            <option value="{{ $b->name }}"
                                                {{ old('brand_name') === $b->name ? 'selected' : '' }}>{{ $b->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('brand_name')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="field-label">Model <span style="color:var(--warn)">*</span></label>
                                    <input type="text" name="model_name" value="{{ old('model_name') }}" required
                                        class="field-input @error('model_name') error @enderror"
                                        placeholder="mis. Galaxy S24, iPhone 15 Pro" />
                                    @error('model_name')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="field-label">Tipe <span style="color:var(--warn)">*</span></label>
                                    <select name="unit_type" required class="field-input">
                                        <option value="baru" {{ old('unit_type', 'baru') === 'baru' ? 'selected' : '' }}>
                                            Baru</option>
                                        <option value="second" {{ old('unit_type') === 'second' ? 'selected' : '' }}>Second
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="field-label">Grade</label>
                                    <select name="grade" class="field-input @error('grade') error @enderror">
                                        <option value="">Pilih Grade (Optional)</option>
                                        <option value="A" {{ old('grade') === 'A' ? 'selected' : '' }}>Grade A
                                        </option>
                                        <option value="B" {{ old('grade') === 'B' ? 'selected' : '' }}>Grade B
                                        </option>
                                        <option value="C" {{ old('grade') === 'C' ? 'selected' : '' }}>Grade C
                                        </option>
                                        <option value="D" {{ old('grade') === 'D' ? 'selected' : '' }}>Grade D
                                        </option>
                                        <option value="E" {{ old('grade') === 'E' ? 'selected' : '' }}>Grade E
                                        </option>
                                    </select>
                                    @error('grade')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="field-label">Warna</label>
                                    <input type="text" name="color" value="{{ old('color') }}"
                                        placeholder="mis. Midnight Black" class="field-input" />
                                </div>
                                <div>
                                    <label class="field-label">RAM</label>
                                    <input type="text" name="ram" value="{{ old('ram') }}" placeholder="mis. 8GB"
                                        class="field-input" />
                                </div>
                                <div>
                                    <label class="field-label">ROM / Storage</label>
                                    <input type="text" name="rom" value="{{ old('rom') }}"
                                        placeholder="mis. 256GB" class="field-input" />
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Harga & Identitas --}}
                    <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                        <div class="px-5 py-3.5" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                            <span class="text-[11px] font-medium uppercase tracking-widest font-mono"
                                style="color:var(--ink-mute)">Harga & Pembelian</span>
                        </div>
                        <div class="p-5 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="field-label">Harga Beli <span style="color:var(--warn)">*</span></label>
                                    <div class="money-wrap">
                                        <span class="rp-prefix">Rp</span>
                                        <input type="text" name="purchase_price" value="{{ old('purchase_price') }}"
                                            required
                                            class="field-input money-input @error('purchase_price') error @enderror"
                                            placeholder="0" inputmode="numeric" />
                                    </div>
                                    @error('purchase_price')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="field-label">Tanggal Beli <span
                                            style="color:var(--warn)">*</span></label>
                                    <input type="date" name="purchase_date"
                                        value="{{ old('purchase_date', today()->toDateString()) }}" required
                                        class="field-input @error('purchase_date') error @enderror" />
                                    @error('purchase_date')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="field-label">IMEI</label>
                                    <input type="text" name="imei" value="{{ old('imei') }}" maxlength="20"
                                        class="field-input @error('imei') error @enderror" placeholder="15 digit IMEI" />
                                    @error('imei')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="field-label">Serial Number</label>
                                    <input type="text" name="serial_number" value="{{ old('serial_number') }}"
                                        class="field-input" />
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Right column: notes + submit --}}
                <div class="space-y-4">

                    {{-- Catatan --}}
                    <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                        <div class="px-5 py-3.5" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                            <span class="text-[11px] font-medium uppercase tracking-widest font-mono"
                                style="color:var(--ink-mute)">Catatan</span>
                        </div>
                        <div class="p-5">
                            <textarea name="notes" rows="4" class="field-input"
                                placeholder="Kondisi, kelengkapan, aksesori bawaan, dll...">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="btn-primary w-full" style="height:44px;font-size:14px">
                        Simpan Unit
                    </button>
                    <a href="{{ route('units.index') }}" class="btn-secondary w-full"
                        style="height:44px;font-size:14px">
                        Batal
                    </a>
                </div>

            </div>
        </form>
    </div>

    @include('components.money-format')
    <script>
        function calcCreateMargin() {
            const rawName = id => parseInt((document.querySelector(`[name="${id}"]`)?.value || '').replace(/[^0-9]/g, ''),
                10) || 0;
            const rawId = id => parseInt((document.getElementById(id)?.value || '').replace(/[^0-9]/g, ''), 10) || 0;
            const buy = rawName('purchase_price');
            const sell = rawId('create-est-jual');
            const margin = sell - buy;
            const amtEl = document.getElementById('create-margin-amount');
            const pctEl = document.getElementById('create-margin-pct');
            const bar = document.getElementById('create-margin-bar');

            if (buy > 0 && sell > 0) {
                const pct = Math.round((margin / sell) * 100);
                const color = margin >= 0 ? 'var(--success)' : 'var(--warn)';
                amtEl.textContent = 'Rp ' + margin.toLocaleString('id-ID');
                amtEl.style.color = color;
                pctEl.textContent = (margin >= 0 ? 'Untung ' : 'Rugi ') + Math.abs(pct) + '% dari harga jual';
                pctEl.style.color = color;
                bar.style.width = Math.max(0, Math.min(100, Math.abs(pct))) + '%';
                bar.style.background = color;
            } else {
                amtEl.textContent = 'Rp 0';
                amtEl.style.color = 'var(--ink-mute)';
                pctEl.textContent = 'Isi harga beli & harga jual estimasi';
                pctEl.style.color = 'var(--ink-mute)';
                bar.style.width = '0%';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelector('[name="purchase_price"]')
                ?.addEventListener('input', calcCreateMargin);
            document.getElementById('create-est-jual')
                ?.addEventListener('input', calcCreateMargin);
        });
    </script>
@endsection

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

    <form method="POST" action="{{ route('units.update', $unit) }}">
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
                                <label class="field-label">Tanggal Beli</label>
                                <input type="date" name="purchase_date" value="{{ old('purchase_date', $unit->purchase_date->toDateString()) }}" required class="field-input" />
                            </div>
                        </div>
                        {{-- Payment Method Selection --}}
                        <div x-data="{
                            method: '{{ old('purchase_cash', $unit->purchase_cash) > 0 && old('purchase_transfer', $unit->purchase_transfer) > 0 ? 'split' : old('purchase_payment_method', $unit->purchase_payment_method ?? 'cash') }}',
                            isSyncing: false,
                            init() {
                                // For edit, if it's already a split or has old inputs, we do not auto-override during init.
                                // But if it is 'cash' or 'transfer', we sync it.
                                this.$nextTick(() => {
                                    if (this.method !== 'split') {
                                        this.syncSplit();
                                    }
                                });
                                // Watch for purchase price input changes
                                const priceInput = document.querySelector('[name=&quot;purchase_price&quot;]');
                                if (priceInput) {
                                    priceInput.addEventListener('input', () => {
                                        this.syncSplit();
                                    });
                                }
                            },
                            getPrice() {
                                const val = document.querySelector('[name=&quot;purchase_price&quot;]')?.value || '';
                                return parseInt(val.replace(/[^0-9]/g, ''), 10) || 0;
                            },
                            syncSplit() {
                                if (this.isSyncing) return;
                                this.isSyncing = true;
                                const total = this.getPrice();
                                if (this.method === 'cash') {
                                    this.setVal('purchase_cash', total);
                                    this.setVal('purchase_transfer', 0);
                                } else if (this.method === 'transfer') {
                                    this.setVal('purchase_cash', 0);
                                    this.setVal('purchase_transfer', total);
                                }
                                this.isSyncing = false;
                            },
                            setVal(name, amount) {
                                const el = document.querySelector(`[name=&quot;${name}&quot;]`);
                                if (el) {
                                    el.value = amount ? amount.toLocaleString('id-ID') : '';
                                    // Dispatch input event to trigger any listeners
                                    el.dispatchEvent(new Event('input'));
                                }
                            },
                            onInputCash(val) {
                                if (this.isSyncing) return;
                                this.isSyncing = true;
                                const cash = parseInt((val || '').replace(/[^0-9]/g, ''), 10) || 0;
                                const total = this.getPrice();
                                const transfer = Math.max(0, total - cash);
                                this.setVal('purchase_transfer', transfer);
                                this.isSyncing = false;
                            },
                            onInputTransfer(val) {
                                if (this.isSyncing) return;
                                this.isSyncing = true;
                                const transfer = parseInt((val || '').replace(/[^0-9]/g, ''), 10) || 0;
                                const total = this.getPrice();
                                const cash = Math.max(0, total - transfer);
                                this.setVal('purchase_cash', cash);
                                this.isSyncing = false;
                            }
                        }" class="space-y-4">
                            <input type="hidden" name="purchase_payment_method" :value="method === 'split' ? 'cash' : method" />

                            <div>
                                <label class="field-label">Bayar Dari <span style="color:var(--warn)">*</span></label>
                                <div class="grid grid-cols-3 gap-3">
                                    {{-- Kas Tunai --}}
                                    <label class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-colors hover:bg-gray-50"
                                           :style="method === 'cash' ? 'border-color:var(--accent);background:rgba(37,99,235,0.03)' : 'border-color:var(--line)'">
                                        <input type="radio" value="cash" x-model="method" @change="syncSplit()"
                                               class="accent-blue-600" />
                                        <div>
                                            <div class="text-xs font-bold" style="color:var(--ink)">Kas Tunai</div>
                                            <div class="text-[10px] font-mono" style="color:var(--ink-mute)">Saldo: Rp {{ number_format($saldoKas, 0, ',', '.') }}</div>
                                        </div>
                                    </label>

                                    {{-- Transfer / ATM --}}
                                    <label class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-colors hover:bg-gray-50"
                                           :style="method === 'transfer' ? 'border-color:var(--accent);background:rgba(37,99,235,0.03)' : 'border-color:var(--line)'">
                                        <input type="radio" value="transfer" x-model="method" @change="syncSplit()"
                                               class="accent-blue-600" />
                                        <div>
                                            <div class="text-xs font-bold" style="color:var(--ink)">Transfer / ATM</div>
                                            <div class="text-[10px] font-mono" style="color:var(--ink-mute)">Saldo: Rp {{ number_format($saldoAtm, 0, ',', '.') }}</div>
                                        </div>
                                    </label>

                                    {{-- Gabungan --}}
                                    <label class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-colors hover:bg-gray-50"
                                           :style="method === 'split' ? 'border-color:var(--accent);background:rgba(37,99,235,0.03)' : 'border-color:var(--line)'">
                                        <input type="radio" value="split" x-model="method" @change="syncSplit()"
                                               class="accent-blue-600" />
                                        <div>
                                            <div class="text-xs font-bold" style="color:var(--ink)">Gabungan</div>
                                            <div class="text-[10px]" style="color:var(--ink-mute)">Cash + Transfer</div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            {{-- Split Inputs --}}
                            <div class="grid grid-cols-2 gap-4" x-show="method === 'split'" x-transition>
                                <div>
                                    <label class="field-label">Bayar Cash <span style="color:var(--warn)">*</span></label>
                                    <div class="money-wrap">
                                        <span class="rp-prefix">Rp</span>
                                        <input type="text" name="purchase_cash"
                                               value="{{ old('purchase_cash', $unit->purchase_cash) }}"
                                               @input="onInputCash($event.target.value)"
                                               class="field-input money-input @error('purchase_cash') error @enderror"
                                               placeholder="0" inputmode="numeric" />
                                    </div>
                                    @error('purchase_cash')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="field-label">Bayar Transfer <span style="color:var(--warn)">*</span></label>
                                    <div class="money-wrap">
                                        <span class="rp-prefix">Rp</span>
                                        <input type="text" name="purchase_transfer"
                                               value="{{ old('purchase_transfer', $unit->purchase_transfer) }}"
                                               @input="onInputTransfer($event.target.value)"
                                               class="field-input money-input @error('purchase_transfer') error @enderror"
                                               placeholder="0" inputmode="numeric" />
                                    </div>
                                    @error('purchase_transfer')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>
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

            {{-- Right column: notes + submit --}}
            <div class="space-y-4">

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

function calcEditMargin() {
    const rawName = id => parseInt((document.querySelector(`[name="${id}"]`)?.value || '').replace(/[^0-9]/g, ''), 10) || 0;
    const rawId   = id => parseInt((document.getElementById(id)?.value || '').replace(/[^0-9]/g, ''), 10) || 0;
    const buy    = rawName('purchase_price');
    const sell   = rawId('edit-est-jual');
    const margin = sell - buy;
    const amtEl  = document.getElementById('edit-margin-amount');
    const pctEl  = document.getElementById('edit-margin-pct');
    const bar    = document.getElementById('edit-margin-bar');

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
        pctEl.textContent = 'Isi harga jual estimasi';
        pctEl.style.color = 'var(--ink-mute)';
        bar.style.width   = '0%';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelector('[name="purchase_price"]')
        ?.addEventListener('input', calcEditMargin);
    document.getElementById('edit-est-jual')
        ?.addEventListener('input', calcEditMargin);
});
</script>
@endsection

@extends('layouts.app')
@section('title', 'Tambah Aksesoris')

@section('content')
    <div class="w-full">

        {{-- Header --}}
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('accessories.index') }}"
                class="flex items-center justify-center w-8 h-8 rounded-lg transition-colors"
                style="background:var(--bg-soft);color:var(--ink-mute)" onmouseenter="this.style.background='var(--line)'"
                onmouseleave="this.style.background='var(--bg-soft)'">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
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
                        <div class="px-5 py-3.5 flex items-center gap-2"
                            style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                                style="color:var(--ink-mute)">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span class="text-[11px] font-medium uppercase tracking-widest font-mono"
                                style="color:var(--ink-mute)">Informasi Produk</span>
                        </div>
                        <div class="p-5 space-y-4">
                            <div>
                                <label class="field-label">Nama Aksesoris <span style="color:var(--warn)">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" required
                                    placeholder="mis. Casing Silicone iPhone 15"
                                    class="field-input @error('name') error @enderror" autofocus />
                                @error('name')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="field-label">Kategori</label>
                                    <select name="category" class="field-input @error('category') error @enderror">
                                        <option value="">Pilih Kategori (Optional)</option>
                                        @php
                                            $defaultCategories = ['Case', 'Charger', 'Kabel', 'TWS', 'Anti Gores', 'Powerbank'];
                                            $allCategories = collect($defaultCategories)->merge($categories ?? [])->unique()->values();
                                        @endphp
                                        @foreach($allCategories as $cat)
                                            <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>
                                                {{ $cat }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="field-label">Stok Awal <span style="color:var(--warn)">*</span></label>
                                    <input type="number" name="stock_qty" value="{{ old('stock_qty', 0) }}" required
                                        min="0" class="field-input @error('stock_qty') error @enderror" />
                                    @error('stock_qty')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                        <div class="px-5 py-3.5 flex items-center gap-2"
                            style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                                style="color:var(--ink-mute)">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-[11px] font-medium uppercase tracking-widest font-mono"
                                style="color:var(--ink-mute)">Harga</span>
                        </div>
                        <div class="p-5 space-y-4">
                            <div>
                                <label class="field-label">Harga Beli <span style="color:var(--warn)">*</span></label>
                                <div class="money-wrap">
                                    <span class="rp-prefix">Rp</span>
                                    <input type="text" name="purchase_price" id="acc-buy"
                                        value="{{ old('purchase_price') }}" required
                                        class="field-input money-input @error('purchase_price') error @enderror"
                                        placeholder="0" inputmode="numeric" />
                                </div>
                                @error('purchase_price')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Payment Method Selection --}}
                            <div x-data="{
                                method: '{{ old('purchase_cash') && old('purchase_transfer') ? 'split' : old('purchase_payment_method', 'cash') }}',
                                isSyncing: false,
                                qty: {{ old('stock_qty', 0) }},
                                init() {
                                    this.$nextTick(() => {
                                        this.syncSplit();
                                    });
                                    // Watch for purchase price input changes
                                    const priceInput = document.querySelector('[name=&quot;purchase_price&quot;]');
                                    if (priceInput) {
                                        priceInput.addEventListener('input', () => {
                                            this.syncSplit();
                                        });
                                    }
                                    // Watch for stock_qty changes
                                    const qtyInput = document.querySelector('[name=&quot;stock_qty&quot;]');
                                    if (qtyInput) {
                                        qtyInput.addEventListener('input', (e) => {
                                            this.qty = parseInt(e.target.value, 10) || 0;
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
                                getRawVal(name) {
                                    const el = document.querySelector(`[name=&quot;${name}&quot;]`);
                                    return parseInt((el?.value || '').replace(/[^0-9]/g, ''), 10) || 0;
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
                                },
                                formatRupiah(amount) {
                                    return 'Rp ' + amount.toLocaleString('id-ID');
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
                                        <label class="field-label">Bayar Cash (per unit) <span style="color:var(--warn)">*</span></label>
                                        <div class="money-wrap">
                                            <span class="rp-prefix">Rp</span>
                                            <input type="text" name="purchase_cash"
                                                   value="{{ old('purchase_cash') }}"
                                                   @input="onInputCash($event.target.value)"
                                                   class="field-input money-input @error('purchase_cash') error @enderror"
                                                   placeholder="0" inputmode="numeric" />
                                        </div>
                                        <p class="text-[10px] mt-1 text-gray-500">
                                            Total Cash: <span class="font-mono font-bold text-gray-700" x-text="formatRupiah(getRawVal('purchase_cash') * qty)"></span>
                                        </p>
                                        @error('purchase_cash')
                                            <p class="field-error">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="field-label">Bayar Transfer (per unit) <span style="color:var(--warn)">*</span></label>
                                        <div class="money-wrap">
                                            <span class="rp-prefix">Rp</span>
                                            <input type="text" name="purchase_transfer"
                                                   value="{{ old('purchase_transfer') }}"
                                                   @input="onInputTransfer($event.target.value)"
                                                   class="field-input money-input @error('purchase_transfer') error @enderror"
                                                   placeholder="0" inputmode="numeric" />
                                        </div>
                                        <p class="text-[10px] mt-1 text-gray-500">
                                            Total Transfer: <span class="font-mono font-bold text-gray-700" x-text="formatRupiah(getRawVal('purchase_transfer') * qty)"></span>
                                        </p>
                                        @error('purchase_transfer')
                                            <p class="field-error">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
             <div class="space-y-5">
                    <div class="bg-white rounded-xl border p-4 space-y-2.5" style="border-color:var(--line)">
                        <button type="submit" class="btn-primary w-full" style="height:44px;font-size:14px">Simpan
                            Aksesoris</button>
                        <a href="{{ route('accessories.index') }}" class="btn-secondary w-full"
                            style="height:44px;font-size:14px">Batal</a>
                    </div>
                </div>

        </form>
    </div>

    @include('components.money-format')
    <script>
        function calcMargin() {
            const rawId = id => parseInt((document.getElementById(id)?.value || '').replace(/[^0-9]/g, ''), 10) || 0;
            const rawName = n => parseInt((document.querySelector(`[name="${n}"]`)?.value || '').replace(/[^0-9]/g, ''),
                10) || 0;
            const buy = rawName('purchase_price');
            const sell = rawId('acc-est-jual');
            const margin = sell - buy;
            const amtEl = document.getElementById('margin-amount');
            const pctEl = document.getElementById('margin-pct');
            const bar = document.getElementById('margin-bar');
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
            document.querySelector('[name="purchase_price"]')?.addEventListener('input', calcMargin);
            document.getElementById('acc-est-jual')?.addEventListener('input', calcMargin);
        });
    </script>
@endsection

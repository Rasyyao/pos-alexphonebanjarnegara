<div>
<div class="grid lg:grid-cols-3 gap-6">

    {{-- Left: Items + Payments --}}
    <div class="lg:col-span-2 space-y-4">

        @error('general')
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium"
             style="background:#FFF5F5;border:1px solid var(--warn);color:var(--warn)">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ $message }}
        </div>
        @enderror

        {{-- Tanggal + Nama Pembeli --}}
        <div class="bg-white rounded-xl border p-5" style="border-color:var(--line)">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="field-label">Tanggal Transaksi</label>
                    <input type="date" wire:model.live="saleDate"
                           class="field-input @error('saleDate') error @enderror"
                           style="width:100%" />
                    @error('saleDate')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="field-label">Nama Pembeli <span class="text-[10px] font-normal" style="color:var(--ink-mute)">(opsional)</span></label>
                    <input type="text" wire:model.live="customerName"
                           placeholder="Nama pelanggan..."
                           class="field-input" style="width:100%" />
                </div>
            </div>
            <div class="mt-4">
                <label class="field-label">Keterangan <span class="text-[10px] font-normal" style="color:var(--ink-mute)">(opsional)</span></label>
                <textarea wire:model.live="description" rows="2"
                          placeholder="Catatan atau keterangan tambahan transaksi..."
                          class="field-input" style="width:100%;resize:none"></textarea>
            </div>
        </div>

        {{-- Items --}}
        <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
            <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid var(--line)">
                <div class="flex items-center gap-2.5">
                    <h3 class="text-sm font-semibold" style="color:var(--ink)">Item Produk</h3>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-medium font-mono"
                          style="background:var(--bg-soft);color:var(--ink-mute)">{{ count($items) }}</span>
                </div>
                <button wire:click="addItem" type="button" class="btn-secondary"
                        style="height:32px;padding:0 14px;font-size:12px">
                    + Tambah Item
                </button>
            </div>

            <div>
                @foreach($items as $i => $item)
                <div class="p-5" style="{{ $i > 0 ? 'border-top:1px solid var(--line)' : '' }}">

                    {{-- Row header --}}
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-semibold font-mono flex-shrink-0"
                                  style="background:var(--bg-soft);color:var(--ink-mute)">{{ $i + 1 }}</span>

                            {{-- Type toggle --}}
                            <div class="flex rounded-lg overflow-hidden" style="border:1px solid var(--line)">
                                <button wire:click="$set('items.{{ $i }}.type', 'unit')" type="button"
                                        class="px-3 py-1.5 text-xs font-medium transition-colors"
                                        style="{{ ($item['type'] ?? 'unit') === 'unit' ? 'background:var(--accent);color:#fff' : 'background:transparent;color:var(--ink-soft)' }}">
                                    HP Unit
                                </button>
                                <button wire:click="$set('items.{{ $i }}.type', 'accessory')" type="button"
                                        class="px-3 py-1.5 text-xs font-medium transition-colors"
                                        style="{{ ($item['type'] ?? 'unit') === 'accessory' ? 'background:var(--accent);color:#fff' : 'background:transparent;color:var(--ink-soft)' }}">
                                    Aksesoris
                                </button>
                            </div>
                        </div>

                        @if(count($items) > 1)
                        <button wire:click="removeItem({{ $i }})" type="button"
                                class="w-7 h-7 rounded-lg flex items-center justify-center transition-colors"
                                style="color:var(--ink-mute)"
                                onmouseenter="this.style.color='var(--warn)';this.style.background='#FFF5F5'"
                                onmouseleave="this.style.color='var(--ink-mute)';this.style.background='transparent'">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                        @endif
                    </div>

                    {{-- Product selector --}}
                    @if(($item['type'] ?? 'unit') === 'unit')
                    <div class="mb-4">
                        <label class="field-label">Unit HP</label>
                        <select wire:model.live="items.{{ $i }}.unit_id" class="field-input">
                            <option value="">Pilih Unit HP (status ready)</option>
                            @foreach($readyUnits as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->model->brand->name }} {{ $unit->model->name }} — {{ $unit->ram }}/{{ $unit->rom }} {{ $unit->color }} ({{ ucfirst($unit->unit_type->value) }}{{ $unit->grade ? ' - Grade ' . $unit->grade : '' }})</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    <div class="grid grid-cols-3 gap-3 mb-4">
                        <div class="col-span-2">
                            <label class="field-label">Aksesoris</label>
                            <select wire:model.live="items.{{ $i }}.accessory_id" class="field-input">
                                <option value="">Pilih Aksesoris</option>
                                @foreach($accessories as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }} (stok: {{ $acc->stock_qty }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Qty</label>
                            <input type="number" wire:model.live="items.{{ $i }}.quantity" min="1"
                                   placeholder="1" class="field-input" />
                        </div>
                    </div>
                    @endif

                    {{-- Harga Jual --}}
                    @php $hargaBeli = $this->purchasePriceFor($item); @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="field-label" style="margin-bottom:0">Harga Jual</label>
                            @if($hargaBeli > 0)
                            <span class="text-[11px] font-mono" style="color:var(--ink-mute)">
                                Harga beli: Rp {{ number_format($hargaBeli, 0, ',', '.') }}
                            </span>
                            @endif
                        </div>
                        {{-- Visible formatted display + hidden wire:model for real value --}}
                        <div class="money-wrap">
                            <span class="rp-prefix">Rp</span>
                            <input type="text" inputmode="numeric" placeholder="0"
                                   id="sell-disp-{{ $i }}"
                                   value="{{ (float)($item['selling_price'] ?? 0) > 0 ? number_format((float)$item['selling_price'], 0, ',', '.') : '' }}"
                                   oninput="syncMoney(this, 'sell-raw-{{ $i }}')"
                                   class="field-input money-input" />
                            <input type="hidden" id="sell-raw-{{ $i }}"
                                   wire:model.live="items.{{ $i }}.selling_price"
                                   value="{{ (int)($item['selling_price'] ?? 0) }}" />
                        </div>
                        @error('items.{{ $i }}.selling_price')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                </div>
                @endforeach
            </div>
        </div>

        {{-- Payments --}}
        <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
            <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid var(--line)">
                <div class="flex items-center gap-2.5">
                    <h3 class="text-sm font-semibold" style="color:var(--ink)">Metode Pembayaran</h3>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-medium font-mono"
                          style="background:var(--bg-soft);color:var(--ink-mute)">{{ count($payments) }}</span>
                </div>
                <button wire:click="addPayment" type="button" class="btn-secondary"
                        style="height:32px;padding:0 14px;font-size:12px">
                    + Split Bayar
                </button>
            </div>

            <div>
                @foreach($payments as $i => $payment)
                <div class="p-5" style="{{ $i > 0 ? 'border-top:1px solid var(--line)' : '' }}">
                    <div class="flex items-start gap-4">

                        {{-- Left: method selector --}}
                        <div class="flex-shrink-0">
                            <label class="field-label">Metode</label>
                            <div class="flex rounded-lg overflow-hidden" style="border:1px solid var(--line)">
                                @foreach(['cash' => 'Cash', 'transfer' => 'Transfer', 'utang' => 'Utang'] as $val => $label)
                                <button wire:click="$set('payments.{{ $i }}.method', '{{ $val }}')" type="button"
                                        class="px-4 py-2.5 text-xs font-semibold transition-colors whitespace-nowrap"
                                        style="{{ ($payment['method'] ?? 'cash') === $val
                                            ? ($val === 'utang' ? 'background:var(--warn);color:#fff' : 'background:var(--accent);color:#fff')
                                            : 'background:transparent;color:var(--ink-soft)' }}">
                                    {{ $label }}
                                </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Right: amount input --}}
                        <div class="flex-1">
                            <label class="field-label">Jumlah Dibayar</label>
                            <div class="money-wrap">
                                <span class="rp-prefix">Rp</span>
                                <input type="text" inputmode="numeric" placeholder="0"
                                       id="pay-disp-{{ $i }}"
                                       value="{{ (float)($payment['amount'] ?? 0) > 0 ? number_format((float)$payment['amount'], 0, ',', '.') : '' }}"
                                       oninput="syncMoney(this, 'pay-raw-{{ $i }}')"
                                       class="field-input money-input" />
                                <input type="hidden" id="pay-raw-{{ $i }}"
                                       wire:model.live="payments.{{ $i }}.amount"
                                       value="{{ (int)($payment['amount'] ?? 0) }}" />
                            </div>
                            @error('payments.{{ $i }}.amount')<p class="field-error">{{ $message }}</p>@enderror
                        </div>

                        {{-- Remove --}}
                        @if(count($payments) > 1)
                        <div class="flex-shrink-0 pt-6">
                            <button wire:click="removePayment({{ $i }})" type="button"
                                    class="w-9 h-9 rounded-lg flex items-center justify-center transition-colors"
                                    style="color:var(--ink-mute)"
                                    onmouseenter="this.style.color='var(--warn)';this.style.background='#FFF5F5'"
                                    onmouseleave="this.style.color='var(--ink-mute)';this.style.background='transparent'">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                        @endif

                    </div>

                    {{-- Utang: debtor name — full width below the payment row --}}
                    @if(($payment['method'] ?? 'cash') === 'utang')
                    <div class="mt-3 pt-3" style="border-top:1px dashed var(--line)">
                        <div class="flex items-center gap-3 px-4 py-3 rounded-lg" style="background:#FFF5F5;border:1px solid #FEE2E2">
                            <svg class="w-4 h-4 flex-shrink-0" style="color:var(--warn)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <label class="text-xs font-semibold whitespace-nowrap flex-shrink-0" style="color:var(--warn)">Nama Pengutang</label>
                            <input type="text" wire:model.live="customerName"
                                   placeholder="Isi nama pelanggan yang berutang..."
                                   class="field-input flex-1"
                                   style="font-size:13px;height:36px" />
                            @if(trim($customerName))
                            <span class="text-xs font-semibold whitespace-nowrap flex-shrink-0" style="color:var(--success)">✓ {{ $customerName }}</span>
                            @else
                            <span class="text-[11px] whitespace-nowrap flex-shrink-0" style="color:var(--warn)">Wajib diisi</span>
                            @endif
                        </div>
                    </div>
                    @endif

                </div>
                @endforeach
            </div>
        </div>

    </div>

    {{-- Right: Summary --}}
    <div>
        <div class="bg-white rounded-xl border overflow-hidden sticky top-20" style="border-color:var(--line)">
            <div class="px-5 py-4" style="border-bottom:1px solid var(--line)">
                <h3 class="text-sm font-semibold" style="color:var(--ink)">Ringkasan</h3>
            </div>

            <div class="p-5 space-y-3">
                {{-- Total item --}}
                <div class="flex justify-between items-center text-sm">
                    <span style="color:var(--ink-soft)">Total Item</span>
                    <span class="font-semibold font-mono tabular-nums" style="color:var(--ink)">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>

                {{-- Per-payment breakdown --}}
                @foreach($payments as $p)
                @if(($p['amount'] ?? 0) > 0)
                <div class="flex justify-between items-center text-xs" style="color:var(--ink-mute)">
                    <span>
                        @if(($p['method'] ?? '') === 'cash') Cash
                        @elseif(($p['method'] ?? '') === 'transfer') Transfer
                        @else Utang
                        @endif
                    </span>
                    <span class="font-mono tabular-nums">Rp {{ number_format($p['amount'], 0, ',', '.') }}</span>
                </div>
                @endif
                @endforeach

                {{-- Total paid --}}
                <div class="flex justify-between items-center text-sm pt-1" style="border-top:1px solid var(--line)">
                    <span class="font-medium" style="color:var(--ink-soft)">Total Dibayar</span>
                    <span class="font-semibold font-mono tabular-nums" style="color:var(--ink)">Rp {{ number_format(collect($payments)->sum('amount'), 0, ',', '.') }}</span>
                </div>

                {{-- Remainder --}}
                <div class="pt-2" style="border-top:1px solid var(--line)">
                    @if($remainder > 0)
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-semibold" style="color:var(--warn)">Kurang Bayar</span>
                        <span class="text-sm font-bold font-mono tabular-nums" style="color:var(--warn)">Rp {{ number_format($remainder, 0, ',', '.') }}</span>
                    </div>
                    <p class="text-xs mt-1" style="color:var(--ink-mute)">Pembayaran belum cukup</p>
                    @elseif($remainder < 0)
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-semibold" style="color:var(--success)">Kembalian</span>
                        <span class="text-sm font-bold font-mono tabular-nums" style="color:var(--success)">Rp {{ number_format(abs($remainder), 0, ',', '.') }}</span>
                    </div>
                    @else
                    <div class="flex items-center gap-2 text-sm font-semibold" style="color:var(--success)">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Pembayaran pas
                    </div>
                    @endif
                </div>
            </div>

            <div class="px-5 pb-5">
                <button wire:click="submit" type="button"
                        wire:loading.attr="disabled"
                        class="btn-primary w-full relative"
                        style="height:44px;font-size:14px;display:flex;flex-direction:row;align-items:center;justify-content:center;gap:8px;{{ $remainder > 0 ? 'opacity:0.45;cursor:not-allowed' : '' }}"
                        {{ $remainder > 0 ? 'disabled' : '' }}>
                    {{-- Default label --}}
                    <span wire:loading.remove wire:target="submit" class="inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan Transaksi
                    </span>
                    {{-- Loading state --}}
                    <span wire:loading wire:target="submit" class="inline-flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-30" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                            <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Menyimpan...
                    </span>
                </button>
                @if($remainder > 0)
                <p class="text-center text-xs mt-2" style="color:var(--warn)">Lengkapi pembayaran untuk melanjutkan</p>
                @endif
            </div>
        </div>
    </div>

</div>


{{-- ═══════════════════════════════════════════════ --}}
{{-- SUCCESS MODAL (pure CSS, no Alpine)             --}}
{{-- ═══════════════════════════════════════════════ --}}
@if($savedSaleId)
<div id="sale-success-modal"
     style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:24px">

    {{-- Backdrop --}}
    <div style="position:absolute;inset:0;background:rgba(10,37,64,0.55);backdrop-filter:blur(6px)"></div>

    {{-- Modal card --}}
    <div style="position:relative;background:#fff;border-radius:20px;max-width:420px;width:100%;overflow:hidden;box-shadow:0 24px 64px rgba(10,37,64,.25);animation:modal-in 220ms cubic-bezier(.34,1.56,.64,1)">

        {{-- Green header --}}
        <div style="background:var(--success);padding:32px 28px 24px;text-align:center">
            <div style="width:60px;height:60px;background:rgba(255,255,255,.2);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px">
                <svg style="width:30px;height:30px;color:#fff" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h2 style="color:#fff;font-size:19px;font-weight:700;margin:0 0 6px">Transaksi Berhasil!</h2>
            <p style="color:rgba(255,255,255,.8);font-size:12px;margin:0;font-family:var(--font-mono);letter-spacing:.04em">{{ $savedInvoice }}</p>
        </div>

        {{-- Body --}}
        <div style="padding:24px 28px 28px">
            <p style="color:var(--ink-soft);font-size:13px;text-align:center;margin:0 0 20px;line-height:1.6">
                Transaksi tersimpan dengan status
                <strong style="color:var(--ink)">Pending</strong>.
                Menunggu verifikasi superadmin.
            </p>

            <div style="display:flex;flex-direction:column;gap:10px">
                {{-- Print --}}
                <a href="{{ route('sales.print', $savedSaleId) }}" target="_blank" rel="noopener"
                   style="display:flex;align-items:center;justify-content:center;gap:8px;height:46px;border-radius:12px;background:var(--ink);color:#fff;font-size:14px;font-weight:600;text-decoration:none">
                    <svg style="width:17px;height:17px" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak Struk
                </a>

                {{-- View detail --}}
                <a href="{{ route('sales.show', $savedSaleId) }}"
                   style="display:flex;align-items:center;justify-content:center;gap:8px;height:46px;border-radius:12px;background:var(--bg-soft);color:var(--ink);font-size:14px;font-weight:600;text-decoration:none;border:1px solid var(--line)">
                    <svg style="width:17px;height:17px" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Lihat Detail Transaksi
                </a>

                {{-- New --}}
                <a href="{{ route('sales.create') }}"
                   style="display:flex;align-items:center;justify-content:center;height:38px;border-radius:10px;color:var(--ink-mute);font-size:13px;font-weight:500;text-decoration:none">
                    + Buat Transaksi Baru
                </a>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes modal-in {
    from { opacity:0; transform:scale(.92) translateY(12px); }
    to   { opacity:1; transform:scale(1) translateY(0); }
}
</style>

<script>
    // Lock body scroll when modal is open
    document.body.style.overflow = 'hidden';
    // Restore on navigation away
    document.addEventListener('livewire:navigating', function() {
        document.body.style.overflow = '';
    }, { once: true });
</script>
@endif

</div>

@extends('layouts.app')
@section('title', 'Mutasi Dana')

@section('content')
<div class="space-y-6">

    {{-- ── Header ── --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold" style="color:var(--ink)">Mutasi Dana</h2>
            <p class="text-xs mt-0.5" style="color:var(--ink-mute)">Catat perpindahan uang antara Kas (tunai) dan ATM (transfer)</p>
        </div>
        <button id="btn-catat-mutasi" onclick="openMutasiForm()"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-all btn-primary"
                style="background:var(--accent);color:#fff">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
            Catat Mutasi
        </button>
    </div>

    {{-- ── Summary Cards ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        {{-- Saldo Kas --}}
        <div class="rounded-xl border p-5 card-lift sm:col-span-1 bg-white">
            <div class="flex items-center gap-2.5 mb-3">
                <span class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                      style="background:#22c55e25;color:#16a34a">
                    <svg class="w-4.5 h-4.5" style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </span>
                <div class="text-[11px] font-semibold uppercase tracking-widest font-mono" style="color:#15803d">Saldo Kas</div>
            </div>
            <div class="text-2xl font-bold font-mono tabular-nums" style="color:#14532d">
                Rp {{ number_format($saldoKas, 0, ',', '.') }}
            </div>
            <div class="text-[11px] mt-1.5" style="color:#16a34a">Uang tunai tersedia saat ini</div>
        </div>

        {{-- Saldo ATM --}}
        <div class="rounded-xl border p-5 card-lift sm:col-span-1 bg-white">
            <div class="flex items-center gap-2.5 mb-3">
                <span class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                      style="background:#3b82f625;color:#1d4ed8">
                    <svg class="w-4.5 h-4.5" style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </span>
                <div class="text-[11px] font-semibold uppercase tracking-widest font-mono" style="color:#1d4ed8">Saldo ATM</div>
            </div>
            <div class="text-2xl font-bold font-mono tabular-nums" style="color:#1e3a8a">
                Rp {{ number_format($saldoAtm, 0, ',', '.') }}
            </div>
            <div class="text-[11px] mt-1.5" style="color:#1d4ed8">Saldo rekening / transfer</div>
        </div>

        <!-- {{-- Total Setor ke ATM --}}
        <div class="bg-white rounded-xl border p-5 card-lift" style="border-color:var(--line)">
            <div class="flex items-center gap-2.5 mb-3">
                <span class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                      style="background:#f0fdf4;color:#16a34a">
                    <svg class="w-4.5 h-4.5" style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                    </svg>
                </span>
                <div class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:#16a34a">Total Setor ATM</div>
            </div>
            <div class="text-xl font-bold font-mono tabular-nums" style="color:#15803d">
                Rp {{ number_format($totalCashToAtm, 0, ',', '.') }}
            </div>
            <div class="text-[11px] mt-1" style="color:var(--ink-mute)">Kas → ATM sepanjang waktu</div>
        </div>

        {{-- Total Tarik Tunai --}}
        <div class="bg-white rounded-xl border p-5 card-lift" style="border-color:var(--line)">
            <div class="flex items-center gap-2.5 mb-3">
                <span class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                      style="background:#eff6ff;color:#1d4ed8">
                    <svg class="w-4.5 h-4.5" style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                    </svg>
                </span>
                <div class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:#1d4ed8">Total Tarik Tunai</div>
            </div>
            <div class="text-xl font-bold font-mono tabular-nums" style="color:#1e40af">
                Rp {{ number_format($totalAtmToCash, 0, ',', '.') }}
            </div>
            <div class="text-[11px] mt-1" style="color:var(--ink-mute)">ATM → Kas sepanjang waktu</div>
        </div> -->

    </div>

    {{-- ── Form + Table layout ── --}}
    <div class="grid lg:grid-cols-5 gap-6 items-start">

        {{-- ── Inline Form Panel ── --}}
        <div id="mutasi-form-panel"
             class="lg:col-span-2 bg-white rounded-2xl border overflow-hidden hidden lg:block"
             style="border-color:var(--line)">
            <div class="px-5 py-4" style="border-bottom:1px solid var(--line)">
                <h3 class="text-sm font-semibold" style="color:var(--ink)">Catat Mutasi Baru</h3>
                <p class="text-xs mt-0.5" style="color:var(--ink-mute)">Pilih arah dan masukkan jumlah</p>
            </div>
            <form method="POST" action="{{ route('fund-transfers.store') }}" class="p-5 space-y-5" id="mutasi-form">
                @csrf

                {{-- Direction cards --}}
                <div>
                    <label class="field-label">Arah Mutasi <span style="color:var(--warn)">*</span></label>
                    <div class="grid grid-cols-2 gap-3 mt-1">

                        {{-- Kas → ATM --}}
                        <label id="lbl-cash-to-atm"
                               class="mutasi-option flex flex-col items-center gap-2 p-4 rounded-xl border-2 cursor-pointer transition-all"
                               style="border-color:var(--line)">
                            <input type="radio" name="direction" value="cash_to_atm"
                                   class="sr-only" onchange="selectDir(this)" required>
                            <span class="w-9 h-9 rounded-lg flex items-center justify-center"
                                  style="background:#f0fdf4;color:#16a34a">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                </svg>
                            </span>
                            <div class="text-center">
                                <div class="text-xs font-bold" style="color:var(--ink)">Kas → ATM</div>
                                <div class="text-[10px] leading-tight mt-0.5" style="color:var(--ink-mute)">Setor tunai ke rekening</div>
                            </div>
                        </label>

                        {{-- ATM → Kas --}}
                        <label id="lbl-atm-to-cash"
                               class="mutasi-option flex flex-col items-center gap-2 p-4 rounded-xl border-2 cursor-pointer transition-all"
                               style="border-color:var(--line)">
                            <input type="radio" name="direction" value="atm_to_cash"
                                   class="sr-only" onchange="selectDir(this)" required>
                            <span class="w-9 h-9 rounded-lg flex items-center justify-center"
                                  style="background:#eff6ff;color:#1d4ed8">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                </svg>
                            </span>
                            <div class="text-center">
                                <div class="text-xs font-bold" style="color:var(--ink)">ATM → Kas</div>
                                <div class="text-[10px] leading-tight mt-0.5" style="color:var(--ink-mute)">Tarik tunai dari ATM</div>
                            </div>
                        </label>
                    </div>
                    @error('direction')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Amount --}}
                <div>
                    <label class="field-label">Jumlah <span style="color:var(--warn)">*</span></label>
                    <div class="money-wrap">
                        <span class="rp-prefix">Rp</span>
                        <input type="text" name="amount" id="mutasi-amount" required placeholder="0"
                               class="field-input money-input @error('amount') error @enderror"
                               inputmode="numeric"
                               style="height:48px;font-size:16px"
                               value="{{ old('amount') }}" />
                    </div>
                    <div class="flex flex-wrap gap-2 mt-2.5">
                        @foreach([500000=>'500rb', 1000000=>'1jt', 2000000=>'2jt', 5000000=>'5jt', 10000000=>'10jt'] as $v => $lbl)
                        <button type="button" onclick="setAmt({{ $v }})"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium font-mono transition-colors"
                                style="background:var(--bg-soft);color:var(--ink-soft)"
                                onmouseenter="this.style.background='var(--line)'" onmouseleave="this.style.background='var(--bg-soft)'">
                            {{ $lbl }}
                        </button>
                        @endforeach
                    </div>
                    @error('amount')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Keterangan --}}
                <div>
                    <label class="field-label">Keterangan</label>
                    <input type="text" name="description" placeholder="mis. Setor modal ke rekening BRI..."
                           class="field-input" value="{{ old('description') }}" />
                    @error('description')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tanggal --}}
                <div>
                    <label class="field-label">Tanggal <span style="color:var(--warn)">*</span></label>
                    <input type="date" name="transfer_date" required class="field-input"
                           value="{{ old('transfer_date', today()->toDateString()) }}" />
                    @error('transfer_date')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" id="btn-submit-form" class="btn-primary w-full">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Mutasi
                </button>
            </form>
        </div>

        {{-- ── History Table ── --}}
        <div class="lg:col-span-3 bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
            <div class="px-5 py-4 flex items-center justify-between" style="border-bottom:1px solid var(--line)">
                <div>
                    <h3 class="text-sm font-semibold" style="color:var(--ink)">Riwayat Mutasi</h3>
                    <p class="text-xs mt-0.5" style="color:var(--ink-mute)">{{ $fundTransfers->total() }} catatan total</p>
                </div>
            </div>

            @forelse($fundTransfers as $ft)
            <div class="px-5 py-3.5 flex items-center gap-4 transition-colors"
                 style="border-bottom:1px solid var(--line)"
                 onmouseenter="this.style.background='var(--bg-soft)'" onmouseleave="this.style.background=''">

                {{-- Direction icon --}}
                @if($ft->direction === 'cash_to_atm')
                    <span class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                          style="background:#f0fdf4;color:#16a34a">
                        <svg class="w-4.5 h-4.5" style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                        </svg>
                    </span>
                @else
                    <span class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                          style="background:#eff6ff;color:#1d4ed8">
                        <svg class="w-4.5 h-4.5" style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                        </svg>
                    </span>
                @endif

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        @if($ft->direction === 'cash_to_atm')
                            <span class="text-sm font-semibold" style="color:#16a34a">Kas → ATM</span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase" style="background:#f0fdf4;color:#16a34a">Setor</span>
                        @else
                            <span class="text-sm font-semibold" style="color:#1d4ed8">ATM → Kas</span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase" style="background:#eff6ff;color:#1d4ed8">Tarik</span>
                        @endif
                    </div>
                    <div class="text-xs font-mono mt-0.5 flex items-center gap-2 flex-wrap" style="color:var(--ink-mute)">
                        <span>{{ $ft->transfer_date->translatedFormat('d M Y') }}</span>
                        @if($ft->description)
                            <span style="color:var(--line)">·</span>
                            <span class="truncate">{{ $ft->description }}</span>
                        @endif
                        <span style="color:var(--line)">·</span>
                        <span>{{ $ft->creator->name ?? '-' }}</span>
                    </div>
                </div>

                {{-- Amount --}}
                <div class="text-right flex-shrink-0">
                    <div class="text-base font-bold font-mono tabular-nums"
                         style="color:{{ $ft->direction === 'cash_to_atm' ? '#15803d' : '#1e40af' }}">
                        Rp {{ number_format($ft->amount, 0, ',', '.') }}
                    </div>
                </div>

                {{-- Delete --}}
                <form method="POST" action="{{ route('fund-transfers.destroy', $ft) }}"
                      onsubmit="return confirm('Hapus catatan mutasi ini?')" class="flex-shrink-0">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors"
                            style="background:var(--bg-soft);color:var(--ink-mute)"
                            onmouseenter="this.style.background='#FEE2E2';this.style.color='var(--warn)'"
                            onmouseleave="this.style.background='var(--bg-soft)';this.style.color='var(--ink-mute)'">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
            </div>
            @empty
            <div class="py-16 flex flex-col items-center justify-center text-center px-6">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-4"
                     style="background:var(--bg-soft)">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"
                         style="color:var(--ink-mute)">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <p class="text-sm font-medium" style="color:var(--ink)">Belum ada catatan mutasi</p>
                <p class="text-xs mt-1" style="color:var(--ink-mute)">Gunakan form di sebelah kiri untuk mencatat perpindahan dana pertama</p>
            </div>
            @endforelse

            {{-- Pagination --}}
            @if($fundTransfers->hasPages())
            <div class="px-5 py-3 flex items-center justify-between" style="border-top:1px solid var(--line);background:var(--bg-soft)">
                <span class="text-xs font-mono" style="color:var(--ink-mute)">
                    {{ $fundTransfers->firstItem() }}–{{ $fundTransfers->lastItem() }} dari {{ $fundTransfers->total() }}
                </span>
                {{ $fundTransfers->links() }}
            </div>
            @endif
        </div>

    </div>
</div>

{{-- ── Mobile Modal (shows on small screens) ── --}}
<div id="mobile-mutasi-modal" class="fixed inset-0 z-[100] hidden overflow-y-auto lg:hidden"
     onclick="closeMutasiModal(event)">
    <div class="fixed inset-0" style="background:rgba(10,37,64,.5)"></div>
    <div class="relative min-h-full flex items-start justify-center px-4 pt-10 pb-10">
        <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden modal-pop"
             onclick="event.stopPropagation()">
            <div class="flex items-start justify-between px-6 py-5" style="border-bottom:1px solid var(--line)">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center"
                          style="background:#eff6ff;color:var(--accent)">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-base font-semibold leading-none" style="color:var(--ink)">Catat Mutasi Dana</h3>
                        <p class="text-xs mt-1.5" style="color:var(--ink-mute)">Kas ↔ ATM</p>
                    </div>
                </div>
                <button onclick="document.getElementById('mobile-mutasi-modal').classList.add('hidden');document.body.style.overflow=''"
                        class="w-8 h-8 flex items-center justify-center rounded-lg"
                        style="color:var(--ink-mute);background:var(--bg-soft)">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            {{-- Reuse same form, POST to same route --}}
            <form method="POST" action="{{ route('fund-transfers.store') }}" class="p-6 space-y-5">
                @csrf

                <div>
                    <label class="field-label">Arah Mutasi <span style="color:var(--warn)">*</span></label>
                    <div class="grid grid-cols-2 gap-3 mt-1">
                        <label class="mutasi-option-m flex flex-col items-center gap-2 p-4 rounded-xl border-2 cursor-pointer transition-all"
                               style="border-color:var(--line)">
                            <input type="radio" name="direction" value="cash_to_atm"
                                   class="sr-only" onchange="selectDirM(this)" required>
                            <span class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:#f0fdf4;color:#16a34a">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                </svg>
                            </span>
                            <div class="text-center">
                                <div class="text-xs font-bold" style="color:var(--ink)">Kas → ATM</div>
                                <div class="text-[10px] mt-0.5" style="color:var(--ink-mute)">Setor ke rekening</div>
                            </div>
                        </label>
                        <label class="mutasi-option-m flex flex-col items-center gap-2 p-4 rounded-xl border-2 cursor-pointer transition-all"
                               style="border-color:var(--line)">
                            <input type="radio" name="direction" value="atm_to_cash"
                                   class="sr-only" onchange="selectDirM(this)" required>
                            <span class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:#eff6ff;color:#1d4ed8">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                </svg>
                            </span>
                            <div class="text-center">
                                <div class="text-xs font-bold" style="color:var(--ink)">ATM → Kas</div>
                                <div class="text-[10px] mt-0.5" style="color:var(--ink-mute)">Tarik tunai</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="field-label">Jumlah <span style="color:var(--warn)">*</span></label>
                    <div class="money-wrap">
                        <span class="rp-prefix">Rp</span>
                        <input type="text" name="amount" id="mutasi-amount-m" required placeholder="0"
                               class="field-input money-input" inputmode="numeric" style="height:48px;font-size:16px" />
                    </div>
                    <div class="flex flex-wrap gap-2 mt-2.5">
                        @foreach([500000=>'500rb', 1000000=>'1jt', 2000000=>'2jt', 5000000=>'5jt'] as $v => $lbl)
                        <button type="button" onclick="setAmtM({{ $v }})"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium font-mono transition-colors"
                                style="background:var(--bg-soft);color:var(--ink-soft)"
                                onmouseenter="this.style.background='var(--line)'" onmouseleave="this.style.background='var(--bg-soft)'">{{ $lbl }}</button>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Keterangan</label>
                        <input type="text" name="description" placeholder="mis. Setor ke BRI..." class="field-input" />
                    </div>
                    <div>
                        <label class="field-label">Tanggal <span style="color:var(--warn)">*</span></label>
                        <input type="date" name="transfer_date" value="{{ today()->toDateString() }}" required class="field-input" />
                    </div>
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="submit" class="btn-primary flex-1">Simpan Mutasi</button>
                    <button type="button" onclick="document.getElementById('mobile-mutasi-modal').classList.add('hidden');document.body.style.overflow=''"
                            class="btn-secondary" style="padding:0 24px">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('components.money-format')
<style>
@keyframes modalPop { from { opacity:0; transform:translateY(-12px) scale(.98); } to { opacity:1; transform:translateY(0) scale(1); } }
.modal-pop { animation: modalPop 200ms cubic-bezier(.16,1,.3,1); }
</style>
<script>
// ── Direction selector (desktop form) ──────────────────────────────────────
function selectDir(radio) {
    document.querySelectorAll('.mutasi-option').forEach(el => {
        el.style.borderColor = 'var(--line)';
        el.style.background  = '';
    });
    const lbl = radio.closest('label');
    if (radio.value === 'cash_to_atm') {
        lbl.style.borderColor = '#16a34a';
        lbl.style.background  = '#f0fdf4';
        document.getElementById('btn-submit-form').style.background = '#16a34a';
    } else {
        lbl.style.borderColor = '#1d4ed8';
        lbl.style.background  = '#eff6ff';
        document.getElementById('btn-submit-form').style.background = '#1d4ed8';
    }
}

// ── Direction selector (mobile modal) ──────────────────────────────────────
function selectDirM(radio) {
    document.querySelectorAll('.mutasi-option-m').forEach(el => {
        el.style.borderColor = 'var(--line)';
        el.style.background  = '';
    });
    const lbl = radio.closest('label');
    if (radio.value === 'cash_to_atm') {
        lbl.style.borderColor = '#16a34a';
        lbl.style.background  = '#f0fdf4';
    } else {
        lbl.style.borderColor = '#1d4ed8';
        lbl.style.background  = '#eff6ff';
    }
}

// ── Amount helpers ──────────────────────────────────────────────────────────
function setAmt(v) {
    const el = document.getElementById('mutasi-amount');
    if (el) el.value = v.toLocaleString('id-ID');
}
function setAmtM(v) {
    const el = document.getElementById('mutasi-amount-m');
    if (el) el.value = v.toLocaleString('id-ID');
}

// ── Mobile modal ────────────────────────────────────────────────────────────
function openMutasiForm() {
    const isMobile = window.innerWidth < 1024; // lg breakpoint
    if (isMobile) {
        const modal = document.getElementById('mobile-mutasi-modal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    } else {
        const panel = document.getElementById('mutasi-form-panel');
        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        const first = panel.querySelector('input[type="radio"]');
        if (first) first.focus();
    }
}

function closeMutasiModal(e) {
    if (e.target === document.getElementById('mobile-mutasi-modal')) {
        document.getElementById('mobile-mutasi-modal').classList.add('hidden');
        document.body.style.overflow = '';
    }
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.getElementById('mobile-mutasi-modal').classList.add('hidden');
        document.body.style.overflow = '';
    }
});

// Money input formatting
document.querySelectorAll('.money-input').forEach(input => {
    input.addEventListener('input', function() {
        const raw = this.value.replace(/[^0-9]/g, '');
        this.value = raw ? parseInt(raw, 10).toLocaleString('id-ID') : '';
    });
});

// If there are validation errors, auto-open the form on mobile
@if($errors->any())
    if (window.innerWidth < 1024) {
        document.getElementById('mobile-mutasi-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
@endif
</script>
@endsection

@extends('layouts.app')
@section('title', 'Verifikasi Data')

@section('content')
<div class="w-full space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold" style="color:var(--ink)">Verifikasi Data</h2>
            <p class="text-xs mt-0.5" style="color:var(--ink-mute)">
                @if($isSuperadmin)
                    Kelola persetujuan transaksi penjualan, stok handphone baru, aksesoris, dan tutup buku harian.
                @else
                    Lihat status pengajuan tutup buku harian yang menunggu persetujuan superadmin.
                @endif
            </p>
        </div>
    </div>

    @php
        $activeTab = $isSuperadmin ? request('tab', 'sales') : 'closings';
    @endphp

    {{-- Tabs --}}
    <div class="flex items-center gap-0" style="border-bottom:2px solid var(--line)">
        @php
            $tabs = $isSuperadmin
                ? [
                    ['key' => 'sales',       'label' => 'Penjualan', 'count' => $pending->total()],
                    ['key' => 'units',       'label' => 'Stok HP',   'count' => $pendingUnits->total()],
                    ['key' => 'accessories', 'label' => 'Aksesoris', 'count' => $pendingAccessories->total()],
                    ['key' => 'closings',    'label' => 'Tutup Buku', 'count' => $pendingClosings->total()],
                  ]
                : [
                    ['key' => 'closings', 'label' => 'Tutup Buku', 'count' => $pendingClosings->total()],
                  ];
        @endphp
        @foreach($tabs as $tab)
            @php $isActive = $activeTab === $tab['key']; @endphp
            <a href="{{ route('sales.verify', ['tab' => $tab['key']]) }}"
               class="flex items-center gap-2 px-5 py-3 text-sm font-medium transition-colors relative"
               style="
                   color: {{ $isActive ? 'var(--accent)' : 'var(--ink-mute)' }};
                   border-bottom: {{ $isActive ? '2px solid var(--accent)' : '2px solid transparent' }};
                   margin-bottom: -2px;
               "
               onmouseenter="if(!{{ $isActive ? 'true' : 'false' }}) this.style.color='var(--ink)'"
               onmouseleave="if(!{{ $isActive ? 'true' : 'false' }}) this.style.color='var(--ink-mute)'">
                <span>{{ $tab['label'] }}</span>
                <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-[10px] font-bold font-mono leading-none"
                      style="{{ $isActive
                          ? 'background:var(--accent);color:#fff'
                          : 'background:var(--bg-soft);color:var(--ink-mute);border:1px solid var(--line)' }}">
                    {{ $tab['count'] }}
                </span>
            </a>
        @endforeach
    </div>

    {{-- Content Table based on active tab --}}
    <div class="bg-white rounded-xl border overflow-hidden shadow-sm" style="border-color:var(--line)">
        <div class="overflow-x-auto">
            @if($isSuperadmin && $activeTab === 'sales')
                {{-- Penjualan Table --}}
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background:var(--bg-soft); border-bottom:1px solid var(--line)">
                            <th class="text-left px-5 py-3.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Invoice</th>
                            <th class="text-left px-4 py-3.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Daftar Item</th>
                            <th class="text-left px-4 py-3.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Pembayaran</th>
                            <th class="text-right px-4 py-3.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Total & Laba</th>
                            <th class="w-56 px-5 py-3.5"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pending as $sale)
                        <tr class="transition-colors" style="border-bottom:1px solid var(--line)"
                            onmouseenter="this.style.background='var(--bg-soft)'" onmouseleave="this.style.background=''">
                            
                            {{-- Invoice --}}
                            <td class="px-5 py-4 align-top">
                                <div class="font-bold font-mono text-[13px]" style="color:var(--ink)">
                                    {{ $sale->invoice_number }}
                                </div>
                                <span class="inline-block px-2 py-0.5 mt-1 rounded-full text-[10px] font-medium" 
                                      style="background:#FFFBEB;color:#B45309">
                                    Pending
                                </span>
                                <div class="text-[11px] mt-2" style="color:var(--ink-mute)">
                                    {{ $sale->sale_date->isoFormat('D MMM YYYY') }}
                                    <span class="block mt-0.5 font-medium text-[10px]" style="color:var(--ink-soft)">Kasir: {{ $sale->creator->name ?? '—' }}</span>
                                </div>
                            </td>

                            {{-- Daftar Item --}}
                            <td class="px-4 py-4 align-top">
                                <div class="space-y-2">
                                    @foreach($sale->items as $item)
                                    <div class="text-xs">
                                        <div class="font-medium" style="color:var(--ink)">
                                            @if($item->unit_id)
                                                {{ $item->unit->model->brand->name ?? '—' }} {{ $item->unit->model->name ?? '' }}
                                                @if($item->unit->imei)
                                                    <span class="text-[10px] font-mono text-gray-500 block mt-0.5">IMEI: {{ $item->unit->imei }}</span>
                                                @elseif($item->unit->serial_number)
                                                    <span class="text-[10px] font-mono text-gray-500 block mt-0.5 font-medium">SN: {{ $item->unit->serial_number }}</span>
                                                @else
                                                    <span class="text-[10px] text-gray-400 block mt-0.5">No IMEI/SN</span>
                                                @endif
                                            @else
                                                {{ $item->accessory->name ?? '—' }}
                                                <span class="text-[11px] font-mono text-gray-400 font-semibold">×{{ $item->quantity }}</span>
                                            @endif
                                        </div>
                                        <div class="text-[10px] font-mono mt-0.5" style="color:var(--ink-mute)">
                                            Subtotal: Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                        </div>
                                    </div>
                                    @if(!$loop->last)
                                    <hr class="border-gray-100 my-1.5" />
                                    @endif
                                    @endforeach
                                </div>
                            </td>

                            {{-- Pembayaran --}}
                            <td class="px-4 py-4 align-top">
                                <div class="flex flex-col gap-1.5">
                                    @foreach($sale->payments as $payment)
                                    <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg text-[11px] font-medium w-fit"
                                         style="{{ $payment->method->value === 'utang' ? 'background:#FFF5F5;color:var(--warn)' : 'background:var(--bg-soft);color:var(--ink-soft)' }}">
                                        <span class="capitalize font-semibold">{{ $payment->method->value }}</span>
                                        <span class="font-mono">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </td>

                            {{-- Total & Laba --}}
                            <td class="px-4 py-4 text-right align-top">
                                <div class="font-bold font-mono text-sm" style="color:var(--ink)">
                                    Rp {{ number_format($sale->total_price, 0, ',', '.') }}
                                </div>
                                <div class="text-[11px] font-mono font-semibold mt-1" style="color:var(--success)">
                                    Laba: Rp {{ number_format($sale->profit, 0, ',', '.') }}
                                </div>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-5 py-4 align-top">
                                <div class="flex items-center justify-end gap-1.5">
                                    <form method="POST" action="{{ route('sales.approve', $sale) }}"
                                          onsubmit="return confirm('Approve transaksi {{ $sale->invoice_number }}? Stok akan berkurang.')">
                                        @csrf
                                        <button type="submit" class="btn-primary" style="height:32px;padding:0 12px;font-size:12px;border-radius:8px">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            Verifikasi
                                        </button>
                                    </form>
                                    <a href="{{ route('sales.show', $sale) }}"
                                       title="Lihat Detail"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg transition-colors"
                                       style="background:var(--bg-soft);color:var(--ink-soft)"
                                       onmouseenter="this.style.background='#E4E9F2'" onmouseleave="this.style.background='var(--bg-soft)'">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('sales.destroy', $sale) }}"
                                          onsubmit="return confirm('Hapus transaksi {{ $sale->invoice_number }}? Aksi ini tidak dapat dibatalkan.')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                title="Hapus"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg transition-colors"
                                                style="background:#FFF5F5;color:var(--warn)"
                                                onmouseenter="this.style.background='#FEE2E2'" onmouseleave="this.style.background='#FFF5F5'">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-5 py-14 text-center">
                                <div class="w-12 h-12 mx-auto rounded-full flex items-center justify-center mb-3" style="background:#F0FDF4;color:var(--success)">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <p class="text-sm font-medium" style="color:var(--ink)">Tidak ada transaksi menunggu verifikasi</p>
                                <p class="text-xs mt-1" style="color:var(--ink-mute)">Semua penjualan sudah diverifikasi.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif($isSuperadmin && $activeTab === 'units')
                {{-- Stok HP Table --}}
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background:var(--bg-soft); border-bottom:1px solid var(--line)">
                            <th class="text-left px-5 py-3.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Unit</th>
                            <th class="text-left px-4 py-3.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Tipe & Spesifikasi</th>
                            <th class="text-left px-4 py-3.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">IMEI & SN</th>
                            <th class="text-right px-4 py-3.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Harga Beli</th>
                            <th class="text-left px-4 py-3.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Tanggal & Inputter</th>
                            <th class="w-56 px-5 py-3.5"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingUnits as $unit)
                        <tr class="transition-colors" style="border-bottom:1px solid var(--line)"
                            onmouseenter="this.style.background='var(--bg-soft)'" onmouseleave="this.style.background=''">
                            <td class="px-5 py-4 align-top">
                                <div class="font-bold text-[13px]" style="color:var(--ink)">{{ $unit->model->brand->name ?? '—' }}</div>
                                <div class="text-xs font-mono mt-0.5" style="color:var(--ink-mute)">{{ $unit->model->name ?? '—' }}</div>
                            </td>
                            <td class="px-4 py-4 align-top text-xs">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium"
                                      style="{{ $unit->unit_type->value === 'baru' ? 'background:#EFF6FF;color:var(--accent)' : 'background:#FFFBEB;color:#B45309' }}">
                                    {{ ucfirst($unit->unit_type->value) }}
                                </span>
                                @if($unit->grade)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wide ml-1"
                                          style="background:var(--ink);color:#fff">
                                        Grade {{ $unit->grade }}
                                    </span>
                                @endif
                                <div class="font-mono mt-2" style="color:var(--ink-soft)">
                                    RAM/ROM: {{ $unit->ram }}/{{ $unit->rom }}<br>
                                    Warna: <span style="color:var(--ink-mute)">{{ $unit->color }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4 align-top text-xs font-mono" style="color:var(--ink-soft)">
                                @if($unit->imei)
                                    <div>IMEI: {{ $unit->imei }}</div>
                                @endif
                                @if($unit->serial_number)
                                    <div class="mt-0.5">SN: {{ $unit->serial_number }}</div>
                                @endif
                                @if(!$unit->imei && !$unit->serial_number)
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right align-top">
                                <div class="font-bold font-mono text-xs" style="color:var(--ink)">
                                    Rp {{ number_format($unit->purchase_price, 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] font-mono mt-1" style="color:var(--ink-mute)">
                                    @if($unit->purchase_payment_method === 'cash')
                                        Tunai
                                    @elseif($unit->purchase_payment_method === 'transfer')
                                        Transfer
                                    @else
                                        Split (C: {{ number_format($unit->purchase_cash, 0, ',', '.') }}, T: {{ number_format($unit->purchase_transfer, 0, ',', '.') }})
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4 align-top text-xs" style="color:var(--ink-soft)">
                                <div>{{ $unit->purchase_date->format('d M Y') }}</div>
                                <div class="text-[10px] mt-1" style="color:var(--ink-mute)">Oleh: {{ $unit->creator->name ?? '—' }}</div>
                            </td>
                            <td class="px-5 py-4 align-top">
                                <div class="flex items-center justify-end gap-1.5">
                                    <form method="POST" action="{{ route('units.approve', $unit) }}"
                                          onsubmit="return confirm('Setujui unit {{ $unit->model->brand->name ?? '' }} {{ $unit->model->name ?? '' }}? Unit akan masuk ke stok ready.')">
                                        @csrf
                                        <button type="submit" class="btn-primary" style="height:32px;padding:0 12px;font-size:12px;border-radius:8px">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            Setujui
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('units.destroy', $unit) }}"
                                          onsubmit="return confirm('Hapus/Tolak unit ini? Aksi ini tidak dapat dibatalkan.')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                title="Tolak / Hapus"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg transition-colors"
                                                style="background:#FFF5F5;color:var(--warn)"
                                                onmouseenter="this.style.background='#FEE2E2'" onmouseleave="this.style.background='#FFF5F5'">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-5 py-14 text-center">
                                <div class="w-12 h-12 mx-auto rounded-full flex items-center justify-center mb-3" style="background:#F0FDF4;color:var(--success)">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <p class="text-sm font-medium" style="color:var(--ink)">Tidak ada unit pending</p>
                                <p class="text-xs mt-1" style="color:var(--ink-mute)">Semua stok handphone baru sudah diverifikasi.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif($isSuperadmin && $activeTab === 'accessories')
                {{-- Aksesoris Table --}}
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background:var(--bg-soft); border-bottom:1px solid var(--line)">
                            <th class="text-left px-5 py-3.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Aksesoris</th>
                            <th class="text-left px-4 py-3.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Kategori</th>
                            <th class="text-center px-4 py-3.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Jumlah Qty</th>
                            <th class="text-right px-4 py-3.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Harga Beli</th>
                            <th class="text-left px-4 py-3.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Inputter</th>
                            <th class="w-56 px-5 py-3.5"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingAccessories as $acc)
                        <tr class="transition-colors" style="border-bottom:1px solid var(--line)"
                            onmouseenter="this.style.background='var(--bg-soft)'" onmouseleave="this.style.background=''">
                            <td class="px-5 py-4 align-top">
                                <div class="font-bold text-[13px]" style="color:var(--ink)">{{ $acc->name }}</div>
                            </td>
                            <td class="px-4 py-4 align-top text-xs">
                                <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700 font-mono text-[10px]">
                                    {{ $acc->category ?: 'Lainnya' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 align-top text-center text-xs font-mono font-bold" style="color:var(--ink-soft)">
                                {{ $acc->stock_qty }} pcs
                            </td>
                            <td class="px-4 py-4 text-right align-top">
                                <div class="font-bold font-mono text-xs" style="color:var(--ink)">
                                    Rp {{ number_format($acc->purchase_price, 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] font-mono mt-1" style="color:var(--ink-mute)">
                                    @if($acc->purchase_payment_method === 'cash')
                                        Tunai
                                    @elseif($acc->purchase_payment_method === 'transfer')
                                        Transfer
                                    @else
                                        Split (C: {{ number_format($acc->purchase_cash, 0, ',', '.') }}, T: {{ number_format($acc->purchase_transfer, 0, ',', '.') }})
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4 align-top text-xs" style="color:var(--ink-soft)">
                                <div>{{ $acc->created_at->format('d M Y') }}</div>
                                <div class="text-[10px] mt-1" style="color:var(--ink-mute)">{{ $acc->created_at->format('H:i') }}</div>
                            </td>
                            <td class="px-5 py-4 align-top">
                                <div class="flex items-center justify-end gap-1.5">
                                    <form method="POST" action="{{ route('accessories.approve', $acc) }}"
                                          onsubmit="return confirm('Setujui aksesoris {{ $acc->name }}? Aksesoris akan tersedia untuk dijual.')">
                                        @csrf
                                        <button type="submit" class="btn-primary" style="height:32px;padding:0 12px;font-size:12px;border-radius:8px">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            Setujui
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('accessories.destroy', $acc) }}"
                                          onsubmit="return confirm('Hapus/Tolak aksesoris ini? Aksi ini tidak dapat dibatalkan.')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                title="Tolak / Hapus"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg transition-colors"
                                                style="background:#FFF5F5;color:var(--warn)"
                                                onmouseenter="this.style.background='#FEE2E2'" onmouseleave="this.style.background='#FFF5F5'">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-5 py-14 text-center">
                                <div class="w-12 h-12 mx-auto rounded-full flex items-center justify-center mb-3" style="background:#F0FDF4;color:var(--success)">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <p class="text-sm font-medium" style="color:var(--ink)">Tidak ada aksesoris pending</p>
                                <p class="text-xs mt-1" style="color:var(--ink-mute)">Semua stok aksesoris baru sudah diverifikasi.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif($activeTab === 'closings')
                {{-- Tutup Buku Table --}}
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background:var(--bg-soft); border-bottom:1px solid var(--line)">
                            <th class="text-left px-5 py-3.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Tanggal</th>
                            <th class="text-right px-4 py-3.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Laba Bersih</th>
                            <th class="text-center px-4 py-3.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Selisih Kas</th>
                            <th class="text-center px-4 py-3.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Selisih ATM</th>
                            <th class="text-left px-4 py-3.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Catatan</th>
                            <th class="w-44 px-5 py-3.5 text-center text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingClosings as $closing)
                        @php
                            $diffCash = $closing->cash_physical - $closing->cash_system;
                            $diffAtm  = $closing->atm_physical  - $closing->atm_system;
                        @endphp
                        <tr class="transition-colors" style="border-bottom:1px solid var(--line)"
                            onmouseenter="this.style.background='var(--bg-soft)'" onmouseleave="this.style.background=''">

                            {{-- Tanggal --}}
                            <td class="px-5 py-4 align-middle">
                                <div class="font-semibold text-[13px]" style="color:var(--ink)">
                                    {{ \Carbon\Carbon::parse($closing->closing_date)->isoFormat('D MMM YYYY') }}
                                </div>
                                <div class="flex items-center gap-1 mt-1">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold" style="background:#FEF3C7;color:#92400E">Pending</span>
                                </div>
                                <div class="text-[11px] mt-1.5" style="color:var(--ink-mute)">
                                    oleh {{ $closing->closedBy->name ?? 'Admin' }} · {{ $closing->closed_at->format('H:i') }}
                                </div>
                            </td>

                            {{-- Laba Bersih --}}
                            <td class="px-4 py-4 align-middle text-right">
                                <div class="font-mono font-bold text-[13px]" style="color:var(--success)">
                                    Rp {{ number_format($closing->laba, 0, ',', '.') }}
                                </div>
                                <div class="text-[11px] font-mono mt-0.5" style="color:var(--ink-mute)">
                                    omzet Rp {{ number_format($closing->total_income, 0, ',', '.') }}
                                </div>
                            </td>

                            {{-- Selisih Kas --}}
                            <td class="px-4 py-4 align-middle text-center">
                                @if ($diffCash == 0)
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-bold" style="background:#D1FAE5;color:#065F46">Klop</span>
                                @elseif ($diffCash > 0)
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-bold font-mono" style="background:#D1FAE5;color:#065F46">+Rp {{ number_format($diffCash, 0, ',', '.') }}</span>
                                @else
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-bold font-mono" style="background:#FEE2E2;color:#991B1B">−Rp {{ number_format(abs($diffCash), 0, ',', '.') }}</span>
                                @endif
                                <div class="text-[10px] font-mono mt-1" style="color:var(--ink-mute)">
                                    fisik Rp {{ number_format($closing->cash_physical, 0, ',', '.') }}
                                </div>
                            </td>

                            {{-- Selisih ATM --}}
                            <td class="px-4 py-4 align-middle text-center">
                                @if ($diffAtm == 0)
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-bold" style="background:#D1FAE5;color:#065F46">Klop</span>
                                @elseif ($diffAtm > 0)
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-bold font-mono" style="background:#D1FAE5;color:#065F46">+Rp {{ number_format($diffAtm, 0, ',', '.') }}</span>
                                @else
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-bold font-mono" style="background:#FEE2E2;color:#991B1B">−Rp {{ number_format(abs($diffAtm), 0, ',', '.') }}</span>
                                @endif
                                <div class="text-[10px] font-mono mt-1" style="color:var(--ink-mute)">
                                    fisik Rp {{ number_format($closing->atm_physical, 0, ',', '.') }}
                                </div>
                            </td>

                            {{-- Catatan --}}
                            <td class="px-4 py-4 align-middle text-xs max-w-[160px]" style="color:var(--ink-mute)">
                                <span class="line-clamp-2">{{ $closing->notes ?: '—' }}</span>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-5 py-4 align-middle">
                                <div class="flex flex-col gap-1.5">
                                    <button type="button"
                                            data-date="{{ $closing->closing_date->toDateString() }}"
                                            data-cash="{{ $closing->cash_physical }}"
                                            data-atm="{{ $closing->atm_physical }}"
                                            data-expense="{{ $closing->expense_physical }}"
                                            data-notes="{{ $closing->notes }}"
                                            onclick="openEditClosingModal(this)"
                                            class="btn-secondary w-full flex items-center justify-center gap-1.5"
                                            style="height:32px;font-size:12px;border-radius:8px">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </button>
                                    @if($isSuperadmin)
                                    <form method="POST" action="{{ route('daily-closings.verify', $closing) }}" class="w-full">
                                        @csrf
                                        <button type="submit" class="btn-primary w-full flex items-center justify-center gap-1.5" style="height:32px;font-size:12px;border-radius:8px;background:var(--success)">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                            Verifikasi
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('daily-closings.revert', $closing) }}" class="w-full"
                                          onsubmit="return confirm('Kembalikan laporan ini ke draft? Tanggal transaksi akan dibuka kembali.')">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center justify-center gap-1.5 text-[11px] font-medium" style="height:28px;color:var(--ink-mute)">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 10v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                            </svg>
                                            Buka Kembali
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-5 py-14 text-center">
                                <div class="w-12 h-12 mx-auto rounded-full flex items-center justify-center mb-3" style="background:#EFF6FF;color:var(--accent)">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-medium" style="color:var(--ink)">Tidak ada laporan menunggu verifikasi</p>
                                <p class="text-xs mt-1" style="color:var(--ink-mute)">Semua laporan keuangan harian sudah diverifikasi & dikunci.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Pagination footer --}}
        @if($isSuperadmin && $activeTab === 'sales')
            <div class="px-5 py-3 flex items-center justify-between" style="border-top:1px solid var(--line);background:var(--bg-soft)">
                <span class="text-xs font-mono" style="color:var(--ink-mute)">
                    @if($pending->total() > 0)
                        Menampilkan {{ $pending->firstItem() }}–{{ $pending->lastItem() }} dari {{ $pending->total() }} transaksi
                    @else
                        0 transaksi
                    @endif
                </span>
                {{ $pending->links() }}
            </div>
        @elseif($isSuperadmin && $activeTab === 'units')
            <div class="px-5 py-3 flex items-center justify-between" style="border-top:1px solid var(--line);background:var(--bg-soft)">
                <span class="text-xs font-mono" style="color:var(--ink-mute)">
                    @if($pendingUnits->total() > 0)
                        Menampilkan {{ $pendingUnits->firstItem() }}–{{ $pendingUnits->lastItem() }} dari {{ $pendingUnits->total() }} unit
                    @else
                        0 unit
                    @endif
                </span>
                {{ $pendingUnits->links() }}
            </div>
        @elseif($isSuperadmin && $activeTab === 'accessories')
            <div class="px-5 py-3 flex items-center justify-between" style="border-top:1px solid var(--line);background:var(--bg-soft)">
                <span class="text-xs font-mono" style="color:var(--ink-mute)">
                    @if($pendingAccessories->total() > 0)
                        Menampilkan {{ $pendingAccessories->firstItem() }}–{{ $pendingAccessories->lastItem() }} dari {{ $pendingAccessories->total() }} aksesoris
                    @else
                        0 aksesoris
                    @endif
                </span>
                {{ $pendingAccessories->links() }}
            </div>
        @elseif($activeTab === 'closings')
            <div class="px-5 py-3 flex items-center justify-between" style="border-top:1px solid var(--line);background:var(--bg-soft)">
                <span class="text-xs font-mono" style="color:var(--ink-mute)">
                    @if($pendingClosings->total() > 0)
                        Menampilkan {{ $pendingClosings->firstItem() }}–{{ $pendingClosings->lastItem() }} dari {{ $pendingClosings->total() }} laporan
                    @else
                        0 laporan
                    @endif
                </span>
                {{ $pendingClosings->links() }}
            </div>
        @endif
    </div>

    {{-- ========== MODAL: Edit Tutup Buku ========== --}}
    <div id="modal-edit-closing" class="fixed inset-0 z-[120] hidden overflow-y-auto"
        onclick="if(event.target===this){closeEditClosingModal()}">
        <div class="fixed inset-0" style="background:rgba(10,37,64,.5)"></div>
        <div class="relative min-h-full flex items-center justify-center px-4 pt-12 pb-12">
            <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden modal-pop animate-fade-in"
                onclick="event.stopPropagation()">
                
                {{-- Modal Header --}}
                <div class="flex items-start justify-between px-6 py-5 border-b" style="border-color:var(--line)">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 bg-blue-50 text-blue-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-base font-semibold leading-none text-gray-900">Edit Data Penutupan Buku</h3>
                            <p class="text-xs text-gray-500 mt-1.5">Ubah realisasi kas fisik dan saldo ATM untuk tanggal terpilih</p>
                        </div>
                    </div>
                    <button onclick="closeEditClosingModal()"
                        class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors text-gray-400 hover:text-gray-600 bg-gray-50 hover:bg-gray-100">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Form --}}
                <form id="edit-closing-form" method="POST" action="{{ route('daily-closings.store') }}" class="p-6 space-y-5">
                    @csrf
                    <input type="hidden" name="closing_date" id="edit-closing-date-input" />

                    <div class="bg-gray-50 rounded-xl p-4 text-xs">
                        <div class="text-gray-500 font-semibold mb-0.5">Tanggal Buku</div>
                        <div class="font-bold text-gray-800 text-sm" id="edit-closing-date-display">-</div>
                    </div>

                    <div>
                        <label class="field-label text-[11px] font-semibold text-gray-700">Realisasi Uang Cash Fisik <span style="color:var(--warn)">*</span></label>
                        <div class="money-wrap mt-1">
                            <span class="rp-prefix">Rp</span>
                            <input type="text" name="cash_physical" id="edit-closing-cash-physical" required placeholder="0"
                                class="field-input money-input font-mono font-bold" inputmode="numeric"
                                style="height:44px;font-size:15px" />
                        </div>
                    </div>

                    <div>
                        <label class="field-label text-[11px] font-semibold text-gray-700">Realisasi Saldo ATM Fisik <span style="color:var(--warn)">*</span></label>
                        <div class="money-wrap mt-1">
                            <span class="rp-prefix">Rp</span>
                            <input type="text" name="atm_physical" id="edit-closing-atm-physical" required placeholder="0"
                                class="field-input money-input font-mono font-bold" inputmode="numeric"
                                style="height:44px;font-size:15px" />
                        </div>
                    </div>

                    <div>
                        <label class="field-label text-[11px] font-semibold text-gray-700">Realisasi Pengeluaran Fisik <span style="color:var(--warn)">*</span></label>
                        <div class="money-wrap mt-1">
                            <span class="rp-prefix">Rp</span>
                            <input type="text" name="expense_physical" id="edit-closing-expense-physical" required placeholder="0"
                                class="field-input money-input font-mono font-bold" inputmode="numeric"
                                style="height:44px;font-size:15px" />
                        </div>
                    </div>

                    <div>
                        <label class="field-label text-[11px] font-semibold text-gray-700">Catatan Penutupan Buku</label>
                        <textarea name="notes" id="edit-closing-notes" rows="3" class="field-input mt-1" placeholder="Tuliskan catatan rekonsiliasi atau selisih jika ada..."></textarea>
                    </div>

                    <div class="flex gap-3 pt-3 border-t" style="border-color:var(--line)">
                        <button type="submit" class="btn-primary flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold">
                            Simpan Perubahan
                        </button>
                        <button type="button" onclick="closeEditClosingModal()" class="btn-secondary px-6">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

@include('components.money-format')

<script>
    function openEditClosingModal(btn) {
        const date = btn.dataset.date;
        const cash = parseFloat(btn.dataset.cash || 0);
        const atm = parseFloat(btn.dataset.atm || 0);
        const expense = parseFloat(btn.dataset.expense || 0);
        const notes = btn.dataset.notes || '';
        
        document.getElementById('edit-closing-date-input').value = date;
        
        // Format date nicely
        const dateObj = new Date(date);
        const formattedDate = dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        document.getElementById('edit-closing-date-display').innerText = formattedDate;
        
        const cashInput = document.getElementById('edit-closing-cash-physical');
        const atmInput = document.getElementById('edit-closing-atm-physical');
        const expenseInput = document.getElementById('edit-closing-expense-physical');
        const notesInput = document.getElementById('edit-closing-notes');
        
        cashInput.value = Math.round(cash).toLocaleString('id-ID');
        atmInput.value = Math.round(atm).toLocaleString('id-ID');
        expenseInput.value = Math.round(expense).toLocaleString('id-ID');
        notesInput.value = notes;
        
        // Trigger input event to format
        cashInput.dispatchEvent(new Event('input', { bubbles: true }));
        atmInput.dispatchEvent(new Event('input', { bubbles: true }));
        expenseInput.dispatchEvent(new Event('input', { bubbles: true }));
        
        const modal = document.getElementById('modal-edit-closing');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        setTimeout(() => cashInput.focus(), 100);
    }

    function closeEditClosingModal() {
        const modal = document.getElementById('modal-edit-closing');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeEditClosingModal();
        }
    });
</script>
@endsection

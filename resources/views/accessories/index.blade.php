@extends('layouts.app')
@section('title', 'Stok Aksesoris')

@section('content')
<div class="flex items-center justify-between mb-5">
    <div>
        <h2 class="text-xl font-bold" style="color:var(--ink)">Stok Aksesoris</h2>
        <p class="text-xs mt-0.5" style="color:var(--ink-mute)">Kelola semua aksesoris</p>
    </div>
    <a href="{{ route('accessories.create') }}" class="btn-primary" style="height:36px;padding:0 16px;font-size:13px">
        + Tambah Aksesoris
    </a>
</div>

@if(auth()->user()->role->value !== 'superadmin')
    @php $pendingAcc = \App\Models\Accessory::where('status', 'pending')->latest()->get(); @endphp
    @if($pendingAcc->count() > 0)
        <div class="mb-4 bg-white rounded-xl overflow-hidden" style="border:1px solid var(--line)">
            <div class="px-4 py-2.5 flex items-center gap-2" style="background:var(--bg-soft);border-bottom:1px solid var(--line)">
                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#F59E0B">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-[11px] font-semibold uppercase tracking-widest font-mono" style="color:var(--ink-mute)">{{ $pendingAcc->count() }} aksesoris menunggu verifikasi</span>
            </div>
            @foreach($pendingAcc as $pa)
            <div class="px-4 py-3 flex items-center justify-between" style="{{ !$loop->last ? 'border-bottom:1px solid var(--line)' : '' }}">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold" style="color:var(--ink)">{{ $pa->name }}</span>
                    @if($pa->category)
                        <span class="text-[11px] font-mono" style="color:var(--ink-mute)">{{ $pa->category }}</span>
                    @endif
                </div>
                <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold" style="background:#FEF3C7;color:#B45309;border:1px solid #FDE68A">Pending</span>
            </div>
            @endforeach
        </div>
    @endif
@endif

<livewire:accessory-filter />
@endsection

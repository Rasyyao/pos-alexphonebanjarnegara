@extends('layouts.app')
@section('title', 'Stok HP')

@section('content')
<div x-data="{ showBrandModal: false }">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-xl font-bold" style="color:var(--ink)">Stok HP</h2>
            <p class="text-xs mt-0.5" style="color:var(--ink-mute)">Kelola semua unit smartphone</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="showBrandModal = true"
                    class="text-xs h-9 px-3 font-semibold rounded-lg transition-all flex items-center gap-1.5 border shadow-sm"
                    style="background:#F8FAFC;color:var(--ink-soft);border-color:var(--line)"
                    onmouseenter="this.style.background='var(--bg-soft)'" onmouseleave="this.style.background='#F8FAFC'">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-5 5a2 2 0 01-2.828 0l-7-7A2 2 0 013 9.828V5a2 2 0 012-2z"/>
                </svg>
                Kelola Brand
            </button>
            <a href="{{ route('units.create') }}" class="btn-primary" style="height:36px;padding:0 16px;font-size:13px">
                + Tambah Unit
            </a>
        </div>
    </div>

    @if(session('brand_success'))
        <div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium" style="background:#F0FDF4;color:var(--success);border:1px solid #BBF7D0">
            {{ session('brand_success') }}
        </div>
    @endif
    @if(session('brand_error'))
        <div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium" style="background:#FFF5F5;color:var(--warn);border:1px solid #FECACA">
            {{ session('brand_error') }}
        </div>
    @endif

    <livewire:stock-filter />

    {{-- Brand Management Modal --}}
    <div x-show="showBrandModal"
         class="fixed inset-0 z-[200]"
         x-cloak
         x-transition>
        <div class="fixed inset-0" style="background:rgba(0,0,0,0.5)" @click="showBrandModal = false"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl w-full max-w-sm border shadow-2xl overflow-hidden"
                 style="border-color:var(--line)" @click.stop>

                {{-- Header --}}
                <div class="px-6 py-4 border-b flex items-center justify-between" style="border-color:var(--line)">
                    <div>
                        <h3 class="font-bold text-sm" style="color:var(--ink)">Kelola Brand HP</h3>
                        <p class="text-[10px] mt-0.5" style="color:var(--ink-mute)">Tambah atau hapus brand dari daftar</p>
                    </div>
                    <button @click="showBrandModal = false" style="color:var(--ink-mute)" class="hover:text-gray-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Add Brand Form --}}
                <div class="px-6 pt-5 pb-4">
                    <form method="POST" action="{{ route('brands.store') }}" class="flex gap-2">
                        @csrf
                        <input type="text" name="name" required placeholder="Nama brand baru..." autocomplete="off"
                               class="field-input flex-1 @error('name') error @enderror"
                               style="height:36px;padding:0 10px;font-size:13px" />
                        <button type="submit" class="btn-primary" style="height:36px;padding:0 14px;font-size:13px;white-space:nowrap">
                            + Tambah
                        </button>
                    </form>
                    @error('name')
                        <p class="field-error mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Brand List --}}
                <div class="px-6 pb-5 space-y-1.5 max-h-64 overflow-y-auto">
                    @forelse($brands as $brand)
                    <div class="flex items-center justify-between px-3 py-2 rounded-lg" style="background:var(--bg-soft)">
                        <span class="text-sm font-medium" style="color:var(--ink)">{{ $brand->name }}</span>
                        <form method="POST" action="{{ route('brands.destroy', $brand) }}"
                              onsubmit="return confirm('Hapus brand {{ $brand->name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-[11px] font-semibold transition-colors"
                                    style="color:var(--warn)"
                                    onmouseenter="this.style.opacity='.7'" onmouseleave="this.style.opacity='1'">
                                Hapus
                            </button>
                        </form>
                    </div>
                    @empty
                    <p class="text-xs text-center py-4" style="color:var(--ink-mute)">Belum ada brand. Tambahkan di atas.</p>
                    @endforelse
                </div>

            </div>
        </div>
    </div>

</div>

{{-- Re-open modal if there's a validation error for brand name --}}
@if($errors->has('name'))
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('brandModal', { open: true });
    });
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            document.querySelector('[\\@click="showBrandModal = true"]')?.click();
        }, 50);
    });
</script>
@endif
@endsection

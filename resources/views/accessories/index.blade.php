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

<livewire:accessory-filter />
@endsection

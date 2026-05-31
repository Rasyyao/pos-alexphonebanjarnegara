@extends('layouts.app')
@section('title', 'Stok HP')

@section('content')
<div class="flex items-center justify-between mb-5">
    <div>
        <h2 class="text-xl font-bold" style="color:var(--ink)">Stok HP</h2>
        <p class="text-xs mt-0.5" style="color:var(--ink-mute)">Kelola semua unit smartphone</p>
    </div>
    <a href="{{ route('units.create') }}" class="btn-primary" style="height:36px;padding:0 16px;font-size:13px">
        + Tambah Unit
    </a>
</div>

<livewire:stock-filter />
@endsection

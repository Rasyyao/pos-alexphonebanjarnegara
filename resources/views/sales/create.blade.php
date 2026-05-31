@extends('layouts.app')
@section('title', 'Input Transaksi')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('sales.index') }}" class="text-[#7A8AA8] hover:text-[#0A2540]">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <h2 class="text-lg font-semibold">Input Transaksi Baru</h2>
</div>

<livewire:sale-form />

<script>
/**
 * syncMoney(displayEl, hiddenId)
 * Formats the visible input with thousand separators (id-ID locale)
 * and syncs the raw integer to the hidden wire:model.live input so
 * Livewire picks up the actual numeric value for its totals.
 */
function syncMoney(displayEl, hiddenId) {
    var raw = parseInt(displayEl.value.replace(/[^0-9]/g, ''), 10) || 0;
    // Format display
    displayEl.value = raw > 0 ? raw.toLocaleString('id-ID') : '';
    // Push raw value to the hidden Livewire-bound input
    var hidden = document.getElementById(hiddenId);
    if (hidden) {
        hidden.value = raw;
        // Dispatch 'input' so Livewire's wire:model.live picks it up
        hidden.dispatchEvent(new Event('input', { bubbles: true }));
    }
}
</script>
@endsection


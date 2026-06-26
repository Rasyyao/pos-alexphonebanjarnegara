@extends('layouts.app')
@section('title', 'Penjualan')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
    <div>
        <h2 class="text-xl font-bold" style="color:var(--ink)">Daftar Transaksi</h2>
        <p class="text-xs mt-0.5" style="color:var(--ink-mute)">Kelola dan pantau semua transaksi penjualan</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        @if(auth()->user()->role->value === 'superadmin')
        <div class="flex flex-wrap items-center gap-2">
            <div class="flex items-center px-2.5 rounded-lg border bg-[#F8FAFC]" style="border-color:var(--line);height:36px">
                <input type="date" id="export-date" value="{{ today()->toDateString() }}"
                       class="text-xs focus:outline-none bg-transparent"
                       style="border:none!important;outline:none!important;box-shadow:none!important;padding:0!important;background:transparent;color:var(--ink);width:115px;" />
            </div>
            <a id="btn-pdf-sales" href="#"
               onclick="(function(){ var d=document.getElementById('export-date').value; window.open('{{ route('reports.pdf', 'sales') }}?date='+d,'_blank'); return false; })()"
               class="text-xs h-9 px-3 font-semibold rounded-lg transition-all flex items-center gap-1.5 border shadow-sm"
               style="background:#EFF6FF;color:#1D4ED8;border-color:#BFDBFE;white-space:nowrap"
               onmouseenter="this.style.background='#DBEAFE'" onmouseleave="this.style.background='#EFF6FF'">
                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Export PDF
            </a>
            <a id="btn-xls-sales" href="#"
               onclick="(function(){ var d=document.getElementById('export-date').value; window.location='{{ route('reports.export', 'sales') }}?date='+d; return false; })()"
               class="text-xs h-9 px-3 font-semibold rounded-lg transition-all flex items-center gap-1.5 border shadow-sm"
               style="background:#F0FDF4;color:var(--success);border-color:#BBF7D0;white-space:nowrap"
               onmouseenter="this.style.background='#DCFCE7'" onmouseleave="this.style.background='#F0FDF4'">
                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel
            </a>
        </div>
        @endif
        <a href="{{ route('sales.create') }}" class="btn-primary" style="height:36px;padding:0 16px;font-size:13px;display:inline-flex;align-items:center;">
            + Input Transaksi
        </a>
    </div>
</div>

<livewire:transaction-filter />
@endsection

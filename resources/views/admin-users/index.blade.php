@extends('layouts.app')
@section('title', 'Kelola Admin')

@section('content')
<div class="flex items-center justify-between mb-5">
    <div>
        <h2 class="text-xl font-bold" style="color:var(--ink)">Kelola Admin</h2>
        <p class="text-xs mt-0.5" style="color:var(--ink-mute)">Kelola akun admin dan superadmin</p>
    </div>
    <a href="{{ route('admin-users.create') }}" class="btn-primary" style="height:36px;padding:0 16px;font-size:13px">
        + Tambah Admin
    </a>
</div>

<div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:var(--bg-soft); border-bottom:1px solid var(--line)">
                    <th class="text-left px-4 py-3 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Nama</th>
                    <th class="text-left px-4 py-3 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Username</th>
                    <th class="text-center px-4 py-3 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Role</th>
                    <th class="text-center px-4 py-3 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Status</th>
                    <th class="text-right px-4 py-3 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr class="group transition-colors" style="border-bottom:1px solid var(--line)"
                    onmouseenter="this.style.background='var(--bg-soft)'" onmouseleave="this.style.background=''">
                    <td class="px-4 py-3.5 font-medium" style="color:var(--ink)">{{ $user->name }}</td>
                    <td class="px-4 py-3.5 font-mono text-xs" style="color:var(--ink-soft)">{{ $user->username }}</td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-medium"
                              style="{{ $user->role->value === 'superadmin' ? 'background:var(--bg-soft);color:var(--ink);border:1px solid var(--line)' : 'background:var(--bg-soft);color:var(--ink-soft)' }}">
                            {{ $user->role->value }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-medium"
                              style="{{ $user->is_active ? 'background:#F0FDF4;color:var(--success)' : 'background:var(--bg-soft);color:var(--ink-mute)' }}">
                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5">
                        @if($user->id !== auth()->id())
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('admin-users.edit', $user) }}"
                               title="Edit"
                               class="inline-flex items-center justify-center w-7 h-7 rounded-lg transition-colors"
                               style="background:#EFF6FF;color:var(--accent)"
                               onmouseenter="this.style.background='#DBEAFE'" onmouseleave="this.style.background='#EFF6FF'">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('admin-users.destroy', $user) }}" onsubmit="return confirm('Hapus admin ini?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        title="Hapus"
                                        class="inline-flex items-center justify-center w-7 h-7 rounded-lg transition-colors"
                                        style="background:#FFF5F5;color:var(--warn)"
                                        onmouseenter="this.style.background='#FEE2E2'" onmouseleave="this.style.background='#FFF5F5'">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                        @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-medium float-right" style="background:var(--bg-soft);color:var(--ink-mute)">Akun saya</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-14 text-center text-sm" style="color:var(--ink-mute)">
                        Belum ada admin terdaftar.
                        <a href="{{ route('admin-users.create') }}" class="font-medium hover:underline ml-1" style="color:var(--accent)">Tambah sekarang</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->total() > 0)
    <div class="px-5 py-3 flex items-center justify-between" style="border-top:1px solid var(--line);background:var(--bg-soft)">
        <span class="text-xs font-mono" style="color:var(--ink-mute)">
            {{ $users->firstItem() ? $users->firstItem().'–'.$users->lastItem().' dari ' : '' }}{{ $users->total() }} admin
        </span>
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection

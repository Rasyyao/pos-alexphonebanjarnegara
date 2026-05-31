@extends('layouts.app')
@section('title', 'Edit Admin')

@section('content')
<div class="max-w-[480px]">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin-users.index') }}" style="color:var(--ink-mute)">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h2 class="text-lg font-semibold" style="color:var(--ink)">Edit Admin</h2>
        </div>
        @if($admin_user->id !== auth()->id())
        <form method="POST" action="{{ route('admin-users.destroy', $admin_user) }}" onsubmit="return confirm('Hapus admin ini?')">
            @csrf @method('DELETE')
            <button type="submit" class="text-sm font-medium" style="color:var(--warn)">Hapus</button>
        </form>
        @endif
    </div>

    <form method="POST" action="{{ route('admin-users.update', $admin_user) }}" class="bg-white rounded-xl border p-6 space-y-5" style="border-color:var(--line)">
        @csrf @method('PUT')
        <div>
            <label class="field-label">Nama Lengkap</label>
            <input type="text" name="name" value="{{ old('name', $admin_user->name) }}" required class="field-input" />
        </div>
        <div>
            <label class="field-label">Username</label>
            <input type="text" name="username" value="{{ old('username', $admin_user->username) }}" required
                   class="field-input @error('username') error @enderror" />
            @error('username')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="field-label">Password Baru <span class="text-xs font-normal" style="color:var(--ink-mute)">(kosongkan jika tidak diubah)</span></label>
            <input type="password" name="password" minlength="6" class="field-input" />
        </div>
        <div>
            <label class="field-label">Role</label>
            <select name="role" required class="field-input">
                <option value="admin"      {{ old('role', $admin_user->role->value) === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="superadmin" {{ old('role', $admin_user->role->value) === 'superadmin' ? 'selected' : '' }}>Superadmin</option>
            </select>
        </div>
        <div class="flex items-center gap-3">
            <input type="checkbox" id="is_active" name="is_active" value="1"
                   {{ old('is_active', $admin_user->is_active) ? 'checked' : '' }}
                   class="w-4 h-4 rounded border" style="border-color:var(--line);accent-color:var(--accent)" />
            <label for="is_active" class="text-sm cursor-pointer" style="color:var(--ink)">Akun Aktif</label>
        </div>
        <div class="flex justify-end pt-2">
            <button type="submit" class="btn-primary">Simpan</button>
        </div>
    </form>
</div>
@endsection

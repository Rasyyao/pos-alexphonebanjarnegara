@extends('layouts.app')
@section('title', 'Tambah Admin')

@section('content')
<div class="max-w-[480px]">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin-users.index') }}" style="color:var(--ink-mute)">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h2 class="text-lg font-semibold" style="color:var(--ink)">Tambah Admin</h2>
    </div>

    <form method="POST" action="{{ route('admin-users.store') }}" class="bg-white rounded-xl border p-6 space-y-5" style="border-color:var(--line)">
        @csrf
        <div>
            <label class="field-label">Nama Lengkap <span style="color:var(--warn)">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" required class="field-input @error('name') error @enderror" />
            @error('name')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="field-label">Username <span style="color:var(--warn)">*</span></label>
            <input type="text" name="username" value="{{ old('username') }}" required class="field-input @error('username') error @enderror" />
            @error('username')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="field-label">Password <span style="color:var(--warn)">*</span></label>
            <input type="password" name="password" required minlength="6" class="field-input @error('password') error @enderror" />
            @error('password')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="field-label">Role <span style="color:var(--warn)">*</span></label>
            <select name="role" required class="field-input">
                <option value="admin"      {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="superadmin" {{ old('role') === 'superadmin' ? 'selected' : '' }}>Superadmin</option>
            </select>
        </div>
        <div class="flex justify-end pt-2">
            <button type="submit" class="btn-primary">Simpan</button>
        </div>
    </form>
</div>
@endsection

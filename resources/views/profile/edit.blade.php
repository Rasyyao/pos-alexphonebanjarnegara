@extends('layouts.app')
@section('title', 'Profil Saya')

@section('content')
<div class="max-w-[560px] space-y-5">

    {{-- Update Profile --}}
    <div class="bg-white rounded-xl border p-6" style="border-color:var(--line)">
        <h3 class="text-[11px] font-medium uppercase tracking-widest font-mono mb-5" style="color:var(--ink-mute)">Informasi Profil</h3>

        <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>

        <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf @method('patch')

            <div>
                <label class="field-label" for="name">Nama</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}"
                       required autofocus autocomplete="name"
                       class="field-input @error('name') error @enderror" />
                @error('name')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="field-label" for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                       required autocomplete="username"
                       class="field-input @error('email') error @enderror" />
                @error('email')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-4 pt-1">
                <button type="submit" class="btn-primary">Simpan</button>
                @if(session('status') === 'profile-updated')
                <p class="text-sm" style="color:var(--success)">Tersimpan.</p>
                @endif
            </div>
        </form>
    </div>

    {{-- Update Password --}}
    <div class="bg-white rounded-xl border p-6" style="border-color:var(--line)">
        <h3 class="text-[11px] font-medium uppercase tracking-widest font-mono mb-5" style="color:var(--ink-mute)">Ubah Password</h3>

        <form method="post" action="{{ route('password.update') }}" class="space-y-4">
            @csrf @method('put')

            <div>
                <label class="field-label" for="current_password">Password Saat Ini</label>
                <input id="current_password" name="current_password" type="password"
                       autocomplete="current-password"
                       class="field-input @if($errors->updatePassword->has('current_password')) error @endif" />
                @if($errors->updatePassword->has('current_password'))
                    <p class="field-error">{{ $errors->updatePassword->first('current_password') }}</p>
                @endif
            </div>

            <div>
                <label class="field-label" for="password">Password Baru</label>
                <input id="password" name="password" type="password"
                       autocomplete="new-password"
                       class="field-input @if($errors->updatePassword->has('password')) error @endif" />
                @if($errors->updatePassword->has('password'))
                    <p class="field-error">{{ $errors->updatePassword->first('password') }}</p>
                @endif
            </div>

            <div>
                <label class="field-label" for="password_confirmation">Konfirmasi Password Baru</label>
                <input id="password_confirmation" name="password_confirmation" type="password"
                       autocomplete="new-password" class="field-input" />
            </div>

            <div class="flex items-center gap-4 pt-1">
                <button type="submit" class="btn-primary">Ubah Password</button>
                @if(session('status') === 'password-updated')
                <p class="text-sm" style="color:var(--success)">Password diperbarui.</p>
                @endif
            </div>
        </form>
    </div>

</div>
@endsection

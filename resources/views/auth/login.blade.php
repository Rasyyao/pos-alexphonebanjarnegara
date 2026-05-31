<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login — Alex Phone POS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full flex" style="font-family:'Inter',system-ui,sans-serif">

    {{-- Left panel --}}
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden flex-col justify-between p-12"
        style="background:linear-gradient(145deg,#0F172A 0%,#1E3A5F 60%,#1E40AF 100%)">
        <div class="relative z-10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-base font-bold text-slate-900"
                    style="background:linear-gradient(135deg,#60A5FA,#2563EB)">A</div>
                <div>
                    <div class="font-bold text-white text-lg leading-none">Alex Phone</div>
                    <div class="text-blue-300 text-xs font-mono tracking-wider">POS SYSTEM</div>
                </div>
            </div>
        </div>

        <div class="relative z-10 space-y-6">
            <div class="space-y-2">
                <h2 class="text-3xl font-bold text-white leading-tight">Kelola toko, lebih<br>efisien setiap hari.</h2>
                <p class="text-blue-200 text-sm leading-relaxed max-w-xs">Stok, penjualan, dan laporan keuangan — semua
                    di satu tempat.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach (['Stok HP', 'Aksesoris', 'Penjualan', 'Laporan', 'Keuangan'] as $feat)
                    <span
                        class="px-3 py-1.5 rounded-full text-xs font-medium bg-white/10 text-blue-100 border border-white/20">{{ $feat }}</span>
                @endforeach
            </div>
        </div>

        <div class="text-xs text-blue-300/60 relative z-10">© 2026 Alex Phone Banjarnegara</div>

        {{-- Decorative circles --}}
        <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full opacity-10"
            style="background:radial-gradient(circle,#60A5FA,transparent)"></div>
        <div class="absolute top-20 -right-10 w-40 h-40 rounded-full opacity-5"
            style="background:radial-gradient(circle,#fff,transparent)"></div>
    </div>

    {{-- Right: Login form --}}
    <div class="flex-1 flex items-center justify-center p-8 bg-slate-50">
        <div class="w-full max-w-sm">

            {{-- Mobile logo --}}
            <div class="lg:hidden text-center mb-8">
                <div class="w-12 h-12 rounded-2xl mx-auto flex items-center justify-center text-xl font-bold text-slate-900 mb-3"
                    style="background:linear-gradient(135deg,#60A5FA,#2563EB)">A</div>
                <h1 class="text-xl font-bold text-slate-900">Alex Phone POS</h1>
            </div>

            <div class="mb-8">
                <h2 class="text-2xl font-bold text-slate-900">Selamat datang!</h2>
                <p class="text-slate-500 text-sm mt-1">Masuk ke dashboard admin</p>
            </div>

            @if (session('status'))
                <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">
                    {{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="username" class="block text-sm font-semibold text-slate-700 mb-2">Username</label>
                    <input id="username" type="text" name="username" value="{{ old('username') }}" required
                        autofocus placeholder="mis. superadmin"
                        class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm transition-all focus:outline-none focus:border-blue-500 hover:border-slate-300 @error('username') border-red-400 @enderror" />
                    @error('username')
                        <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
                    <input id="password" type="password" name="password" required placeholder="••••••"
                        class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm transition-all focus:outline-none focus:border-blue-500 hover:border-slate-300 @error('password') border-red-400 @enderror" />
                    @error('password')
                        <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="remember" name="remember"
                            class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                        <span class="text-sm text-slate-600">Ingat saya</span>
                    </label>
                </div>

                <button type="submit"
                    class="w-full py-3.5 text-white rounded-xl text-sm font-bold transition-all active:scale-[.98] hover:shadow-lg hover:shadow-blue-200"
                    style="background:linear-gradient(135deg,#2563EB,#1D4ED8)">
                    Masuk ke Dashboard →
                </button>
            </form>

            <p class="text-center text-xs text-slate-400 mt-8">
                <a href="/" class="hover:text-slate-600 transition-colors">← Kembali ke halaman utama</a>
            </p>
        </div>
    </div>
</body>

</html>

<!DOCTYPE html>
<html lang="id" class="overflow-x-hidden">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Alex Phone POS — @yield('title', 'Dashboard')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @livewireStyles
    <style>
        /* ── DESIGN.MD TOKENS ── */
        :root {
            --bg: #FFFFFF;
            --bg-soft: #F3F4F6;
            --bg-elev: #FFFFFF;
            --ink: #0A2540;
            --ink-soft: #3D5374;
            --ink-mute: #7A8AA8;
            --line: #E4E9F2;
            --accent: #2563EB;
            --success: #10806B;
            --warn: #C2410C;
            --font-ui: "Inter", system-ui, -apple-system, sans-serif;
            --font-mono: "Satoshi", "Inter", system-ui, sans-serif;
        }

        body {
            font-family: var(--font-ui);
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
        }

        .font-mono {
            font-family: var(--font-mono);
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--line);
            border-radius: 4px;
        }

        /* Toast slide-in */
        @keyframes toastIn {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes toastOut {
            from {
                opacity: 1;
                transform: translateY(0);
            }

            to {
                opacity: 0;
                transform: translateY(12px);
            }
        }

        .toast-enter {
            animation: toastIn 280ms ease forwards;
        }

        .toast-leave {
            animation: toastOut 280ms ease forwards;
        }

        /* Livewire list item enter */
        @keyframes itemIn {
            from {
                opacity: 0;
                transform: translateY(-6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .item-enter {
            animation: itemIn 200ms ease forwards;
        }

        /* ── FORM INPUT SPEC (DESIGN.MD §12.5) ── */
        .field-input {
            width: 100%;
            height: 44px;
            padding: 0 12px;
            border-radius: 10px;
            border: 1px solid var(--line);
            background: var(--bg);
            font-family: var(--font-ui);
            font-size: 14px;
            color: var(--ink);
            outline: none;
            transition: border-color 180ms, box-shadow 180ms;
        }

        .field-input:hover {
            border-color: #C5D0DF;
        }

        .field-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
        }

        .field-input.error {
            border-color: var(--warn);
        }

        .field-input::placeholder {
            color: var(--ink-mute);
        }

        textarea.field-input {
            height: auto;
            padding: 10px 12px;
        }

        select.field-input {
            cursor: pointer;
            appearance: auto;
        }

        .field-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--ink);
            margin-bottom: 6px;
        }

        .field-error {
            font-size: 13px;
            color: var(--warn);
            margin-top: 5px;
        }

        /* Money input wrapper */
        .money-wrap {
            position: relative;
        }

        .money-wrap .rp-prefix {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 13px;
            color: var(--ink-mute);
            font-family: var(--font-mono);
            pointer-events: none;
        }

        .money-wrap .field-input {
            padding-left: 36px;
            font-family: var(--font-mono);
        }

        /* Primary button — radius 12px to match cards/tables, --accent bg */
        .btn-primary {
            display: inline-flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 44px;
            padding: 0 24px;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-family: var(--font-ui);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: filter 180ms, transform 180ms;
            white-space: nowrap;
        }

        .btn-primary:hover {
            filter: brightness(1.08);
            transform: translateY(-1px);
        }

        .btn-primary:active {
            transform: translateY(0);
            filter: brightness(.96);
        }

        /* Secondary button — refined with 1px soft border and elegant hover */
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 44px;
            padding: 0 24px;
            background: #ffffff;
            color: var(--ink-soft);
            border: 1px solid var(--line);
            border-radius: 12px;
            font-family: var(--font-ui);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background 180ms, border-color 180ms, color 180ms;
            white-space: nowrap;
        }

        .btn-secondary:hover {
            background: var(--bg-soft);
            color: var(--ink);
            border-color: #C5D0DF;
        }

        .card-lift {
            transition: transform 200ms ease, box-shadow 200ms ease;
        }

        .card-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px -6px rgba(10, 37, 64, .10) !important;
        }

        /* Premium soft shadow for cards and tables */
        .bg-white.rounded-xl.border {
            box-shadow: 0 1px 3px rgba(10, 37, 64, 0.03), 0 8px 24px rgba(10, 37, 64, 0.04);
            transition: transform 200ms ease, box-shadow 200ms ease;
        }

        /* Unify rounded-lg to 12px (rounded-xl) for absolute UI border-radius consistency */
        .rounded-lg {
            border-radius: 12px !important;
        }
    </style>
</head>

<body class="overflow-x-hidden bg-[#F3F4F6] text-[#0A2540]"
    style="font-family:'Inter',system-ui,-apple-system,sans-serif">

    <div class="flex min-h-screen">

        {{-- ── SIDEBAR (DESIGN.MD §12.1: white, flat, quiet) ── --}}
        <aside id="sidebar"
            class="fixed inset-y-0 left-0 z-50 w-64 flex flex-col bg-white border-r transition-transform duration-200 lg:translate-x-0 -translate-x-full"
            style="border-color:var(--line)">

            {{-- Logo --}}
            <div class="h-14 flex items-center px-5 flex-shrink-0 border-b" style="border-color:var(--line)">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-lg flex items-center justify-center text-[13px] font-bold text-white"
                        style="background:var(--accent)">A</span>
                    <div>
                        <div class="font-semibold text-[15px] leading-none" style="color:var(--ink)">Alex Phone</div>
                        <div class="text-[10px] uppercase tracking-widest mt-0.5 font-mono"
                            style="color:var(--ink-mute)">POS</div>
                    </div>
                </a>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 overflow-y-auto py-4 px-2 space-y-0.5">
                @php
                    $navItems = [
                        [
                            'route' => 'dashboard',
                            'label' => 'Dashboard',
                            'icon' =>
                                'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                        ],
                        [
                            'route' => 'units.index',
                            'label' => 'Stok HP',
                            'icon' => 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z',
                        ],
                        [
                            'route' => 'accessories.index',
                            'label' => 'Aksesoris',
                            'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                        ],
                        [
                            'route' => 'sales.index',
                            'label' => 'Penjualan',
                            'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
                        ],
                        [
                            'route' => 'debts.index',
                            'label' => 'Kelola Piutang',
                            'icon' =>
                                'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
                        ],
                    ];
                @endphp

                @foreach ($navItems as $item)
                    @php $active = request()->routeIs(str_replace('.index','',$item['route']).'*'); @endphp
                    <a href="{{ route($item['route']) }}"
                        class="group flex items-center gap-3 py-2 pr-3 rounded-r-lg text-sm font-medium transition-colors duration-150"
                        style="
                     padding-left: {{ $active ? '9px' : '12px' }};
                     color: {{ $active ? 'var(--accent)' : 'var(--ink-soft)' }};
                     background: {{ $active ? 'var(--bg-soft)' : 'transparent' }};
                     {{ $active ? 'border-left: 3px solid var(--accent);' : '' }}
                   ">
                        <svg class="w-[17px] h-[17px] flex-shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                        </svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach

                {{-- ── NESTED LAPORAN DROPDOWN (Alpine.js) ── --}}
                <div x-data="{ open: {{ request()->routeIs('reports.*') ? 'true' : 'false' }} }" class="space-y-0.5">
                    <button @click="open = !open"
                        class="group flex items-center justify-between w-full py-2 px-3 rounded-lg text-sm font-medium transition-colors duration-150"
                        style="color: var(--ink-soft); background: transparent;">
                        <div class="flex items-center gap-3">
                            <svg class="w-[17px] h-[17px] flex-shrink-0" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Laporan</span>
                        </div>
                        <svg class="w-3.5 h-3.5 transform transition-transform duration-200"
                            :class="open ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse x-cloak class="pl-7 space-y-1">
                        {{-- Laporan Keuangan --}}
                        @php $financeActive = request()->routeIs('reports.finance'); @endphp
                        <a href="{{ route('reports.finance') }}"
                            class="flex items-center py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors"
                            style="color: {{ $financeActive ? 'var(--accent)' : 'var(--ink-mute)' }}; background: {{ $financeActive ? 'var(--bg-soft)' : 'transparent' }}">
                            Laporan Keuangan
                        </a>

                        {{-- Laporan Stok --}}
                        @php $stockActive = request()->routeIs('reports.stock'); @endphp
                        <a href="{{ route('reports.stock') }}"
                            class="flex items-center py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors"
                            style="color: {{ $stockActive ? 'var(--accent)' : 'var(--ink-mute)' }}; background: {{ $stockActive ? 'var(--bg-soft)' : 'transparent' }}">
                            Laporan Stok
                        </a>

                        {{-- Arus Kas (Cashflow) (Superadmin Only) --}}
                        @if (auth()->user()->role->value === 'superadmin')
                            @php $cashflowActive = request()->routeIs('reports.cashflow'); @endphp
                            <a href="{{ route('reports.cashflow') }}"
                                class="flex items-center py-1.5 px-3 rounded-lg text-xs font-semibold transition-colors"
                                style="color: {{ $cashflowActive ? 'var(--accent)' : 'var(--ink-mute)' }}; background: {{ $cashflowActive ? 'var(--bg-soft)' : 'transparent' }}">
                                Arus Kas (Cashflow)
                            </a>
                        @endif
                    </div>
                </div>

                @if (auth()->user()->role->value === 'superadmin')
                    @php $pendingSales = \App\Models\Sale::where('status', 'pending')->count(); @endphp
                    <div class="pt-4 pb-1 px-3">
                        <span class="text-[10px] font-medium uppercase tracking-widest font-mono"
                            style="color:var(--ink-mute)">Superadmin</span>
                    </div>

                    @php $verifyActive = request()->routeIs('sales.verify'); @endphp
                    <a href="{{ route('sales.verify') }}"
                        class="group flex items-center gap-3 py-2 pr-3 rounded-r-lg text-sm font-medium transition-colors duration-150"
                        style="
                     padding-left: {{ $verifyActive ? '9px' : '12px' }};
                     color: {{ $verifyActive ? 'var(--accent)' : 'var(--ink-soft)' }};
                     background: {{ $verifyActive ? 'var(--bg-soft)' : 'transparent' }};
                     {{ $verifyActive ? 'border-left: 3px solid var(--accent);' : '' }}
                   ">
                        <svg class="w-[17px] h-[17px] flex-shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="flex-1">Verifikasi</span>
                        @if ($pendingSales > 0)
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold font-mono leading-none"
                                style="background:var(--warn);color:#fff">{{ $pendingSales }}</span>
                        @endif
                    </a>

                    @foreach ([['route' => 'admin-users.index', 'label' => 'Kelola Admin', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z']] as $item)
                        @php $active = request()->routeIs(str_replace('.index','',$item['route']).'*'); @endphp
                        <a href="{{ route($item['route']) }}"
                            class="group flex items-center gap-3 py-2 pr-3 rounded-r-lg text-sm font-medium transition-colors duration-150"
                            style="
                         padding-left: {{ $active ? '9px' : '12px' }};
                         color: {{ $active ? 'var(--accent)' : 'var(--ink-soft)' }};
                         background: {{ $active ? 'var(--bg-soft)' : 'transparent' }};
                         {{ $active ? 'border-left: 3px solid var(--accent);' : '' }}
                       ">
                            <svg class="w-[17px] h-[17px] flex-shrink-0" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                            </svg>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                @endif
            </nav>

            {{-- User footer: Clean Logout button --}}
            <div class="border-t p-3" style="border-color:var(--line)">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center gap-3 py-2 px-3 rounded-lg text-sm font-medium transition-colors"
                        style="color:var(--ink-mute);background:transparent"
                        onmouseenter="this.style.color='var(--warn)';this.style.background='var(--bg-soft)'"
                        onmouseleave="this.style.color='var(--ink-mute)';this.style.background='transparent'">
                        <svg class="w-[17px] h-[17px] flex-shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Keluar (Logout)
                    </button>
                </form>
            </div>
        </aside>

        {{-- Mobile overlay --}}
        <div id="sidebar-overlay" class="fixed inset-0 bg-black/30 z-40 lg:hidden hidden" onclick="closeSidebar()">
        </div>

        {{-- ── MAIN AREA ── --}}
        <div class="flex-1 lg:pl-64 flex flex-col min-h-screen min-w-0">

            {{-- Topbar (56px, flat, quiet) --}}
            <header class="sticky top-0 z-30 h-14 bg-white border-b flex items-center px-6 md:px-8 justify-between"
                style="border-color:var(--line)">
                <div class="flex items-center min-w-0 flex-1">
                    <button onclick="openSidebar()" class="lg:hidden mr-3 transition-colors"
                        style="color:var(--ink-mute)">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1 class="text-[15px] font-semibold truncate" style="color:var(--ink)">@yield('title', 'Dashboard')</h1>
                </div>
                <div class="flex items-center gap-4 flex-shrink-0">
                    <span class="hidden md:block text-xs font-mono"
                        style="color:var(--ink-mute)">{{ now()->locale('id')->isoFormat('D MMMM YYYY') }}</span>
                    <div class="h-8 w-px bg-gray-100 hidden md:block"></div>
                    <div class="flex items-center gap-2.5">
                        <div class="text-right">
                            <div class="text-xs font-semibold leading-tight" style="color:var(--ink)">
                                {{ auth()->user()->name }}</div>
                            <div class="text-[9px] uppercase tracking-wider font-mono font-bold leading-none mt-0.5"
                                style="color:var(--accent)">
                                {{ auth()->user()->role->value }}
                            </div>
                        </div>
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0"
                            style="background:var(--ink)">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    </div>
                </div>
            </header>

            {{-- Toasts --}}
            @if (session('success'))
                <div class="fixed bottom-6 right-6 z-50 toast-enter">
                    <div class="flex items-center gap-3 px-4 py-3 bg-white border rounded-lg shadow-md text-sm font-medium max-w-sm"
                        style="border-color:var(--success);color:var(--success)">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        {{ session('success') }}
                    </div>
                </div>
            @endif
            @if (session('error'))
                <div class="fixed bottom-6 right-6 z-50 toast-enter">
                    <div class="flex items-center gap-3 px-4 py-3 bg-white border rounded-lg shadow-md text-sm font-medium max-w-sm"
                        style="border-color:var(--warn);color:var(--warn)">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            <main class="flex-1" style="padding: 2.5rem 2rem 3rem;">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function openSidebar() {
            document.getElementById('sidebar').classList.remove('-translate-x-full');
            document.getElementById('sidebar-overlay').classList.remove('hidden');
        }

        function closeSidebar() {
            document.getElementById('sidebar').classList.add('-translate-x-full');
            document.getElementById('sidebar-overlay').classList.add('hidden');
        }

        // Auto-dismiss toasts after 4s
        document.querySelectorAll('.toast-enter').forEach(el => {
            setTimeout(() => {
                el.classList.remove('toast-enter');
                el.classList.add('toast-leave');
                setTimeout(() => el.remove(), 350);
            }, 4000);
        });
    </script>

    @livewireScripts
</body>

</html>

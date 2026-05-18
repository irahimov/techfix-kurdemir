<!DOCTYPE html>

<html lang="az" class="dark">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') — TechFix-Kurdemir</title>



    {{-- Google Fonts: Space Grotesk + IBM Plex Mono --}}

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">



    {{-- Tailwind CSS CDN --}}

    <script src="https://cdn.tailwindcss.com"></script>

    <script>

        tailwind.config = {

            darkMode: 'class',

            theme: {

                extend: {

                    fontFamily: {

                        sans: ['Syne', 'sans-serif'],

                        mono: ['IBM Plex Mono', 'monospace'],

                    },

                    colors: {

                        brand: {

                            50:   '#eff6ff',

                            400: '#60a5fa',

                            500: '#3b82f6',

                            600: '#2563eb',

                            900: '#1e3a5f',

                        },

                        surface: {

                            900: '#030712',

                            800: '#0a0f1e',

                            700: '#0f172a',

                            600: '#1e293b',

                            500: '#334155',

                        }

                    },

                    backdropBlur: {

                        xs: '2px',

                    },

                    animation: {

                        'slide-up':   'slideUp 0.4s ease-out',

                        'fade-in':    'fadeIn 0.3s ease-out',

                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',

                        'glow':       'glow 2s ease-in-out infinite alternate',

                    },

                    keyframes: {

                        slideUp:  { '0%': { opacity: 0, transform: 'translateY(16px)' }, '100%': { opacity: 1, transform: 'translateY(0)' } },

                        fadeIn:   { '0%': { opacity: 0 }, '100%': { opacity: 1 } },

                        glow:     { '0%': { boxShadow: '0 0 5px #3b82f620' }, '100%': { boxShadow: '0 0 20px #3b82f640, 0 0 40px #3b82f620' } },

                    }

                }

            }

        }

    </script>



    {{-- Alpine.js --}}

    <script defer src="https://unpkg.com/alpinejs@3.12.0/dist/cdn.min.js"></script>



    {{-- Chart.js --}}

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>



    <style>

        /* ── Base ── */

        *, *::before, *::after { box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {

            background: #030712;

            background-image:

                radial-gradient(ellipse 80% 60% at 50% -20%, rgba(59, 130, 246, 0.08) 0%, transparent 60%),

                radial-gradient(ellipse 60% 40% at 90% 100%, rgba(99, 102, 241, 0.05) 0%, transparent 50%);

            min-height: 100vh;

        }



        /* Alpine Cloak tərz təmizləməsi */

        [x-cloak] { display: none !important; }



        /* ── Glassmorphism Card ── */

        .glass {

            background: rgba(255, 255, 255, 0.04);

            backdrop-filter: blur(20px);

            -webkit-backdrop-filter: blur(20px);

            border: 1px solid rgba(255, 255, 255, 0.07);

        }

        .glass-strong {

            background: rgba(255, 255, 255, 0.07);

            backdrop-filter: blur(32px);

            -webkit-backdrop-filter: blur(32px);

            border: 1px solid rgba(255, 255, 255, 0.10);

        }



        /* ── Neon Glow Button ── */

        .btn-primary {

            background: linear-gradient(135deg, #3b82f6, #2563eb);

            box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);

            transition: all 0.25s ease;

        }

        .btn-primary:hover {

            box-shadow: 0 0 30px rgba(59, 130, 246, 0.5), 0 0 60px rgba(59, 130, 246, 0.2);

            transform: translateY(-1px);

        }



        /* ── Scrollbar ── */

        ::-webkit-scrollbar { width: 4px; height: 4px; }

        ::-webkit-scrollbar-track { background: transparent; }

        ::-webkit-scrollbar-thumb { background: rgba(59, 130, 246, 0.3); border-radius: 9999px; }

        ::-webkit-scrollbar-thumb:hover { background: rgba(59, 130, 246, 0.5); }



        /* ── Sidebar Active ── */

        .nav-item-active {

            background: rgba(59, 130, 246, 0.12) !important;

            border-left: 2px solid #3b82f6 !important;

            color: #93c5fd !important;

        }

        .nav-item {

            border-left: 2px solid transparent;

            transition: all 0.2s ease;

        }

        .nav-item:hover {

            background: rgba(255, 255, 255, 0.04);

            color: #e2e8f0;

        }



        /* ── Table ── */

        .tf-table th {

            background: rgba(255,255,255,0.03);

            font-size: 0.7rem;

            letter-spacing: 0.08em;

            text-transform: uppercase;

            color: #64748b;

            padding: 12px 16px;

        }

        .tf-table td { padding: 14px 16px; border-top: 1px solid rgba(255,255,255,0.04); }

        .tf-table tr:hover td { background: rgba(255,255,255,0.02); }



        /* ── Input ── */

        .tf-input {

            background: rgba(255, 255, 255, 0.05) !important;

            border: 1px solid rgba(255, 255, 255, 0.10) !important;

            color: #e2e8f0 !important;

            transition: border-color 0.2s, box-shadow 0.2s;

        }

        .tf-input:focus {

            outline: none !important;

            border-color: rgba(59, 130, 246, 0.5) !important;

            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;

        }



        /* ── Badge ── */

        .badge {

            display: inline-flex;

            align-items: center;

            padding: 3px 10px;

            border-radius: 9999px;

            font-size: 0.72rem;

            font-weight: 500;

            border: 1px solid;

        }

    </style>



    @stack('styles')

</head>

<body class="text-slate-200 font-sans antialiased">



    {{-- ── SIDEBAR ── --}}

    <aside class="fixed inset-y-0 left-0 z-30 w-64 glass-strong border-r border-white/[0.06] flex flex-col transition-transform duration-300 translate-x-0">

        {{-- Logo --}}

        <div class="flex items-center gap-3 px-6 py-5 border-b border-white/[0.06]">

            <div class="w-9 h-9 rounded-xl btn-primary flex items-center justify-center text-lg">⚡</div>

            <div>

                <p class="font-bold text-white text-sm tracking-wide">TechFix</p>

                <p class="text-xs text-slate-500 font-mono">Kürdəmir</p>

            </div>

        </div>

       

        {{-- Səhifə Keçidləri --}}

        <nav class="flex-1 px-4 py-6 space-y-1">

            @if(isset($is_customer_panel) && $is_customer_panel)

                {{-- MÜŞTƏRİ PANELİ SİDEBAR --}}

                <a href="{{ route('customer.panel') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl nav-item text-slate-400 text-sm font-medium {{ request()->routeIs('customer.panel') ? 'nav-item-active' : '' }}">

                    <span>🎫</span> Müraciətlərim

                </a>

                <a href="{{ route('tickets.create', ['from_customer_panel' => 1]) }}" class="flex items-center gap-3 px-4 py-3 rounded-xl nav-item text-slate-400 text-sm font-medium {{ request()->routeIs('tickets.create') ? 'nav-item-active' : '' }}">

                    <span>➕</span> Yeni Müraciət

                </a>

            @else

                {{-- ADMİN PANELİ SİDEBAR --}}

                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl nav-item text-slate-400 text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'nav-item-active' : '' }}">

                    <span>📊</span> İcmal Paneli

                </a>

                <a href="{{ route('tickets.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl nav-item text-slate-400 text-sm font-medium {{ request()->routeIs('tickets.*') ? 'nav-item-active' : '' }}">

                    <span>🎫</span> Müraciətlər

                </a>

                <a href="{{ route('admin.categories') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl nav-item text-slate-400 text-sm font-medium {{ request()->routeIs('admin.categories') ? 'nav-item-active' : '' }}">

                    <span>🗂️</span> Kateqoriyalar

                </a>

                <a href="{{ route('admin.users') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl nav-item text-slate-400 text-sm font-medium {{ request()->routeIs('admin.users') ? 'nav-item-active' : '' }}">

                    <span>👥</span> İstifadəçilər

                </a>

            @endif

        </nav>

    </aside>



    {{-- ── MAIN CONTENT AREA ── --}}

    <div class="pl-64 min-h-screen flex flex-col">

        {{-- Top Header --}}

        <header class="h-16 border-b border-white/[0.06] flex items-center justify-between px-8 glass bg-surface-900/50 sticky top-0 z-50">

            <div class="flex items-center gap-4">

                <h1 class="text-lg font-bold text-white font-mono">

                    @if(isset($is_customer_panel) && $is_customer_panel)

                        TechFix Müştəri Paneli

                    @else

                        TechFix İdarəetmə Paneli

                    @endif

                </h1>

            </div>

           

            {{-- Dropdown Menyu --}}

            <div class="flex items-center gap-4" x-data="{ open: false }">

                <div class="relative">

                    {{-- Düymənin özü də keçidə əsasən rəngini dəyişir --}}

                    <button @click="open = !open" class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/[0.05] border border-white/[0.08] text-sm font-mono text-slate-300 hover:bg-white/[0.08] transition-colors">

                        @if(isset($is_customer_panel) && $is_customer_panel)

                            <span class="text-blue-400">🔵 Müştəri Paneli</span>

                        @else

                            <span class="text-red-400">🔴 İdarəetmə Paneli</span>

                        @endif

                        <span class="text-xs text-slate-500">▼</span>

                    </button>

                   

                    {{-- Dropdown Elementləri --}}

                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 rounded-xl glass-strong border border-white/[0.08] shadow-xl py-1 z-[100] font-mono text-xs" x-cloak>

                       

                        {{-- İdarəetmə Paneli Linki --}}

                        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-slate-400 hover:bg-white/[0.04] hover:text-white transition-colors">

                            🔴 İdarəetmə Paneli

                        </a>



                        {{-- Müştəri Paneli Linki --}}

                        <a href="{{ route('customer.panel') }}" class="block px-4 py-2 text-slate-400 hover:bg-white/[0.04] hover:text-white transition-colors">

                            🔵 Müştəri Paneli

                        </a>



                        <div class="border-t border-white/[0.04] my-1"></div>

                       

                        <form method="POST" action="{{ route('logout') }}">

                            @csrf

                            <button type="submit" class="w-full text-left block px-4 py-2 text-red-400 hover:bg-red-500/10 transition-colors">

                                🚪 Çıxış Et

                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </header>



        {{-- Səhifə məzmunu --}}

        <main class="flex-1 p-8">

            @yield('content')

        </main>

        {{-- ── FOOTER ── --}}
        <footer class="border-t border-white/[0.04] mt-auto px-8 py-5 glass">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-xs font-mono text-slate-500">

                {{-- Sol: Branding --}}
                <div class="flex items-center gap-2">
                    <span class="text-blue-400">⚡</span>
                    <span class="text-slate-400 font-semibold">TechFix Kürdəmir</span>
                    <span class="text-slate-600">© {{ date('Y') }}</span>
                </div>

                {{-- Orta: Əlaqə --}}
                <div class="flex flex-wrap items-center justify-center gap-5">
                    <a href="tel:+994513041077" class="flex items-center gap-1.5 text-slate-500 hover:text-blue-400 transition-colors">
                        📞 +994 51 304 10 77
                    </a>
                    <a href="mailto:kenan.rhimov.07@gmail.com" class="flex items-center gap-1.5 text-slate-500 hover:text-blue-400 transition-colors">
                        ✉️ kenan.rhimov.07@gmail.com
                    </a>
                    <span class="flex items-center gap-1.5 text-slate-500">
                        📍 Kürdəmir Şəhəri
                    </span>
                </div>

                {{-- Sağ: İş saatları --}}
                <div class="flex items-center gap-1.5 text-slate-600">
                    🕐 B.e – Şənbə: 09:00 – 18:00
                </div>

            </div>
        </footer>

    </div>



    @stack('scripts')

</body>

</html>
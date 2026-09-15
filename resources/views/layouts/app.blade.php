<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Sahabat Sekolah' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-mark">✦</div>
            <div>
                <strong>Sahabat<br>Sekolah</strong>
                <small>
                    @if(session('user_role') === 'ADMIN')
                        Platform Admin
                    @elseif(session('user_role') === 'PRINCIPAL')
                        Monitoring Kepsek
                    @else
                        Dashboard Guru BK
                    @endif
                </small>
            </div>
        </div>

        <nav>
            @if(session('user_role') === 'ADMIN')
                <a class="nav-item {{ ($activeNav ?? '') === 'admin-dashboard' ? 'active' : '' }}" href="{{ route('admin.index') }}">
                    <i data-lucide="layout-dashboard"></i> Overview Admin
                </a>
                <a class="nav-item {{ ($activeNav ?? '') === 'admin-users' ? 'active' : '' }}" href="{{ route('admin.users') }}">
                    <i data-lucide="users"></i> Pengguna
                </a>
                <a class="nav-item {{ ($activeNav ?? '') === 'admin-schools' ? 'active' : '' }}" href="{{ route('admin.schools') }}">
                    <i data-lucide="building-2"></i> Sekolah
                </a>
                <a class="nav-item {{ ($activeNav ?? '') === 'admin-master-data' ? 'active' : '' }}" href="{{ route('admin.master-data') }}">
                    <i data-lucide="database"></i> Master Data
                </a>
            @elseif(session('user_role') === 'PRINCIPAL')
                <a class="nav-item {{ ($activeNav ?? '') === 'principal' ? 'active' : '' }}" href="{{ route('principal.index') }}">
                    <i data-lucide="shield-check"></i> Monitoring
                </a>
                <a class="nav-item {{ ($activeNav ?? '') === 'inbox' ? 'active' : '' }}" href="{{ route('reports.inbox') }}">
                    <i data-lucide="inbox"></i> Laporan Masuk
                </a>
                <a class="nav-item {{ ($activeNav ?? '') === 'cases' ? 'active' : '' }}" href="{{ route('reports.inbox', ['status' => 'IN_HANDLING']) }}">
                    <i data-lucide="briefcase-business"></i> Manajemen Kasus
                    @php
                        $activeCasesCount = \Illuminate\Support\Facades\DB::table('cases')->where('school_id', session('school_id'))->where('status', 'IN_HANDLING')->count();
                    @endphp
                    @if($activeCasesCount > 0)
                        <b>{{ $activeCasesCount }}</b>
                    @endif
                </a>
                <a class="nav-item {{ ($activeNav ?? '') === 'statistics' ? 'active' : '' }}" href="{{ route('statistics.index') }}">
                    <i data-lucide="bar-chart-3"></i> Statistik
                </a>
                <a class="nav-item {{ ($activeNav ?? '') === 'notifications' ? 'active' : '' }}" href="{{ route('notifications.index') }}">
                    <i data-lucide="bell"></i> Notifikasi
                </a>
            @else
                <a class="nav-item {{ ($activeNav ?? '') === 'dashboard' ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i data-lucide="layout-dashboard"></i> Dashboard
                </a>
                <a class="nav-item {{ ($activeNav ?? '') === 'inbox' ? 'active' : '' }}" href="{{ route('reports.inbox') }}">
                    <i data-lucide="inbox"></i> Laporan Masuk
                    <b>{{ $reportsCount ?? \Illuminate\Support\Facades\DB::table('reports')->count() }}</b>
                </a>
                <a class="nav-item {{ ($activeNav ?? '') === 'cases' ? 'active' : '' }}" href="{{ route('reports.inbox', ['status' => 'IN_HANDLING']) }}">
                    <i data-lucide="briefcase-business"></i> Manajemen Kasus
                    @php
                        $activeCasesCount = \Illuminate\Support\Facades\DB::table('cases')->where('school_id', session('school_id'))->where('status', 'IN_HANDLING')->count();
                    @endphp
                    @if($activeCasesCount > 0)
                        <b>{{ $activeCasesCount }}</b>
                    @endif
                </a>
                <a class="nav-item {{ ($activeNav ?? '') === 'statistics' ? 'active' : '' }}" href="{{ route('statistics.index') }}">
                    <i data-lucide="bar-chart-3"></i> Statistik
                </a>
                <a class="nav-item {{ ($activeNav ?? '') === 'notifications' ? 'active' : '' }}" href="{{ route('notifications.index') }}">
                    <i data-lucide="bell"></i> Notifikasi
                    @php
                        $unreadCount = \Illuminate\Support\Facades\DB::table('notifications')->whereNull('read_at')->count();
                    @endphp
                    @if($unreadCount > 0)
                        <b>{{ $unreadCount }}</b>
                    @endif
                </a>
            @endif
        </nav>

        <div class="sidebar-footer">
            <span class="online-dot"></span>
            <span>Semua sistem normal</span>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div>
                <span class="eyebrow">{{ $eyebrow ?? strtoupper(\Illuminate\Support\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y')) }}</span>
                <h1>{{ $pageTitle ?? ($title ?? 'Dashboard') }}</h1>
                @if(!empty($pageSubtitle))
                    <p>{{ $pageSubtitle }}</p>
                @endif
            </div>

            <div class="profile">
                @if(session('user_role') !== 'ADMIN')
                    <a class="icon-button" href="{{ route('notifications.index') }}" title="Notifikasi">
                        <i data-lucide="bell"></i>
                        @if(\Illuminate\Support\Facades\DB::table('notifications')->whereNull('read_at')->exists())
                            <span></span>
                        @endif
                    </a>
                @endif
                <div class="avatar">
                    {{ strtoupper(substr(session('user_name', 'BK'), 0, 2)) }}
                </div>
                <div>
                    <strong>{{ session('user_name', 'Bu Ratna Sari') }}</strong>
                    <small>
                        @if(session('user_role') === 'ADMIN')
                            Administrator Platform
                        @elseif(session('user_role') === 'PRINCIPAL')
                            Kepala Sekolah
                        @else
                            Guru BK · MIN Kota Bukittinggi
                        @endif
                    </small>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout-button" type="submit" title="Keluar">
                        <i data-lucide="log-out"></i>
                    </button>
                </form>
            </div>
        </header>

        @if (session('success'))
            <div class="success-banner"><i data-lucide="circle-check"></i>{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="success-banner error-banner"><i data-lucide="circle-alert"></i>{{ $errors->first() }}</div>
        @endif

        @yield('content')

        <footer>
            <span>© 2026 Sahabat Sekolah</span>
            <span>“Dengarkan, Lindungi, Dampingi”</span>
            <span>Sistem Proteksi Siswa Terpadu</span>
        </footer>
    </main>
</div>
<script>lucide.createIcons();</script>
</body>
</html>

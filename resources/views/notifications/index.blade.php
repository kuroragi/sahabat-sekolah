<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi | Sahabat Sekolah</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
<div class="notification-shell">
    <header class="case-topbar"><a class="brand-link" href="{{ url('/') }}"><span class="brand-mark">✦</span><strong>Sahabat Sekolah</strong></a><a class="back-link" href="{{ url('/') }}"><i data-lucide="arrow-left"></i> Dashboard</a></header>
    <main class="notification-main"><div class="notification-heading"><div><span class="eyebrow">NOTIFICATION CENTER</span><h1>Notifikasi</h1><p>Peristiwa penting yang perlu diketahui Guru BK.</p></div><span class="notification-count">{{ $notifications->whereNull('read_at')->count() }} belum dibaca</span></div>
        <section class="notification-list">@forelse ($notifications as $notification)<article class="notification-item {{ $notification->read_at ? 'read' : '' }}"><div class="notification-icon {{ strtolower($notification->priority) }}"><i data-lucide="{{ $notification->priority === 'URGENT' ? 'triangle-alert' : ($notification->type === 'NEW_REPORT' ? 'inbox' : 'bell') }}"></i></div><div class="notification-copy"><div><strong>{{ $notification->title }}</strong><small>{{ \Illuminate\Support\Carbon::parse($notification->created_at)->diffForHumans() }}</small></div><p>{{ $notification->message }}</p>@if ($notification->case_number)<a href="{{ route('cases.show', $notification->case_number) }}">Buka kasus <i data-lucide="arrow-up-right"></i></a>@endif</div>@if (!$notification->read_at)<form method="POST" action="{{ route('notifications.read', $notification->id) }}">@csrf<button class="read-button" type="submit">Tandai dibaca</button></form>@endif</article>@empty<div class="empty-notifications"><i data-lucide="bell-off"></i><strong>Belum ada notifikasi</strong><p>Notifikasi baru akan muncul saat ada laporan atau aktivitas kasus.</p></div>@endforelse</section>
    </main>
</div><script>lucide.createIcons();</script>
</body>
</html>

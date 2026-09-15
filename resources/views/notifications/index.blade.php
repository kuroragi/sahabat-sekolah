@extends('layouts.app', [
    'title' => 'Notifikasi | Sahabat Sekolah',
    'activeNav' => 'notifications',
    'eyebrow' => 'NOTIFICATION CENTER',
    'pageTitle' => 'Notifikasi Sistem',
    'pageSubtitle' => 'Peristiwa penting dan pembaruan kasus yang membutuhkan perhatian Anda.'
])

@section('content')
<div class="notification-heading-bar">
    <span class="notification-count">{{ $notifications->whereNull('read_at')->count() }} belum dibaca</span>
</div>

<section class="notification-list">
    @forelse ($notifications as $notification)
        <article class="notification-item {{ $notification->read_at ? 'read' : '' }}">
            <div class="notification-icon {{ strtolower($notification->priority) }}">
                <i data-lucide="{{ $notification->priority === 'URGENT' ? 'triangle-alert' : ($notification->type === 'NEW_REPORT' ? 'inbox' : 'bell') }}"></i>
            </div>
            <div class="notification-copy">
                <div>
                    <strong>{{ $notification->title }}</strong>
                    <small>{{ \Illuminate\Support\Carbon::parse($notification->created_at)->diffForHumans() }}</small>
                </div>
                <p>{{ $notification->message }}</p>
                @if ($notification->case_number)
                    <a href="{{ route('cases.show', $notification->case_number) }}">
                        Buka kasus <i data-lucide="arrow-up-right"></i>
                    </a>
                @endif
            </div>
            @if (!$notification->read_at)
                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                    @csrf
                    <button class="read-button" type="submit">Tandai dibaca</button>
                </form>
            @endif
        </article>
    @empty
        <div class="empty-notifications">
            <i data-lucide="bell-off"></i>
            <strong>Belum ada notifikasi</strong>
            <p>Notifikasi baru akan muncul saat ada laporan atau aktivitas kasus.</p>
        </div>
    @endforelse
</section>
@endsection

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kanal Anonim | Sahabat Sekolah</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
<div class="anonymous-shell">
    <main class="anonymous-card">
        <a class="back-link" href="{{ url('/') }}"><i data-lucide="arrow-left"></i> Kembali</a>
        <div class="anonymous-intro"><div class="receipt-check"><i data-lucide="message-circle-heart"></i></div><span class="eyebrow">KOMUNIKASI AMAN</span><h1>Kanal anonim</h1><p>Gunakan token tanda terima Anda untuk berkomunikasi dengan Guru BK tanpa membuka identitas.</p></div>
        <form class="token-form" method="GET" action="{{ route('anonymous.channel') }}"><label>Anonymous Token<input name="token" value="{{ $token }}" maxlength="12" placeholder="Contoh: AB12CD34EF56" required></label><button class="primary-button" type="submit"><i data-lucide="key-round"></i> Buka kanal</button></form>
        @if ($token && !$report)<div class="channel-alert"><i data-lucide="triangle-alert"></i>Token tidak valid atau sudah kedaluwarsa.</div>@endif
        @if (session('success'))<div class="channel-success"><i data-lucide="circle-check"></i>{{ session('success') }}</div>@endif
        @if ($report)
            <div class="channel-case"><small>Laporan terhubung</small><strong>{{ $report->report_number }}</strong><span><i data-lucide="lock-keyhole"></i> Identitas tetap anonim</span></div>
            <div class="message-list">@forelse ($messages as $message)<div class="message-bubble {{ $message->sender_type === 'REPORTER' ? 'reporter' : 'counselor' }}"><small>{{ $message->sender_type === 'REPORTER' ? 'Anda' : 'Guru BK' }} · {{ \Illuminate\Support\Carbon::parse($message->created_at)->format('d M Y, H:i') }}</small><p>{{ $message->message }}</p></div>@empty<div class="empty-message"><i data-lucide="messages-square"></i><p>Belum ada pesan. Anda dapat mengirim informasi tambahan di bawah.</p></div>@endforelse</div>
            <form class="message-form" method="POST" action="{{ route('anonymous.message') }}">@csrf<input type="hidden" name="token" value="{{ $token }}"><label>Pesan tambahan<textarea name="message" rows="4" minlength="5" required placeholder="Tuliskan informasi tambahan untuk Guru BK..."></textarea></label><button class="primary-button" type="submit"><i data-lucide="send"></i> Kirim pesan anonim</button></form>
        @endif
    </main>
</div><script>lucide.createIcons();</script>
</body>
</html>

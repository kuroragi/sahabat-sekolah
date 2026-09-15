<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tanda Terima Laporan | Sahabat Sekolah</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
<div class="receipt-shell"><main class="receipt-card"><div class="receipt-check"><i data-lucide="shield-check"></i></div><span class="eyebrow">LAPORAN BERHASIL DITERIMA</span><h1>Simpan tanda terima ini dengan aman.</h1><p>Laporan Anda sudah diteruskan kepada Guru BK. Gunakan token berikut untuk komunikasi anonim pada tahap berikutnya.</p><div class="receipt-number"><small>Nomor laporan</small><strong>{{ $reportNumber }}</strong></div><div class="token-box"><small>Anonymous Token</small><strong id="anonymous-token">{{ $anonymousToken }}</strong><button type="button" onclick="copyToken()"><i data-lucide="copy"></i> Salin token</button></div><div id="copy-message" class="copy-message">Token hanya ditampilkan sekarang. Simpan di tempat yang aman.</div><a class="primary-button receipt-button" href="{{ url('/') }}">Kembali ke halaman utama <i data-lucide="arrow-right"></i></a></main></div><script>lucide.createIcons();function copyToken(){navigator.clipboard.writeText(document.getElementById('anonymous-token').textContent.trim());document.getElementById('copy-message').textContent='Token berhasil disalin.';}</script>
</body>
</html>

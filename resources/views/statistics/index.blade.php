@extends('layouts.app', [
    'title' => 'Statistik | Sahabat Sekolah',
    'activeNav' => 'statistics',
    'eyebrow' => 'EVALUASI PILOT',
    'pageTitle' => 'Statistik Penanganan Kasus',
    'pageSubtitle' => 'Data agregat untuk evaluasi sekolah tanpa identitas sensitif.'
])

@section('content')
<div class="statistics-heading-bar">
    <div></div>
    <a class="secondary-button export-link" href="{{ route('statistics.export') }}">
        <i data-lucide="download"></i> Export Data CSV
    </a>
</div>

<section class="principal-stat-grid">
    <div class="principal-stat">
        <span class="principal-stat-icon blue-bg"><i data-lucide="files"></i></span>
        <div>
            <small>Total kasus</small>
            <strong>{{ $stats['total'] }}</strong>
            <p>Lingkup sekolah</p>
        </div>
    </div>
    <div class="principal-stat">
        <span class="principal-stat-icon orange-bg"><i data-lucide="loader-circle"></i></span>
        <div>
            <small>Aktif</small>
            <strong>{{ $stats['active'] }}</strong>
            <p>Masih ditangani</p>
        </div>
    </div>
    <div class="principal-stat">
        <span class="principal-stat-icon red-bg"><i data-lucide="shield-alert"></i></span>
        <div>
            <small>Risiko tinggi</small>
            <strong>{{ $stats['highRisk'] }}</strong>
            <p>HIGH dan CRITICAL</p>
        </div>
    </div>
    <div class="principal-stat">
        <span class="principal-stat-icon pink-bg"><i data-lucide="clock-alert"></i></span>
        <div>
            <small>SLA overdue</small>
            <strong>{{ $stats['overdue'] }}</strong>
            <p>Perlu evaluasi</p>
        </div>
    </div>
</section>

<section class="statistics-grid">
    <div class="panel stat-breakdown">
        <h3>Per kategori</h3>
        @foreach ($categories as $label => $count)
            <div class="bar-row">
                <span>{{ $label }}</span>
                <div><i style="width:{{ $stats['total'] ? max(8, ($count / $stats['total']) * 100) : 0 }}%"></i></div>
                <b>{{ $count }}</b>
            </div>
        @endforeach
    </div>
    <div class="panel stat-breakdown">
        <h3>Per status kasus</h3>
        @foreach ($statuses as $label => $count)
            <div class="stat-line">
                <span>{{ str_replace('_', ' ', $label) }}</span>
                <strong>{{ $count }}</strong>
            </div>
        @endforeach
        <h3 class="second-breakdown">Per level risiko</h3>
        @foreach ($risks as $label => $count)
            <div class="stat-line">
                <span>{{ $label }}</span>
                <strong>{{ $count }}</strong>
            </div>
        @endforeach
    </div>
    <div class="panel stat-breakdown">
        <h3>Status SLA</h3>
        @foreach ($slas as $label => $count)
            <div class="stat-line">
                <span>{{ $label }}</span>
                <strong>{{ $count }}</strong>
            </div>
        @endforeach
        <div class="statistics-note">
            <i data-lucide="shield-check"></i>
            <p>Statistik hanya menampilkan data agregat dalam school scope pengguna.</p>
        </div>
    </div>
</section>
@endsection

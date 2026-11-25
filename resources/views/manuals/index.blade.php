@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <span class="eyebrow">DOKUMENTASI RESMI SISTEM</span>
        <h1>Panduan Penggunaan Sahabat Sekolah</h1>
        <p>Buku panduan operasional terstandarisasi untuk peran kedinasan Anda</p>
    </div>
    <div class="profile">
        @if ($selectedManual)
            <a href="{{ route('manuals.docx', $selectedKey) }}" class="btn-secondary" style="display:inline-flex;align-items:center;gap:6px;padding:9px 15px;background:#f8fafc;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;font-weight:600;color:#334155;text-decoration:none;">
                <i data-lucide="file-text" style="width:16px;height:16px;color:#2563eb;"></i> Unduh Format Word (.docx)
            </a>
            <a href="{{ route('manuals.stream', $selectedKey) }}" target="_blank" class="primary-button" style="display:inline-flex;align-items:center;gap:6px;padding:9px 15px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
                <i data-lucide="external-link" style="width:16px;height:16px;"></i> Buka Tab Penuh
            </a>
        @endif
    </div>
</div>

<div style="display: grid; grid-template-columns: 320px 1fr; gap: 20px; align-items: start; margin-top: 15px;">
    <!-- Daftar Buku Panduan Tersedia Sesuai Role -->
    <div class="panel" style="padding: 16px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid #f1f5f9;">
            <strong style="font-size: 13px; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Dokumen Panduan Anda</strong>
            <span style="font-size: 11px; background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 12px; font-weight: 700;">{{ count($manuals) }} Buku</span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 8px;">
            @foreach ($manuals as $key => $item)
                @php
                    $isActive = ($key === $selectedKey);
                @endphp
                <a href="{{ route('manuals.index', ['doc' => $key]) }}"
                   style="display: block; padding: 12px 14px; border-radius: 8px; text-decoration: none; transition: all 0.15s ease; {{ $isActive ? 'background: #eff6ff; border: 1.5px solid #3b82f6;' : 'background: #f8fafc; border: 1px solid #e2e8f0;' }}">
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 6px;">
                        <span style="font-weight: 700; font-size: 13px; color: {{ $isActive ? '#1d4ed8' : '#1e293b' }}; line-height: 1.35;">
                            {{ $item['title'] }}
                        </span>
                        <span style="font-size: 10px; font-weight: 600; padding: 2px 6px; border-radius: 4px; white-space: nowrap; {{ $isActive ? 'background:#dbeafe;color:#1e40af;' : 'background:#e2e8f0;color:#475569;' }}">
                            {{ $item['badge'] }}
                        </span>
                    </div>
                    <p style="margin: 6px 0 0 0; font-size: 11.5px; color: #64748b; line-height: 1.4;">
                        {{ Str::limit($item['subtitle'], 75) }}
                    </p>
                </a>
            @endforeach
        </div>

        <div style="margin-top: 20px; padding: 12px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; font-size: 11.5px; color: #166534; line-height: 1.5;">
            <div style="display: flex; items-center; gap: 6px; font-weight: 700; margin-bottom: 4px;">
                <i data-lucide="shield-check" style="width: 14px; height: 14px;"></i> Akses Berbasis Wewenang
            </div>
            Panduan yang ditampilkan disaring secara otomatis berdasarkan peran kedinasan akun Anda (<em>{{ session('user_role') }}</em>).
        </div>
    </div>

    <!-- Area Tampilan Preview PDF Interaktif -->
    <div class="panel" style="padding: 18px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; min-height: 780px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; flex-direction: column;">
        @if ($selectedManual)
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">
                <div>
                    <h2 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 4px 0;">
                        {{ $selectedManual['title'] }}
                    </h2>
                    <span style="font-size: 12px; color: #64748b;">
                        Sasaran: <strong>{{ $selectedManual['target'] }}</strong> · Versi Edisi 2.0 (November 2026)
                    </span>
                </div>
            </div>

            <!-- Embed PDF Viewer -->
            <div style="flex: 1; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #525659; min-height: 680px;">
                <iframe src="{{ route('manuals.stream', $selectedKey) }}#toolbar=1&navpanes=0&scrollbar=1"
                        style="width: 100%; height: 100%; min-height: 680px; border: none;"
                        title="{{ $selectedManual['title'] }}">
                    <p style="color: white; padding: 20px; text-align: center;">
                        Browser Anda tidak mendukung pratinjau PDF langsung. Silakan
                        <a href="{{ route('manuals.stream', $selectedKey) }}" style="color: #60a5fa;">klik di sini untuk membuka PDF</a>.
                    </p>
                </iframe>
            </div>
        @else
            <div style="text-align: center; padding: 80px 20px; color: #64748b;">
                <i data-lucide="book-open" style="width: 48px; height: 48px; color: #cbd5e1; margin-bottom: 12px;"></i>
                <p>Silakan pilih dokumen panduan dari daftar di sebelah kiri.</p>
            </div>
        @endif
    </div>
</div>
@endsection

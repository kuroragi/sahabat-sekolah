<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Laporan | Sahabat Sekolah</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
<div class="form-shell">
    <a class="back-link" href="{{ url('/') }}"><i data-lucide="arrow-left"></i> Kembali ke dashboard</a>
    <div class="form-layout">
        <section class="form-intro"><span class="pill">KANAL PELAPORAN AMAN</span><h1>Mulai dari cerita yang ingin Anda sampaikan.</h1><p>Setiap laporan akan diterima Guru BK dan ditangani dengan prinsip kerahasiaan serta perlindungan peserta didik.</p><div class="trust-list"><span><i data-lucide="shield-check"></i> Identitas dilindungi</span><span><i data-lucide="clock-3"></i> Respons awal maksimal 1 × 24 jam</span><span><i data-lucide="message-circle-heart"></i> Komunikasi yang aman</span></div></section>
        <form class="report-form" method="POST" action="{{ route('reports.store') }}">
            @csrf
            <div class="form-heading"><div><span class="eyebrow">LAPORAN BARU</span><h2>Sampaikan laporan</h2><p>Kolom bertanda * wajib diisi.</p></div><div class="form-step">01 <span>/ 01</span></div></div>
            @if ($errors->any())<div class="form-alert error">Periksa kembali isian laporan Anda.</div>@endif
            <label>Sekolah <span>*</span><select name="school_id" required><option value="">Pilih sekolah</option>@foreach ($schools as $school)<option value="{{ $school->id }}" @selected(old('school_id') == $school->id)>{{ $school->name }}</option>@endforeach</select></label>
            <div class="two-fields"><label>Peran pelapor <span>*</span><select name="reporter_role" required><option value="">Pilih peran</option><option value="VICTIM">Saya korban</option><option value="WITNESS">Saya saksi</option><option value="CONCERNED_PERSON">Saya mengetahui kejadian</option></select></label><label>Mode identitas <span>*</span><select name="identity_mode" required><option value="CONFIDENTIAL">Rahasia</option><option value="IDENTIFIED">Terbuka</option><option value="ANONYMOUS">Anonim</option></select></label></div>
            <div class="two-fields"><label>Nama pelapor <span>* untuk identitas terbuka/rahasia</span><input type="text" name="reporter_name" value="{{ old('reporter_name') }}" placeholder="Nama lengkap"></label><label>Kontak <input type="text" name="reporter_contact" value="{{ old('reporter_contact') }}" placeholder="Opsional"></label></div>
            <div class="two-fields"><label>Kategori kejadian <span>*</span><select name="category" id="category-select" required><option value="">Pilih kategori</option>@foreach ($categories as $category)<option value="{{ $category->name }}" data-category-id="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></label><label>Subkategori <select name="subcategory" id="subcategory-select"><option value="">Pilih subkategori</option>@foreach ($categories as $category)@foreach ($category->subcategories as $subcategory)<option value="{{ $subcategory->name }}" data-category-id="{{ $category->id }}">{{ $subcategory->name }}</option>@endforeach @endforeach</select></label></div>
            <label>Ceritakan kejadian <span>*</span><textarea name="description" rows="6" minlength="20" required placeholder="Tuliskan apa yang terjadi, kapan, dan di mana. Hindari menebak hal yang belum Anda ketahui.">{{ old('description') }}</textarea><small>Minimal 20 karakter. Ceritakan fakta yang Anda lihat atau alami.</small></label>
            <button class="primary-button" type="submit">Kirim laporan <i data-lucide="arrow-right"></i></button>
        </form>
    </div>
</div><script>lucide.createIcons();const categorySelect=document.getElementById('category-select');const subcategorySelect=document.getElementById('subcategory-select');function filterSubcategories(){const id=categorySelect.options[categorySelect.selectedIndex]?.dataset.categoryId;Array.from(subcategorySelect.options).forEach(option=>{option.hidden=Boolean(option.dataset.categoryId)&&option.dataset.categoryId!==id;});subcategorySelect.value='';}categorySelect.addEventListener('change',filterSubcategories);filterSubcategories();</script>
</body>
</html>

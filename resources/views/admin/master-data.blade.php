@extends('layouts.app', [
    'title' => 'Master Data | Sahabat Sekolah',
    'activeNav' => 'admin-master-data',
    'eyebrow' => 'MASTER DATA',
    'pageTitle' => 'Kategori Perundungan & Parameter Risiko',
    'pageSubtitle' => 'Kelola kategori, subkategori, dan parameter risiko untuk seluruh sekolah.'
])

@section('content')
<section class="admin-grid">
    <div class="panel">
        <div class="panel-heading">
            <div>
                <h3>Tambah Kategori</h3>
                <p>Kategori utama perundungan</p>
            </div>
        </div>
        <form class="admin-form" method="POST" action="{{ route('admin.categories.store') }}">
            @csrf
            <input name="name" required placeholder="Contoh: Perundungan Verbal">
            <textarea name="description" rows="3" placeholder="Deskripsi kategori"></textarea>
            <button class="primary-button" type="submit">
                <i data-lucide="plus"></i> Tambah kategori
            </button>
        </form>
    </div>

    <div class="panel">
        <div class="panel-heading">
            <div>
                <h3>Tambah Subkategori</h3>
                <p>Parameter default risk engine</p>
            </div>
        </div>
        <form class="admin-form" method="POST" action="{{ route('admin.subcategories.store') }}">
            @csrf
            <select name="category_id" required>
                <option value="">Pilih kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <input name="name" required placeholder="Nama subkategori">
            <select name="default_risk_level">
                <option value="LOW">LOW</option>
                <option value="MEDIUM">MEDIUM</option>
                <option value="HIGH">HIGH</option>
                <option value="CRITICAL">CRITICAL</option>
            </select>
            <input type="number" name="risk_score" min="0" max="100" value="0" required placeholder="Risk score">
            <button class="primary-button" type="submit">
                <i data-lucide="plus"></i> Tambah subkategori
            </button>
        </form>
    </div>
</section>

<section class="panel master-category-list">
    <div class="panel-heading">
        <div>
            <h3>Daftar Kategori dan Subkategori</h3>
            <p>Data aktif yang digunakan pada form laporan.</p>
        </div>
    </div>
    @foreach ($categories as $category)
        <div class="master-category">
            <div>
                <strong>{{ $category->name }}</strong>
                <small>{{ $category->description }}</small>
            </div>
            <span>{{ $category->subcategories->count() }} subkategori</span>
        </div>
        @foreach ($category->subcategories as $subcategory)
            <div class="master-subcategory">
                <span>{{ $subcategory->name }}</span>
                <small>{{ $subcategory->default_risk_level }} · {{ $subcategory->risk_score }}</small>
            </div>
        @endforeach
    @endforeach
</section>
@endsection

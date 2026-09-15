@extends('layouts.app', [
    'title' => 'Administrator | Sahabat Sekolah',
    'activeNav' => 'admin-dashboard',
    'eyebrow' => 'SYSTEM ADMINISTRATION',
    'pageTitle' => 'Pengaturan Platform',
    'pageSubtitle' => 'Kelola sekolah, pengguna, dan master data aplikasi Sahabat Sekolah.'
])

@section('content')
<section class="admin-grid">
    <div class="panel">
        <div class="panel-heading">
            <div>
                <h3>Tambah Sekolah</h3>
                <p>Master data sekolah terdaftar</p>
            </div>
        </div>
        <form class="admin-form" method="POST" action="{{ route('admin.schools.store') }}">
            @csrf
            <input name="name" required placeholder="Nama sekolah">
            <select name="education_level">
                <option value="MIN">MIN</option>
                <option value="MTS">MTs</option>
                <option value="SD">SD</option>
                <option value="SDI">SDI</option>
                <option value="SMP">SMP</option>
            </select>
            <button class="primary-button" type="submit">
                <i data-lucide="plus"></i> Tambah sekolah
            </button>
        </form>
    </div>

    <div class="panel">
        <div class="panel-heading">
            <div>
                <h3>Tambah Pengguna</h3>
                <p>Role dan penugasan sekolah</p>
            </div>
        </div>
        <form class="admin-form" method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <input name="name" required placeholder="Nama lengkap">
            <input type="email" name="email" required placeholder="Email login">
            <select name="role">
                <option value="COUNSELOR">Guru BK</option>
                <option value="PRINCIPAL">Kepala Sekolah</option>
                <option value="ADMIN">Administrator</option>
            </select>
            <select name="school_id">
                <option value="">Tanpa sekolah (Platform)</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}">{{ $school->name }}</option>
                @endforeach
            </select>
            <button class="primary-button" type="submit">
                <i data-lucide="user-plus"></i> Buat pengguna
            </button>
        </form>
    </div>
</section>
@endsection

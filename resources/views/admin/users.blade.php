@extends('layouts.app', [
    'title' => 'Pengguna | Sahabat Sekolah',
    'activeNav' => 'admin-users',
    'eyebrow' => 'USER MANAGEMENT',
    'pageTitle' => 'Manajemen Pengguna Platform',
    'pageSubtitle' => 'Edit role, penugasan sekolah, dan status pengguna.'
])

@section('content')
<section class="panel admin-table-panel">
    <table class="admin-crud-table">
        <thead>
            <tr>
                <th>Pengguna</th>
                <th>Email</th>
                <th>Role</th>
                <th>Sekolah</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
                        @csrf
                        <td><input name="name" value="{{ $user->name }}" required></td>
                        <td><strong>{{ $user->email }}</strong></td>
                        <td>
                            <select name="role">
                                <option value="COUNSELOR" @selected($user->role==='COUNSELOR')>Guru BK</option>
                                <option value="PRINCIPAL" @selected($user->role==='PRINCIPAL')>Kepala Sekolah</option>
                                <option value="ADMIN" @selected($user->role==='ADMIN')>Administrator</option>
                            </select>
                        </td>
                        <td>
                            <select name="school_id">
                                <option value="">Platform</option>
                                @foreach($schools as $school)
                                    <option value="{{ $school->id }}" @selected($user->school_id===$school->id)>{{ $school->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select name="status">
                                <option value="ACTIVE" @selected(($user->status ?? 'ACTIVE')==='ACTIVE')>Aktif</option>
                                <option value="INACTIVE" @selected(($user->status ?? '')==='INACTIVE')>Nonaktif</option>
                            </select>
                        </td>
                        <td><button class="secondary-button" type="submit">Simpan</button></td>
                    </form>
                </tr>
            @endforeach
        </tbody>
    </table>
</section>
@endsection

@extends('layouts.app', [
    'title' => 'Sekolah | Sahabat Sekolah',
    'activeNav' => 'admin-schools',
    'eyebrow' => 'SCHOOL MANAGEMENT',
    'pageTitle' => 'Manajemen Data Sekolah',
    'pageSubtitle' => 'Edit nama, jenjang pendidikan, dan status keaktifan sekolah.'
])

@section('content')
<section class="panel admin-table-panel">
    <table class="admin-crud-table">
        <thead>
            <tr>
                <th>Nama sekolah</th>
                <th>Jenjang</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($schools as $school)
                <tr>
                    <form method="POST" action="{{ route('admin.schools.update', $school->id) }}">
                        @csrf
                        <td><input name="name" value="{{ $school->name }}" required></td>
                        <td>
                            <select name="education_level">
                                <option value="SD" @selected($school->education_level==='SD')>SD</option>
                                <option value="SDI" @selected($school->education_level==='SDI')>SDI</option>
                                <option value="MIN" @selected($school->education_level==='MIN')>MIN</option>
                                <option value="SMP" @selected($school->education_level==='SMP')>SMP</option>
                                <option value="MTS" @selected($school->education_level==='MTS')>MTs</option>
                            </select>
                        </td>
                        <td>
                            <select name="status">
                                <option value="1" @selected($school->status)>Aktif</option>
                                <option value="0" @selected(!$school->status)>Nonaktif</option>
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

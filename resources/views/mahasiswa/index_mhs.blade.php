@extends('layout.master')
@section('judul', 'Halaman Mahasiswa')
@section('content')
@if (session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  {{ session('success') }}
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
@endif
@php
    $userRole = auth('mahasiswas')->check() ? auth('mahasiswas')->user()->role : null;
@endphp

@if ($userRole === 'admin')
  <a href="/mhs/baru" class="btn btn-primary mb-3">Tambah Data Mahasiswa</a>
@endif
<table class="table table-bordered dataTable" id="dataTable" width="100%" cellspacing="0" role="grid" aria-describedby="dataTable_info" style="width: 100%;">
  <thead>
    <tr role="row">
      <th class="sorting sorting_asc" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Name: activate to sort column descending" style="width: 400px;">No</th>
      <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 299px;">NIM</th>
      <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 299px;">Nama</th>
      <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Age: activate to sort column ascending" style="width: 160px;">Tempat Lahir</th>
      <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Start date: activate to sort column ascending" style="width: 285px;">Tanggal Lahir</th>
      <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Salary: activate to sort column ascending" style="width: 260px;">Nama Prodi</th>
      <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Salary: activate to sort column ascending" style="width: 260px;">Tahun Masuk</th>
      <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Salary: activate to sort column ascending" style="width: 260px;">Role</th>
      @if ($userRole === 'admin')
        <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Salary: activate to sort column ascending" style="width: 260px;">Aksi</th>
      @endif
    </tr>
  </thead>
  
  <tbody>
  @php $no = 1; @endphp
  @forelse ($mhs as $m)
    <tr>
      <td>{{ $no++ }}</td>
      <td>{{ $m->nim }}</td>
      <td>{{ $m->nama }}</td>
      <td>{{ $m->tempat_lahir }}</td>
      <td>{{ $m->tgl_lahir }}</td>
      <td>{{ $m->nama_prodi ?? $m->prodi_id }}</td>
      <td>{{ $m->th_masuk }}</td>
      <td>{{ $m->role }}</td>
      @if ($userRole === 'admin')
        <td>
          <a href="/mhs/edit/{{ $m->nim }}" class="btn btn-sm btn-warning">Edit</a>
          <a href="/mhs/hapus/{{ $m->nim }}" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus data {{ $m->nama }}?')">Hapus</a>
        </td>
      @endif
    </tr>
  @empty
    <tr>
      <td colspan="9" class="text-center">Belum ada data mahasiswa.</td>
    </tr>
  @endforelse
  </tbody>
</table>
@endsection
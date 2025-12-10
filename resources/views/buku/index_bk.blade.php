@extends('layout.master')
@section('judul', 'Form Buku')
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
  <a href="/bk/baru" class="btn btn-primary mb-3">Tambah Data Buku</a>
@endif
<table class="table table-bordered dataTable" id="dataTable" width="100%" cellspacing="0" role="grid" aria-describedby="dataTable_info" style="width: 100%;">
  <thead>
    <tr role="row">
      <th class="sorting sorting_asc" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Name: activate to sort column descending" style="width: 400px;">No</th>
      <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 299px;">Kode Buku</th>
      <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 299px;">Nama Buku</th>
      <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Age: activate to sort column ascending" style="width: 160px;">Penerbit</th>
      <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Salary: activate to sort column ascending" style="width: 260px;">Tahun Terbit</th>
      <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 260px;">Stok</th>
      @if ($userRole === 'admin')
        <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Salary: activate to sort column ascending" style="width: 260px;">Aksi</th>
      @endif
    </tr>
  </thead>
  
  <tbody>
  @php $no = 1; @endphp
  @forelse ($bk as $b)
    <tr>
      <td>{{ $no++ }}</td>
      <td>{{ $b->kode_buku }}</td>
      <td>{{ $b->nama_buku }}</td>
      <td>{{ $b->penerbit }}</td>
      <td>{{ $b->th_terbit }}</td>
      <td>{{ $b->stock }}</td>
      @if ($userRole === 'admin')
        <td>
          <a href="/bk/edit/{{ $b->kode_buku }}" class="btn btn-sm btn-warning">Edit</a>
          <a href="/bk/hapus/{{ $b->kode_buku }}" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus data {{ $b->nama_buku }}?')">Hapus</a>
        </td>
      @endif
    </tr>
  @empty
    <tr>
      <td colspan="6" class="text-center">Belum ada data buku.</td>
    </tr>
  @endforelse
  </tbody>
</table>
@endsection
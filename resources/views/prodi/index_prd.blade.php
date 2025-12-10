@extends('layout.master')
@section('judul', 'Form Prodi')
@section('content')
@if (session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  {{ session('success') }}
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
@endif

<a href="/prd/baru" class="btn btn-primary mb-3">Tambah Data Prodi</a>
<table class="table table-bordered dataTable" id="dataTable" width="100%" cellspacing="0" role="grid" aria-describedby="dataTable_info" style="width: 100%;">
  <thead>
    <tr role="row">
      <th class="sorting sorting_asc" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Name: activate to sort column descending" style="width: 400px;">No</th>
      <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 299px;">kode_prodi</th>
      <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 299px;">nama_prodi</th>
      <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 299px;">singkatan</th>
      <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Salary: activate to sort column ascending" style="width: 260px;">aksi</th>
    </tr>
  </thead>
  
  <tbody>
  @php $no = 1; @endphp
  @forelse ($prd as $p)
    <tr>
      <td>{{ $no++ }}</td>
      <td>{{ $p->kode_prodi }}</td>
      <td>{{ $p->nama_prodi }}</td>
      <td>{{ $p->singkatan }}</td>
      <td>
        <a href="/prd/edit/{{ $p->kode_prodi }}" class="btn btn-sm btn-warning">Edit</a>
        <a href="/prd/hapus/{{ $p->kode_prodi }}" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus data {{ $p->nama_prodi }}?')">Hapus</a>
      </td>
    </tr>
  @empty
    <tr>
      <td colspan="5" class="text-center">Belum ada data Prodi.</td>
    </tr>
  @endforelse
  </tbody>
</table>
@endsection
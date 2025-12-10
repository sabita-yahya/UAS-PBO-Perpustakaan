@extends('layout.master')
@section('judul','Laporan Pengembalian Buku')
@section('content')

@if (session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  {{ session('success') }}
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
@endif

<!-- Statistik Card -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Peminjaman</div>  <!-- ini untuk melakukan panggilan ke controller laporan peminjaman-->
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $statistik['total_peminjaman'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-book fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Belum Dikembalikan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $statistik['total_belum_kembali'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Terlambat</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $statistik['total_telat'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Sudah Dikembalikan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $statistik['total_sudah_kembali'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Laporan -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Laporan Pengembalian Buku Semua User</h6>
        <div>
            <!-- <button class="btn btn-sm btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Cetak Laporan
            </button> -->
        </div>
    </div>
    <div class="card-body">
        @if ($laporan->isEmpty()) <!-- Cek apakah data laporan kosong -->
            <p class="text-center mb-0">Belum ada data peminjaman.</p>
        @else
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>Role</th>
                            <th>Kode Buku</th>
                            <th>Judul Buku</th>
                            <th>Jumlah</th>
                            <th>Tanggal Pinjam</th>
                            <th>Tanggal Kembali</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($laporan as $index => $item)
                        <tr class="{{ $item->is_telat ? 'table-danger' : ($item->status == 0 ? 'table-success' : '') }}">
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->nim }}</td>
                            <td>{{ $item->nama_mahasiswa }}</td>
                            <td>
                                <span class="badge {{ $item->role === 'admin' ? 'badge-primary' : 'badge-info' }}">
                                    {{ $item->role }}
                                </span>
                            </td>
                            <td>{{ $item->kode_buku }}</td>
                            <td>{{ $item->nama_buku }}</td>
                            <td>{{ $item->jml_buku }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tgl_pinjam)->format('d M Y') }}</td>
                            <td class="{{ $item->is_telat ? 'text-danger font-weight-bold' : '' }}">
                                {{ \Carbon\Carbon::parse($item->tgl_kembali)->format('d M Y') }}
                                @if ($item->is_telat)
                                    <span class="badge badge-danger ml-1">TERLAMBAT</span>
                                @endif
                            </td>
                            <td>
                                @if ($item->status == 1)
                                    @if ($item->is_telat)
                                        <span class="badge badge-danger">Terlambat</span>
                                    @else
                                        <span class="badge badge-warning">Belum Dikembalikan</span>
                                    @endif
                                @else
                                    <span class="badge badge-success">Sudah Dikembalikan</span>
                                @endif
                            </td>
                            <td>
                                @if ($item->is_telat)
                                    <span class="text-danger">
                                        <i class="fas fa-exclamation-triangle"></i> Terlambat 
                                        {{ \Carbon\Carbon::parse($item->tgl_kembali)->diffForHumans() }}
                                    </span>
                                @elseif ($item->status == 1)
                                    <span class="text-info">
                                        <i class="fas fa-clock"></i> Masih dipinjam
                                    </span>
                                @else
                                    <span class="text-success">
                                        <i class="fas fa-check"></i> Sudah dikembalikan
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<style>
@media print {
    .card-header .btn,
    .sidebar,
    .topbar,
    .navbar {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>

@endsection


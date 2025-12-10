@extends('layout.master')
@section('judul','Riwayat Peminjaman')
@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Riwayat Peminjaman Anda</h6>
        <span class="badge badge-info">{{ $mahasiswa->nama }} ({{ $mahasiswa->nim }})</span>
    </div>
    <div class="card-body">
        @if ($riwayat->isEmpty())
            <p class="text-center mb-0">Belum ada riwayat peminjaman.</p>
        @else
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal Pinjam</th>
                            <th>Tanggal Kembali</th>
                            <th>Detail Buku</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($riwayat as $index => $pinjam)
                            @php
                                $isTelat = isset($pinjam->is_telat) && $pinjam->is_telat;
                                $tglKembali = \Carbon\Carbon::parse($pinjam->tgl_kembali);
                                $today = \Carbon\Carbon::today();
                                $isOverdue = $tglKembali->lt($today) && $pinjam->detil->where('status', 1)->isNotEmpty();
                                $semuaDikembalikan = $pinjam->detil->where('status', 1)->isEmpty();
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($pinjam->tgl_pinjam)->format('d M Y') }}</td>
                                <td>
                                    {{ \Carbon\Carbon::parse($pinjam->tgl_kembali)->format('d M Y') }}
                                </td>
                                <td>
                                    @if($pinjam->detil->isEmpty())
                                        <span class="text-muted">Tidak ada detail buku</span>
                                    @else
                                        <ul class="mb-0 pl-3">
                                            @foreach ($pinjam->detil as $detil)
                                                <li>
                                                    <strong>{{ $detil->judul_buku }}</strong> ({{ $detil->kode_buku }}) - {{ $detil->jml_buku }} buku
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($pinjam->detil->isEmpty())
                                        <span class="badge badge-warning">Tidak ada data</span>
                                    @else
                                        @foreach ($pinjam->detil as $detil)
                                            <div class="mb-1">
                                                <span class="badge {{ $detil->status ? 'badge-info' : 'badge-success' }}">
                                                    {{ $detil->status ? 'Dipinjam' : 'Dikembalikan' }}
                                                </span>
                                            </div>
                                        @endforeach
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
@endsection


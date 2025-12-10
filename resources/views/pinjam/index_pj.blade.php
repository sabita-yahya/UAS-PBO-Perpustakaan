@extends('layout.master')
@section('judul','Peminjaman')
@section('content')
@if (session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  {{ session('success') }}
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
@endif
@if (session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  {{ session('error') }}
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
@endif
<form action = "/pinjam/simpan" method="POST">
<div class="card">
    <div class="card-header">Cari Mahasiswa</div>
    <div class="card-body">
        <input type="text" class="form-control" placeholder="Masukkan Nama Mahasiswa" id="cariMahasiswa" name="cariMahasiswa">
        <input type="text" class="form-control " id="nim" name="nim" readonly="">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
    </div>
    <div class="card shadow mb-4" id="buku_form" style="display:none;">
        <div class="card-header py-3">
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        Cari Buku
                        <input type="text" class="form-control form-control-user" id="caribuku" name="caribuku"
                            placeholder="Cari buku...." value="">
                    </div>

                    <div class="form-group">
                        Kode Buku
                        <input type="text" class="form-control form-control-user" id="kode_buku" name="kode_buku"
                            placeholder="Kode Buku" value="">
                    </div>
                    <div class="form-group">
                        Tanggal Pinjam
                        <input type="text" class="form-control form-control-user" id="tgl_pinjam"
                            name="tgl_pinjam" placeholder="Kode Buku" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        Pegawai
                        <input type="text" class="form-control form-control-user" id="pegawai_id" name="pegawai_id"
                            placeholder="Kode pegawai" value="1">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        Jumlah Peminjaman
                        <input type="text" class="form-control form-control-user" id="jumlah_pinjam" name="jumlah_pinjam"
                            placeholder="Jumlah dipinjam" value="">
                    </div>
                    <div class="form-group">
                        Stok
                        <input type="text" class="form-control form-control-user" id="stock" name="stock"
                            placeholder="Stok Buku" value="" readonly>
                    </div>
                    <div class="form-group">
                        Tanggal Kembali
                        <input type="text" class="form-control form-control-user" id="tgl_kembali"
                            name="tgl_kembali" placeholder="Kode Buku"
                            value="{{ date('Y-m-d', strtotime('+6 day', time())) }}">
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-info" id="tambah_tabel">
                Tambah Keranjang
            </button>

        </div>

        <table id="tabelPinjam" name="tabelPinjam" class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Buku</th>
                    <th>Kode Buku</th>
                    <th>Stok Buku</th>
                    <th>Jumlah dipinjam</th>
                    <th>Sisa</th>
                    <th>Status</th>

                </tr>
            </thead>

            <tbody id="template">

            </tbody>
        </table>
        <div class="card-body">
            <button type="submit" class="btn btn-info">
                Pinjam
            </button>

            </a>
        </div>

    </div>
</div>
</form>

<!-- Tabel Daftar Peminjaman untuk Admin
@php
    $userRole = auth('mahasiswas')->check() ? auth('mahasiswas')->user()->role : null;
@endphp

@if ($userRole === 'admin' && isset($pj) && $pj->isNotEmpty())
<div class="card shadow mb-4 mt-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Peminjaman</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIM</th>
                        <th>Nama Mahasiswa</th>
                        <th>Role</th>
                        <th>Tanggal Pinjam</th>
                        <th>Tanggal Kembali</th>
                        <th>Total Buku</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pj as $index => $p)
                    <tr class="{{ $p->is_telat ? 'table-danger' : '' }}">
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $p->nim }}</td>
                        <td>{{ $p->mahasiswa_nama ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $p->mahasiswa_role === 'admin' ? 'badge-primary' : 'badge-info' }}">
                                {{ $p->mahasiswa_role ?? '-' }}
                            </span>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($p->tgl_pinjam)->format('d M Y') }}</td>
                        <td class="{{ $p->is_telat ? 'text-danger font-weight-bold' : '' }}">
                            {{ \Carbon\Carbon::parse($p->tgl_kembali)->format('d M Y') }}
                            @if ($p->is_telat)
                                <span class="badge badge-danger ml-2">TERLAMBAT</span>
                            @endif
                        </td>
                        <td>{{ $p->total_buku }}</td>
                        <td>
                            @if ($p->is_telat)
                                <span class="badge badge-danger">Terlambat</span>
                            @else
                                <span class="badge badge-success">Tepat Waktu</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div> -->
@endif
@endsection
@push('Java')
<script type="text/javascript">
    // jquery
    $(document).ready(function() { // fungsi jquery
        //    alert('test'); untuk mengecek saja apakah jquery sudah terhubung
        // Autocomplete Mahasiswa
        $('#cariMahasiswa').autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: '/autocomplete-mahasiswa',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        term: request.term,
                        _token: $('input[name=_token]').val()
                    },
                    success: function(data) {
                        response(data);
                    }
                });
            },
            select: function(event, ui) {
                console.log(ui);
                $('#nim').val(ui.item.value);
                $('#cariMahasiswa').val(ui.item.label);
                $('#buku_form').show();
                return false;

            },
        });




        // Autocomplete Buku
        // Autocomplete Buku
        $('#caribuku').autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: '/autocomplete-buku',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        term: request.term,
                        _token: $('input[name=_token]').val()
                    },
                    success: function(data) {
                        response(data);
                    }
                });
            },
            select: function(event, ui) {
                $('#kode_buku').val(ui.item.value);
                $('#stock').val(ui.item.stock);
                $('#caribuku').val(ui.item.label);
                return false;
            }
        });

        // ============================================
        // FUNGSI TAMBAH BUKU KE KERANJANG (TABEL)
        // ============================================
        // Fungsi ini dipanggil ketika user klik tombol "Tambah Keranjang"
        // Berfungsi untuk menambahkan buku ke tabel keranjang sebelum submit
        $('#tambah_tabel').click(function() {
            // ============================================
            // BAGIAN 1: AMBIL DATA DARI FORM INPUT
            // ============================================
            var no = $('#template tr').length + 1; // Otomatis menghitung nomor urut berdasarkan jumlah baris yang sudah ada
            let nama_buku = $('#caribuku').val(); // Ambil nama buku dari input field
            var kode_buku = $('#kode_buku').val(); // Ambil kode buku dari input field
            var stock = parseInt($('#stock').val()) || 0; // Ambil stok buku dan konversi ke integer, jika kosong maka 0
            // Mengambil jumlah peminjaman dari input field jumlah_pinjam dan dikonversi ke integer
            // parseInt() adalah fungsi JavaScript untuk mengkonversi string ke integer
            // Jika tidak ada nilai atau null, maka diisi dengan 0 (menggunakan operator ||)
            var jumlah_pinjam = parseInt($('#jumlah_pinjam').val()) || 0;
            var status = 1; // Status default: 1 = masih dipinjam

            // ============================================
            // BAGIAN 2: VALIDASI CLIENT-SIDE (SEBELUM TAMBAH KE TABEL)
            // ============================================
            
            // VALIDASI 1: Cek apakah jumlah peminjaman lebih dari 0
            // Validasi ini mencegah user memasukkan jumlah 0 atau negatif
            if (jumlah_pinjam <= 0) {
                alert('Jumlah peminjaman harus lebih dari 0!');
                return false; // Hentikan proses, jangan tambahkan ke tabel
            }

            // VALIDASI 2: Cek apakah jumlah peminjaman melebihi stok yang tersedia
            // Ini adalah validasi utama untuk mencegah peminjaman melebihi stok
            // Alert akan muncul jika user mencoba meminjam lebih dari stok yang ada
            if (jumlah_pinjam > stock) {
                alert('Jumlah peminjaman (' + jumlah_pinjam + ') tidak boleh melebihi stok yang tersedia (' + stock + ')!');
                return false; // Hentikan proses, jangan tambahkan ke tabel
            }

            // VALIDASI 3: Cek apakah buku sudah ada di tabel (untuk menghindari duplikasi)
            // Mencegah user menambahkan buku yang sama dua kali di keranjang
            var kodeBukuSudahAda = false;
            // Loop melalui setiap baris di tabel untuk mengecek duplikasi
            $('#template tr').each(function() {
                var kodeBukuTabel = $(this).find('input[name="kode_buku[]"]').val();
                if (kodeBukuTabel === kode_buku) {
                    kodeBukuSudahAda = true;
                    return false; // break loop jika sudah ditemukan
                }
            });

            // Jika buku sudah ada di keranjang, tampilkan alert dan hentikan proses
            if (kodeBukuSudahAda) {
                alert('Buku dengan kode ' + kode_buku + ' sudah ada di keranjang!');
                return false; // Hentikan proses
            }

            // ============================================
            // BAGIAN 3: HITUNG SISA STOK
            // ============================================
            // Hitung sisa stok setelah peminjaman
            // Sisa stok = stok awal - jumlah yang dipinjam
            var sisa = stock - jumlah_pinjam;
            // ============================================
            // BAGIAN 4: TAMBAHKAN BARIS BARU KE TABEL
            // ============================================
            // Buat HTML untuk baris baru di tabel keranjang
            // Baris ini akan berisi data buku yang akan dipinjam
            var newRow = '<tr>' +
                '<td>' + no + '</td>' + // Nomor urut
                '<td><input type="text" class="form-control-plaintext" value="' + nama_buku + '" name="nama_buku[]" readonly></td>' + // Nama buku (readonly)
                '<td><input type="text" class="form-control-plaintext" value="' + kode_buku + '" name="kode_buku[]" readonly></td>' + // Kode buku (readonly, akan dikirim ke server)
                '<td><input type="text" class="form-control-plaintext" value="' + stock + '" name="stock[]" readonly></td>' + // Stok awal (readonly)
                '<td><input type="text" class="form-control-plaintext" value="' + jumlah_pinjam + '" name="jumlah_pinjam[]" readonly></td>' + // Jumlah pinjam (readonly, akan dikirim ke server)
                '<td><input type="text" class="form-control-plaintext" value="' + sisa + '" name="sisa[]" readonly></td>' + // Sisa stok setelah pinjam (readonly)
                '<td><input type="text" class="form-control-plaintext" value="' + status + '" name="status[]" readonly></td>' + // Status: 1 = masih dipinjam (readonly, akan dikirim ke server)
                '<td><button type="button" class="btn btn-danger btn-sm btn-hapus"><i class="fas fa-trash"></i>Hapus</button></td>' + // Tombol hapus
                '</tr>';

            // Tambahkan baris baru ke tabel dengan ID 'template'
            $('#template').append(newRow);

            // ============================================
            // BAGIAN 5: RESET FORM INPUT
            // ============================================
            // Setelah berhasil menambahkan ke tabel, reset semua input field
            // agar siap untuk input buku berikutnya
            $('#caribuku').val('').focus(); // Reset dan fokus ke field cari buku
            $('#kode_buku').val(''); // Reset kode buku
            $('#stock').val(''); // Reset stok
            $('#jumlah_pinjam').val(''); // Reset jumlah pinjam

            // ============================================
            // BAGIAN 6: FUNGSI HAPUS BARIS DARI TABEL
            // ============================================
            // Event handler untuk tombol hapus (delete) di setiap baris tabel
            // Menggunakan event delegation agar bisa bekerja untuk elemen yang ditambahkan dinamis
            $(document).on('click', '.btn-hapus', function() {
                $(this).closest('tr').remove(); // Hapus baris yang berisi tombol hapus yang diklik
                
                // Update nomor urut setelah menghapus baris
                // Loop melalui semua baris dan update nomor urut secara berurutan
                $('#template tr').each(function(index) {
                    $(this).find('td:first').text(index + 1); // Set nomor urut mulai dari 1
                });
            });

        });

    });
</script>
@endpush
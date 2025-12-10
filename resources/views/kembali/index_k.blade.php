@extends('layout.master')
@section('judul', 'Form Pengembalian')
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

<!-- Form Pencarian Mahasiswa -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Cari Mahasiswa untuk Pengembalian</h6>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label for="cari_mahasiswa">Cari Nama Mahasiswa</label> <!-- mencari nama mahasiswa untuk memilih pengembalian-->
            <input type="text" class="form-control" id="cari_mahasiswa" name="cari_mahasiswa" 
                   placeholder="Ketik nama mahasiswa.." autocomplete="off">
            <input type="hidden" id="selected_nim" name="selected_nim">
        </div>
        <div class="form-group">
            <!-- <label for="nim_mahasiswa">NIM</label> -->
            <input type="text" class="form-control" id="nim_mahasiswa" name="nim_mahasiswa" 
                   placeholder="" readonly>
        </div>
    </div>
</div>

<!-- Daftar Peminjaman (Akan muncul setelah mahasiswa dipilih) -->
<div class="card shadow mb-4" id="daftarPeminjamanCard" style="display: none;">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Peminjaman Aktif</h6>
    </div>
    <div class="card-body">
        <!-- <div id="infoMahasiswa" class="mb-3"></div> --> <!-- Menampilkan info mahasiswa yang dipilih -->
         <div id="infoMahasiswa" class="mb-3"></div>
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Judul Buku</th>
                        <th>Tanggal Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Jumlah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbodyPeminjaman">
                    <!-- Data akan diisi via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Pengembalian (Dinamis) -->
<div class="modal fade" id="kembaliModal" tabindex="-1" role="dialog" aria-labelledby="kembaliModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="kembaliModalLabel">Pengembalian Buku</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('kembali.proses') }}" method="POST" id="formPengembalian">
                @csrf
                <input type="hidden" name="detil_id" id="modal_detil_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>NIM</label>
                        <input type="text" class="form-control" id="modal_nim" readonly>
                    </div>
                    <div class="form-group">
                        <label>Nama Peminjam</label>
                        <input type="text" class="form-control" id="modal_nama" readonly>
                    </div>
                    <div class="form-group">
                        <label>Judul Buku</label>
                        <input type="text" class="form-control" id="modal_judul" readonly>
                    </div>
                    <!-- <div class="form-group">
                        <label for="modal_kondisi_buku">Kondisi Buku (Opsional)</label>
                        <select class="form-control" id="modal_kondisi_buku" name="kondisi_buku">
                            <option value="">Pilih Kondisi Buku</option>
                            <option value="baik">Baik</option>
                            <option value="rusak_ringan">Rusak Ringan</option>
                            <option value="rusak_berat">Rusak Berat</option>
                            <option value="hilang">Hilang</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="modal_keterangan">Keterangan (Opsional)</label>
                        <textarea class="form-control" id="modal_keterangan" name="keterangan" rows="2"></textarea>
                    </div> -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan Pengembalian</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('Java')
<script>
    $(document).ready(function() {
        let dataTable = null;
        let selectedNim = null;
        let selectedNama = null;

        // Autocomplete untuk pencarian mahasiswa
        $('#cari_mahasiswa').autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "{{ route('autocomplete.mahasiswa') }}",
                    dataType: "json",
                    data: {
                        term: request.term
                    },
                    success: function(data) {
                        response(data);
                    }
                });
            },
            minLength: 2,
            select: function(event, ui) {
                event.preventDefault();
                // Tampilkan nama di input field
                $('#cari_mahasiswa').val(ui.item.label);
                // Tampilkan NIM di kolom NIM (readonly)
                $('#nim_mahasiswa').val(ui.item.value);
                $('#selected_nim').val(ui.item.value);
                selectedNim = ui.item.value;
                selectedNama = ui.item.label;
                
                // Load data peminjaman
                loadPeminjaman(selectedNim);
            }
        });

        // Fungsi untuk load data peminjaman berdasarkan NIM
        function loadPeminjaman(nim) {
            $.ajax({
                url: "{{ route('kembali.getByNim') }}",
                type: "GET",
                data: {
                    nim: nim
                },
                success: function(response) {
                    if (response.success) {
                        if (response.data.length > 0) {
                            // Tampilkan card daftar peminjaman
                            $('#daftarPeminjamanCard').show();
                            
                            // Tampilkan info mahasiswa
                            $('#infoMahasiswa').html(
                                '<div class="alert alert-info">' +
                                '<strong>Mahasiswa:</strong> ' + selectedNama + ' (NIM: ' + selectedNim + ')' +
                                '</div>'
                            );
                            
                            // Isi tabel
                            let tbody = $('#tbodyPeminjaman');
                            tbody.empty();
                            
                            response.data.forEach(function(pinjam, index) {
                                let row = '<tr>' +
                                    '<td>' + (index + 1) + '</td>' +
                                    '<td>' + pinjam.judul_buku + '</td>' +
                                    '<td>' + formatDate(pinjam.tgl_pinjam) + '</td>' +
                                    '<td>' + formatDate(pinjam.tgl_kembali) + '</td>' +
                                    '<td>' + pinjam.jml_buku + '</td>' +
                                    '<td>' +
                                    '<button type="button" class="btn btn-sm btn-primary btn-kembalikan" ' +
                                    'data-detil-id="' + pinjam.detil_id + '" ' +
                                    'data-nim="' + pinjam.nim + '" ' +
                                    'data-nama="' + pinjam.nama_peminjam + '" ' +
                                    'data-judul="' + pinjam.judul_buku + '">' +
                                    '<i class="fas fa-undo"></i> Kembalikan</button>' +
                                    '</td>' +
                                    '</tr>';
                                tbody.append(row);
                            });
                            
                            // Inisialisasi ulang DataTable jika sudah ada
                            if (dataTable) {
                                dataTable.destroy();
                            }
                            dataTable = $('#dataTable').DataTable({
                                "pageLength": 10,
                                "language": {
                                    "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Indonesian.json"
                                }
                            });
                        } else {
                            // Tidak ada data
                            $('#daftarPeminjamanCard').hide();
                            alert('Tidak ada data peminjaman aktif untuk mahasiswa ' + selectedNama);
                        }
                    }
                },
                error: function(xhr) {
                    console.error('Error loading data:', xhr);
                    let errorMsg = 'Terjadi kesalahan saat memuat data peminjaman';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    alert(errorMsg);
                }
            });
        }

        // Format tanggal
        function formatDate(dateString) {
            if (!dateString) return '';
            let date = new Date(dateString);
            let day = String(date.getDate()).padStart(2, '0');
            let month = String(date.getMonth() + 1).padStart(2, '0');
            let year = date.getFullYear();
            return day + '/' + month + '/' + year;
        }

        // Event handler untuk button kembalikan
        $(document).on('click', '.btn-kembalikan', function() {
            let detilId = $(this).data('detil-id');
            let nim = $(this).data('nim');
            let nama = $(this).data('nama');
            let judul = $(this).data('judul');
            
            // Isi modal
            $('#modal_detil_id').val(detilId);
            $('#modal_nim').val(nim);
            $('#modal_nama').val(nama);
            $('#modal_judul').val(judul);
            $('#modal_kondisi_buku').val('');
            $('#modal_keterangan').val('');
            
            // Tampilkan modal
            $('#kembaliModal').modal('show');
        });

        // Form submit biasa (bukan AJAX) agar session flash message bisa digunakan
        // Setelah submit, halaman akan reload dan menampilkan pesan sukses
        // Kita akan simpan NIM dan nama di sessionStorage untuk reload data setelah redirect
        $('#formPengembalian').on('submit', function() {
            if (selectedNim && selectedNama) {
                sessionStorage.setItem('reloadNim', selectedNim);
                sessionStorage.setItem('reloadNama', selectedNama);
            }
        });

        // Reload data jika ada NIM di sessionStorage (setelah redirect dari submit)
        let reloadNim = sessionStorage.getItem('reloadNim');
        let reloadNama = sessionStorage.getItem('reloadNama');
        if (reloadNim && reloadNama) {
            sessionStorage.removeItem('reloadNim');
            sessionStorage.removeItem('reloadNama');
            // Tampilkan nama di input field
            $('#cari_mahasiswa').val(reloadNama);
            // Tampilkan NIM di kolom NIM (readonly)
            $('#nim_mahasiswa').val(reloadNim);
            $('#selected_nim').val(reloadNim);
            selectedNim = reloadNim;
            selectedNama = reloadNama;
            loadPeminjaman(reloadNim);
        }
    });
</script>
@endpush
@endsection
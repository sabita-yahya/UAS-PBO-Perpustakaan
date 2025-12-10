@extends('layout.master')
@section('judul', 'Form Prodiel')
@section('content')
@if (session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  {{ session('success') }}
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
@endif

<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModalLong">
  Tambah Prodi
</button>
<br><br>

<table class="table table-bordered dataTable" id="dataTable" width="100%" cellspacing="0" role="grid" aria-describedby="dataTable_info" style="width: 100%;">
  <thead>
    <tr role="row">
      <th class="sorting sorting_asc" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Name: activate to sort column descending" style="width: 400px;">No</th>
      <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 299px;">Kode Prodi</th>
      <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 299px;">Nama Prodi</th>
      <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 299px;">Singkatan</th>
      <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Salary: activate to sort column ascending" style="width: 260px;">Aksi</th>
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
        <a href="javascript:void(0)" type="button" class="btn btn-sm btn-warning edit"
          data-id="{{ $p->kode_prodi }}">Edit</a>
        <a href="javascript:void(0)" type="button" class="btn btn-sm btn-danger delete"
          data-id="{{ $p->kode_prodi }}">Hapus</a>
      </td>
    </tr>
    @empty
    <tr>
      <td colspan="5" class="text-center">Belum ada data Prodi.</td>
    </tr>
    @endforelse
  </tbody>
</table>

<!-- Modal -->
<div class="modal fade" id="exampleModalLong" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="text" id="kode_prodi" name="kode_prodi" placeholder="Masukan Kode Prodi" class="form-control"><br>
        <input type="text" id="nama_prodi" name="nama_prodi" placeholder="Masukan Nama Prodi" class="form-control"><br>
        <input type="text" id="singkatan" name="singkatan" placeholder="Masukan Singkatan" class="form-control"><br>
        <input type="hidden" name="_token" value="{{csrf_token()}}">
        <input type="hidden" id="cek" value="0">


      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-primary" id="tambah_prd">Simpan Data</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('Java')
<script type="text/javascript">
  $(document).ready(function() {
    $('#tambah_prd').click(function() {
      let kode_prodi = $('#kode_prodi').val();
      let nama_prodi = $('#nama_prodi').val();
      var singkatan = $('#singkatan').val();
      let _token = $('input[name=_token]').val();
      let cek = $('#cek').val();

      let url = "/prodi/simpan";
      if (cek == '1') {
        url = "/prodi/update/" + kode_prodi;
      }

      $.ajax({
        url: url,
        type: "POST",
        data: {
          kode_prodi: kode_prodi,
          nama_prodi: nama_prodi,
          singkatan: singkatan,
          _token: _token
        },
        success: function(response) {
          console.log(response);
          // Tutup modal setelah data berhasil disimpan
          $('#exampleModalLong').modal('hide');

          // Mengosongkan form setelah data berhasil disimpan
          // Ini memastikan form bersih untuk input berikutnya
          $('#kode_prodi').val(''); // Kosongkan kode_prodi
          $('#nama_prodi').val(''); // Kosongkan nama_prodi
          $('#singkatan').val(''); // Kosongkan singkatan

          // Mengembalikan semua field menjadi bisa diisi
          $('#kode_prodi').prop('readonly', false);
          $('#nama_prodi').prop('readonly', false);
          $('#singkatan').prop('readonly', false);

          // ============================================
          // RESET CEK MENJADI 0 SETELAH DATA DISIMPAN
          // ============================================
          // Menggunakan .val('0') untuk mengembalikan mode ke tambah
          // Setelah data berhasil disimpan, form dikembalikan ke mode tambah
          $('#cek').val('0');

          // Reset title dan button
          $('#exampleModalLongTitle').html("Modal title");
          $('#tambah_prd').html('Simpan Data').show();

          // Tampilkan pesan sukses
          alert(response.message);

          // Reload halaman untuk menampilkan data terbaru
          location.reload();
        },
        error: function(xhr, status, error) {
          console.log(xhr.responseText);
          var errorMsg = 'Data Gagal Ditambahkan';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            if (typeof xhr.responseJSON.message === 'object') {
              errorMsg = Object.values(xhr.responseJSON.message).join(', ');
            } else {
              errorMsg = xhr.responseJSON.message;
            }
          }
          alert(errorMsg);
        }

      });
    });

    // ============================================
    // CARA MENGOSONGKAN FORM TAMBAH
    // ============================================
    // Event handler ini akan dipanggil ketika button "Launch demo modal" diklik
    // Fungsi ini mengosongkan semua field di form agar siap untuk input data baru

    // Reset form ketika modal dibuka untuk tambah data
    $('[data-target="#exampleModalLong"]').on('click', function() {
      // Mengosongkan field kode_prodi dengan mengset value menjadi string kosong
      $('#kode_prodi').val('');

      // Mengosongkan field nama_prodi dengan mengset value menjadi string kosong
      $('#nama_prodi').val('');

      // Mengosongkan field singkatan dengan mengset value menjadi string kosong
      $('#singkatan').val('');

      // Mengembalikan field kode_prodi menjadi bisa diisi (tidak readonly)
      // Ini penting karena saat edit, field ini dibuat readonly
      $('#kode_prodi').prop('readonly', false);

      // Mengembalikan field nama_prodi menjadi bisa diisi
      $('#nama_prodi').prop('readonly', false);

      // Mengembalikan field singkatan menjadi bisa diisi
      $('#singkatan').prop('readonly', false);

      // ============================================
      // MENGGUNAKAN .val() UNTUK MENGATUR CEK MENJADI 0 DAN MASUK KE MODE TAMBAH
      // ============================================
      // Menggunakan .val('0') untuk mengset nilai field cek menjadi 0
      // Nilai 0 menandakan mode tambah (bukan edit)
      // Ini akan membuat form siap untuk input data baru
      $('#cek').val('0');

      // Mengubah title modal menjadi "Form Tambah Prodi"
      $('#exampleModalLongTitle').html("Form Tambah Prodi");

      // Mengubah text button menjadi "Simpan Data"
      $('#tambah_prd').html('Simpan Data').show();
    });

    // ============================================
    // RESET FORM KETIKA MODAL DITUTUP
    // ============================================
    // Event handler ini akan dipanggil ketika modal ditutup
    // Fungsi ini memastikan form dikosongkan setelah modal ditutup
    // sehingga saat dibuka lagi, form sudah bersih

    // Reset form ketika modal ditutup
    $('#exampleModalLong').on('hidden.bs.modal', function() {
      // Mengosongkan semua field input
      $('#kode_prodi').val(''); // Kosongkan kode_prodi
      $('#nama_prodi').val(''); // Kosongkan nama_prodi
      $('#singkatan').val(''); // Kosongkan singkatan

      // Mengembalikan semua field menjadi bisa diisi (tidak readonly)
      $('#kode_prodi').prop('readonly', false);
      $('#nama_prodi').prop('readonly', false);
      $('#singkatan').prop('readonly', false);

      // ============================================
      // RESET CEK MENJADI 0 SAAT MODAL DITUTUP
      // ============================================
      // Menggunakan .val('0') untuk mengembalikan mode ke tambah
      // Saat modal ditutup, form dikembalikan ke mode tambah (cek = 0)
      $('#cek').val('0');

      // Reset title modal
      $('#exampleModalLongTitle').html("Modal title");

      // Reset text button
      $('#tambah_prd').html('Simpan Data').show();
    });

    // Event handler untuk button Edit
    $(document).on('click', '.edit', function() {
      let kode_prodi = $(this).data('id');

      // ============================================
      // MENGGUNAKAN .val() UNTUK MENGATUR CEK MENJADI 1 DAN MASUK KE MODE EDIT
      // ============================================
      // Menggunakan .val('1') untuk mengset nilai field cek menjadi 1
      // Nilai 1 menandakan mode edit (bukan tambah)
      // Ini akan membuat form siap untuk mengedit data yang sudah ada
      $('#cek').val('1'); // Mode edit
      $('#kode_prodi').prop('readonly', true);
      $('#nama_prodi').prop('readonly', false);
      $('#singkatan').prop('readonly', false);
      $('#exampleModalLong').modal('show');
      $.ajax({
        url: "/prodi/edit/" + kode_prodi,
        type: "GET",
        success: function(response) {
          console.log(response);
          $('#kode_prodi').val(response.kode_prodi);
          $('#nama_prodi').val(response.nama_prodi);
          $('#singkatan').val(response.singkatan);
          $('#exampleModalLongTitle').html("Form Edit Prodi");
          $('#tambah_prd').html('Update Data Prodi').show();
        },
        error: function(xhr, status, error) {
          console.log(xhr.responseText);
          alert('Gagal mengambil data prodi');
        }
      });
    });

    // Event handler untuk button Delete
    $(document).on('click', '.delete', function() {
      let kode_prodi = $(this).data('id');

      if (confirm('Apakah Anda yakin ingin menghapus data prodi ini?')) {
        let _token = $('input[name=_token]').val();

        $.ajax({
          url: "/prodi/hapus/" + kode_prodi,
          type: "DELETE",
          data: {
            _token: _token
          },
          success: function(response) {
            console.log(response);
            alert(response.message);
            location.reload();
          },
          error: function(xhr, status, error) {
            console.log(xhr.responseText);
            var errorMsg = 'Gagal menghapus data prodi';
            if (xhr.responseJSON && xhr.responseJSON.message) {
              errorMsg = xhr.responseJSON.message;
            }
            alert(errorMsg);
          }
        });
      }
    });

  });
</script>
@endpush
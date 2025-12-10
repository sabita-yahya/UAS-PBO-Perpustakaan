@extends('layout.master')
@section('judul', 'Form Buku')
@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<form action="/bk/simpan" method="POST">
    <input type="text" name="nama_buku" placeholder="Masukan Nama Buku" class="form-control"><br>
    <input type="text" name="pengarang" placeholder="Masukan Pengarang" class="form-control"><br>
    <input type="text" name="penerbit" placeholder="Masukan Penerbit" class="form-control"><br>
    <input type="text" name="th_terbit" placeholder="Masukan Tahun Terbit" class="form-control"><br>
    <input type="text" name="stock" placeholder="Masukan stock" class="form-control"><br>

    <input type="hidden" name="_token" value="{{csrf_token()}}">
    <input type="submit" value="Simpan Data" class="btn btn-primary">

</form>

@endsection


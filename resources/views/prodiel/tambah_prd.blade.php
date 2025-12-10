@extends('layout.master')
@section('judul', 'Form Prodiel')
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
<form action="/prodi/simpan" method="POST">
    <input type="text" name="kode_prodi" placeholder="Masukan Kode Prodi" class="form-control"><br>
    <input type="text" name="nama_prodi" placeholder="Masukan Nama Prodi" class="form-control"><br>
    <input type="text" name="singkatan" placeholder="Masukan Singkatan" class="form-control"><br>

    @csrf
    <input type="submit" value="Simpan Data" class="btn btn-primary">

</form>

@endsection

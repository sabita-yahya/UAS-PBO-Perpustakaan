@extends('layout.master')
@section('judul', 'Edit Buku')
@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<form action="/bk/update/{{$bk->kode_buku}}" method="POST">
    <input type="text" name="kode_buku" placeholder="Masukan Kode Buku" class="form-control" value="{{$bk->kode_buku}}" readonly><br>
    <input type="text" name="nama_buku" placeholder="Masukan Nama Buku" class="form-control" value="{{$bk->nama_buku}}"><br>
    <input type="text" name="penerbit" placeholder="Masukan Penerbit" class="form-control" value="{{$bk->penerbit}}"><br>
    <input type="text" name="th_terbit" placeholder="Masukan Tahun Terbit" class="form-control" value="{{$bk->th_terbit}}"><br>

    <input type="hidden" name="_token" value="{{csrf_token()}}">
    <input type="submit" value="Simpan Data" class="btn btn-primary">

</form>

@endsection


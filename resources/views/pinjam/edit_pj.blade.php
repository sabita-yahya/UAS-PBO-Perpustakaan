@extends('layout.master')
@section('judul', 'Edit mahasiswa')
@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
<form action="/mhs/update/{{$mhs->nim}}" method="POST">
    <input type="text" name="nim" placeholder="Masukan Nim" class="form-control" value="{{$mhs->nim}}" readonly><br>
    <input type="text" name="nama" placeholder="Masukan Nama" class="form-control" value="{{$mhs->nama}}"><br>
    <input type="text" name="tempat_lahir" placeholder="Masukan Tempat lahir" class="form-control" value="{{$mhs->tempat_lahir}}"><br>
    <input type="date" name="tgl_lahir" placeholder="Masukan Tanggal lahir" class="form-control" value="{{$mhs->tgl_lahir}}"><br>
    <select name="prodi_id" class="form-control">
        @foreach ($prodi as $p)
            <option value="{{ $p->kode_prodi }}" {{ $p->kode_prodi == $mhs->prodi_id ? 'selected' : '' }}>
                {{ $p->nama_prodi }}
            </option>
        @endforeach
    </select><br>
    <input type="text" name="th_masuk" placeholder="Masukan Tahun Masuk" class="form-control" value="{{$mhs->th_masuk}}"><br>

    <input type="hidden" name="_token" value="{{csrf_token()}}">
    <input type="submit" value="Simpan Data" class="btn btn-primary">

</form>

@endsection
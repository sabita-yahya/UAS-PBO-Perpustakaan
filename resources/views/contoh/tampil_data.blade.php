@extends('layout.master')
@section('judul', 'asjk')
@section('content')
    <ol>
        <li>risky</li>
        <li>ganjar</li>

    </ol>
    @php
    $nomor=1;
    echo $nomor."diatas"
    @endphp
   
    @endsection

    @push('Java')
    <script>
        alert("Selamat Datang di Halaman Data Mahasiswa");
    </script>
    @endpush
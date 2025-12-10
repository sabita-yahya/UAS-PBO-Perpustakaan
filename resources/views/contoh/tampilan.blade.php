<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('judul')</title> 
<body>
    <div>
        <a href="/mhs" style="margin-right: 2%">Home</a>
        <a href="/mhs/show" style="margin-right: 2%">Data Mahasiswa</a>
        <a href="/mhs/about">About</a>
    </div>

    <h1>Daftar Mahasiswa</h1>
    <div class="content">
        @yield('content')  

    </div>
</body>
</html>
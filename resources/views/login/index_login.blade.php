@extends('layout.master')
@section('judul','Login')
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



<!-- form login -->
<form action="/login/simpan" method="POST">
    <input type="text" name="nim" placeholder="Masukan Nim" class="form-control"><br>
    <input type="password" name="password" placeholder="Masukan password" class="form-control" value="{{old('password')}}"><br>
    <input type="hidden" name="_token" value="{{csrf_token()}}">
    <input type="submit" value="Login" class="btn btn-primary">
</form>
@endsection
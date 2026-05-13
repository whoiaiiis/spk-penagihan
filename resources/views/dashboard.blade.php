@extends('layouts.app')

@section('content')

<h3>Dashboard</h3>

<!-- FILTER -->
<div class="row mt-3">
    <div class="col-md-6">
        <input type="date" class="form-control">
    </div>
    <div class="col-md-6">
        <input type="date" class="form-control">
    </div>
</div>

<!-- ADMIN -->
@if($role == 'admin')
<div class="row mt-4">
    <div class="col-md-4">
        <div class="card shadow p-3">
            <h6>Saldo Tunggakan</h6>
            <h4>Rp 0</h4>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow p-3">
            <h6>Saldo Pelunasan</h6>
            <h4>Rp 0</h4>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow p-3">
            <h6>Shortcut Input Data Master</h6>
        </div>
    </div>
</div>
@endif

@endsection
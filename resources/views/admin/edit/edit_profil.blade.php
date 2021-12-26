@extends('layouts.back')

@section('title', 'Ubah Profil')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/dashboard/sb-admin-2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/animate.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.css') }}">
@endsection

@section('menu')
<li class="nav-item active">
    <a class="nav-link" href="{{ route('admin_profil') }}">
        <i class="fas fa-user"></i>
        <span>Profil</span>
    </a>
</li>
<li class="nav-item">
    <a class="nav-link" href="{{ route('admin_jadwal') }}">
        <i class="fas fa-calendar-alt"></i>
        <span>Jadwal Praktek</span>
    </a>
</li>
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities"
        aria-expanded="true" aria-controls="collapseUtilities">
        <i class="fas fa-fw fa-wrench"></i>
        <span>Manajemen Data</span>
    </a>
    <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities"
        data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Pilih:</h6>
            <a class="collapse-item" href="{{ route('admin_man_datapasien') }}">Pasien</a>
            <a class="collapse-item" href="{{ route('admin_man_dataapoteker') }}">Apoteker</a>
            <a class="collapse-item" href="{{ route('admin_man_datanakes') }}">Tenaga Kesehatan</a>
            <a class="collapse-item" href="#">Dokumentasi Kegiatan</a>
            <a class="collapse-item" href="{{ route('admin_man_datarekammedik') }}">Rekam Medik</a>
        </div>
    </div>
</li>
<li class="nav-item">
    <a class="nav-link" href="#">
        <i class="fas fa-tasks"></i>
        <span>Rekap Rekam Medik</span>
    </a>
</li>
@endsection

@section('subhead', 'Ubah Profil')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="about-row row">
                <div class="image-col col-md-2">
                    <hr><img src="{{ asset('/img/klinik.png') }}" alt=""><hr>
                </div>
                <div class="detail-col col-md-8">
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="info-list">
                                <div class="form-group">
                                <ul>
                                    <li>
                                        <label class="font-weight-bold text-primary" for="">Nama</label>
                                        <input type="text" name="" class="form-control" value="Jhon Smith">
                                    </li>
                                    <li>
                                        <label class="font-weight-bold text-primary" for="">Tanggal lahir</label>
                                        <input type="date" name="" class="form-control">
                                    </li>
                                    <li>
                                        <label class="font-weight-bold text-primary" for="">Tempat lahir</label>
                                        <input type="text" name="" class="form-control" value="Banjarmasin">
                                    </li>
                                    <li>
                                        <label class="font-weight-bold text-primary" for="">Alamat</label>
                                        <input type="text" name="" class="form-control" value="Jl. H. Hasan Basri">
                                    </li>
                                </ul></div>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="info-list">
                                <ul>
                                    <li>
                                        <label class="font-weight-bold text-primary" for="">Umur</label>
                                        <input type="text" name="" class="form-control" value="21" disabled>
                                    </li>
                                    <li>
                                        <label class="font-weight-bold text-primary" for="">No. Hp</label>
                                        <input type="text" name="" class="form-control" value="081234567890">
                                    </li>
                                    <li>
                                        <label class="font-weight-bold text-primary" for="">Jenis Kelamin</label>
                                        <input type="dropdown" name="" class="form-control" value="Jl. H. Hasan Basri">
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <a href="#" class="btn btn-success btn-icon-split btn-sm">
                        <span>
                            <i class="fas fa-check"></i>
                        </span>    
                        <span class="text">Simpan</span>
                    </a>&nbsp;
                    <a href="{{ route('admin_profil') }}" class="btn btn-secondary btn-icon-split btn-sm">
                        <span>
                            <i class="fas fa-times"></i>
                        </span>    
                        <span class="text">Batal</span>
                    </a>
                </div>                    
            </div>
        </div>
    </div>
</div>
@endsection
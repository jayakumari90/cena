@extends('layouts.admin') 
@section('content')
<div class="main-panel">
    <div class="content-wrapper">
          
          
          <!-- row end -->
        <div class="row">
            <div class="col-lg-12">
                <div class="row mb-3">
                    <a href="{{ route('admin.user') }}" style="text-decoration:none;"><div class="col-xl-3 col-sm-6 py-2">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body bg-success">
                            <div class="rotate">
                                <i class="fa fa-user fa-4x"></i>
                            </div>
                            <h6 class="text-uppercase">Total Users</h6>
                            <h1 class="display-4">{{ $totalUser }}</h1>

                            <h6 class="text-uppercase">Active</h6>
                            <h1 class="display-4">{{ $totalActiveUser }}</h1>
                        </div>
                    </div></a>
                </div>

                <div class="col-xl-3 col-sm-6 py-2">
                    <a href="{{ route('admin.restaurant') }}" style="text-decoration:none;"><div class="card text-white bg-info h-100">
                        <div class="card-body bg-danger">
                            <div class="rotate">
                                <i class="fa fa-twitter fa-4x"></i>
                            </div>
                            <h6 class="text-uppercase">Total Restaurant</h6>
                            <h1 class="display-4">{{ $totalRestaurant}}</h1>

                            <h6 class="text-uppercase"> Active</h6>
                            <h1 class="display-4">{{ $totalActiveRestaurant}}</h1>
                        </div>
                    </div></a>
                </div>
                <div class="col-xl-3 col-sm-6 py-2">
                    <a href="{{ route('order') }}" style="text-decoration:none;"><div class="card text-white bg-info h-100">
                        <div class="card-body bg-info">
                            <div class="rotate">
                                <i class="fa fa-twitter fa-4x"></i>
                            </div>
                            <h6 class="text-uppercase">Total Orders</h6>
                            <h1 class="display-4">{{ $orders}}</h1>
                        </div>
                    </div></a>
                </div>
                <div class="col-xl-3 col-sm-6 py-2">
                    <a href="{{ route('order') }}" style="text-decoration:none;"><div class="card text-white bg-warning h-100">
                        <div class="card-body">
                            <div class="rotate">
                                <i class="fa fa-share fa-4x"></i>
                            </div>
                            <h6 class="text-uppercase">Total Earning</h6>
                            <h1 class="display-4">{{ $earning}}</h1>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
          <!-- row end -->
</div>
@endsection
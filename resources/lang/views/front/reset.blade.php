@extends('layouts.app')

@section('content')

<!-- [ signin-img-tabs ] start -->
<!-- <div class="blur-bg-images"></div> -->
<div class="bg-dark"></div>
<div class="auth-wrapper bg-dark">
    <div class="auth-content container">
        <div class="card col-md-6 m-auto">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="card-body">
                        <h2 class="mb-4">Reset <span class="text-c-blue">Password</span></h2>
                        <p>Create new password for your account</p>
                        <ol class="position-relative carousel-indicators justify-content-start">
                            <li class="active"></li>
                            <li class=""></li>
                        </ol>
                        <form method="POST" action="{{ route('user.resetPassword',['token'=>$secret]) }}">
                            @csrf
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="feather icon-lock"></i></span>
                                </div>
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="New Password" autocomplete="new-password" autofocus>
                            </div>
                            @error('password')
                            <div class="form-group text-right">
                                <span class="text-danger" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            </div>
                            @enderror
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="feather icon-lock"></i></span>
                                </div>
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="Confirm New Password" autocomplete="new-password">
                            </div>
                            <div class="form-group mt-2">
                                <button class="btn btn-primary btn-block mb-4">Reset Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
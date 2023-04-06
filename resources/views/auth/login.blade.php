@extends('layouts.app')

@section('content')
<div class="login-form" style="margin-top: 60px;">
    <form method="post" action="{{ route('login') }}">
                        @csrf
        <div class="avatar">
            <img src="{{asset('public/assets/images/logo.png') }}" alt="cena">
        </div>
        <h2 class="text-center">Administrator</h2>   
        <div class="form-group">
            <input type="text" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="Username" required="required">
            <span class="input-group-text"><i class="fa fa-user"></i></span>
            @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
        </div>
        <div class="form-group">
            <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Password" required="required">
            <span class="input-group-text"><i class="fa fa-key"></i></span>
            @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
        </div>        
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-lg btn-block">Sign in</button>
        </div>
        <div class="bottom-action clearfix">
            @if (Route::has('password.request'))
                <a class="float-right" href="{{ route('password.request') }}">
                    {{ __('Forgot Your Password?') }}
                </a>
            @endif
            
        </div>
    </form>
    
</div>
@endsection

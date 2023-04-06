@extends('layouts.admin')
 
@section('content')
<style>
  .user-card .change-profile .profile-dp {
    position: relative;
    overflow: hidden;
    padding: 5px;
    width: 110px;
    height: 110px;
    border-radius: 50%;
}
.user-card .change-profile .profile-dp .overlay {
    position: absolute;
    top: 5px;
    left: 5px;
    width: calc(100% - 10px);
    height: calc(100% - 10px);
    border-radius: 50%;
    opacity: 0;
    z-index: 1;
    overflow: hidden;
    background: rgba(0, 0, 0, 0.4);
    transition: all 0.3s ease-in-out;
}
.user-card .change-profile .profile-dp .overlay span {
    background: rgb(199 70 70 / 50%);
    color: #fff;
    position: absolute;
    bottom: 0;
    width: 100%;
    text-align: center;
    border-top: 1px solid rgba(255, 255, 255, 0.4);
    padding: 0 0 5px;
}
</style>
<style>
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
</style>

<!-- [ Main Content ] start -->
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            
            <div class="col-md-6 m-auto">
                <div class="card">
                    
                    <div class="card-body">
                        <h4 class="card-title">Settings</h4>
                        @if(session('status'))
                        <div class="alert alert-{{session('type')}} alert-dismissible fade show" role="alert">
                            {{session('status')}}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        @endif
                        <form action="{{route('settings')}}" method="post" enctype="multipart/form-data">
                            @csrf
                            
                            
                            <div class="form-group">
                                <label for="name">Is Maintenance</label>
                                <label class="switch"><input type="checkbox" class="form-control" value="1" <?php echo ($setting->is_maintenance == 1)?'checked="checked"':''?> id="is_maintenance" name="is_maintenance"><span class="slider round"></span></label>
                                @error('is_maintenance')
                                <div class="form-group text-right">
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="name">Ios Version</label>
                                <input type="text" class="form-control" value="{{ $setting->ios_version}}" id="ios_version" name="ios_version">
                                @error('ios_version')
                                <div class="form-group text-right">
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="name">Android Version</label>
                                <input type="text" class="form-control" value="{{ $setting->android_version}}" id="android_version" name="android_version">
                                @error('android_version')
                                <div class="form-group text-right">
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="name">Is ios force update</label>
                                <label class="switch"><input type="checkbox" class="form-control" value="1" <?php echo ($setting->is_ios_force_update == 1)?'checked="checked"':''?> id="is_ios_force_update" name="is_ios_force_update"><span class="slider round"></span></label>
                                @error('is_ios_force_update')
                                <div class="form-group text-right">
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="name">Is android force update</label>
                                <label class="switch"><input type="checkbox" class="form-control" value="1" <?php echo ($setting->is_android_force_update == 1)?'checked="checked"':''?> id="is_android_force_update" name="is_android_force_update"><span class="slider round"></span></label>
                                @error('is_android_force_update')
                                <div class="form-group text-right">
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="name">Auth Token</label>
                                <input type="text" class="form-control" value="{{ $setting->auth_token }}" id="auth_token" name="auth_token">
                                @error('auth_token')
                                <div class="form-group text-right">
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="name">Default Miles</label>
                                <input type="text" class="form-control" value="{{ $setting->default_miles }}" id=" default_miles" name="   default_miles">
                                @error('default_miles')
                                <div class="form-group text-right">
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="name">Commission</label>
                                <input type="text" class="form-control" value="{{ $setting->commission }}" id="commission" name="commission">
                                @error('commission')
                                <div class="form-group text-right">
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="name">Tax</label>
                                <input type="text" class="form-control" value="{{ $setting->tax }}" id="auth_token" name="tax">
                                @error('tax')
                                <div class="form-group text-right">
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                </div>
                                @enderror
                            </div>
                            
                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>

                    </div>
                    
                </div>
            </div>

                            <!-- [ Main Content ] end -->
        </div>
    </div>
</div>
@endsection('content')


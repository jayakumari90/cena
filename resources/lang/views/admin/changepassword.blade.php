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
<script src = "http://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js" defer ></script>
<div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        
        <div class="col-md-6 m-auto">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Change Password</h4>
                  @if(session('status'))
                  <div class="alert alert-{{session('type')}} alert-dismissible fade show" role="alert">
                      {{session('status')}}
                      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  </div>
                  @endif
                  <form class="forms-sample" action="{{route('admin.changepassword')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="form-group row">
                      <label for="current-password">Current Password</label>
                      <input type="password" class="form-control" id="current-password" name="current_password">
                      @error('current_password')
                      <div class="form-group text-right">
                          <span class="text-danger" role="alert">
                              <strong>{{ $message }}</strong>
                          </span>
                      </div>
                      @enderror
                    </div>
                    <div class="form-group row">
                      <label for="new-password">New Password</label>
                      <input type="password" class="form-control" id="new-password" name="new_password">
                      @error('new_password')
                      <div class="form-group text-right">
                          <span class="text-danger" role="alert">
                              <strong>{{ $message }}</strong>
                          </span>
                      </div>
                      @enderror
                    </div>
                    <div class="form-group row">
                      <label for="new-password-confirmation">Confirm New Password</label>
                      <input type="password" class="form-control" id="new-password-confirmation" name="new_password_confirmation">
                    </div>
                    
                    <button type="submit" class="btn btn-primary mr-2">Submit</button>
                  </form>
                </div>
              </div>
            </div>
        
      </div>
    </div>
    <!-- content-wrapper ends -->
    
  </div>


@endsection

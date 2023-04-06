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
                  <h4 class="card-title">Edit Profile</h4>
                  @if(session('status'))
                                        <div class="alert alert-{{session('type')}} alert-dismissible fade show" role="alert">
                                            {{session('status')}}
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        </div>
                                        @endif
                  <form class="forms-sample" action="{{route('admin.editprofile')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group user-card mt-5">
                      <div class="user-about-block">
                        <div class="change-profile">
                            <div class="profile-dp m-auto shadow">
                        
                            
                              @if($user->profile_image)
                              <img class="img-radius img-fluid wid-100" src="{{asset($user->profile_image)}}" alt="Profile Picture" id="preview_profile_image" width="100px">
                              @else
                                <img class="img-radius img-fluid wid-100" src="{{asset('public/uploads/default-avatar.svg')}}" alt="Profile Picture" id="preview_profile_image" width="100px">
                              @endif
                              <a href="javascript:void(0)" class="overlay cursor-pointer" id="file_browser">
                                <span>change</span>
 
                              </a>
                              <input class="d-none" type="file" name="profile_image" id="profile_image" accept="image/*">
                            </div>
                          </div>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="exampleInputEmail2" class="col-sm-3 col-form-label">Email</label>
                      <div class="col-sm-9">
                        <input type="email" class="form-control" id="exampleInputEmail2" placeholder="Email" value="{{$user->email}}" name="email" readonly>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="exampleInputMobile" class="col-sm-3 col-form-label">Name</label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control" id="exampleInputMobile" placeholder="Name" value="{{old('name')?old('name'):Auth::user()->name}}" name="name">
                      </div>
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

<script>
    const defaultAvatar = "{{asset('public/uploads/default-avatar.svg')}}";
    $('#file_browser').on('click', function() {
        $('#profile_image').click();
    });
    $("#profile_image").on('change', function() {
        if (this.value == '') {
            $("#preview_profile_image").attr('src', defaultAvatar);
        } else {
            var reader = new FileReader();
            reader.onload = function(e) {
                $("#preview_profile_image").attr('src', e.target.result);
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
</script>
@endsection

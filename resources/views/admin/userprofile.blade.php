@extends('layouts.admin')
 
@section('content')

<script src = "{{asset('public/assets/js/jquery.dataTables.min.js') }}" defer ></script>

<div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        
        <div class="col-lg-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">User Detail</h4>
              
              <div class="table-responsive pt-3">
                <table class="table table-bordered datatable">
                    
                        <tr>
                            <th>Profile Image</th>
                            @if($user->profile_image)
                            <td><img src="{{asset('public/uploads/profile_picture/').'/'.$user->profile_image}}" width="100px"></td>
                            @else
                            <td><img src="{{asset('public/uploads/default-avatar.svg')}}" width="100px"></td>
                            @endif
                        </tr>
                        <tr>                        
                            <th>Name</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>Age</th>
                            <td>{{ $user->age }}</td>
                        </tr>
                        <tr>
                            <th>Gender</th>
                            <td>{{ $user->gender }}</td>
                        </tr>
                        <tr>
                            <th>User Preference</th>
                            <td>{{ $user->user_preference }}</td>
                        </tr><tr>
                            <th>Preferred Age</th>
                            <td>{{ $user->preferred_age }}</td>
                        </tr><tr>
                            <th>Preferred Gender</th>
                            <td>{{ $user->preferred_gender }}</td>
                        </tr><tr>
                            <th>Profession</th>
                            <td>{{ $user->user_profession }}</td>
                        </tr><tr>
                            <th>Company</th>
                            <td>{{ $user->user_company }}</td>
                        </tr>
                       
                                        
                </table>
                
                
              </div>
            </div>
          </div>
        </div>
        
      </div>
    </div>
    <!-- content-wrapper ends -->
    
  </div>
@endsection
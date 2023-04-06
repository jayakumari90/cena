@extends('layouts.admin')
 
@section('content')

<script src = "http://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js" defer ></script>
<div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        
        <div class="col-lg-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">User List</h4>
              
              <div class="table-responsive pt-3">
                <table class="table table-bordered datatable">
                  <thead>
                    <tr>
                      <th>
                        #
                      </th>
                      <th>
                        Name
                      </th>
                      <th>
                        Email
                      </th>
                      <th>
                        Action
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    
                    
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        
      </div>
    </div>
    <!-- content-wrapper ends -->
    
  </div>
  <div id="viewUserModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="viewUserModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">User Profile</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="row align-items-center">
                                            <!--<div class="col-auto col pr-0">
                                                <img class="img-radius img-fluid wid-60" id="user_profile_picture" src="../assets/images/user/avatar-2.jpg" alt="User image">
                                            </div>-->
                                            <div class="col">
                                                <h6 class="mb-1" id="user_name_value"></h6>
                                                <p class="mb-0" id="user_email_value"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col">
                                        <p class="mb-0">Phone Number</p>
                                        <h6 class="mb-1" id="phone"></h6>
                                    </div>
                                    <div class="col">
                                        <p class="mb-0">Profile Image</p>
                                        <div class="mb-1" id="profile"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- <script type="text/javascript" src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js"></script> -->

  <script type="text/javascript">
    function showProfile(user_id) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      jQuery.ajax({

          url: "{{ route('admin.profile') }}",
          method: 'post',
          data: {
              id: user_id,
          },
          success: function(result) {
              console.log(result);
              var country_code = (result.country_code == null)?' ':result.country_code;
              var phone = (result.phone == null)?' ':result.phone;
              $('#user_name_value').html(result.name);
              $('#user_email_value').html(result.email);
              $('#phone').html(country_code+' '+phone);
              $('#profile').html('<a href="{{asset('/')}}'+result.profile_image+'"><img src="{{asset('/')}}'+result.profile_image+'" width="100px"></a>');

              $('#viewUserModal').modal();

          }
      });
    }
      $(function () {
        
        var table = $('.datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.user') }}",
            columns: [
                {data: 'id', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'email', name: 'email'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
        
      });
    </script>
@endsection
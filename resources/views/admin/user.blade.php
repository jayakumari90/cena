@extends('layouts.admin')
 
@section('content')

<script src = "{{asset('public/assets/js/jquery.dataTables.min.js') }}" defer ></script>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.1/css/all.css" crossorigin="anonymous">
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        
        <div class="col-lg-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">User List</h4>
              
              <div class="table-responsive">
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
                <h5 class="modal-title" id="exampleModalCenterTitle">User Detail</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                
                            </div>
                            <div class="card-body">
                                <div class="form-group user-card mt-5">
                                  <div class="user-about-block">
                                    <div class="change-profile">
                                        <div class="profile-dp m-auto shadow">
                                                                                   
                                        </div>
                                    </div>
                                  </div>
                                </div>
                                <table id="user-detail"></table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

  <script type="text/javascript">
    function changeStatus(id,status){
      var status_msg = (status == 1)?'active':'deactivate';
      var status_msg_1 = (status == 1)?'activated':'deactivated';
      var icons = (status == 1)?"success":"warning"; 
      swal({
        title: "Are you sure you want to "+status_msg+" this?",
        icon: icons,
        buttons: [
          'No, cancel it!',
          'Yes, I am sure!'
        ],
        dangerMode: false,
      }).then(function(isConfirm) {
        if (isConfirm) {
          $.ajax({
                 url: "{{ route('admin.activate') }}", 

                data: {'status': status, 'id': id},
                 success:function(data)
                 {

                  swal("", "User account successfully "+status_msg_1, icons)
                  // setTimeout(function(){
                  //  $('.datatable').DataTable().ajax.reload();
                  // }, 1000);
                  // console.log(data);
                  $("#status_"+id).html(data);
                 }
                });
        } 
      });
    
    }
      $(function () {
        
        var table = $('.datatable').DataTable({
            processing: true,
            stateSave: true,
            serverSide: true,
            ajax: "{{ route('admin.user') }}",
            columns: [
                {data: 'rownum', name: 'rownum'},
                {data: 'name', name: 'name'},
                {data: 'email', name: 'email'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
        
      });
    </script>
@endsection
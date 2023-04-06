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
              <h4 class="card-title">Restaurant List</h4>
              
              <div class="table-responsive">
                <table class="table table-bordered datatable">
                  
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Restaurant Category</th>
                            <th>Restaurant Name</th>
                            <th>Restaurant Email</th>
                            <th>Is Approved</th>
                            <!-- <th>Status</th> -->
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Restaurant Category</th>
                            <th>Restaurant Name</th>
                            <th>Restaurant Email</th>
                            <th>Is Approved</th>
                            <!-- <th>Status</th> -->
                            <th>Action</th>
                        </tr>
                    </tfoot>
                </table>
              </div>
            </div>
          </div>
        </div>
        
      </div>
    </div>
    <!-- content-wrapper ends -->
    
  </div>
 <div id="addCategoryModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalTitle" aria-hidden="true">
    
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
                   url: "{{ route('restaurant.activate') }}", 

                  data: {'status': status, 'id': id},
                   success:function(data)
                   {

                    swal("", "Restaurant successfully "+status_msg_1, icons)
                    // setTimeout(function(){
                    //  $('.datatable').DataTable().ajax.reload();
                    // }, 1000);
                    $("#status_"+id).html(data);
                   }
                  });
          } 
        });
  
  }
      $(function () { 
        
        var table = $('.datatable').DataTable({
            processing: true,
        serverSide: true,
        // pageLength: 2,
        ajax: "{{ route('restaurant.list') }}",
        columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex'
            },
            {
                data: 'category_name',
                name: 'category_name'
            },{
                data: 'name',
                name: 'name'
            },{
                data: 'email',
                name: 'email'
            },
            {
                data: 'is_approved',
                name: 'is_approved'
            },
            // {
            //     data: 'status',
            //     name: 'status'
            // },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            },
        ]
        });
        
      });


    
    </script>
@endsection
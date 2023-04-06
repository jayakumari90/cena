@extends('layouts.admin')
 
@section('content')

<script src = "{{asset('public/assets/js/jquery.dataTables.min.js') }}" defer ></script>

<div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
       
        <div class="col-lg-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Plans</h4>
              
              <div class="table-responsive">
                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Plan Type</th>
                            <th>Price</th>
                            <th>Description</th>
                            <th width="150px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Plan Type</th>
                            <th>Price</th>
                            <th>Description</th>
                            <th width="150px">Action</th>
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

<div id="editSubscriptionModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editSubscriptionModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSubscriptionModalTitle">Edit Subscription</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form method="post" action="{{route('plan.edit')}}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="inputSubscriptionTitle">Title</label>
                        <input type="text" class="form-control" name="name" required id="inputSubscriptionTitle" placeholder="Enter title">
                    </div>
                    <div class="form-group">
                        <label for="inputSubscriptionTitle">Plan Type</label>
                        <input type="text" class="form-control" name="plan_type" required id="inputSubscriptionType" placeholder="Enter title" readonly>
                    </div>
                    <div class="form-group">
                        <label for="inputSubscriptionTitle">Amount</label>
                        <input type="text" class="form-control" name="price" required id="inputSubscriptionPrice" placeholder="Enter price">
                    </div>
                    <div class="form-group">
                        <label for="inputSubscriptionDescription">Description</label>
                        <textarea class="form-control" name="description" required id="inputSubscriptionDescription"></textarea>
                    </div>
                    <div class="form-group">
                        <input type="hidden" value="" name="subscription_id" id="subscription_id">
                        <button type="submit" class="btn btn-primary m-auto d-block rounded-0" style="width:200px">Update Subscription</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

      $(function () { 
        
        var table = $('.datatable').DataTable({
            processing: true,
        serverSide: true,
        // pageLength: 2,
        ajax: "{{ route('plan.list') }}",
        columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex'
            },
            {
                data: 'name',
                name: 'name'
            },
            {
                data: 'plan_type',
                name: 'plan_type'
            },
            {
                data: 'price',
                name: 'price'
            },
            {
                data: 'description',
                name: 'description'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            },
        ]
        });
        
      });

    function getPlanDetail(plan_id) {
        $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        jQuery.ajax({
            url: "{{ route('plan.detail') }}",
            method: 'post',
            data: {
                id: plan_id,
            },
            success: function(result) {
                $('#editSubscriptionModal #subscription_id').val(result.id);
                $('#editSubscriptionModal #inputSubscriptionTitle').val(result.name);
                $('#editSubscriptionModal #inputSubscriptionType').val(result.plan_type);
                $('#editSubscriptionModal #inputSubscriptionPrice').val(result.price);
                $('#editSubscriptionModal #inputSubscriptionDescription').text(result.description);
                
                $('#editSubscriptionModal').modal();

            }
        });
    }

 

    function editRestroCat() {
        $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        jQuery.ajax({
            url: "{{route('plan.edit')}}",
            method: 'post',
            data: $('#edit-restaurant').serialize(),
            success: function(result) {
                if(result.status == false){
                    $.each(result.msg, function(key, value){
                        // console.log(value);
                        $('#editMsg').html('<span style="alert alert-danger">'+value+'</span>')
                    })
                }else{
                    $('#editMsg').html('<span class="alert alert-success">'+result.msg+'</span>');
                    setInterval(window.location.reload(), 10000);
                }

            }
        });
    }

    
    </script>
@endsection
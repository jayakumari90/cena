@extends('layouts.admin')
 
@section('content')

<script src = "http://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js" defer ></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote.min.css" integrity="sha512-KbfxGgOkkFXdpDCVkrlTYYNXbF2TwlCecJjq1gK5B+BYwVk7DGbpYi4d4+Vulz9h+1wgzJMWqnyHQ+RDAlp8Dw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        
        <div class="col-lg-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">App Pages</h4>
              
              <div class="table-responsive pt-3">
                <table class="table table-bordered datatable">
                  <thead>
                                                   <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Title</th>
                                                        <th>Description</th>
                                                        <th>Price (in USD)</th>
                                                        <th>Status</th>
                                                        <th width="150px">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Title</th>
                                                        <th>Description</th>
                                                        <th>Price (in USD)</th>
                                                        <th>Status</th>
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
<div id="addSubscriptionModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="addSubscriptionModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSubscriptionModalTitle">Add Subscription</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form method="post" action="{{route('subscription.add')}}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="inputSubscriptionTitle">Title</label>
                        <input type="text" class="form-control" name="subscription_title" required id="inputSubscriptionTitle" placeholder="Enter title">
                    </div>
                    <div class="form-group">
                        <label for="inputSubscriptionTitle">Amount</label>
                        <input type="text" class="form-control" name="subscription_price" required id="inputSubscriptionPrice" placeholder="Enter price">
                    </div>
                    <div class="form-group">
                        <label for="inputSubscriptionDescription">Description</label>
                        <textarea class="form-control" name="subscription_description" required id="inputSubscriptionDescription"></textarea>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary m-auto d-block rounded-0" style="width:200px">Add Subscription</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<div id="editSubscriptionModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editSubscriptionModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSubscriptionModalTitle">Edit Subscription</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form method="post" action="{{route('subscription.edit')}}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="inputSubscriptionTitle">Title</label>
                        <input type="text" class="form-control" name="subscription_title" required id="inputSubscriptionTitle" placeholder="Enter title">
                    </div>
                    <div class="form-group">
                        <label for="inputSubscriptionTitle">Amount</label>
                        <input type="text" class="form-control" name="subscription_price" required id="inputSubscriptionPrice" placeholder="Enter price">
                    </div>
                    <div class="form-group">
                        <label for="inputSubscriptionDescription">Description</label>
                        <textarea class="form-control" name="subscription_description" required id="inputSubscriptionDescription"></textarea>
                    </div>
                    <div class="form-group">
                        <input type="hidden" value="" name="subscription_id" id="edit_subscription_id">
                        <button type="submit" class="btn btn-primary m-auto d-block rounded-0" style="width:200px">Update Subscription</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- <script type="text/javascript" src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js"></script> -->
<script src="http://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote.js" defer></script>
  <script type="text/javascript">

      $(function () { 
        
        var table = $('.datatable').DataTable({
            processing: true,
        serverSide: true,
        // pageLength: 2,
        ajax: "{{ route('subscription.list') }}",
        columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex'
            },
            {
                data: 'title',
                name: 'title'
            },
            {
                data: 'description',
                name: 'description'
            },
            {
                data: 'price',
                name: 'price'
            },
            {
                data: 'status',
                name: 'status'
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
      function getSubscriptionDetail(subscription_id) {
        jQuery.ajax({
            url: "{{ route('subscription.detail') }}",
            method: 'post',
            data: {
                id: subscription_id,
            },
            success: function(result) {
                $('#editSubscriptionModal #inputSubscriptionTitle').val(result.title);
                $('#editSubscriptionModal #edit_subscription_id').val(result.id);
                $('#editSubscriptionModal #inputSubscriptionPrice').val(result.price);
                $('#editSubscriptionModal #inputSubscriptionDescription').text(result.description);
                
                $('#editSubscriptionModal').modal();

            }
        });
    }

    function activateSubscription(subscription_id) {
        jQuery.ajax({
            url: "{{ route('subscription.activate') }}",
            method: 'post',
            data: {
                id: subscription_id,
            },
            success: function(result) {
                if(result.status)   {
                    table.ajax.reload( null, false );
                    notify(result.message, "success");

                }
                else    {
                    notify(result.message, "danger");
                }
            }
        });
    }
    function deactivateSubscription(subscription_id) {
        jQuery.ajax({
            url: "{{ route('subscription.deactivate') }}",
            method: 'post',
            data: {
                id: subscription_id,
            },
            success: function(result) {
                if(result.status)   {
                    table.ajax.reload( null, false );
                    notify(result.message, "success");
                }
                else    {
                    notify(result.message, "danger");
                }
            }
        });
    }
    </script>
@endsection
@extends('layouts.admin')
 
@section('content')

<script src = "{{asset('public/assets/js/jquery.dataTables.min.js') }}" defer ></script>

<div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
     
        <div class="col-lg-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Struggling List</h4>
              <a href="" class="btn btn-primary" data-toggle="modal" data-target="#addCategoryModal" style="float: right;margin-top:-30px;margin-bottom:10px;">Add Struggling</a>
              @error('options')
                      <div class="form-group text-right">
                          <span class="text-danger" role="alert">
                              <strong>{{ $message }}</strong>
                          </span>
                      </div>
                      @enderror
                <div class="table-responsive">
                <table class="table table-bordered datatable">
                  
                       <thead>
                        <tr>
                            <th>#</th>
                            <th>Options</th>
                            <th width="150px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Options</th>
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
 <div id="addCategoryModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSubscriptionModalTitle">Add Stuggling Options</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="addMsg"></div>
                <form method="post" action="#" enctype="multipart/form-data" id="add-struggling">
                    @csrf
                    <div class="form-group">
                        <label for="inputSubscriptionTitle">Option</label>
                        <input type="text" class="form-control" name="options" required id="options" placeholder="Enter title">
                        
                    </div>
                    
                    <div class="form-group">
                        <button type="button" class="btn btn-primary m-auto d-block rounded-0" style="width:200px" onclick="addStruggling()">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<div id="editStrugglingModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editStrugglingModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editStruggling">Edit Struggling</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                @if(session('status'))
                <div class="alert alert-{{session('type')}} alert-dismissible fade show" role="alert">
                    {{session('status')}}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                @endif
                <div id="editMsg"></div>
                <form method="post" action="#" enctype="multipart/form-data" id="edit-struggling">
                    @csrf
                    <div class="form-group">
                        <label for="inputSubscriptionTitle">Options</label>
                        <input type="text" class="form-control" name="options" required id="option" placeholder="Enter option">
                        @error('options')
                      <div class="form-group text-right">
                          <span class="text-danger" role="alert">
                              <strong>{{ $message }}</strong>
                          </span>
                      </div>
                      @enderror
                    </div>
                    
                    <div class="form-group">
                        <input type="hidden" value="" name="struggling_id" id="edit_struggling_id">
                        <button type="button" class="btn btn-primary m-auto d-block rounded-0" style="width:200px" onclick="editStruggling()">Update </button>
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
        ajax: "{{ route('struggling.list') }}",
        columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex'
            },
            {
                data: 'options',
                name: 'options'
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
    function getStrugglingDetail(subscription_id) {
        $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        jQuery.ajax({
            url: "{{ route('struggling.detail') }}",
            method: 'post',
            data: {
                id: subscription_id,
            },
            success: function(result) {
                $('#editStrugglingModal #option').val(result.options);
                $('#editStrugglingModal #edit_struggling_id').val(result.id);
                
                $('#editStrugglingModal').modal();

            }
        });
    }

    function addStruggling() {
        $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        jQuery.ajax({
            url: "{{route('struggling.add')}}",
            method: 'post',
            data: $('#add-struggling').serialize(),
            success: function(result) {
                if(result.status == false){
                    $.each(result.msg, function(key, value){
                        console.log(value);
                        $('#addMsg').html('<span style="alert alert-danger">'+value+'</span>')
                    })
                }else{
                    $('#addMsg').html('<span class="alert alert-success">'+result.msg+'</span>');
                    setInterval(window.location.reload(), 10000);
                }

            }
        });
    }

    function editStruggling() {
        $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        jQuery.ajax({
            url: "{{route('struggling.edit')}}",
            method: 'post',
            data: $('#edit-struggling').serialize(),
            success: function(result) {
                if(result.status == false){
                    $.each(result.msg, function(key, value){
                        console.log(value);
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
@extends('layouts.admin')
 
@section('content')

<script src = "{{asset('public/assets/js/jquery.dataTables.min.js') }}" defer ></script>

<div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
       
        <div class="col-lg-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Dish Category</h4>
              <a href="" class="btn btn-primary" data-toggle="modal" data-target="#addCategoryModal" style="float: right;margin-top:-30px;margin-bottom:10px;">Add Category</a>
              
              <div class="table-responsive">
                <table class="table table-bordered datatable">
           
                   <thead>
                    <tr>
                        <th>#</th>
                        <th>category_name</th>
                        <th width="150px">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th>category_name</th>
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
                <h5 class="modal-title" id="addSubscriptionModalTitle">Add Dish Category</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="addMsg"></div>
                <form method="post" action="#" enctype="multipart/form-data" id="add-dish">
                    @csrf
                    <div class="form-group">
                        <label for="inputSubscriptionTitle">Category Name</label>
                        <input type="text" class="form-control" name="category_name" required id="category_name" placeholder="Enter title">
                    </div>
                    
                    <div class="form-group">
                        <button type="button" class="btn btn-primary m-auto d-block rounded-0" style="width:200px" onclick="addDishCat()">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="editDishCatModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editDishCatModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDishCatModaltitle">Edit Dish Category</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="editMsg"></div>
                <form method="post" action="#" enctype="multipart/form-data" id="edit-dish">
                    @csrf
                    <div class="form-group">
                        <label for="inputSubscriptionTitle">Category Name</label>
                        <input type="text" class="form-control" name="category_name" required id="category_name" placeholder="Enter title">
                    </div>
                    
                    <div class="form-group">
                        <input type="hidden" value="" name="cat_id" id="edit_cat_id">
                        <button type="button" class="btn btn-primary m-auto d-block rounded-0" style="width:200px" onclick="editDishCat()">edit Category</button>
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
        ajax: "{{ route('dish_cat.list') }}",
        columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex'
            },
            {
                data: 'category_name',
                name: 'category_name'
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

      function getDishCatDetail(cat_id) {
        $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        jQuery.ajax({
            url: "{{ route('dish_cat.detail') }}",
            method: 'post',
            data: {
                id: cat_id,
            },
            success: function(result) {
                $('#editDishCatModal #category_name').val(result.category_name);
                $('#editDishCatModal #edit_cat_id').val(result.id);
                
                $('#editDishCatModal').modal();

            }
        });
    }

    function addDishCat() {
        $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        jQuery.ajax({
            url: "{{route('dish_cat.add')}}",
            method: 'post',
            data: $('#add-dish').serialize(),
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
    function editDishCat() {
        $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        jQuery.ajax({
            url: "{{route('dish_cat.edit')}}",
            method: 'post',
            data: $('#edit-dish').serialize(),
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
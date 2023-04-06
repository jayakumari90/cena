@extends('layouts.admin')
 
@section('content')

<script src = "{{asset('public/assets/js/jquery.dataTables.min.js') }}" defer ></script>

<div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        
        <div class="col-lg-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Dish List</h4>
              
              <div class="table-responsive">
                <table class="table table-bordered datatable">
            
                   <thead>
                    <tr>
                        <th>#</th>
                        <th>Restaurant Name</th>
                        <th>Dish Category</th>
                        <th>Dish Name</th>
                        <th>Dish Price</th>
                        <th width="150px">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th>Restaurant Name</th>
                        <th>Dish Category</th>
                        <th>Dish Name</th>
                        <th>Dish Price</th>
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

 <script type="text/javascript">

      $(function () { 
        
        var table = $('.datatable').DataTable({
            processing: true,
        serverSide: true,
        // pageLength: 2,
        ajax: "{{ route('dish.list') }}",
        columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex'
            },
            {
                data: 'name',
                name: 'name'
            },{
                data: 'getcategory.category_name',
                name: 'getcategory.category_name'
            },{
                data: 'name',
                name: 'name'
            },{
                data: 'price',
                name: 'price'
            },{
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
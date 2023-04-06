@extends('layouts.admin')
 
@section('content')

<script src = "{{asset('public/assets/js/jquery.dataTables.min.js') }}" defer ></script>

<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.1/css/all.css" crossorigin="anonymous">
<link rel="stylesheet" type="text/css" href="{{asset('public/assets/css/daterangepicker.css') }}" />
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Orders</h4>
                  
                        <div class="table-responsive">

                            <form class="form-horizontal m-t-30 row" autocomplete="off" method="get" action="">
                                <div class="col-md-4">
                                    <div class="form-group d-md-flex align-items-center">
                                        <label> Filters</label>    
                                        <input type="text" name="daterange" value="{{(isset($_GET['daterange']))?$_GET['daterange']:''}}" class="form-control ml-md-2 daterange_input" required="" />
                                    </div>             
                                </div>
                                <div class="col-md-4 text-md-left" >
                                    <button class="btn btn-success dt-button buttons-print" type="submit">Submit</button>
                                    <a href="javascript:void();" id="reset" ><button class="btn btn-success dt-button buttons-print" type="button">Reset</button></a>
                                </div>
                            </form>
                    

                            <table class="table table-bordered datatable">
                                
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>User Name</th>
                                        <th>Restaurant Name</th>
                                        <th>Order Date</th>
                                        <th>Paid Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                        <!-- <th>Action</th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>#</th>
                                        <th>User Name</th>
                                        <th>Restaurant Name</th>
                                        <th>Order Date</th>
                                        <th>Paid Amount</th>
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
<script type="text/javascript" src="{{asset('public/assets/js/moment.min.js') }}"></script>
<script type="text/javascript" src="{{asset('public/assets/js/daterangepicker.min.js')}}"></script>
<script>
   
    $('input[name="daterange"]').daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear',
            // format: 'DD/MM/YYYY'
            format: 'MM/DD/YYYY'
        },
        opens: 'left',
        // $('.daterange_input').val(start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, function(start, end, label) {

            console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));

            // $('.daterange_input').val(start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
            $('.daterange_input').val(start.format('MM/DD/YYYY') + ' to ' + end.format('MM/DD/YYYY'));

        });
    // cb(start, end);
    $('#reset').click(function() {
        window.location.href = '{{route('order')}}'; 
    });
      
   

$(function() {
        var table = $('.datatable').DataTable({
            processing: true,
        serverSide: true,
        // pageLength: 2,
        
         "ajax":{
            "url": '{!! route('order.list') !!}',
            "dataType": "json",
            "type": "POST",           
            "data":{
             // 'dfdfdf': "{{csrf_token()}}",
                 '_token': "{{csrf_token()}}",
                 'daterange':$('.daterange_input').val()
             }
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex'
            },
            {
                data: 'user_id',
                name: 'user_id'
            },
            {
                data: 'restro_id',
                name: 'restro_id'
            },
            {
                data: 'date',
                name: 'date'
            },
            {
                data: 'paid_amount',
                name: 'paid_amount'
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

    </script>
@endsection
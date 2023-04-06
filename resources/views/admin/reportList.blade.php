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
                        @if(Session::has('message'))
                        <div class="alert alert-success" role="alert">
                                  {{ Session::get('message') }}
                                </div>
                            @endif
                        <h4 class="card-title">Reported Users List</h4>
                  
                        <div class="table-responsive">
                            <table class="table table-bordered datatable">
                                
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>User Name</th>
                                        <th>Reported User Name</th>
                                        <th>Responsed At</th>
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
                                        <th>Reported User Name</th>
                                        <th>Responsed At</th>
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

$(function() {
        var table = $('.datatable').DataTable({
            processing: true,
        serverSide: true,
        // pageLength: 2,
        
         "ajax":{
            "url": '{!! route('reported.users.list') !!}',
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
                data: 'reported_user_id',
                name: 'reported_user_id'
            },
            {
                data: 'responded_at',
                name: 'responded_at'
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
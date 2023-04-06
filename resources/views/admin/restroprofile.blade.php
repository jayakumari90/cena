@extends('layouts.admin')
 
@section('content')

<script src = "{{asset('public/assets/js/jquery.dataTables.min.js') }}" defer ></script>

<div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        
        <div class="col-lg-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Restaurant Detail</h4>
              
              <div class="table-responsive pt-3">
                <table class="table table-bordered datatable">
                    
                        <tr>                        
                            <th>Restaurant Category</th>
                            <td>{{ $restaurant->category_name }}</td>
                        </tr>
                        <tr>
                            <th>Restaurant Name</th>
                            <td>{{ $restaurant->name }}</td>
                        </tr>
                        <tr>
                            <th>Restaurant Email</th>
                            <td>{{ $restaurant->email }}</td>
                        </tr>
                        <tr>
                            <th>Licence Number</th>
                            <td>{{ $restaurant->license_number }}</td>
                        </tr>
                        <tr>
                            <th>Address</th>
                            <td>{{ $restaurant->address }}</td>
                        </tr>
                        @if($restaurant->struggling_restaurant == '1')
                            <tr>
                                <th>Struggling Options</th>
                                <td>{{ $restaurant->struggling_options }}</td>
                            </tr>
                            
                       
                            @if($restaurant->getStrugglingDocs) 
                            <tr>
                                <th>Struggling Document</th>
                                <td>
                                @foreach($restaurant->getStrugglingDocs as $k => $v)

                                <a href="{{ $v->document }}">{{ $v->document }}</a>
                                @endforeach
                                </td>
                            </tr>
                            @endif

                        @endif
                                        
                </table>
                
                <table style="border-spacing: 5px 10px;">
                      <tr>
                        
                        @foreach($restaurant->getRestaurantImages as $k => $v)

                        <td height="150px"><img src="{{ $v->thumb }}" width="150px" /></td>
                        @endforeach
                        
                    </tr>
                </table>
              </div>
            </div>
          </div>
        </div>
        
      </div>
    </div>
    <!-- content-wrapper ends -->
    
  </div>
@endsection
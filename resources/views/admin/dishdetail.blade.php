@extends('layouts.admin')
 
@section('content')

<script src = "{{asset('public/assets/js/jquery.dataTables.min.js') }}" defer ></script>

<div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        
        <div class="col-lg-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">Dish Detail</h4>
              
              <div class="table-responsive pt-3">
                <table class="table table-bordered ">
                    
                        <tr>
                            <th>Restaurant Name</th>
                            <td>{{ isset($dish->getrestro->name) ? $dish->getrestro->name : '' }}</td>
                        </tr>
                        <tr>
                        
                            <th>Dish Category</th>
                            <td>{{ isset($dish->getcategory->category_name) ? $dish->getcategory->category_name : ''}}</td>

                        </tr>
                        <tr>
                            <th>Dish Name</th>
                            <td>{{ $dish->name }}</td>
                        </tr>
                        <tr>
                            <th>Dish Price</th>
                            <td>{{ $dish->price }}</td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td>{{ $dish->description }}</td>
                        </tr>
                        <tr>
                            <th>Discount</th>
                            <td>{{ $dish->discount }}</td>
                        </tr>
                                              
                </table>
                @if($dish->getDishImages)
                <table style="border-spacing: 5px 10px;" class="table table-bordered">
                      <tr>
                        
                        @foreach($dish->getDishImages as $k => $v)

                        <td height="150px"><img src="{{ $v->thumb }}" width="150px" /></td>
                        @endforeach
                        
                    </tr>
                </table>
                @endif
              </div>
            </div>
          </div>
        </div>
        
      </div>
    </div>
    <!-- content-wrapper ends -->
    
  </div>

@endsection
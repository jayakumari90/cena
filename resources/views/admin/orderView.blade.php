@extends('layouts.admin')
 
@section('content')

<script src = "{{asset('public/assets/js/jquery.dataTables.min.js') }}" defer ></script>

<style>
.table th, .table td {
    padding: 0.25rem 0.9375rem;
    vertical-align: top;
    border-top: 1px solid #f3f3f3;
}
</style>
<div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        
        <div class="col-lg-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <!-- <h4 class="card-title">Order Detail</h4> -->
              
              <div class="table-responsive pt-3">
                <table class="table table-bordered ">
                        <tr>
                            <th>User Name</th>
                            <td>{{ isset($order->getUserDetail->name) ? $order->getUserDetail->name : '' }}</td>
                        </tr>
                        <tr>
                            <th>Restaurant Name</th>
                            <td>{{ isset($order->getRestroDetail->name) ? $order->getRestroDetail->name : '' }}</td>
                        </tr>
                        <tr>
                        
                            <th>Date & Time</th>
                            <td>{{ isset($order->date) ? date('m-d-Y h:i A',strtotime($order->date)) : ''}}</td>

                        </tr>
                        <tr>
                            <th>Total Amount</th>
                            <td>{{ $order->total_amount }}</td>
                        </tr>
                        <tr>
                            <th>Discount</th>
                            <td>{{ $order->discount }}</td>
                        </tr>
                        <tr>
                            <th>Tax Amount</th>
                            <td>{{ $order->tax_amount }}</td>
                        </tr>
                        <tr>
                            <th>Paid Amount</th>
                            <td>{{ $order->paid_amount }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <?php
                                if ($order->status == 'cancelled') {
                                    echo 'Cancelled by User';
                                } 

                                else if ($order->status == 'rejected') {
                                    echo 'Cancelled by Restaurant';
                                }
                                else{
                                    echo ucwords($order->status);
                                }
                                ?>
                            </td>
                        </tr>
                           <tr>
                            <th>Rating</th>
                            <td>{{ $order->rating }}</td>
                        </tr>
                           <tr>
                            <th>Rating Text</th>
                            <td>{{ $order->rating_text }}</td>
                        </tr>
                                              
                </table>
                @if($order->getOrderItems)
                <br/><br/>
                <table style="border-spacing: 5px 10px;" class="table table-bordered">
                      <tr>
                        <th>Dish Name</th>
                        <th>Quantity</th>
                        <th>Original Price</th>
                        <th>Discount</th>
                        <th>Best Price</th>
                      </tr>
                      
                        
                        @foreach($order->getOrderItems as $k => $v)
                        <tr>
                            <td>{{$v->getDishDetail->name}}</td>
                            <td >{{$v->quantity}}</td>
                            <td >{{$v->original_price}}</td>
                            <td >{{$v->discount}}</td>
                            <td >{{$v->best_price}}</td>
                        </tr>
                        @endforeach
                        
                    
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
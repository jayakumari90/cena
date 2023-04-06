<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

use DataTables;

class OrderController extends Controller
{
    /**
     * Show products.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function orderList()
    {
        $title = "Order List";
        $breadcum = ['Orders'=>route('order')];
        return view('admin.orderList', compact('title','breadcum'));
    }

    /**
     * Show the session list.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showOrderList(Request $request)
    {
        $order = Order::select('id','user_id','restro_id','date', 'paid_amount', 'status');
         $daterange= ($request->input('daterange'))?$request->input('daterange'):'';
        if(!empty($daterange)){
            $daterange_arr = explode(' to ',$daterange);

            if(!empty($daterange_arr) && is_array($daterange_arr) && !empty($daterange_arr[0]) && !empty($daterange_arr[1])){
                // dd($daterange_arr[1]);

                $newDate = array(
                     date('Y-m-d', strtotime($daterange_arr[0])),
                    date('Y-m-d', strtotime($daterange_arr[1])),

                  );
                $order = $order->whereBetween('date', [$newDate[0]." 00:00:00",$newDate[1]." 23:59:59"]);
            }
        }


        $order = $order->orderBy('id','desc')->get();
        // dd($order->toArray());
        return Datatables::of($order)
            ->addIndexColumn()
            
             ->editColumn('user_id', function ($row) {
                if ($row->user_id) {
                    return isset($row->getUserDetail)?$row->getUserDetail->name:'';
                } 
            })

            ->editColumn('date', function ($row) {
                if ($row->date) {
                    return date('m-d-Y',strtotime($row->date));
                } 
            })

             ->editColumn('restro_id', function ($row) {
                if ($row->restro_id) {
                    return isset($row->getRestroDetail)?$row->getRestroDetail->name:'';
                } 
            })
         
            ->editColumn('status', function ($row) {
                if ($row->status == 'cancelled') {
                    return 'Cancelled by User';
                } 

                else if ($row->status == 'rejected') {
                    return 'Cancelled by Restaurant';
                }
                else{
                    return ucwords($row->status);
                }
            })
           ->addColumn('action', function($row){
                        $btn = '';
                        
                        $btn .= '<a href="' . route('order.view', $row->id) . '" type="button" data-toggle="tooltip" title="View" data-title="View" class="btn btn-warning btn-sm">View</a>';
                        return $btn;
                    })
            ->rawColumns(['action', 'user_id','restro_id','date'])
            ->make(true);
    }

    public function viewOrder($id)
    {
        $breadcum = ['Orders'=>route('order'),'View Order' =>''];
        $order = Order::where('id', $id)->with('getOrderItems')->first();
        if($order){
            return view('admin.orderView', compact('order','breadcum'));
        }else{
            return view("errors/404");
        }
        
    }
}

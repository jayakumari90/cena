<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Plan;
use DataTables;

class PlanController extends Controller
{
    public function index(Request $request){
        return view('admin.plansList');
    }


    public function planListing()
    {
        $struggling = Plan::get();

        return Datatables::of($struggling)
            ->addIndexColumn()
                    ->addColumn('action', function($row){
                        $btn = '';
                        $btn .= '<button type="button" class="btn btn-primary btn-sm" onclick="getPlanDetail(' . $row->id . ')">Edit</button>';
                        
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
    }

    public function editPlan(Request $request)
    {
        if($request->isMethod('post')){
            $validator = Validator::make($request->all(), [
                'subscription_id' => 'required',
                'name' => 'required',
                'description' => 'required',
                'price' => 'required',
            ]);

            if ($validator->fails()) {
                $arr = array('msg' => $validator->errors(), 'status' => false);
                    return Response()->json($arr);
            }else{

                $subscription = Plan::whereId($request->subscription_id)->first();
                if ($subscription) {
                    // $subscription->name = $request->get('name');
                    $subscription->description = $request->get('description');
                    $subscription->price = $request->get('price');

                    $subscription->save();

                    return redirect()->back()->with(['type' => 'success', 'status' => 'Subscription successfully updated']);
                } else {
                    return redirect()->back()->with(['type' => 'danger', 'status' => 'Subscription not found']);
                }
            }

        }
    }

    public function viewPlan(Request $request)
    {
        $music = Plan::where('id', $request->id)->first();

        return response()->json($music, 200);
    }
}


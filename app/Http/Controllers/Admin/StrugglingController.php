<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\RestaurantCategory;
use App\Models\Struggling;
use App\Models\User;
use App\Models\RestaurantDetail;
use DataTables;

class StrugglingController extends Controller
{
    public function struglingList(Request $request){
        return view('admin.struggling');
    }

    /**
     * Show the Struggling list.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showStruglingList()
    {
        $struggling = Struggling::orderBy('created_at', 'desc')->get();

        return Datatables::of($struggling)
            ->addIndexColumn()
                    ->addColumn('action', function($row){
                        $btn = '';
                        $btn .= '<button type="button" class="btn btn-primary btn-sm" onclick="getStrugglingDetail(' . $row->id . ')">Edit</button>';
                        $btn .= '<a href="' . route('struggling.delete', $row->id) . '" type="button" data-toggle="tooltip" data-title="Delete" title="Delete" class="btn btn-danger btn-sm">Delete</a>';
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
    }

    public function addStrugling(Request $request){
        if($request->isMethod('post')){
          // print_r($request->all());die('jjkhjj');
            $validator = Validator::make($request->all(), [
                'options' => 'required|unique:strugglings,options'
            ]);

            if ($validator->fails()) {
                $arr = array('msg' => $validator->errors(), 'status' => false);
                return Response()->json($arr);
            }else{
                Struggling::create([
                    'options'=>$request->options
                ]);
                return Response()->json(['status'=>true,'type' => 'success', 'msg' => 'option added successfully']);    
            }
            
        }
    }

    public function strugglingDetail(Request $request)
    {
        $struggling = Struggling::where('id', $request->id)->first();
        
        return response()->json($struggling, 200);
    }

    public function editStruggling(Request $request)
    {
        if($request->isMethod('post')){
            $validator = Validator::make($request->all(), [
                'options' => 'required|unique:strugglings,options'
            ]);

            if ($validator->fails()) {
                $arr = array('msg' => $validator->errors(), 'status' => false);
                    return Response()->json($arr);
            }else{
                
                    $struggling = Struggling::whereId($request->struggling_id)->first();
                    if ($struggling) {
                        $struggling->options = $request->get('options');
                        $struggling->save();

                        return Response()->json(['status'=>true,'type' => 'success', 'msg' => 'option updated successfully']); 
                    } 
            }

        }
    }

    /**
     * Delete Category
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function deletestuggling($id)
    {
        Struggling::where('id', $id)->delete();
        return redirect()->back()->with(['type' => 'success', 'status' => "Seasonally affected deleted successfully"]);
    }

    
   

}


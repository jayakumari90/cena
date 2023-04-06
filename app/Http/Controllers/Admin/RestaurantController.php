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

class RestaurantController extends Controller
{
    public function restauraCatList(Request $request){
        return view('admin.restaurant_cat');
    }

    public function showRestauraCatList()
    {
        $restaurentCat = RestaurantCategory::orderBy('created_at', 'desc')->get();

        return Datatables::of($restaurentCat)
            ->addIndexColumn()
                    ->addColumn('action', function($row){
                        $btn = '';
                        $btn .= '<button type="button" class="btn btn-primary btn-sm" onclick="getRestroCatDetail(' . $row->id . ')">Edit</button>';
                        $btn .= '<a href="' . route('restaurant_cat.delete', $row->id) . '" type="button" data-toggle="tooltip" data-title="Delete" title="Delete" class="btn btn-danger btn-sm">Delete</a>';
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
    }

    public function restroCatDetail(Request $request)
    {
        $restrocat = RestaurantCategory::where('id', $request->id)->first();
        
        return response()->json($restrocat, 200);
    }

    public function addRestauraCat(Request $request){
        if($request->isMethod('post')){
           
            $validator = Validator::make($request->all(), [
                'category_name' => 'required|unique:restaurant_categories,category_name'
            ]);

            if ($validator->fails()) {
                $arr = array('msg' => $validator->errors(), 'status' => false);
                    return Response()->json($arr);
            }else{

                RestaurantCategory::create([
                    'category_name'=>$request->category_name
                ]);
                return Response()->json(['status'=>true,'type' => 'success', 'msg' => 'Category updated successfully']);
            }
        }
    }

    public function editRestaurantCat(Request $request)
    {
        if($request->isMethod('post')){
           //print_r($request->all());die('sas');
            $validator = Validator::make($request->all(), [
                'category_name' => 'required|unique:restaurant_categories,category_name'
            ]);
            if ($validator->fails()) {
                //die('yes');
                $arr = array('msg' => $validator->errors(), 'status' => false);
                    return Response()->json($arr);
            }else{
                //die('no');
                    $restrocat = RestaurantCategory::whereId($request->cat_id)->first();
                    if ($restrocat) {
                        $restrocat->category_name = $request->get('category_name');
                        $restrocat->save();

                        return Response()->json(['status'=>true,'type' => 'success', 'msg' => 'category updated successfully']); 
                    } 
            }

        }
    }

    /**
     * Delete Category
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function deleteCat($id)
    {
        RestaurantCategory::where('id', $id)->delete();
        return redirect()->back()->with(['type' => 'success', 'status' => "Category deleted successfully"]);
    }

    public function restaurantList(){
        return view('admin.restaurant');
    }
    
    public function showRestaurantList()
    {
        $restaurent = User::leftJoin('restaurant_details', function($join) {
                  $join->on('users.id', '=', 'restaurant_details.user_id');

                })->leftJoin('restaurant_categories', function($join) {
                  $join->on('restaurant_details.category_id', '=', 'restaurant_categories.id');
                })
                 ->select('users.id',
                    'users.name',
                    'users.email',
                    'users.status',
                    'users.is_approved',
                    'restaurant_details.struggling_restaurant',
                    'restaurant_details.license_number',
                    'restaurant_details.address',
                    'restaurant_categories.category_name',
                )->where('user_type',2)
                ->orderBy('users.created_at', 'desc')->get();
        return Datatables::of($restaurent)
            ->addIndexColumn()
            ->editColumn('status', function ($row) {
                                return ($row->status == 1) ? 'Active':'Inactive';
                           
                        })
            ->editColumn('struggling_restaurant', function ($row) {
                                 $struggling_restaurant = ($row->struggling_restaurant == '0') ? '<span class="f-left margin-r-5 1" id = "strug_status_' . $row->id . '"><a href="#" type="button" data-toggle="tooltip" class="btn btn-danger btn-sm" onclick="checkStruglling('.$row->id.',1)"><i class="fa fa-toggle-on" aria-hidden="true"></i></a></span>':'<span class="f-left margin-r-5 1" id = "strug_status_' . $row->id . '"><a href="#" type="button" data-toggle="tooltip" class="btn btn-success btn-sm" onclick="checkStruglling('.$row->id.',0)" ><i class="fa fa-toggle-off " aria-hidden="true"></i></a></span>';
                            return $struggling_restaurant;
                           
                        })

               ->editColumn('is_approved', function ($row) {
                                return ($row->is_approved == 1)?'Approved':'Not Approved';
                            
                        })
                    ->addColumn('action', function($row){
                        $btn = '';
                        
                        if ($row->status == 0) {
                            $btn .= '<span class="f-left margin-r-5 1" id = "status_' . $row->id . '"><a href="#" type="button" data-toggle="tooltip" class="btn btn-danger btn-sm" onclick="changeStatus('.$row->id.',1)"><i class="fa fa-toggle-on" aria-hidden="true"></i></a></span>';
                        } else {
                            $btn .= '<span class="f-left margin-r-5 1" id = "status_' . $row->id . '"><a href="#" type="button" data-toggle="tooltip" class="btn btn-success btn-sm" onclick="changeStatus('.$row->id.',0)" ><i class="fa fa-toggle-off " aria-hidden="true"></i></a></span>';
                        }
                        $btn .= '<a href="' . route('admin.restroprofile', $row->id) . '" type="button" data-toggle="tooltip" title="View" data-title="Deactivate" class="btn btn-warning btn-sm"><i class="fa fa-eye" aria-hidden="true"></i></a>';

                        if ($row->is_approved != '1') {
                            $btn .= '<a href="' . route('restaurant.approve', $row->id) . '" type="button" data-toggle="tooltip" title="Approve" data-title="Approve" class="btn btn-success btn-sm"><i class="fa fa-check" aria-hidden="true"></i></a>';

                        } else {
                            $btn .= '<a href="' . route('restaurant.disapprove', $row->id) . '" type="button" data-toggle="tooltip" title="Disapprove" data-title="Disapprove" class="btn btn-danger btn-sm"><i class="fa fa-times" aria-hidden="true"></i></a>';
                        }
                        
                        return $btn;
                    })
                    ->rawColumns(['action','struggling_restaurant'])
                    ->make(true);
    }

    public function viewRestroProfile($id){
        $breadcum = ['Restaurants'=>route('admin.restaurant'),'View Restaurant' =>''];
        $restaurant = User::leftJoin('restaurant_details', function($join) {
                  $join->on('users.id', '=', 'restaurant_details.user_id');

                })->leftJoin('restaurant_categories', function($join) {
                  $join->on('restaurant_details.category_id', '=', 'restaurant_categories.id');
                })
                ->leftjoin("strugglings",\DB::raw("FIND_IN_SET(strugglings.id,restaurant_details.struggling_options)"),">",\DB::raw("'0'"))
        ->where('users.id',$id)
        ->select('users.id',
            'users.name',
            'users.email',
            'users.user_type',
            'users.is_onboarding',
            'restaurant_details.license_number',
            'restaurant_details.address',
            'restaurant_details.struggling_restaurant',
            // 'restaurant_details.struggling_options',
            'restaurant_details.lat',
            'restaurant_details.lng',
            'restaurant_categories.category_name',
            \DB::raw("GROUP_CONCAT(strugglings.options) as struggling_options")
        )
        ->with(['getStrugglingDocs','getRestaurantImages'])
        ->groupBy('restaurant_details.user_id')
        ->first();
        
       // print_r($restaurant->getRestaurantDishes);die;
        return view('admin.restroprofile',compact('restaurant','breadcum'));
    }

    public function activateUser(Request $request)
    {
        $status = ($request->status == 1)?'Activated':'deactivated';
        $user = User::where('id', $request->id)->first();
        $user->status = $request->status;
        $user->save();
        // $data = array('status'=>'success','msg'=>'User account successfully '.$status);
        // return json_encode($data);

         $html = '';
            switch ($user->status) {
              case 1:
                   $html =  '<span class="f-left margin-r-5 1" id = "status_' . $user->id . '"><a href="#" type="button" data-toggle="tooltip" class="btn btn-success btn-sm" onclick="changeStatus('.$user->id.',0)" ><i class="fa fa-toggle-off " aria-hidden="true"></i></a></span>';
                  break;
                   case 0:
                   $html =  '<span class="f-left margin-r-5 1" id = "status_' . $user->id . '"><a href="#" type="button" data-toggle="tooltip" class="btn btn-danger btn-sm" onclick="changeStatus('.$user->id.',1)"><i class="fa fa-toggle-on" aria-hidden="true"></i></a></span>';
                  break;
              
              default:
                
                  break;
          }
          return $html;
    }

    public function changeStruglling(Request $request)
    {
        $status = ($request->status == 1)?'Continue':'Cancel';
        
        $restro = RestaurantDetail::where('user_id',$request->id)->first();
        $restro->struggling_restaurant = ($request->status == '1')?'1':'0';
        $restro->save();
        //RestaurantDetail::where('user_id',$request->id)->update(['struggling_restaurant'=>$request->status]);
        // if($request->status == 0){

        // }
        // $data = array('status'=>'success','msg'=>'User account successfully '.$status);
        // return json_encode($data);

         $html = '';
            switch ($restro->struggling_restaurant) {
              case 1:
                    $html =  '<span class="f-left margin-r-5 1" id = "strug_status_' . $restro->user_id . '"><a href="#" type="button" data-toggle="tooltip" class="btn btn-success btn-sm" onclick="checkStruglling('.$restro->user_id.',0)" ><i class="fa fa-toggle-off " aria-hidden="true"></i></a></span>';
                  break;
                   case 0:
                   $html =  '<span class="f-left margin-r-5 1" id = "strug_status_' . $restro->user_id . '"><a href="#" type="button" data-toggle="tooltip" class="btn btn-danger btn-sm" onclick="checkStruglling('.$restro->user_id.',1)"><i class="fa fa-toggle-on" aria-hidden="true"></i></a></span>';

                   
              
              default:
                
                  break;
          }
          return $html;
    }


    public function approveUser($id)
    {
        $user = User::where('id', $id)->first();
        $user->is_approved = '1';
        $user->save();
        return redirect()->back()->with(['type' => 'success', 'status' => "User account successfully activated"]);
    }

     public function disapproveUser($id)
    {
        $user = User::where('id', $id)->first();
        $user->is_approved = '0';
        $user->save();
        return redirect()->back()->with(['type' => 'success', 'status' => "User account successfully activated"]);
    }

}


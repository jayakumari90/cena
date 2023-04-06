<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\DishCategory;
use App\Models\RestroDish;
use App\Models\User;
use App\Models\DishImage;
use DataTables;

class DishController extends Controller
{
    public function dishCatList(Request $request){
        return view('admin.dish_cat');
    }

    /**
     * Show the Category list.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showDishCatList()
    {
        $dishCat = DishCategory::orderBy('created_at', 'desc')->get();

        return Datatables::of($dishCat)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                $btn = '';
                
                $btn .= '<button type="button" class="btn btn-primary btn-sm" onclick="getDishCatDetail(' . $row->id . ')">Edit</button>';
                $btn .= '<a href="' . route('dish_cat.delete', $row->id) . '" type="button" data-toggle="tooltip" data-title="Delete" title="Delete" class="btn btn-danger btn-sm">Delete</a>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function dishCatDetail(Request $request)
    {
        $dishcat = DishCategory::where('id', $request->id)->first();
        
        return response()->json($dishcat, 200);
    }

    public function addDishCat(Request $request){
        if($request->isMethod('post')){
           // die('jjkhjj');
            $validator = Validator::make($request->all(), [
                'category_name' => 'required|unique:dish_categories,category_name'
            ]);

            if ($validator->fails()) {
                $arr = array('msg' => $validator->errors(), 'status' => false);
                    return Response()->json($arr);
            }else {

                DishCategory::create([
                    'category_name'=>$request->category_name
                ]);
                return Response()->json(['status'=>true,'type' => 'success', 'msg' => 'category added successfully']); 
            }
        }
    }

    public function editDishCat(Request $request)
    {
        if($request->isMethod('post')){
           //print_r($request->all());die('sas');
            $validator = Validator::make($request->all(), [
                'category_name' => 'required|unique:dish_categories,category_name'
            ]);
            if ($validator->fails()) {
                //die('yes');
                $arr = array('msg' => $validator->errors(), 'status' => false);
                    return Response()->json($arr);
            }else{
                //die('no');
                    $dishcat = DishCategory::whereId($request->cat_id)->first();
                    if ($dishcat) {
                        $dishcat->category_name = $request->get('category_name');
                        $dishcat->save();

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
        DishCategory::where('id', $id)->delete();
        return redirect()->back()->with(['type' => 'success', 'status' => "Category deleted successfully"]);
    }

    public function dishList(){
        return view('admin.dish');
    }
    
    public function showDishList()
    {
        $restaurent = RestroDish::with(['getrestro','getcategory'])->orderBy('created_at', 'desc')->get();
        //print_r($restaurent);die;
        return Datatables::of($restaurent)
            ->addIndexColumn()
                    ->addColumn('action', function($row){
                        $btn = '';
                        
                        $btn .= '<a href="' . route('admin.dishdetail', $row->id) . '" type="button" data-toggle="tooltip" title="View" data-title="View" class="btn btn-warning btn-sm">View</a>';
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
    }

    public function viewDishDetail($id){
        $breadcum = ['Dishes'=>route('admin.dish'),'View Dish' =>''];
        $dish  =  RestroDish::where(['id'=>$id])->with(['getrestro','getcategory','getDishImages'])->first();
            $data = '';
  
        return view('admin.dishdetail',compact('dish','data','breadcum'));
    }

}


<?php
   
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DataTables;
use DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserLikeDislike;
use App\Models\Order;
   
class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
   public function __construct()
    {
        $this->middleware('auth');
    }
  
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        
        return view('home')->with('breadcum','dashboard');
    }
  
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function adminHome()
    {
        
        $totalUser = User::where(['user_type'=>1, 'is_admin'=>0])->count();
        $totalRestaurant = User::where(['user_type'=>2, 'is_admin'=>0])->count();
        $totalActiveUser = User::where(['user_type'=>1,'status'=>1, 'is_admin'=>0])->count();
        $totalActiveRestaurant = User::where(['user_type'=>2,'status'=>1,'is_admin'=>0])->count();
        $orders = Order::count();
        $earning = Order::sum('paid_amount');
        return view('admin.home', compact('totalUser', 'totalRestaurant','totalActiveUser','totalActiveRestaurant','orders','earning'));
    }
    
    public function editProfile(Request $request)
    {
        $title = "Edit Profile";
        $user = User::where('id', Auth::user()->id)->first();
        if($request->isMethod('post')){
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email|unique:users,email,' . Auth::user()->id,
            ]);

            if ($validator->fails()) {
                //print_r($validator->errors());die('ihjhj');
                return redirect()->back()->withErrors($validator->errors());
            }else{
                
                $user->name = $request->get('name');
                $user->email = $request->get('email');
                if ($request->file('profile_image')) {
                    $old = $user->profile_image;
                    $name = 'user_' . time() . '.' . $request->file('profile_image')->getClientOriginalExtension();
                    $destinationPath = public_path('/uploads/profile_picture');
                    $request->file('profile_image')->move($destinationPath, $name);
                    $user->profile_image = 'public/uploads/profile_picture/' . $name;
                    //print_r($old);die;
                    
                }
                $user->save();
            }

            return redirect()->route('admin.editprofile')->with(['type' => 'success', 'status' => 'Profile successfully updated']);
        }
        return view('admin.editprofile', compact('title', 'user'))->with('breadcum','Edit Profile');
    }

    public function changePassword(Request $request)
    {
        $title = "Change Password";
        $users = User::get();
        if($request->isMethod('post')){
            $validator = Validator::make($request->all(), [
                'current_password' => 'required',
                'new_password' => 'required|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors());
            }

            $user = User::findOrFail(Auth::user()->id);

            if (Hash::check($request->get('current_password'), $user->password)) {
                if (Hash::check($request->get('new_password'), $user->password)) {
                    return redirect()->route('changePassword')->with(['type' => 'danger', 'status' => 'New password and old password can not be same']);
                } else {
                    $user->password = Hash::make($request->get('new_password'));
                    $user->save();
                    return redirect()->route('admin.changepassword')->with(['type' => 'success', 'status' => 'Password successfully changed']);
                }
            } else {
                return redirect()->route('admin.changepassword')->with(['type' => 'danger', 'status' => 'Current password does not match']);
            }
        }
        return view('admin.changepassword', compact('title'));
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showUser(Request $request)
    {
        if ($request->ajax()) {
            //die('jjjj');
            DB::statement(DB::raw('set @rownum=0'));
            $data = User::select('*',DB::raw('@rownum  := @rownum  + 1 AS rownum'))->whereRaw('is_admin=0 and user_type=1 order by id desc')->get();

            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('action', function($row){
                        $btn = '';
                        if ($row->status == 0) {
                            $btn .= '<span class="f-left margin-r-5 1" id = "status_' . $row->id . '"><a href="#" type="button" data-toggle="tooltip" title="Activate" data-title="Deactivate" class="btn btn-danger btn-sm change-status" onclick="changeStatus('.$row->id.',1)">Deactivate</a></span>';
                        } else {
                            $btn .= '<span class="f-left margin-r-5 1" id = "status_' . $row->id . '"><a href="#" type="button" data-toggle="tooltip" title="Deactivate" data-title="Activate" class="btn btn-success btn-sm change-status" onclick="changeStatus('.$row->id.',0)">Activate</a></span>';
                        }
                        $btn .= '<a href="' . route('admin.userprofile', $row->id) . '" type="button" data-toggle="tooltip" title="View" data-title="Deactivate" class="btn btn-warning btn-sm"><i class="fa fa-eye" aria-hidden="true"></i></a>';
                        //$btn .= '<a href="' . route('user.delete', $row->id) . '" type="button" data-toggle="tooltip" data-title="Delete" title="Delete" class="btn btn-icon btn-rounded btn-danger"><i class="feather icon-trash"></i></a>';
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }
        return view('admin.user');
    }

    /**
     * Activate user account
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
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
                   $html =  '<span class="f-left margin-r-5 1" id = "status_' . $user->id . '"><a href="#" type="button" data-toggle="tooltip" data-title="Activate" class="btn btn-success btn-sm change-status"  onclick="changeStatus('.$user->id.',0)">Activate</a></span>';
                  break;
                   case 0:
                   $html =  '<span class="f-left margin-r-5 1" id = "status_' . $user->id . '"><a href="#" type="button" data-toggle="tooltip" data-title="Deactivate" class="btn btn-danger btn-sm change-status" onclick="changeStatus('.$user->id.',1)">Deactivate</a></span>';
                  break;
              
              default:
                
                  break;
          }
          return $html;
    }

    

    public function viewUserProfile(Request $request)
    {
        $breadcum = ['Users'=>route('admin.user'),'View User Detail' =>''];
        $id = $request->id;
        $user = User::leftJoin('user_details', function($join) {
                  $join->on('users.id', '=', 'user_details.user_id');

                })
                
        ->where('users.id',$id)
        ->select('users.id',
            'users.name',
            'users.email',
            'users.profile_image',
            'users.user_type',
            'users.is_onboarding',
            'user_details.user_preference',
            'user_details.gender',
            'user_details.age',
            'user_details.preferred_gender',
            'user_details.preferred_age',
            'user_details.user_profession',
            'user_details.user_company',
            'user_details.radius',
            'user_details.lat',
            'user_details.lng',
            'user_details.about_us',
            
        )
        ->groupBy('user_details.user_id')
        ->first();
        return view('admin.userprofile',compact('user','breadcum'));
    }

    //-------------Check Deletion Status
    public function checkDeletionStatus($id)
    {
        $user = User::where('id', $id)->count();
        if (!$user) {
            return 'Data successfully removed';
        } else {
            return 'Data available';
        }
    }

    public function logout()
    {
        Auth::logout();

        return redirect()->back();
    }
}
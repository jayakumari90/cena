<?php
    
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
class HomeController extends Controller
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
        return view('home');
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
                //die('yess');

                //echo Auth::id();
                //print_r(Auth::user()->id);die;
                //print_r($request->all());die;
                ////$user = User::findOrFail(Auth::id());

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
        return view('admin.editprofile', compact('title', 'user'));
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
            $data = User::select('*')->whereRaw('is_admin=0')->get();
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('action', function($row){
                        $btn = '';
                        if ($row->status == 2) {
                            $btn .= '<a href="' . route('admin.activate', $row->id) . '" type="button" data-toggle="tooltip" title="Activate" data-title="Activate" class="btn btn-icon btn-rounded btn-success"><i class="feather icon-check-circle"></i></a>';
                        } else {
                            $btn .= '<a href="' . route('admin.block', $row->id) . '" type="button" data-toggle="tooltip" title="Block" data-title="Block" class="btn btn-icon btn-rounded btn-warning"><i class="feather icon-slash"></i></a>';
                        }
                        $btn .= '<a href="javascript:void(0)" onclick="showProfile(' . $row->id . ')" type="button" data-toggle="tooltip" title="View" data-title="View" class="btn btn-icon btn-rounded btn-primary"><i class="feather icon-eye"></i></a>';
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
    public function activateUser($id)
    {
        $user = User::where('id', $id)->first();
        $user->status = '1';
        $user->save();
        return redirect()->back()->with(['type' => 'success', 'status' => "User account successfully activated"]);
    }

    /**
     * Block user account
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function blockUser($id)
    {
        $user = User::where('id', $id)->first();
        $user->status = '2';
        $user->save();
        return redirect()->back()->with(['type' => 'success', 'status' => "User account successfully blocked"]);
    }

    public function viewUserProfile(Request $request)
    {
        $user = User::where('id', $request->id)->first();
        
        
        return response()->json($user, 200);
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function adminHome()
    {
        return view('admin.home');
    }
     
    public function logout()
    {
        Auth::logout();

        return redirect()->back();
    }
}
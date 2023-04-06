<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ReportedUser;
use App\Models\User;
use DataTables;
use Session;
//
class ReportController extends Controller
{
    public function index(Request $request){
        return view('admin.reportList');
    }

    public function showReportedUserList()
    {
        $dishCat = ReportedUser::orderBy('id', 'desc')->get();

        return Datatables::of($dishCat)
            ->addIndexColumn()

            ->editColumn('user_id', function ($row) {
                if ($row->user_id) {
                    return isset($row->getUserDetail)?$row->getUserDetail->name:'';
                } 
            })

            ->editColumn('reported_user_id', function ($row) {
                if ($row->user_id) {
                    return isset($row->getReportedUserDetail)?$row->getReportedUserDetail->name:'';
                } 
            })

            ->editColumn('status', function ($row) {
                
                return ($row->status == '1')?'Resolved':'Unresolved';
                
            })

             ->editColumn('responded_at', function ($row) {
                
                return isset($row->responded_at)?date('m-d-Y H:i',strtotime($row->responded_at)):'';
                
            })

            ->addColumn('action', function($row){
                $btn = '';
                if($row->status == '0'){
                    $btn .= '<a href="'.route("reported.users.deactivate", $row->id).'" type="button" data-toggle="tooltip" data-title="Respond" title="Respond" class="btn btn-primary btn-sm" >Deactive Reported User</a>';
                }else{
                    $btn .= '<a href="'.route("reported.users.deactivate", $row->id).'" type="button" data-toggle="tooltip" data-title="Respond" title="Respond" class="btn btn-primary btn-sm" >Activate Reported User</a>';
                }

                $btn .= '&nbsp;&nbsp;<a href="javascript:void(0)" onclick="showDetail(' . $row->id . ')" type="button" data-toggle="tooltip" title="View" data-title="View" class="btn  btn-primary">View</a>';
                
                return $btn;
            })
            ->rawColumns(['user_id','reported_user_id','status','action'])
            ->make(true);
    }

    public function reportDetail(Request $request)
    {
        $rec = ReportedUser::where('id', $request->id)->with('getUserDetail')->first();
        //print_r($rec);die;
        
        
        return response()->json($rec, 200);
    }

      public function deactivateReportedUser($id)
      {
        try {
            // add data in payment_settlement data.
            $row = ReportedUser::where('id',$id)->first();
            if($row->status == 1){
                $user = User::where('id',$row->reported_user_id)->update(["status"=>"1"]);
                $status = 'Activate';
                $row->status = '0';
            }else{
                $user = User::where('id',$row->reported_user_id)->update(["status"=>"0"]);
                $status = 'Deactivate';
                $row->status = '1';
            }
            
            $row->responded_at = date('Y-m-d H:i');
            $row->save();

            
            Session::flash('message', __('Reported user '.$status.' successfully.'));
            return redirect()->route('admin.reportList');
         }
         catch(\Exception $e){
           $msg = $e->getMessage();
           Session::flash('danger', $msg);
           return redirect()->back()->withInput();
         }
      }

}


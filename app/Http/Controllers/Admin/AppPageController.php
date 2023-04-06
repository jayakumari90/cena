<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\AppPage;
use App\Models\Category;

use DataTables;

class AppPageController extends Controller
{


     /**
     * Show AppPages.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function appPageList()
    {
        $title = "AppPages List";
        return view('admin.appPageList', compact('title'));
    }

    /**
     * Show the appPage list.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showAppPageList()
    {
        $appPage = AppPage::orderBy('created_at', 'desc')->get();

        return Datatables::of($appPage)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group btn-group-sm" role="group" aria-label="button groups sm">';
                $btn .= '<button type="button" class="btn btn-secondary btn-sm" onclick="getAppPageDetail(' . $row->id . ')">Edit</button>';
                $btn .= '<a href="'.route('show.page',$row->slug).'" target="_blank" class="btn btn-info btn-sm">View</button>';
                $btn .= '</div';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }


    /**
     * View appPage detail
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function viewAppPageDetail(Request $request)
    {
        $appPage = AppPage::where('id', $request->id)->first();
        if($appPage){
            return response()->json($appPage, 200);
        }else{
            return view("errors/404");
        }
        
        
    }
    /**
     * Edit AppPage
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function editAppPage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page_id' => 'required',
            'page_content' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        $appPage = AppPage::whereId($request->page_id)->first();
        if ($appPage) {
            $appPage->content = $request->get('page_content');
            $appPage->save();

            return redirect()->back()->with(['type' => 'success', 'status' => 'App Page successfully updated']);
        } else {
            return redirect()->back()->with(['type' => 'danger', 'status' => 'App Page not found']);
        }
    }

}

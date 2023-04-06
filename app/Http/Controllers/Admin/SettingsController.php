<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Setting;
use DataTables;

class SettingsController extends Controller
{

    
    /**
     * Show Subscriptions.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function setting(Request $request)
    {
        $title = "Setting";
        $setting = Setting::where('id',1)->first();
        if ($request->isMethod('post')) {
               
            $setting->is_maintenance = ($request->is_maintenance)?$request->is_maintenance:0;   
            $setting->ios_version = $request->ios_version;   
            $setting->android_version = $request->android_version;   
            $setting->is_ios_force_update = ($request->is_ios_force_update)?$request->is_ios_force_update:0;   
            $setting->is_android_force_update = ($request->is_android_force_update)?$request->is_android_force_update:0;   
            $setting->auth_token = ($request->auth_token)?$request->auth_token:$setting->auth_token;   
            $setting->default_miles = ($request->default_miles)?$request->default_miles:$setting->default_miles;   
            $setting->tax = ($request->tax)?$request->tax:$setting->tax;   
            $setting->commission = ($request->commission)?$request->commission:$setting->commission;   
            $setting->save();
            return redirect()->route('settings')->with(['type' => 'success', 'status' => 'Seetings successfully updated']);
        }
        return view('admin.settings', compact('title','setting'));
    }
}

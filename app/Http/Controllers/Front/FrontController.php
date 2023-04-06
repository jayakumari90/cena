<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\AppPage;
use App\Models\User;
use Illuminate\Http\Request;
use JWTAuth;
use Exception;
use Validator;
use Illuminate\Support\Facades\Hash;

class FrontController extends Controller
{

    /**
     * Show app page based on slug
     */
    public function showAppPage($slug)
    {
        $content = AppPage::where('slug', $slug)->first();
        return view('front.appPage', ['content' => $content]);
    }


    /**
     * Account activation page
     */
    public function activateAccount($token)
    {
        try {
            $user = JWTAuth::parseToken($token)->authenticate();
            $user = User::findOrFail($user->id);
            if ($user) {
                $user->status = '1';
                $user->email_verified_at = date("Y-m-d H:i:s");
                $user->save();
                JWTAuth::invalidate();
                return view('front.activateAccount', ['type' => 'success', 'title' => 'Activated', 'message' => "You account has been successfully activated"]);
            } else {
                return view('front.activateAccount', ['type' => 'danger', 'title' => 'Link Expired', 'message' => "You account activation link is expired."]);
            }
        } catch (Exception $e) {
            return view('front.activateAccount', ['type' => 'danger', 'title' => 'Link Expired', 'message' => "You account activation link is expired."]);
        }
    }

    /**
     * Reset password page
     */
    public function resetPassword($token)
    {
        try {
            $user = JWTAuth::parseToken($token)->authenticate();
            $user = User::findOrFail($user->id);
            if ($user) {
                $user->status = '1';
                $user->email_verified_at = date("Y-m-d H:i:s");
                $user->save();
                return view('front.reset', ['title' => 'Reset Password', 'secret' => $token]);
            } else {
                return view('front.activateAccount', ['type' => 'danger', 'title' => 'Link Expired', 'message' => "You reset password link is expired."]);
            }
        } catch (Exception $e) {
            return view('front.activateAccount', ['type' => 'danger', 'title' => 'Link Expired', 'message' => "You reset password link is expired."]);
        }
    }

    /**
     * Set new password
     */
    public function setNewPassword(Request $request, $token)
    {
        try {
            $user = JWTAuth::parseToken($token)->authenticate();
            $user = User::findOrFail($user->id);
            if ($user) {
                $validator = Validator::make($request->all(), [
                    'password' => 'required|min:8|confirmed',
                ]);

                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator->errors())->withInput($request->all());
                }
                $user->password = Hash::make($request->password);
                $user->save();
                JWTAuth::invalidate();
                return view('front.activateAccount', ['type' => 'success', 'title' => 'Successfully Reset', 'message' => "Password has been successfully reset."]);
            } else {
                return redirect()->route('user.resetPassword');
            }
        } catch (Exception $e) {
            return redirect()->back();
        }
    }
}

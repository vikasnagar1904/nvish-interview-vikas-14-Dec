<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\User;

class LogoutController extends Controller
{
    /**
     * Log out account user.
     *
     * @return \Illuminate\Routing\Redirector
     */
    public function perform()
    {
        User::where('id',Auth::user()->id)->update(['login_status'=>'0']);

        Session::flush();
        
        Auth::logout();

        return redirect('login');
    }
}

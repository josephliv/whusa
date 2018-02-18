<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use UserTestModel;

class UserTestController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function home()
    {
        if(\Auth::user()->name == 'usera'){
            return view('dashboard.dashboard');
        } elseif(\Auth::user()->name == 'userb'){
            return view('dashboard.profile');
        } else {
            return view('dashboard.dashboard');
        }
        
    }

    public function dashboard()
    {

        return view('dashboard.dashboard');
        
    }

    public function profile()
    {
        if(\Auth::user()->role_id != 4){ # Only users with user role 'defaultb' should be able to access /user/profile
            return redirect('user/dashboard');
        } else {
            return view('dashboard.profile');
        }
    }

}

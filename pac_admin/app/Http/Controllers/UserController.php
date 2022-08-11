<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\User;
use DB;
use Hash;
use Illuminate\Support\Facades\Auth;


use Illuminate\Support\Facades\Config;

class UserController extends Controller {

    public function __construct()
    {
        
    }

    public function logout() {
        Auth::logout();

        return redirect(route('home'));
    }

    public function getUser() {
        
    }

}

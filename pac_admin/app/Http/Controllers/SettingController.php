<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;

class SettingController extends Controller {

    public function __construct()
    {
        
    }
    
    public function changeNavMenuActive(Request $request)
    {
        $isNavMenuActive = $request->get('navMenuActive', 0);
        Session::put('isNavMenuActive', (int)$isNavMenuActive);
        return response()->json(['status' => true]);
    }
}

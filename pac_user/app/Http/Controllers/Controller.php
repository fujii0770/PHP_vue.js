<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Session;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $data = [];

    public $meta = [];

    protected function getUser(){
        $token = request()->bearerToken();
        // Session::get(null) とすると Session のすべてが返される
        // これを防止するため null かどうかを確認している
        return is_null($token) ? null : Session::get($token);
    }

    public function assign($name, $value){
        $this->data[$name] = $value;
    }

    public function render($view){
        $this->data['meta'] = $this->meta;
        return view($view, $this->data);
    }
}

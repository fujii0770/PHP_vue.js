<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use DB;
use Hash;


class DashboardController extends AdminController
{

    public function __invoke()
    {
        $this->setMetaTitle("Screen Dasbboard");
        return $this->render('dashboard');
    }


}

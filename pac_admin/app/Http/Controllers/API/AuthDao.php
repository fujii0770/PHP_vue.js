<?php

namespace App\Http\Controllers\API;

use App\Chat\Properties\ChatAwsProperties;
use Illuminate\Support\Facades\DB;

class AuthDao
{
    /**
     *
     * @return object
     */
    public function getAuth($key)
    {
        return DB::table('api_authentication')->where('api_name', $key)->first();
    }
}

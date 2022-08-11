<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'mst_company';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';
}

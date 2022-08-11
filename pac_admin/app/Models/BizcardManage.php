<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BizcardManage extends Model
{
    public $table = 'bizcard_manage';

    public $guarded = [
        'id',
        'mst_company_id'
    ];
}
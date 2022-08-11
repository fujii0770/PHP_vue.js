<?php

namespace App\Models;

use App\Http\Utils\AppUtils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Facility extends Model
{
    protected $table = 'mst_facility';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    protected $fillable = [];
    
    public function rules()
    {
        return [];
    }
}
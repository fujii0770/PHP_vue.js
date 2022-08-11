<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bizcard extends Model
{
    public $table = 'bizcard';

    public $guarded = [
        'id',
        'bizcard_id',
        'mst_user_id',
        'path',
        'link_page_url',
    ];
}

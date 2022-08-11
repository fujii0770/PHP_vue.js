<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SanitizingLine extends Model
{
    protected $table = 'mst_sanitizing_line';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'sanitizing_line_name','sanitize_request_limit', 'create_user','update_user'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    public function rules(){
        return [
            'sanitizing_line_name' => 'required',
            'sanitize_request_limit' => 'nullable|numeric',
            'create_user' => 'required|max:128',
            'update_user' => 'nullable|max:128',
        ];
    }
}

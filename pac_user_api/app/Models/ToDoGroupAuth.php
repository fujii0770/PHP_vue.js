<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ToDoGroupAuth
 * @package App\Models
 */
class ToDoGroupAuth extends Model
{
    public $table = 'to_do_group_auth';

    public $fillable = [
        'group_id',
        'auth_type',
        'auth_department_id',
        'auth_user_id',
        'mst_user_id',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'group_id' => 'required|numeric',
        'auth_type' => 'required|numeric|in:1,2',
        'auth_department_id' => 'numeric',
        'auth_user_id' => 'numeric',
        'mst_user_id' => 'required|numeric',
    ];
}

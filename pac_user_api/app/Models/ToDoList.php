<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ToDoList
 * @package App\Models
 */
class ToDoList extends Model
{
    public $table = 'to_do_list';

    public $fillable = [
        'mst_user_id',
        'mst_company_id',
        'type',
        'title',
        'created_at',
        'updated_at',
        'create_user',
        'update_user',
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
        'mst_user_id' => 'required|numeric',
        'mst_company_id' => 'required|numeric',
        'type' => 'required|numeric|in:1,2',
        'title' => 'required|string|max:50',
        'create_user' => 'required|string',
        'update_user' => 'string',
    ];
}

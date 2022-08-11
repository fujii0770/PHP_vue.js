<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ToDoTask
 * @package App\Models
 */
class ToDoTask extends Model
{
    public $table = 'to_do_task';

    public $fillable = [
        'to_do_list_id',
        'mst_user_id',
        'mst_company_id',
        'parent_id',
        'title',
        'content',
        'important',
        'deadline',
        'scheduler_id',
        'scheduler_task_id',
        'state',
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
        'to_do_list_id' => 'required|numeric',
        'mst_user_id' => 'required|numeric',
        'mst_company_id' => 'required|numeric',
        'parent_id' => 'numeric',
        'title' => 'required|string|max:50',
        'content' => 'string',
        'important' => 'numeric|in:0,1,2,3',
        'scheduler_id' => 'numeric',
        'scheduler_task_id' => 'numeric',
        'state' => 'numeric|in:-1,0,1,2',
        'create_user' => 'required|string',
        'update_user' => 'string',
    ];
}

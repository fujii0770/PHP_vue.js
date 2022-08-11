<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ToDoCircularTask
 * @package App\Models
 */
class ToDoCircularTask extends Model
{
    public $table = 'to_do_circular_task';

    public $fillable = [
        'circular_user_id',
        'mst_user_id',
        'title',
        'content',
        'important',
        'deadline',
        'scheduler_id',
        'scheduler_task_id',
        'state',
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
        'circular_user_id' => 'numeric',
        'mst_user_id' => 'required|numeric',
        'title' => 'required|string|max:50',
        'content' => 'string',
        'important' => 'numeric|in:0,1,2,3',
        'scheduler_id' => 'numeric',
        'scheduler_task_id' => 'numeric',
        'state' => 'numeric|in:-1,0,1,2',
    ];
}

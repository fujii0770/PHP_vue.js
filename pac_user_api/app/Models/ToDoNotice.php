<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ToDoListNotice
 * @package App\Models
 */
class ToDoNotice extends Model
{
    public $table = 'to_do_notice';

    public $fillable = [
        'from_id',
        'mst_user_id',
        'title',
        'from_type',
        'is_read',
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
        'from_id' => 'required|numeric',
        'mst_user_id' => 'required|numeric',
        'title' => 'required|string|max:100',
        'from_type' => 'required|numeric',
        'is_read' => 'numeric|in:0,1',
    ];
}

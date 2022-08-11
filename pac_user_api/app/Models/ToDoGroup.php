<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ToDoGroup
 * @package App\Models
 */
class ToDoGroup extends Model
{
    public $table = 'to_do_group';

    public $fillable = [
        'title',
        'mst_company_id',
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
        'mst_user_id' => 'required|numeric',
        'title' => 'required|string|max:50',
    ];
}

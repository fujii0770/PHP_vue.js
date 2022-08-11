<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ToDoNoticeConfig
 * @package App\Models
 */
class ToDoNoticeConfig extends Model
{
    public $table = 'to_do_notice_config';

    public $fillable = [
        'mst_user_id',
        'email_flg',
        'notice_flg',
        'state',
        'advance_time',
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
        'email_flg' => 'numeric|in:0,1',
        'notice_flg' => 'numeric|in:0,1',
        'state' => 'numeric|in:0,1',
        'advance_time' => 'numeric',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LongTermDocument extends Model
{
    public $timestamps = false;
    public $table = 'long_term_document';

    public $fillable = [
        'circular_id',
        'mst_company_id',
        'sender_email',
        'sender_name',
        'destination_name',
        'destination_email',
        'circular_status',
        'file_name',
        'file_size',
        'keyword',
        'request_at',
        'completed_at',
        'title',
        'create_user',
        'update_user',
        'create_at',
        'update_at',
        'add_timestamp_automatic_date',
        'timestamp_automatic_flg',
        'upload_status',
        'upload_id',
        'user_id',
        'long_term_folder_id',
    ];
}

<?php

namespace App\Models;
use Eloquent as Model;
class UploadData extends Model
{
    protected $table = 'upload_data';
    protected $fillable = [
        'upload_data',
        'first_img_review',
        'file_size',
        'status',
    ];
}
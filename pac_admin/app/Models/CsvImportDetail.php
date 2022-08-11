<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CsvImportDetail extends Model
{
    protected $table = 'csv_import_detail';
    public $timestamps = false;

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','list_id', 'row_id','email','comment','create_at', 'update_at'
    ];
}

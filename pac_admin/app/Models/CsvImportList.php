<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CsvImportList extends Model
{
    protected $table = 'csv_import_list';
    public $timestamps = false;

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','company_id', 'user_id','name','success_num','failed_num', 'total_num', 'result', 'create_at','update_at'
    ];
}

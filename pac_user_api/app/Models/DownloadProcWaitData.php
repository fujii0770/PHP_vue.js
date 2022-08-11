<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DownloadProcWaitData extends Model
{
    protected $table = 'download_proc_wait_data';

    const CREATED_AT = NULL;

    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'state',
        'download_request_id',
        'num',
        'circular_document_id',
        'document_data_id',
        'document_data',
        'title',
        'file_name',
        'create_at',
        'create_user',
        'circular_id',
        'circular_update_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [ ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [  ];

    public function rules($id){
        return [
            'id' => 'required|numeric',
            'state' => 'required|numeric',
            'download_request_id' => 'required|numeric',
            'num' => 'required|numeric',
            'circular_document_id' => 'required|numeric',
            'document_data_id' => 'nullable|numeric',
            'document_data' => 'nullable',
            'title' => 'nullable|string|max:256',
            'file_name' => 'nullable|string|max:256',
            'circular_at' => 'nullable',
            'create_user' => 'required|numeric',
            'circular_id' => 'required|numeric',
            'circular_update_at' => 'nullable'
        ];
    }
}

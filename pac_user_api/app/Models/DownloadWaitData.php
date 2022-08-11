<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DownloadWaitData extends Model
{
    protected $table = 'download_wait_data';

    const CREATED_AT = NULL;
    
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'download_request_id',
        'data',
        'file_token',
        'circular_at',
        'update_at',
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
            'download_request_id' => 'required|numeric',
            'data' => 'nullable',
            'file_token' => 'nullable|string|max:256',
            'circular_at' => 'nullable',
            'update_at' => 'nullable'
        ];
    }
}

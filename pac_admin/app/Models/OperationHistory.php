<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class OperationHistory extends Model
{
    protected $table = 'operation_history';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'auth_flg','user_id', 'mst_display_id', 'mst_operation_id',  'result','detail_info', 'ip_address', 'create_at','create_user'
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
    protected $casts = [ 
        'auth_flg' => 'numeric',
        'user_id' => 'numeric',
        'mst_display_id' => 'numeric',
        'mst_operation_id' => 'numeric',
        'result' => 'numeric',
        'detail_info' => 'string',
        'ip_address' => 'string',
     ];
 

    public function userAdmin(){
        return $this->belongsTo('App\CompanyAdmin', 'user_id');
    }

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}

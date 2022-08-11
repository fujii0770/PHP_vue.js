<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Permission extends Model
{
    protected $table = 'permissions';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','menu','group_menu','guard_name'
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

    public function getListMaster()
    {
        $items = $this->select('id', 'group_menu','menu','name', 'action')->orderBy('display_no','asc')->orderBy('id','asc')->get();
        $arrPermission = [];
        if(count($items)){
            foreach($items as $item){
                $arrPermission[$item->group_menu][$item->menu][$item->action] = $item->id;
            }
        }
        return $arrPermission;
    }
}

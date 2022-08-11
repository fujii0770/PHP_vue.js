<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AssignStamp extends Model
{
    protected $table = 'mst_assign_stamp';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mst_user_id','mst_admin_id','stamp_id', 'display_no', 'stamp_flg', 'create_at','create_user','time_stamp_permission'
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
            'mst_user_id' => 'required|numeric',
            'mst_admin_id' => 'required|numeric',
            'stamp_id' => 'required|numeric',
            'create_user' => 'required|max:128'
        ];
    }

    public function stampCompany(){
        return $this->belongsTo('App\Models\CompanyStamp', 'stamp_id');
    }

    public function stampConvenient(){
        return $this->belongsTo('App\Models\CompanyStampConvenient', 'stamp_id');
    }

    public function stampMaster(){
        return $this->belongsTo('App\Models\Stamp', 'stamp_id');
    }

    public function stampDepartment(){
        return $this->belongsTo('App\Models\DepartmentStamp', 'stamp_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User','mst_user_id');
    }

	public function stampAdmin()
	{
		return $this->hasManyThrough('App\Models\CompanyStampGroupsAdmin','App\Models\CompanyStampGroupsRelation','stamp_id','group_id','stamp_id','group_id');
	}

    public function stampGroup()
    {
        return $this->HasOne('App\Models\CompanyStampGroupsRelation','stamp_id','stamp_id');
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CompanyStamp extends Model
{
	protected $table = 'mst_company_stamp';

	const CREATED_AT = 'create_at';
	const UPDATED_AT = 'update_at';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'mst_company_id', 'stamp_name', 'stamp_division', 'font', 'stamp_image', 'serial',
		'width', 'height', 'date_dpi', 'date_width', 'date_height', 'date_x', 'date_y', 'date_color', 'del_flg',
		'create_user', ' update_user'
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = ['password'];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [];

	public function rules($id)
	{
		return [
			'mst_company_id' => 'required',
			'stamp_name' => 'required|max:32',
			'stamp_division' => 'required',
			'font' => 'required|numeric',
			'stamp_image' => 'required',
			'width' => 'required|numeric',
			'height' => 'required|numeric',
			'date_width' => 'nullable|numeric',
			'date_height' => 'nullable|numeric',
			'date_x' => 'nullable|numeric',
			'date_y' => 'nullable|numeric',
			'date_color' => 'nullable',
			'del_flg' => 'required|numeric',
			'create_user' => 'required|max:128',
			'update_user' => 'nullable|max:128'
		];
	}

	public function stampGroup()
	{
		return $this->hasOne('App\Models\CompanyStampGroupsRelation', 'stamp_id');
	}

    public function stampAdmin()
    {
        return $this->hasManyThrough('App\Models\CompanyStampGroupsAdmin','App\Models\CompanyStampGroupsRelation','stamp_id','group_id','id','group_id');
    }
}

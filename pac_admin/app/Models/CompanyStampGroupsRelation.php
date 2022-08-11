<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class CompanyStampGroupsRelation extends Model
{
	protected $table = 'mst_company_stamp_groups_relation';

	protected $primaryKey = 'stamp_id';
	const CREATED_AT = 'create_at';
	const UPDATED_AT = 'update_at';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['stamp_id','group_id', 'state'];

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
	protected $casts = [ ];
}

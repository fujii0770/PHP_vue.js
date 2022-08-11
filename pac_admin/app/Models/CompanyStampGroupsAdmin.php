<?php

namespace App\Models;

use App\Models\Traits\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use DB;

class CompanyStampGroupsAdmin extends Model
{


	protected $table = 'mst_company_stamp_groups_admin';

	public $incrementing = false;

	const CREATED_AT = 'create_at';
	const UPDATED_AT = 'update_at';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [];

}

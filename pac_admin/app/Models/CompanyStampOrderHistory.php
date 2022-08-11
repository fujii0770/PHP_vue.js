<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class CompanyStampOrderHistory extends Model
{
	protected $table = 'mst_company_stamp_order_history';

	protected $primaryKey = 'pdf_number';
	const CREATED_AT = 'create_at';

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
	protected $hidden = [ ];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [ ];
}

<?php

namespace App\Models;

use App\Http\Utils\AppUtils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Position extends Model
{
    protected $table = 'mst_position';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mst_company_id','position_name', 'state', 'create_user','update_user'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['password' ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [  ];

    public function rules($id){
        return [
            'mst_company_id' => 'required',
            'position_name' => 'required|max:256',
            'state' => 'nullable|numeric',
            'create_user' => 'required|max:128',
            'update_user' => 'nullable|max:128',
        ];
    }

    /**
     * 検索時にドロップダウンボックスに表示される役職
     * @param $mst_company_id
     * @return array
     */
    public function getSearchPositionItems($mst_company_id): array
    {
        return $this->select('id', 'position_name as text', 'position_name as sort_name')
            ->where('state', 1)
            ->where('mst_company_id', $mst_company_id)
            ->get()
            ->map(function ($sort_name) {
                $sort_name->sort_name = str_replace(AppUtils::STR_KANJI, AppUtils::STR_SUUJI, $sort_name->sort_name);

                return $sort_name;
            })
            ->keyBy('id')
            ->sortBy('sort_name')
            ->toArray();
    }
}

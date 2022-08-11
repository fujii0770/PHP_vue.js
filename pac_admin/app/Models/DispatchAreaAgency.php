<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class DispatchAreaAgency extends Model
{
    protected $table = 'dispatcharea_agency';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'mst_admin_id',
        'mst_company_id',
        'company_name', 
        'office_name', 
        'conflict_date',
        'postal_code', 
        'address1', 
        'address2', 
        'billing_address',
        'del_flg',
        'create_at',
        'create_user',
        'update_at',
        'update_user'
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

    public function rules($id = 'null'){
        return [
            'company_name' => 'required|max:128', 
            'office_name' => 'nullable|max:128', 
            'conflict_date' => 'nullable|date',
            'postal_code' => 'nullable|max:8|regex:/^[0-9-]{0,8}$/', 
            'address1' => 'nullable|max:128', 
            'address2' => 'nullable|max:128', 
            'billing_address' => 'nullable|numeric',
        ];
    }
    public function messages()
    {
        return [
            'postal_code.regex' => ':attribute は、000-0000, 0000000形式で入力してください。',
        ];
    }
    public function attributes(): array
    {
        return [
            'company_name' => '会社名',
            'office_name' => '事業所名(支店名など)',
            'conflict_date' => '事業者抵触日',
            'postal_code' => '郵便番号',
            'address1' => '住所1',
            'address2' => '住所2',
            'billing_address' => '請求先部署',
        ];
    }
}

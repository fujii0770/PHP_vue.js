<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Protection extends Model
{
    protected $table = 'mst_protection';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    protected $fillable = [
        'mst_company_id', 'protection_setting_change_flg', 'destination_change_flg', 'enable_email_thumbnail',
        'access_code_protection','text_append_flg', 'create_user', 'update_user',
    ];

    public function rules(){
        return [
            'mst_company_id' => 'required|numeric',
            'protection_setting_change_flg' => 'required|numeric|max:1',
            'destination_change_flg' => 'required|numeric|max:1',
            'enable_email_thumbnail' => 'required|numeric|max:1',
            'access_code_protection' => 'required|numeric|max:1',
            'text_append_flg' => 'required|numeric|max:1',
            'create_user' => 'max:128',
            'update_user' => 'max:128',
        ];
    }
}

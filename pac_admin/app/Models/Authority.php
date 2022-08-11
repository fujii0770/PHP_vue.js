<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

class Authority extends Model
{
    protected $table = 'admin_authorities_default';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mst_company_id',
        'code',
        'read_authority',
        'create_authority',
        'update_authority',
        'delete_authority',
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
    protected $casts = [
        'mst_company_id' => 'integer',
        'code' => 'string',
        'read_authority' => 'integer',
        'create_authority' => 'integer',
        'update_authority' => 'integer',
        'delete_authority' => 'integer',
     ];

     public function initDefaultValue($mst_company_id, $user_create){
        $arrInsert = [];
        foreach(\App\Http\Utils\PermissionUtils::DEFAULT_VALUE as $code => $values){
            $arrInsert[] = [
                'mst_company_id' => $mst_company_id,
                'code' => $code,
                'read_authority' => $values[0],
                'create_authority' => $values[1],
                'update_authority' => $values[2],
                'delete_authority' => $values[3],
                'create_at' => Carbon::now(),
                'create_user' => $user_create,
                'update_at' => Carbon::now(),
                'update_user' => $user_create,
            ];
        }
        DB::table($this->table)->insert($arrInsert);
     }
     public function initDefaultValuePortal($mst_company_id, $user_create){
        $arrInsert = [];
        foreach(\App\Http\Utils\PermissionUtils::DEFAULT_VALUE_PORTAL as $code => $values){
            $arrInsert[] = [
                'mst_company_id' => $mst_company_id,
                'code' => $code,
                'read_authority' => $values[0],
                'create_authority' => $values[1],
                'update_authority' => $values[2],
                'delete_authority' => $values[3],
                'create_at' => Carbon::now(),
                'create_user' => $user_create,
                'update_at' => Carbon::now(),
                'update_user' => $user_create,
            ];
        }
        DB::table($this->table)->insert($arrInsert);
     }

     public function delDefaultValuePortal($mst_company_id){
        $arrDeleteCode = [];
        foreach(\App\Http\Utils\PermissionUtils::DEFAULT_VALUE_PORTAL as $code => $values){
            $arrDeleteCode[] = $code;
        }
        DB::table($this->table)->where('mst_company_id',$mst_company_id)->wherein('code',$arrDeleteCode)->delete();
     }


     public function initDefaultValueHr($mst_company_id, $user_create){
        $arrInsert = [];
        foreach(\App\Http\Utils\PermissionUtils::DEFAULT_VALUE_HR as $code => $values){
            $arrInsert[] = [
                'mst_company_id' => $mst_company_id,
                'code' => $code,
                'read_authority' => $values[0],
                'create_authority' => $values[1],
                'update_authority' => $values[2],
                'delete_authority' => $values[3],
                'create_at' => Carbon::now(),
                'create_user' => $user_create,
                'update_at' => Carbon::now(),
                'update_user' => $user_create,
            ];
        }
        DB::table($this->table)->insert($arrInsert);
     }

     public function delDefaultValueHr($mst_company_id){
        $arrDeleteCode = [];
        foreach(\App\Http\Utils\PermissionUtils::DEFAULT_VALUE_HR as $code => $values){
            $arrDeleteCode[] = $code;
        }
        DB::table($this->table)->where('mst_company_id',$mst_company_id)->wherein('code',$arrDeleteCode)->delete();
     }

     public function initDefaultValueTalk($mst_company_id, $user_create)
     {
         $arrInsert = [];
         foreach(\App\Http\Utils\PermissionUtils::DEFAULT_VALUE_TALK as $code => $values){
             $arrInsert[] = [
                 'mst_company_id' => $mst_company_id,
                 'code' => $code,
                 'read_authority' => $values[0],
                 'create_authority' => $values[1],
                 'update_authority' => $values[2],
                 'delete_authority' => $values[3],
                 'create_at' => Carbon::now(),
                 'create_user' => $user_create,
                 'update_at' => Carbon::now(),
                 'update_user' => $user_create,
             ];
         }
         DB::table($this->table)->insert($arrInsert);
     }

    public function delDefaultValueTalk($mst_company_id){
        $arrDeleteCode = [];
        foreach(\App\Http\Utils\PermissionUtils::DEFAULT_VALUE_TALK as $code => $values){
            $arrDeleteCode[] = $code;
        }
        DB::table($this->table)->where('mst_company_id',$mst_company_id)
            ->whereIn('code',$arrDeleteCode)->delete();
    }

     public function initDefaultValueExpense($mst_company_id, $user_create){
        $arrInsert = [];
        foreach(\App\Http\Utils\PermissionUtils::DEFAULT_VALUE_EXPENSE as $code => $values){
            $arrInsert[] = [
                'mst_company_id' => $mst_company_id,
                'code' => $code,
                'read_authority' => $values[0],
                'create_authority' => $values[1],
                'update_authority' => $values[2],
                'delete_authority' => $values[3],
                'create_at' => Carbon::now(),
                'create_user' => $user_create,
                'update_at' => Carbon::now(),
                'update_user' => $user_create,
            ];
        }
        DB::table($this->table)->insert($arrInsert);
     }

     public function delDefaultValueExpense($mst_company_id){
        $arrDeleteCode = [];
        foreach(\App\Http\Utils\PermissionUtils::DEFAULT_VALUE_EXPENSE as $code => $values){
            $arrDeleteCode[] = $code;
        }
        DB::table($this->table)->where('mst_company_id',$mst_company_id)->wherein('code',$arrDeleteCode)->delete();
     }
    public function initDefaultValueAttachment($mst_company_id, $user_create){
        $arrInsert = [];
        foreach(\App\Http\Utils\PermissionUtils::DEFAULT_VALUE_ATTACHMENT as $code => $values){
            $arrInsert[] = [
                'mst_company_id' => $mst_company_id,
                'code' => $code,
                'read_authority' => $values[0],
                'create_authority' => $values[1],
                'update_authority' => $values[2],
                'delete_authority' => $values[3],
                'create_at' => Carbon::now(),
                'create_user' => $user_create,
                'update_at' => Carbon::now(),
                'update_user' => $user_create,
            ];
        }
        DB::table($this->table)->insert($arrInsert);
    }
    public function delDefaultValueAttachment($mst_company_id){
        $arrDeleteCode = [];
        foreach(\App\Http\Utils\PermissionUtils::DEFAULT_VALUE_ATTACHMENT as $code => $values){
            $arrDeleteCode[] = $code;
        }
        DB::table($this->table)->where('mst_company_id',$mst_company_id)
            ->whereIn('code',$arrDeleteCode)->delete();
    }
    public function initDefaultValueBizCard($mst_company_id, $user_create){
        $arrInsert = [];
        foreach(\App\Http\Utils\PermissionUtils::DEFAULT_VALUE_BIZ_CARD as $code => $values){
            $arrInsert[] = [
                'mst_company_id' => $mst_company_id,
                'code' => $code,
                'read_authority' => $values[0],
                'create_authority' => $values[1],
                'update_authority' => $values[2],
                'delete_authority' => $values[3],
                'create_at' => Carbon::now(),
                'create_user' => $user_create,
                'update_at' => Carbon::now(),
                'update_user' => $user_create,
            ];
        }
        DB::table($this->table)->insert($arrInsert);
    }
    public function delDefaultValueBizCard($mst_company_id){
        $arrDeleteCode = [];
        foreach(\App\Http\Utils\PermissionUtils::DEFAULT_VALUE_BIZ_CARD as $code => $values){
            $arrDeleteCode[] = $code;
        }
        DB::table($this->table)->where('mst_company_id',$mst_company_id)
            ->whereIn('code',$arrDeleteCode)->delete();
    }
    public function initDefaultValueTemplate($mst_company_id, $user_create){
        $arrInsert = [];
        foreach(\App\Http\Utils\PermissionUtils::DEFAULT_VALUE_TEMPLATE as $code => $values){
            $arrInsert[] = [
                'mst_company_id' => $mst_company_id,
                'code' => $code,
                'read_authority' => $values[0],
                'create_authority' => $values[1],
                'update_authority' => $values[2],
                'delete_authority' => $values[3],
                'create_at' => Carbon::now(),
                'create_user' => $user_create,
                'update_at' => Carbon::now(),
                'update_user' => $user_create,
            ];
        }
        DB::table($this->table)->insert($arrInsert);
    }
    public function delDefaultValueTemplate($mst_company_id){
        $arrDeleteCode = [];
        foreach(\App\Http\Utils\PermissionUtils::DEFAULT_VALUE_TEMPLATE as $code => $values){
            $arrDeleteCode[] = $code;
        }
        DB::table($this->table)->where('mst_company_id',$mst_company_id)
            ->whereIn('code',$arrDeleteCode)->delete();
    }
    public function initDefaultValueTemplateCsv($mst_company_id, $user_create){
        $arrInsert = [];
        foreach(\App\Http\Utils\PermissionUtils::DEFAULT_VALUE_TEMPLATE_CSV as $code => $values){
            $arrInsert[] = [
                'mst_company_id' => $mst_company_id,
                'code' => $code,
                'read_authority' => $values[0],
                'create_authority' => $values[1],
                'update_authority' => $values[2],
                'delete_authority' => $values[3],
                'create_at' => Carbon::now(),
                'create_user' => $user_create,
                'update_at' => Carbon::now(),
                'update_user' => $user_create,
            ];
        }
        DB::table($this->table)->insert($arrInsert);
    }
    public function delDefaultValueTemplateCsv($mst_company_id){
        $arrDeleteCode = [];
        foreach(\App\Http\Utils\PermissionUtils::DEFAULT_VALUE_TEMPLATE_CSV as $code => $values){
            $arrDeleteCode[] = $code;
        }
        DB::table($this->table)->where('mst_company_id',$mst_company_id)
            ->whereIn('code',$arrDeleteCode)->delete();
    }
}

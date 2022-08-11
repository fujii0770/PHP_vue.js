<?php

use App\Http\Utils\AppUtils;
use App\Http\Utils\LongTermFolderUtils;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class UpdateLongTermFolderAuthPac53132Data extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            $long_term_folder_auths = DB::table('long_term_folder_auth')
                ->select('long_term_folder_auth.*','long_term_folder.mst_company_id')
                ->leftJoin('long_term_folder','long_term_folder.id', 'long_term_folder_auth.long_term_folder_id')
                ->where('long_term_folder_auth.auth_kbn','!=',LongTermFolderUtils::AUTH_KBN_USER)
                ->get()->groupBy('mst_company_id');

            $insert_auths = [];

            foreach ($long_term_folder_auths as $company_id => $long_term_folders){
                $all_users_folders = [];
                $position_folders = [];
                $department_folders = [];
                $multiple_department_position_flg = DB::table('mst_company')->select('multiple_department_position_flg')->where('id',$company_id)->value('multiple_department_position_flg');
                foreach ($long_term_folders as $long_term_folder_auth){
                    if ($long_term_folder_auth->auth_kbn == LongTermFolderUtils::AUTH_KBN_ALL){
                        $all_users_folders[] = $long_term_folder_auth;
                    }elseif($long_term_folder_auth->auth_kbn == LongTermFolderUtils::AUTH_KBN_POSITION){
                        $position_folders[] = $long_term_folder_auth;
                    }elseif($long_term_folder_auth->auth_kbn == LongTermFolderUtils::AUTH_KBN_DEPARTMENT){
                        $department_folders[] = $long_term_folder_auth;
                    }
                }
                foreach ($all_users_folders as $all_users_folder){
                    foreach ($this->getCompanyUserIds($company_id) as $userId){
                        $insert_auths[] = [
                            'long_term_folder_id' => $all_users_folder->long_term_folder_id,
                            'auth_kbn' => LongTermFolderUtils::AUTH_KBN_USER,
                            'auth_link_id' => $userId,
                            'create_user' => 'Shachihata',
                        ];
                    }
                }
                //役職
                $folder_positions = [];
                foreach ($position_folders as $position_folder){
                    $folder_positions[$position_folder->long_term_folder_id][] = $position_folder->auth_link_id;
                }
                foreach ($folder_positions as $folder_id => $position_ids){
                    foreach ($this->getPositionUserIds($position_ids, $multiple_department_position_flg) as $userId){
                        $insert_auths[] = [
                            'long_term_folder_id' => $folder_id,
                            'auth_kbn' => LongTermFolderUtils::AUTH_KBN_USER,
                            'auth_link_id' => $userId,
                            'create_user' => 'Shachihata',
                        ];
                    }
                }
                //部署
                $folder_departments = [];
                foreach ($department_folders as $department_folder){
                    $folder_departments[$department_folder->long_term_folder_id][] = $department_folder->auth_link_id;
                }

                foreach ($folder_departments as $folder_id => $department_ids){
                    foreach ($this->getDepartmentUserIds($department_ids, $multiple_department_position_flg) as $userId){
                        $insert_auths[] = [
                            'long_term_folder_id' => $folder_id,
                            'auth_kbn' => LongTermFolderUtils::AUTH_KBN_USER,
                            'auth_link_id' => $userId,
                            'create_user' => 'Shachihata',
                        ];
                    }
                }
            }

            DB::beginTransaction();

            $delete_ids = DB::table('long_term_folder_auth')
                ->select('long_term_folder_auth.id')
                ->leftJoin('long_term_folder','long_term_folder.id', 'long_term_folder_auth.long_term_folder_id')
                ->where('long_term_folder_auth.auth_kbn','!=',LongTermFolderUtils::AUTH_KBN_USER)
                ->get()->pluck('id');

            DB::table('long_term_folder_auth')->whereIn('id',$delete_ids)->delete();

            foreach (collect($insert_auths)->unique()->chunk(500) as $items){
                DB::table('long_term_folder_auth')->insert($items->toArray());
            }
            DB::commit();
        }catch (\Exception $ex){
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
        }
    }

    private function getCompanyUserIds($mst_company_id){
        return  DB::table('mst_user')->select('id')
            ->where('mst_company_id',$mst_company_id)
            ->where('state_flg',  AppUtils::STATE_VALID)
            ->where('option_flg', AppUtils::USER_NORMAL)
            ->get()->pluck('id');
    }

    private function getPositionUserIds($position_ids, $multiple_department_position_flg){
        return DB::table('mst_user as mu')
            ->select('mu.id')
            ->join('mst_user_info as mui',function ($query) use($multiple_department_position_flg, $position_ids){
                $query->on('mui.mst_user_id', 'mu.id');

                if ($multiple_department_position_flg === 1) {
                    if ($position_ids) {
                        $query->where(function ($query) use ($position_ids) {
                            $query->orWhereIn('mst_position_id', $position_ids)
                                ->orWhereIn('mst_position_id_1', $position_ids)
                                ->orWhereIn('mst_position_id_2', $position_ids);
                        });
                    }
                } else {
                    if ($position_ids) $query->whereIn('mst_position_id', $position_ids);
                }
            })
            ->where('state_flg',  AppUtils::STATE_VALID)
            ->where('option_flg', AppUtils::USER_NORMAL)
            ->get()->pluck('id');
    }

    private function getDepartmentUserIds($department_ids, $multiple_department_position_flg){
        return DB::table('mst_user as mu')
            ->select('mu.id')
            ->join('mst_user_info as mui',function ($query) use($multiple_department_position_flg, $department_ids){
                $query->on('mui.mst_user_id', 'mu.id');

                if ($multiple_department_position_flg === 1) {
                    if ($department_ids) {
                        $query->where(function ($query) use ($department_ids) {
                            $query->orWhereIn('mst_department_id', $department_ids)
                                ->orWhereIn('mst_department_id_1', $department_ids)
                                ->orWhereIn('mst_department_id_2', $department_ids);
                        });
                    }
                } else {
                    if ($department_ids) $query->whereIn('mst_department_id', $department_ids);
                }
            })
            ->where('state_flg',  AppUtils::STATE_VALID)
            ->where('option_flg', AppUtils::USER_NORMAL)
            ->get()->pluck('id');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('long_term_folder_auth', function (Blueprint $table) {
            //
        });
    }
}

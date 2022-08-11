<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class  reorganizationOperationHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reorganizationOperationHistory';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'release operation history data';
    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
//        $time=date('Y-m', strtotime("-3 month"));
        $time=Carbon::now()->addMonthsNoOverflow(-3)->format('Y-m');
        
        $company_ids = DB::table('mst_company')->select('id')->get();

        foreach($company_ids as $company_id){
            $id_length=mb_strlen($company_id->id);
            $id_end=substr($company_id->id,-1);
            //利用者ID
            $user_ids = DB::table('mst_user')->where('mst_company_id', $company_id->id)->select('id')->get();
            $mst_user_group_id = json_decode(json_encode($user_ids),true);
            //管理者ID
            $admin_ids = DB::table('mst_admin')->where('mst_company_id', $company_id->id)->select('id')->get();
            $mst_admin_group_id = json_decode(json_encode($admin_ids),true);

            $history_user_datas = DB::table('operation_history')
            ->whereIn('user_id',$mst_user_group_id)
            ->where('create_at','<=',$time)
            ->where('copy_flg',0)
            ->where('auth_flg',1)
            ->get();


            $history_admin_datas = DB::table('operation_history')
            ->whereIn('user_id',$mst_admin_group_id)
            ->where('create_at','<=',$time)
            ->where('copy_flg',0)
            ->where('auth_flg',0)
            ->get();

            foreach($history_user_datas as $history_data){
            
                DB::table('operation_history'.$id_end)->insert([
                'auth_flg' => $history_data->auth_flg,
                'user_id' => $history_data->user_id,
                'mst_display_id' => $history_data->mst_display_id,
                'mst_operation_id' => $history_data->mst_operation_id,
                'result' => $history_data->result,
                'detail_info' => $history_data->detail_info,
                'ip_address' => $history_data->ip_address,
                'create_at' => $history_data->create_at,
                'create_user' => $history_data->create_user,
                'update_at' => $history_data->update_at,
                'update_user' => $history_data->update_user
                ]);

            }

            foreach($history_admin_datas as $history_data){
            
                DB::table('operation_history'.$id_end)->insert([
                'auth_flg' => $history_data->auth_flg,
                'user_id' => $history_data->user_id,
                'mst_display_id' => $history_data->mst_display_id,
                'mst_operation_id' => $history_data->mst_operation_id,
                'result' => $history_data->result,
                'detail_info' => $history_data->detail_info,
                'ip_address' => $history_data->ip_address,
                'create_at' => $history_data->create_at,
                'create_user' => $history_data->create_user,
                'update_at' => $history_data->update_at,
                'update_user' => $history_data->update_user
                ]);

            }

        }

        DB::table('operation_history')
        ->where('create_at','<=',$time)
        ->update(['copy_flg' => 1]);

        //DB::table('operation_history')
        //->where('copy_flg',1)
       //->delete();
        
    }
}

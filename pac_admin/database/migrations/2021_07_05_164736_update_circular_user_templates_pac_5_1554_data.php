<?php

use App\Http\Utils\AppUtils;
use App\Http\Utils\TemplateRouteUtils;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateCircularUserTemplatesPac51554Data extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $templates = DB::table('circular_user_templates as T')
            ->join('circular_user_template_routes as TR','TR.template','T.id')
            ->where('T.state',TemplateRouteUtils::TEMPLATE_ROUTE_STATE_VALID)
            ->select('T.mst_company_id','TR.mst_department_id','TR.mst_position_id','TR.mode','TR.option','T.id')
            ->get();

        //既存中に修正された承認ルトートID
        $templateIds = [];
        foreach ($templates as $template){
            //有効な部門と役職を獲得するユーザー数
            $userNum = DB::table('mst_user as U')
                ->join('mst_user_info as UI','UI.mst_user_id','U.id')
                ->where('U.mst_company_id',$template->mst_company_id)
                ->where('U.state_flg',AppUtils::STATE_VALID)
                ->where('UI.mst_department_id',$template->mst_department_id)
                ->where('UI.mst_position_id',$template->mst_position_id)
                ->count();

            if (!in_array($template->id,$templateIds)){
                if ($template->mode == TemplateRouteUtils::TEMPLATE_MODE_MORE_THAN && $template->option > $userNum){
                    DB::table('circular_user_templates')
                        ->where('id',$template->id)
                        ->update([
                            'state' => TemplateRouteUtils::TEMPLATE_ROUTE_STATE_INVALID,
                            'update_at' => Carbon::now(),
                            'update_user' => 'admin',
                        ]);
                    $templateIds[] = $template->id;
                }else {
                    if (!$userNum){
                        DB::table('circular_user_templates')
                            ->where('id',$template->id)
                            ->update([
                                'state' => TemplateRouteUtils::TEMPLATE_ROUTE_STATE_INVALID,
                                'update_at' => Carbon::now(),
                                'update_user' => 'admin',
                            ]);
                        $templateIds[] = $template->id;
                    }
                }
            }

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

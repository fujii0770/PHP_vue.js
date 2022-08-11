、<?php

use App\Models\Constraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDlRequestLimitPerOneHourToMstConstraintsPac52450 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_constraints', function (Blueprint $table) {
            $table->integer('dl_request_limit_per_one_hour')->comment('1時間当たりのダウンロード要求数(0:無制限)')->default(0);
        });

        /**
         * 既存の dl_request_limit が「1時間当たりのダウンロード要求数(0:無制限)」に当たる設定であったが、
         * 命名規則の兼ね合いで
         * dl_request_limit              ⇒「保有可能ダウンロード要求数」
         * dl_request_limit_per_one_hour ⇒「1時間当たりのダウンロード要求数(0:無制限)」
         * として、本マイグレーションを実行時点で
         * dl_request_limit の値 ⇒ 0 (無制限)
         * dl_request_limit の値 ⇒ 5 (config('app.reserve_file_max') のデフォルト値)
         * に設定する
         * (注意)この時点から config('app.reserve_file_max') は不要となり削除予定です。
         * */
        Constraint::all()->reduce(function($carry, $item){
            Constraint::where('id', $item->id)
                        ->update([
                            'dl_request_limit' => 5
                        ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Constraint::all()->reduce(function($carry, $item){
            Constraint::where('id', $item->id)
                        ->update(['dl_request_limit' => $item->dl_request_limit_per_one_hour]);
        });

        Schema::table('mst_constraints', function (Blueprint $table) {
            $table->dropColumn('dl_request_limit_per_one_hour');
        });
    }
}

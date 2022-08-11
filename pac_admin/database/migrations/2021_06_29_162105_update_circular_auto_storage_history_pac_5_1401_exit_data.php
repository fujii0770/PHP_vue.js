<?php

use App\Models\Circular;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Support\Facades\DB;

class UpdateCircularAutoStorageHistoryPac51401ExitData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            DB::beginTransaction();
            // データ移行
            // file_name つづり合わせ
            $circular_document_sub = DB::table('circular_document')->select(DB::raw('circular_document.circular_id, GROUP_CONCAT(circular_document.file_name  ORDER BY circular_document.id ASC SEPARATOR \', \') as file_names'))
                ->groupBy('circular_document.circular_id');

            // history from circular
            $auto_histories = DB::table('circular')->joinSub($circular_document_sub, 'D', function ($query) {
                $query->on('circular.id', '=', 'D.circular_id');
            })
                ->join('mst_user', 'circular.mst_user_id', 'mst_user.id')
                ->join('circular_user', 'circular.id', 'circular_user.circular_id')
                ->where('circular_user.parent_send_order', '0')
                ->where('circular_user.child_send_order', '0')
                ->where('circular_user.env_flg', config('app.pac_app_env'))
                ->where('circular_user.edition_flg', config('app.pac_contract_app'))
                ->where('circular_user.server_flg', config('app.pac_contract_server'))
                ->where('circular.box_automatic_storage_result', '!=', 0)
                ->select('circular.id', 'mst_user.mst_company_id', 'circular_user.email', 'circular_user.name', 'circular_user.title', 'D.file_names',
                    'circular.box_automatic_storage_result', 'circular.box_automatic_storage_route', 'circular.update_at')
                ->get();

            foreach ($auto_histories as $auto_history) {
                DB::table('circular_auto_storage_history')->insert([
                    'circular_id' => $auto_history->id,
                    'mst_company_id' => $auto_history->mst_company_id,
                    'applied_email' => $auto_history->email,
                    'applied_name' => $auto_history->name,
                    'title' => $auto_history->title,
                    'file_name' => $auto_history->file_names,
                    'route' => $auto_history->box_automatic_storage_route,
                    'result' => $auto_history->box_automatic_storage_result,
                    'create_at' => $auto_history->update_at,
                ]);
            }

            DB::commit();

        } catch (\Exception $exception) {
            DB::rollback();
            $consoleOutput = new ConsoleOutput();
            $consoleOutput->writeln($exception->getMessage());
            return;
        }

        if (Schema::hasColumn("circular", 'box_automatic_storage_result')) {
            Schema::table("circular", function (Blueprint $table) {
                $table->dropColumn('box_automatic_storage_result');
            });
        }
        if (Schema::hasColumn("circular", 'box_automatic_storage_route')) {
            Schema::table("circular", function (Blueprint $table) {
                $table->dropColumn('box_automatic_storage_route');
            });
        }

        $month = date('Ym');
        for ($i = 0; $i < 12; $i++) {
            $month = date('Ym', strtotime("$month -$i month"));
            if (Schema::hasTable("circular$month")) {
                if (Schema::hasColumn("circular$month", 'box_automatic_storage_result')) {
                    Schema::table("circular$month", function (Blueprint $table) {
                        $table->dropColumn('box_automatic_storage_result');
                    });
                }
                if (Schema::hasColumn("circular$month", 'box_automatic_storage_route')) {
                    Schema::table("circular$month", function (Blueprint $table) {
                        $table->dropColumn('box_automatic_storage_route');
                    });
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

    }
}

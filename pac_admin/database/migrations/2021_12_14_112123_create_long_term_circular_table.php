<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLongTermCircularTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('long_term_circular', function (Blueprint $table) {
            $table->bigInteger('id');
            $table->bigInteger('mst_user_id')->unsigned()->index('mst_user_id');
            $table->integer('access_code_flg')->comment('0：無効1：有効');
            $table->string('access_code', 10)->nullable();
            $table->integer('outside_access_code_flg')->default(0)->comment('0：無効|1：有効');
            $table->string('outside_access_code', 10)->nullable()->comment('社外用アクセスコード');
            $table->integer('hide_thumbnail_flg')->comment('0：無効1：有効');
            $table->date('re_notification_day')->nullable();
            $table->integer('circular_status')->comment('0：保存中(アップロード直後)
1：回覧中　（送信直後の状態）
2：回覧完了（最後の承認者が承認した時点でこの状態になる。）
3：回覧完了(保存済)。依頼者が文書をダウンロードするとこの状態になる。
4：差戻　（差戻直後のみこの状態。差戻後に再度承認を行うと回覧中に戻る。）
5：引戻（削除と同様。依頼者の引き戻し）
9：削除（回覧を削除するとこの状態になる。）');
            $table->datetime('create_at')->useCurrent();
            $table->string('create_user', 128)->nullable();
            $table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable();
            $table->string('update_user', 128)->nullable();
            $table->integer('address_change_flg')->unsigned();
            $table->longtext('first_page_data')->nullable();
            $table->integer('env_flg');
            $table->integer('edition_flg');
            $table->integer('server_flg')->default(0);
            $table->bigInteger('origin_circular_id')->unsigned()->nullable()->index('INX_origin_circular_id');
            $table->bigInteger('current_aws_circular_id')->nullable();
            $table->bigInteger('current_k5_circular_id')->nullable();
            $table->dateTime('applied_date')->nullable();
            $table->dateTime('completed_date')->nullable();
            $table->integer('box_automatic_storage_result')->unsigned()->default(0)->comment('自動保管結果(1:成功 2:失敗)');
            $table->string('box_automatic_storage_route', 512)->nullable()->comment('自動保管のパス');
            $table->integer('completed_copy_flg')->nullable()->default(0)->comment("完了データコピーフラッグ;0:コピーなし;\r\n1:コピー済み");
            $table->integer('has_signature')->nullable();
            $table->dateTime('final_updated_date')->nullable()->comment('最終更新日');
            $table->integer("special_site_flg")->default(0)->comment("特設サイトのフラグ;0：未使用|1：使用する");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('long_term_circular');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCircularItemsOnFrmTemplateByPac52280 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('frm_template', function (Blueprint $table) {
            if (!Schema::hasColumn("frm_template", "title")) {
                $table->string("title", 256)->nullable()->comment("件名");
            }
            if (!Schema::hasColumn("frm_template", "message")) {
                $table->string("message", 256)->nullable()->comment("メッセージ");
            }
            if (!Schema::hasColumn("frm_template", "address_change_flg")) {
                $table->tinyInteger("address_change_flg")->default(0)->comment("0：許可しない|1：許可する");
            }
            if (!Schema::hasColumn("frm_template", "text_append_flg")) {
                $table->tinyInteger("text_append_flg")->default(0)->comment("0：許可しない|1：許可する");
            }
            if (!Schema::hasColumn("frm_template", "hide_thumbnail_flg")) {
                $table->tinyInteger("hide_thumbnail_flg")->default(0)->comment("0：表示|1：非表示");
            }
            if (!Schema::hasColumn("frm_template", "require_print")) {
                $table->tinyInteger("require_print")->default(0)->comment("0：必須ではない|1：必須");
            }
            if (!Schema::hasColumn("frm_template", "access_code_flg")) {
                $table->tinyInteger("access_code_flg")->default(0)->comment("0：利用なし|1：利用あり");
            }
            if (!Schema::hasColumn("frm_template", "access_code")) {
                $table->string("access_code", 100)->nullable()->comment("アクセス_社内コード");
            }
            if (!Schema::hasColumn("frm_template", "outside_access_code_flg")) {
                $table->tinyInteger("outside_access_code_flg")->default(0)->comment("0：利用なし|1：利用あり");
            }
            if (!Schema::hasColumn("frm_template", "outside_access_code")) {
                $table->string("outside_access_code", 100)->nullable()->comment("アクセス_社外コード");
            }
            if (!Schema::hasColumn("frm_template", "re_notification_day")) {
                $table->date("re_notification_day")->nullable()->comment("再通知日");
            }
            if (!Schema::hasColumn("frm_template", "auto_ope_flg")) {
                $table->tinyInteger("auto_ope_flg")->default(0)->comment("0：保存|1：完了保存|2：自動回覧");
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('frm_template', function (Blueprint $table) {
            $table->dropColumn("title");
            $table->dropColumn("message");
            $table->dropColumn("address_change_flg");
            $table->dropColumn("text_append_flg");
            $table->dropColumn("hide_thumbnail_flg");
            $table->dropColumn("require_print");
            $table->dropColumn("access_code_flg");
            $table->dropColumn("access_code");
            $table->dropColumn("outside_access_code_flg");
            $table->dropColumn("outside_access_code");
            $table->dropColumn("re_notification_day");
            $table->dropColumn("auto_ope_flg");
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChildSendOrderToFavoriteRoutePac51698 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('favorite_route', function (Blueprint $table) {
            if (!Schema::hasColumn("favorite_route", "child_send_order")) {
                $table->integer("child_send_order")->default(0)->comment("");
            }
            if (!Schema::hasColumn("favorite_route", "parent_send_order")) {
                $table->integer("parent_send_order")->default(0)->comment("");
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
        Schema::table('favorite_route', function (Blueprint $table) {
            $table->dropColumn("plan_id");
        });
    }
}

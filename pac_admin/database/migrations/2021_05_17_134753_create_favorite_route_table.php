<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateFavoriteRouteTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('favorite_route', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
            $table->bigInteger('id', true);
            $table->bigInteger('mst_user_id');
            $table->integer('favorite_no')->unsigned();
            $table->integer('display_no')->unsigned();
            $table->string('name', 128)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('email', 255)->charset('utf8')->collation('utf8_general_ci');
            $table->dateTime('create_at');
            $table->bigInteger('email_company_id')->nullable();
            $table->bigInteger('email_user_id')->nullable();
            $table->integer('email_env_flg')->nullable();
            $table->integer('email_server_flg')->default(0);
            $table->integer('email_edition_flg')->nullable();
            $table->string('email_company_name', 256)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('favorite_route');
    }

}

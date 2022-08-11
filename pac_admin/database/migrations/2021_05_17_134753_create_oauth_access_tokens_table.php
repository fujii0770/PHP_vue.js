<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOauthAccessTokensTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oauth_access_tokens', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            $table->string('id', 100)->primary()->charset('utf8')->collation('utf8_unicode_ci');
            $table->bigInteger('user_id')->nullable()->index();
            $table->integer('client_id')->unsigned();
            $table->string('name', 191)->nullable()->charset('utf8')->collation('utf8_unicode_ci');
            $table->text('scopes')->nullable()->charset('utf8')->collation('utf8_unicode_ci');
            $table->boolean('revoked');
            $table->timestamps();
            $table->dateTime('expires_at')->nullable();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('oauth_access_tokens');
    }

}

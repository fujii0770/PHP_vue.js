<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstCompanyTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mst_company', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->string('company_name', 256);
			$table->string('company_name_kana', 256);
			$table->string('dstamp_style', 16);
			$table->text('domain');
			$table->integer('upper_limit')->unsigned();
			$table->integer('esigned_flg')->comment('0：無効
1：有効');
			$table->integer('use_api_flg')->comment('0：無効
1：有効');
			$table->integer('department_stamp_flg')->comment('0：無効
1：有効');
			$table->integer('login_type')->comment('0：メールアドレスとパスワード
1：シングルサインオン');
			$table->string('url_domain_id', 64)->nullable();
			$table->string('saml_unique', 100)->nullable();
			$table->text('saml_metadata')->nullable();
			$table->string('url_help', 2048)->nullable();
			$table->string('url_contact', 2048)->nullable();
			$table->string('url_term', 2048)->nullable();
			$table->string('url_policy', 2048)->nullable();
			$table->integer('ip_restriction_flg')->default(0)->comment('0:無効|1:有効');
			$table->integer('permit_unregistered_ip_flg')->default(0)->comment('0:無効|1:有効');
			$table->integer('mfa_flg')->default(0)->comment('0:無効|1:有効');
			$table->integer('state')->comment('0:無効
1:有効');
			$table->datetime('create_at')->useCurrent();
			$table->string('create_user', 128);
            $table->dateTime('update_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->nullable();
			$table->string('update_user', 128)->useCurrent()->nullable();
			$table->integer('stamp_flg')->comment('0：無効  1：有効');
			$table->string('certificate_destination', 256)->nullable();
			$table->integer('certificate_flg')->default(0)->comment('0：デフォルト（シャチハタ証明書） 1：アップロードした証明書');
			$table->string('certificate_name', 256)->nullable();
			$table->string('certificate_pwd', 256)->nullable();
			$table->integer('trial_flg')->default(0);
			$table->integer('portal_flg')->nullable()->default(0)->comment('0:無効|1:有効');
			$table->integer('trial_time')->default(30);
			$table->integer('long_term_storage_flg');
			$table->integer('max_usable_capacity')->nullable();
			$table->integer('view_notification_email_flg')->default(0)->comment('0：無効|1：有効');
			$table->integer('updated_notification_email_flg')->default(0)->comment('0：無効|1：有効');
			$table->integer('guest_company_flg')->default(0)->comment('0：無効|1：有効');
			$table->integer('guest_document_application')->default(0)->comment('0：無効|1：有効');
			$table->integer('host_app_env')->unsigned()->nullable()->comment('0：AWS|1：K5');
			$table->bigInteger('mst_company_id')->unsigned()->nullable();
			$table->string('host_company_name')->nullable();
			$table->integer('host_contract_server')->unsigned()->default(0);
			$table->integer('contract_edition')->default(1)->comment('0：Business|1：Business Pro|2：Business Pro+|3：トライアル');
			$table->string('system_name', 256)->default('契約Edition');
			$table->integer('time_stamp_issuing_count')->default(0)->comment('0：無効|1：有効|※1の場合は承認側でも自社にカウントする');
			$table->integer('template_flg')->default(0)->comment('テンプレートフラグ');
			$table->integer('signature_flg')->default(0)->comment('0:無効|1:有効');
			$table->integer('enable_email_thumbnail')->default(0);
			$table->integer('trial_times')->default(0)->comment('トライアル延長回数');
			$table->integer('phone_app_flg')->default(0)->comment('携帯アプリフラッグ 0:無効|1:有効');
			$table->dateTime('trial_times_update_at')->nullable()->comment('トライアル延長回数更新日時');
			$table->integer('box_enabled')->unsigned()->default(0)->comment('外部連携Box(1:有効 0:無効)');
            $table->integer('template_search_flg')->default(0)->comment('テンプレート検索フラグ');
            $table->integer('received_only_flg')->default(0)->comment('0：通常企業|1：承認限定企業');
            $table->integer('rotate_angle_flg')->default(0)->comment('おじぎ印 0：無効|1：有効');
            $table->integer('enable_email')->default(1)->comment('0:無効/1:有効');
            $table->integer('email_format')->default(1)->comment('0:テキスト/1:HTML');
        });
    }


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('mst_company');
	}

}

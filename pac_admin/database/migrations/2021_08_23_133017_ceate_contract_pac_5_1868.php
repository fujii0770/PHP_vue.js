<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CeateContractPac51868 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contract', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned()
                ->comment('契約ID');
            $table->bigInteger('mst_admin_id')->unsigned()
                ->comment('作成者ID');
            $table->bigInteger('mst_company_id')->unsigned()
                ->comment('作成会社ID');                
            $table->date('contract_fromdate')
                ->nullable()
                ->comment('管理用：派遣開始日');
            $table->date('contract_todate')
                ->nullable()
                ->comment('管理用：派遣終了日');
            $table->integer('intro_type')->unsigned()
                ->nullable()
                ->comment('管理用：紹介派遣か否かの別');
            $table->integer('period_type')->unsigned()
                ->nullable()
                ->comment('管理用：期間の定め');
            $table->integer('contractupdate_type')->unsigned()
                ->nullable()
                ->comment('管理用：契約更新の有無');
            $table->string('update_judgment', 256)
                ->nullable()
                ->comment('管理用：契約の更新の判断基準');
            $table->date('contract_date_sheet')
                ->nullable()
                ->comment('管理用：契約日(帳票用)');
            $table->date('basiccontract_fromdate')
                ->nullable()
                ->comment('管理用：基本契約開始日');
            $table->date('basiccontract_todate')
                ->nullable()
                ->comment('管理用：基本契約終了日');
            $table->string('comment', 256)
                ->nullable()
                ->comment('管理用：コメント');
            $table->string('dispatcharea_name', 128)
                ->nullable()
                ->comment('派遣先：派遣先名');
            $table->string('office_name', 128)
                ->nullable()
                ->comment('派遣先：事業所名');
            $table->string('office_address', 256)
                ->nullable()
                ->comment('派遣先：事業所住所');
            $table->string('department', 128)
                ->nullable()
                ->comment('派遣先：部署名');
            $table->string('position', 128)
                ->nullable()
                ->comment('派遣先：部署長役職');
            $table->string('employment_address', 256)
                ->nullable()
                ->comment('派遣先：就業先所在地');
            $table->string('employment_phone_no', 15)
                ->nullable()
                ->comment('派遣先：就業先TEL');

            $table->string('responsible_name', 128)
                ->nullable()
                ->comment('派遣先：責任者');
            $table->string('responsible_phone_no', 15)
                ->nullable()
                ->comment('派遣先：責任者TEL');

            $table->string('commander_name', 128)
                ->nullable()
                ->comment('派遣先：指揮命令者');
            $table->string('commander_phone_no', 15)
                ->nullable()
                ->comment('派遣先：指揮命令者TEL');

            $table->string('troubles_name', 128)
                ->nullable()
                ->comment('派遣先：苦情処理の申出先');
            $table->string('troubles_phone_no', 15)
                ->nullable()
                ->comment('派遣先：苦情処理の申出先TEL');
                
            $table->integer('fraction_type')->unsigned()
                ->default(3)
                ->comment('派遣先：1円未満端数処理');  
                $table->string('register_no', 10)
                ->nullable()
                ->comment('スタッフ：人材登録番号');

            $table->string('name', 128)
                ->nullable()
                ->comment('スタッフ：氏名');

            $table->integer('gender_type')->unsigned()
                ->default(1)
                ->comment('スタッフ：性別');  

            $table->integer('age')->unsigned()
                ->nullable()
                ->comment('スタッフ：年齢');  

            $table->string('personnel', 64)
                ->nullable()
                ->comment('スタッフ：派遣人員');

            $table->date('socialinsurance_fromdate')
                ->nullable()
                ->comment('スタッフ：社会保険加入日');
            $table->date('socialinsurance_todate')
                ->nullable()
                ->comment('スタッフ：社会保険喪失日');
            $table->date('employmentinsurance_fromdate')
                ->nullable()
                ->comment('スタッフ：雇用保険加入日');
            $table->date('employmentinsurance_todate')
                ->nullable()
                ->comment('スタッフ：雇用保険喪失日');
            $table->date('employmentcontract_date')
                ->nullable()
                ->comment('スタッフ：有期雇用契約雇入れ日');
            $table->date('employmentconversion_date')
                ->nullable()
                ->comment('スタッフ：有期雇用転換日');

            $table->string('insured_certification', 256)
                ->nullable()
                ->comment('スタッフ：被保険者資格取得の有無');
            $table->string('employment_stabilization', 256)
                ->nullable()
                ->comment('スタッフ：雇用安定措置(管理台帳)');
            $table->string('education', 256)
                ->nullable()
                ->comment('スタッフ：教育訓練の状況(管理台帳)');
            $table->string('career_consulting', 256)
                ->nullable()
                ->comment('スタッフ：キャリアコンサルティングの実施状況(管理台帳)');
            $table->string('dispatch_education', 256)
                ->nullable()
                ->comment('スタッフ：派遣先での教育訓練の状況(管理台帳)');
            $table->string('troubles_ledger', 256)
                ->nullable()
                ->comment('スタッフ：苦情処理状況(管理台帳)');
                $table->string('dispatchsource_name', 128)
                ->nullable()
                ->comment('派遣元：名称');
            $table->string('source_address', 256)
                ->nullable()
                ->comment('派遣元：所在地');
            $table->string('source_phone_no', 15)
                ->nullable()
                ->comment('派遣元：所在地TEL');
            $table->string('source_responsible_name', 128)
                ->nullable()
                ->comment('派遣元：責任者');
            $table->string('source_responsible_phone_no', 15)
                ->nullable()
                ->comment('派遣元：責任者TEL');
            $table->string('business_permit_no', 15)
                ->nullable()
                ->comment('派遣元：事業許可番号');
            $table->string('troubles_sheet', 128)
                ->nullable()
                ->comment('派遣元：苦情処理の申出先(帳票用)');
                $table->string('attendance_time', 5)
                ->nullable()
                ->comment('計算項目：出社');
            $table->string('leave_time', 5)
                ->nullable()
                ->comment('計算項目：退社');
            $table->string('break_time', 5)
                ->nullable()
                ->comment('計算項目：休憩');
            $table->string('predetermined_time', 5)
                ->nullable()
                ->comment('計算項目：所定');
            $table->string('overtime', 5)
                ->nullable()
                ->comment('計算項目：残業基準');
            $table->string('paid_time', 5)
                ->nullable()
                ->comment('計算項目：有給日時間数');

            $table->integer('workday_kbn')->unsigned()
                ->default(8)
                ->comment('計算項目：出勤曜日');  

            $table->integer('mmax_overtime')->unsigned()
                ->nullable()
                ->comment('計算項目：月最大時間外労働');  
            $table->integer('wmax_overtime')->unsigned()
                ->nullable()
                ->comment('計算項目：最大週時間外労働');
            $table->integer('wmax_workday')->unsigned()
                ->nullable()
                ->comment('計算項目：最大週勤務日数');  
            $table->integer('request_deadline')->unsigned()
                ->nullable()
                ->comment('計算項目：請求計算締日');  
            $table->integer('salary_deadline')->unsigned()
                ->nullable()
                ->comment('計算項目：給与計算締日');
            $table->integer('time_round_unit')->unsigned()
                ->nullable()
                ->comment('計算項目：時間丸め単位');  

            $table->integer('price_timeflat_type')->unsigned()
                ->default(1)
                ->comment('計算項目：料金_時間制定額制');  
            $table->integer('price_predetermined_time')->unsigned()
                ->nullable()
                ->comment('計算項目：料金_所定時間');  
            $table->integer('price_overtime')->unsigned()
                ->nullable()
                ->comment('計算項目：料金_時間外');  
            $table->integer('price_holiday')->unsigned()
                ->nullable()
                ->comment('計算項目：料金_休日出勤');  
            $table->integer('price_legal_holiday')->unsigned()
                ->nullable()
                ->comment('計算項目：料金_法定休日出勤');  
            $table->integer('price_legal_midnight')->unsigned()
                ->nullable()
                ->comment('計算項目：料金_法定内深夜');  
            $table->integer('price_price_legal_holiday_midnight')->unsigned()
                ->nullable()
                ->comment('計算項目：料金_法定休日深夜');  
            $table->integer('price_other')->unsigned()
                ->nullable()
                ->comment('計算項目：料金_その他');  
            $table->integer('price_month_overtime_flg')->unsigned()
                ->nullable()
                ->comment('計算項目：料金_1か月の時間外労働による割増料金を適用する');  
            $table->integer('price_month60')->unsigned()
                ->nullable()
                ->comment('計算項目：料金_月60時間超過');  
            $table->integer('price_month45')->unsigned()
                ->nullable()
                ->comment('計算項目：料金_月45時間超過');  

            $table->integer('wage_timeflat_type')->unsigned()
                ->default(1)
                ->comment('計算項目：賃金_時間制定額制');  
            $table->integer('wage_predetermined_time')->unsigned()
                ->nullable()
                ->comment('計算項目：賃金_所定時間');  
            $table->integer('wage_overtime')->unsigned()
                ->nullable()
                ->comment('計算項目：賃金_時間外');  
            $table->integer('wage_holiday')->unsigned()
                ->nullable()
                ->comment('計算項目：賃金_休日出勤');  
            $table->integer('wage_legal_holiday')->unsigned()
                ->nullable()
                ->comment('計算項目：賃金_法定休日出勤');  
            $table->integer('wage_legal_midnight')->unsigned()
                ->nullable()
                ->comment('計算項目：賃金_法定内深夜');  
            $table->integer('wage_legal_holiday_midnight')->unsigned()
                ->nullable()
                ->comment('計算項目：賃金_法定休日深夜');  
            $table->integer('wage_other')->unsigned()
                ->nullable()
                ->comment('計算項目：賃金_その他');  
            $table->integer('wage_month_overtime_flg')->unsigned()
                ->nullable()
                ->comment('計算項目：賃金_1か月の時間外労働による割増料金を適用する');  
            $table->integer('wage_month60')->unsigned()
                ->nullable()
                ->comment('計算項目：賃金_月60時間超過');  
            $table->integer('wage_month45')->unsigned()
                ->nullable()
                ->comment('計算項目：賃金_月45時間超過'); 

                $table->string('con_working_style', 256)
                ->nullable()
                ->comment('派遣条件：就業形態');
            $table->string('con_working_hours', 256)
                ->nullable()
                ->comment('派遣条件：就業時間');
            $table->string('con_break_time', 256)
                ->nullable()
                ->comment('派遣条件：休憩時間');
            $table->string('con_contract_time', 256)
                ->nullable()
                ->comment('派遣条件：契約時間');

            $table->integer('business_content_kbn')->unsigned()
                ->default(16)
                ->comment('派遣条件：業務内容'); 
            $table->string('business_other', 256)
                ->nullable()
                ->comment('派遣条件：業務内容(詳細その他)');

            $table->date('organization_date')
                ->nullable()
                ->comment('派遣条件：組織単位抵触日');
            $table->date('organization_date_sheet')
                ->nullable()
                ->comment('派遣条件：組織単位抵触日_帳票用');
            $table->date('office_date')
                ->nullable()
                ->comment('派遣条件：事業所抵触日');
            $table->date('office_date_sheet')
                ->nullable()
                ->comment('派遣条件：事業所抵触日_帳票用');

            $table->integer('dispatchperiod_type')->unsigned()
                ->nullable()
                ->comment('派遣条件：派遣期間制限'); 
            $table->integer('employmentperiod_type')->unsigned()
                ->nullable()
                ->comment('派遣条件：雇用期間の定め有無'); 

            $table->date('employmentworker_fromdate')
                ->nullable()
                ->comment('派遣条件：有期雇用派遣労働者_開始日');
            $table->date('employmentworker_todate')
                ->nullable()
                ->comment('派遣条件：有期雇用派遣労働者_終了日');

            $table->integer('employmentworker_reason_type')->unsigned()
                ->nullable()
                ->comment('派遣条件：無期雇用派遣労働者－理由');
            $table->integer('employmentworker_detail_type')->unsigned()
                ->nullable()
                ->comment('派遣条件：無期雇用派遣労働者－詳細');

            $table->date('project_fromdate')
                ->nullable()
                ->comment('派遣条件：有期プロジェクト業務_開始日');
            $table->date('project_todate')
                ->nullable()
                ->comment('派遣条件：有期プロジェクト業務_終了日');


            $table->integer('monthly_days')->unsigned()
                ->nullable()
                ->comment('派遣条件：１か月間に行われる日数');  
            $table->integer('monthly_workday')->unsigned()
                ->nullable()
                ->comment('派遣条件：通常の労働者の１か月間の所定労働日数');  


            $table->date('closedwork_fromdate')
                ->nullable()
                ->comment('派遣条件：産前・産後・育児・介護休業業務_開始日');
            $table->date('closedwork_todate')
                ->nullable()
                ->comment('派遣条件：産前・産後・育児・介護休業業務_終了日');

            $table->string('worker_name', 256)
                ->nullable()
                ->comment('派遣条件：業務労働者名');
            $table->string('workday_detail', 256)
                ->nullable()
                ->comment('派遣条件：就業日の詳細');
            $table->string('workday', 256)
                ->nullable()
                ->comment('派遣条件：就業日');
            $table->string('payment_terms', 256)
                ->nullable()
                ->comment('派遣条件：料金支払条件');
            $table->string('wage_payment_terms', 256)
                ->nullable()
                ->comment('派遣条件：賃金支払条件');
            $table->string('contract_other', 256)
                ->nullable()
                ->comment('派遣条件：派遣先契約内容その他（派遣契約書)');
            $table->string('staff_other', 256)
                ->nullable()
                ->comment('派遣条件：スタッフ契約内容その他（雇入通知書)');
            $table->string('overtime_terms', 256)
                ->nullable()
                ->comment('派遣条件：時間外労働');
            $table->string('holydaywork_terms', 256)
                ->nullable()
                ->comment('派遣条件：休日労働');
            $table->string('holyday_terms', 256)
                ->nullable()
                ->comment('派遣条件：休日');
            $table->string('health_safety_terms', 256)
                ->nullable()
                ->comment('派遣条件：安全・衛生');
            $table->string('contract_cancel_dispatch', 512)
                ->nullable()
                ->comment('派遣条件：契約解除の措置（派遣契約書)');
            $table->string('contract_cancel_employment', 256)
                ->nullable()
                ->comment('派遣条件：契約解除の措置（雇入通知書)');
            $table->string('retirement', 256)
                ->nullable()
                ->comment('派遣条件：退職に関する事項');
            $table->string('welfare', 256)
                ->nullable()
                ->comment('派遣条件：福利厚生等');
            $table->string('troubles_dispatch', 256)
                ->nullable()
                ->comment('派遣条件：苦情処理事項（派遣契約書)');
            $table->string('troubles_employment', 256)
                ->nullable()
                ->comment('派遣条件：苦情処理事項（雇入通知書)');
            $table->string('dispatch_fee', 256)
                ->nullable()
                ->comment('派遣条件：派遣料金の明示');
            $table->string('dispatch36', 256)
                ->nullable()
                ->comment('派遣条件：派遣元36協定');
            $table->string('dispute_measures', 256)
                ->nullable()
                ->comment('派遣条件：派遣先が派遣労働者を雇用する場合の紛争措置');
            $table->string('contract_deemed', 256)
                ->nullable()
                ->comment('派遣条件：労働契約みなし制度');
            $table->string('validity_period', 256)
                ->nullable()
                ->comment('派遣条件：有効期間の日程');
            $table->string('special_provisions', 256)
                ->nullable()
                ->comment('派遣条件：特別条項');
            $table->string('remarks', 256)
                ->nullable()
                ->comment('派遣条件：備考');
                $table->string('contract_period', 128)
                ->nullable()
                ->comment('契約帳票：契約期間');
            $table->string('business_content', 128)
                ->nullable()
                ->comment('契約帳票：業務内容');
            $table->string('work_place', 128)
                ->nullable()
                ->comment('契約帳票：就業場所');
            $table->string('sh_breaktime', 128)
                ->nullable()
                ->comment('契約帳票：休憩時間');
            $table->string('sh_overwork', 128)
                ->nullable()
                ->comment('契約帳票：所定時間外労働');
            $table->string('sh_holyday', 128)
                ->nullable()
                ->comment('契約帳票：休日');
            $table->string('sh_timeoff', 128)
                ->nullable()
                ->comment('契約帳票：休暇');
            $table->string('sh_wage', 128)
                ->nullable()
                ->comment('契約帳票：賃金');
            $table->string('extra_wage_rate', 128)
                ->nullable()
                ->comment('契約帳票：割増賃金率');
                
            $table->integer('welfarepension_type')->unsigned()
                ->nullable()
                ->comment('契約帳票：厚生年金');  
            $table->integer('healthinsurance_type')->unsigned()
                ->nullable()
                ->comment('契約帳票：健康保険');  
            $table->integer('employmentinsurance_type')->unsigned()
                ->nullable()
                ->comment('契約帳票：雇用保険');  
            $table->integer('accidentinsurance_type')->unsigned()
                ->nullable()
                ->comment('契約帳票：労災保険');  

            $table->string('sheet_other', 128)
                ->nullable()
                ->comment('契約帳票：その他');
            $table->string('introduction_dispatching', 128)
                ->nullable()
                ->comment('管理台帳：紹介予定派遣である旨');
            $table->string('offer_purpose', 128)
                ->nullable()
                ->comment('管理台帳：求人・求職の意思確認');
            $table->string('result', 128)
                ->nullable()
                ->comment('管理台帳：採否結果');
            $table->string('result_reason', 256)
                ->nullable()
                ->comment('管理台帳：採否結果の理由');

            $table->integer('del_flg')->unsigned()
                ->default(0)
                ->comment('削除フラグ');  
            $table->timestamp('create_at')
                ->default(DB::raw('CURRENT_TIMESTAMP'))
                ->comment('作成日');
            $table->string('create_user', 128)
                ->comment('作成者');
            $table->timestamp('update_at')
                ->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))
                ->nullable()
                ->comment('更新日');
            $table->string('update_user', 128)
                ->comment('更新者');
        });
        DB::statement("alter table contract comment '契約テーブル';");    
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contract');
    }
}

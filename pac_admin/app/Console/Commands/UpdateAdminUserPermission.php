<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateAdminUserPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:adminUserPermission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'adminUserPermission';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::beginTransaction();
        try {
            DB::table("mst_company")->orderBy('id')
                ->chunk(100, function ($companies) {
                    $companies->each(function ($company) {
                        // update 保存文書一覧
                        DB::table('admin_authorities_default')
                            ->updateOrInsert(
                                [
                                    'mst_company_id' => $company->id,
                                    'code' => '保存文書一覧',
                                ], [
                                    'mst_company_id' => $company->id,
                                    'code' => '保存文書一覧',
                                    'read_authority' => 1,
                                    'create_authority' => 0,
                                    'update_authority' => 0,
                                    'delete_authority' => 1,
                                    'create_at' => Carbon::now(),
                                    'create_user' => "dev-admin",
                                    'update_at' => Carbon::now(),
                                    'update_user' => "dev-admin",
                                ]
                            );
                        // update 保存文書一覧
                        DB::table("mst_admin")
                            ->where('mst_company_id', $company->id)
                            ->get()
                            ->each(function ($admin) {
                                DB::table('model_has_permissions')
                                    ->updateOrInsert(
                                        ['model_id' => $admin->id, 'model_type' => 'App\CompanyAdmin', 'permission_id' => '201'],
                                        ['model_id' => $admin->id, 'model_type' => 'App\CompanyAdmin', 'permission_id' => '201']
                                    );
                                DB::table('model_has_permissions')
                                    ->updateOrInsert(
                                        ['model_id' => $admin->id, 'model_type' => 'App\CompanyAdmin', 'permission_id' => '202'],
                                        ['model_id' => $admin->id, 'model_type' => 'App\CompanyAdmin', 'permission_id' => '202']
                                    );
                            });
                        // update 添付ファイル一覧
                        if ($company->attachment_flg) {
                            DB::table('admin_authorities_default')
                                ->updateOrInsert(
                                    [
                                        'mst_company_id' => $company->id,
                                        'code' => '添付ファイル一覧',
                                    ], [
                                        'mst_company_id' => $company->id,
                                        'code' => '添付ファイル一覧',
                                        'read_authority' => 1,
                                        'create_authority' => 1,
                                        'update_authority' => 0,
                                        'delete_authority' => 1,
                                        'create_at' => Carbon::now(),
                                        'create_user' => "dev-admin",
                                        'update_at' => Carbon::now(),
                                        'update_user' => "dev-admin",
                                    ]
                                );
                            DB::table("mst_admin")
                                ->where('mst_company_id', $company->id)
                                ->get()
                                ->each(function ($admin) {
                                    DB::table('model_has_permissions')
                                        ->updateOrInsert(
                                            ['model_id' => $admin->id, 'model_type' => 'App\CompanyAdmin', 'permission_id' => '199'],
                                            ['model_id' => $admin->id, 'model_type' => 'App\CompanyAdmin', 'permission_id' => '199']
                                        );
                                    DB::table('model_has_permissions')
                                        ->updateOrInsert(
                                            ['model_id' => $admin->id, 'model_type' => 'App\CompanyAdmin', 'permission_id' => '200'],
                                            ['model_id' => $admin->id, 'model_type' => 'App\CompanyAdmin', 'permission_id' => '200']
                                        );
                                });
                        }
                        // update 名刺一覧
                        if ($company->bizcard_flg) {
                            DB::table('admin_authorities_default')
                                ->updateOrInsert(
                                    [
                                        'mst_company_id' => $company->id,
                                        'code' => '名刺一覧',
                                    ], [
                                        'mst_company_id' => $company->id,
                                        'code' => '名刺一覧',
                                        'read_authority' => 1,
                                        'create_authority' => 0,
                                        'update_authority' => 1,
                                        'delete_authority' => 1,
                                        'create_at' => Carbon::now(),
                                        'create_user' => "dev-admin",
                                        'update_at' => Carbon::now(),
                                        'update_user' => "dev-admin",
                                    ]
                                );
                            DB::table("mst_admin")
                                ->where('mst_company_id', $company->id)
                                ->get()
                                ->each(function ($admin) {
                                    DB::table('model_has_permissions')
                                        ->updateOrInsert(
                                            ['model_id' => $admin->id, 'model_type' => 'App\CompanyAdmin', 'permission_id' => '203'],
                                            ['model_id' => $admin->id, 'model_type' => 'App\CompanyAdmin', 'permission_id' => '203']
                                        );
                                    DB::table('model_has_permissions')
                                        ->updateOrInsert(
                                            ['model_id' => $admin->id, 'model_type' => 'App\CompanyAdmin', 'permission_id' => '204'],
                                            ['model_id' => $admin->id, 'model_type' => 'App\CompanyAdmin', 'permission_id' => '204']
                                        );
                                    DB::table('model_has_permissions')
                                        ->updateOrInsert(
                                            ['model_id' => $admin->id, 'model_type' => 'App\CompanyAdmin', 'permission_id' => '205'],
                                            ['model_id' => $admin->id, 'model_type' => 'App\CompanyAdmin', 'permission_id' => '205']
                                        );
                                });
                        }
                        // update 回覧完了テンプレート一覧
                        if ($company->template_flg && $company->template_csv_flg) {
                            DB::table('admin_authorities_default')
                                ->updateOrInsert(
                                    [
                                        'mst_company_id' => $company->id,
                                        'code' => '回覧完了テンプレート一覧',
                                    ], [
                                        'mst_company_id' => $company->id,
                                        'code' => '回覧完了テンプレート一覧',
                                        'read_authority' => 1,
                                        'create_authority' => 0,
                                        'update_authority' => 0,
                                        'delete_authority' => 0,
                                        'create_at' => Carbon::now(),
                                        'create_user' => "dev-admin",
                                        'update_at' => Carbon::now(),
                                        'update_user' => "dev-admin",
                                    ]
                                );
                            DB::table("mst_admin")
                                ->where('mst_company_id', $company->id)
                                ->get()
                                ->each(function ($admin) {
                                    DB::table('model_has_permissions')
                                        ->updateOrInsert(
                                            ['model_id' => $admin->id, 'model_type' => 'App\CompanyAdmin', 'permission_id' => '206'],
                                            ['model_id' => $admin->id, 'model_type' => 'App\CompanyAdmin', 'permission_id' => '206']
                                        );
                                });
                        }
                        // update テンプレート
                        if ($company->template_flg) {
                            DB::table('admin_authorities_default')
                                ->updateOrInsert(
                                    [
                                        'mst_company_id' => $company->id,
                                        'code' => 'テンプレート',
                                    ], [
                                        'mst_company_id' => $company->id,
                                        'code' => 'テンプレート',
                                        'read_authority' => 1,
                                        'create_authority' => 1,
                                        'update_authority' => 0,
                                        'delete_authority' => 1,
                                        'create_at' => Carbon::now(),
                                        'create_user' => "dev-admin",
                                        'update_at' => Carbon::now(),
                                        'update_user' => "dev-admin",
                                    ]
                                );
                            DB::table("mst_admin")
                                ->where('mst_company_id', $company->id)
                                ->get()
                                ->each(function ($admin) {
                                    DB::table('model_has_permissions')
                                        ->updateOrInsert(
                                            ['model_id' => $admin->id, 'model_type' => 'App\CompanyAdmin', 'permission_id' => '207'],
                                            ['model_id' => $admin->id, 'model_type' => 'App\CompanyAdmin', 'permission_id' => '207']
                                        );
                                    DB::table('model_has_permissions')
                                        ->updateOrInsert(
                                            ['model_id' => $admin->id, 'model_type' => 'App\CompanyAdmin', 'permission_id' => '208'],
                                            ['model_id' => $admin->id, 'model_type' => 'App\CompanyAdmin', 'permission_id' => '208']
                                        );
                                    DB::table('model_has_permissions')
                                        ->updateOrInsert(
                                            ['model_id' => $admin->id, 'model_type' => 'App\CompanyAdmin', 'permission_id' => '209'],
                                            ['model_id' => $admin->id, 'model_type' => 'App\CompanyAdmin', 'permission_id' => '209']
                                        );
                                });
                        }
                        
                    });
                });
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage() . $e->getTraceAsString());
        }

    }
}

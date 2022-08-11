<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        $this->call(api_authentication_seeder::class);
        $this->call(mst_shachihata_seeder::class);
        $this->call(oauth_clients_seeder::class);
        $this->call(oauth_personal_access_clients_seeder::class);
        $this->call(mst_display_seeder::class);
        $this->call(mst_operation_info_seeder::class);
        $this->call(permissions_seeder::class);
        $this->call(roles_seeder::class);
        $this->call(role_has_permissions_seeder::class);
        $this->call(mst_stamp_special_seeder::class);
        $this->call(mst_template_placeholder_seeder::class);
        $this->call(mst_favorite_service_seeder::class);
        $this->call(mst_mypage_layout_seeder::class);
        $this->call(mst_stamp_synonyms_map_seeder::class);
        $this->call(mst_longterm_index_seeder::class);
        $this->call(mst_stamp_convenient_division_seeder::class);
        $this->call(bbs_auth_seeder::class);
        $this->call(mst_app_function_seeder::class);
        $this->call(mst_app_function_management_seeder::class);
        $this->call(mst_access_privileges_seeder::class);
        $this->call(app_role_seeder::class);
        $this->call(app_role_detail_seeder::class);
        $this->call(mst_application_seeder::class);
        $this->call(dispatch_code_seeder::class);
        $this->call(dispatchhr_template_seeder::class);
        $this->call(dispatchhr_screenitems_seeder::class);
        $this->call(mst_contract_edition_seeder::class);
        $this->call(mst_company_seeder::class);
    }
}

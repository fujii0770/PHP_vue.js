<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InsertMstRegionPac51902SpecialSite extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            insert into mst_region (region_id, region_name, created_at, create_user, updated_at, update_user)
            VALUES (1, '東京都', now(), 'admin', now(), 'admin'),
                   (2, '北海道', now(), 'admin', now(), 'admin'),
                   (3, '大阪府', now(), 'admin', now(), 'admin'),
                   (4, '京都府', now(), 'admin', now(), 'admin'),
                   (5, '青森県', now(), 'admin', now(), 'admin'),
                   (6, '秋田県', now(), 'admin', now(), 'admin'),
                   (7, '岩手県', now(), 'admin', now(), 'admin'),
                   (8, '山形県', now(), 'admin', now(), 'admin'),
                   (9, '宮城県', now(), 'admin', now(), 'admin'),
                   (10, '福島県', now(), 'admin', now(), 'admin'),
                   (11, '神奈川県', now(), 'admin', now(), 'admin'),
                   (12, '千葉県', now(), 'admin', now(), 'admin'),
                   (13, '埼玉県', now(), 'admin', now(), 'admin'),
                   (14, '茨城県', now(), 'admin', now(), 'admin'),
                   (15, '群馬県', now(), 'admin', now(), 'admin'),
                   (16, '栃木県', now(), 'admin', now(), 'admin'),
                   (17, '山梨県', now(), 'admin', now(), 'admin'),
                   (18, '新潟県', now(), 'admin', now(), 'admin'),
                   (19, '長野県', now(), 'admin', now(), 'admin'),
                   (20, '愛知県', now(), 'admin', now(), 'admin'),
                   (21, '岐阜県', now(), 'admin', now(), 'admin'),
                   (22, '三重県', now(), 'admin', now(), 'admin'),
                   (23, '静岡県', now(), 'admin', now(), 'admin'),
                   (24, '富山県', now(), 'admin', now(), 'admin'),
                   (25, '石川県', now(), 'admin', now(), 'admin'),
                   (26, '福井県', now(), 'admin', now(), 'admin'),
                   (27, '兵庫県', now(), 'admin', now(), 'admin'),
                   (28, '滋賀県', now(), 'admin', now(), 'admin'),
                   (29, '奈良県', now(), 'admin', now(), 'admin'),
                   (30, '和歌山県', now(), 'admin', now(), 'admin'),
                   (31, '岡山県', now(), 'admin', now(), 'admin'),
                   (32, '広島県', now(), 'admin', now(), 'admin'),
                   (33, '鳥取県', now(), 'admin', now(), 'admin'),
                   (34, '島根県', now(), 'admin', now(), 'admin'),
                   (35, '山口県', now(), 'admin', now(), 'admin'),
                   (36, '香川県', now(), 'admin', now(), 'admin'),
                   (37, '徳島県', now(), 'admin', now(), 'admin'),
                   (38, '高知県', now(), 'admin', now(), 'admin'),
                   (39, '愛媛県', now(), 'admin', now(), 'admin'),
                   (40, '福岡県', now(), 'admin', now(), 'admin'),
                   (41, '佐賀県', now(), 'admin', now(), 'admin'),
                   (42, '大分県', now(), 'admin', now(), 'admin'),
                   (43, '長崎県', now(), 'admin', now(), 'admin'),
                   (44, '熊本県', now(), 'admin', now(), 'admin'),
                   (45, '宮崎県', now(), 'admin', now(), 'admin'),
                   (46, '鹿児島県', now(), 'admin', now(), 'admin'),
                   (47, '沖縄県', now(), 'admin', now(), 'admin')
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

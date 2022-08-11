<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOperationHistory7Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('operation_history7', function(Blueprint $table)
        {
            $ddl = "CREATE TABLE `operation_history7` (
                    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                    `auth_flg` int NOT NULL COMMENT '権限フラグ,0:管理者/1:利用者',
                    `user_id` bigint unsigned NOT NULL COMMENT 'ユーザーID',
                    `mst_display_id` bigint NOT NULL COMMENT '画面マスタID',
                    `mst_operation_id` bigint NOT NULL COMMENT '操作情報ID',
                    `result` int NOT NULL COMMENT '結果,処理の結果を格納。0:成功/1:失敗',
                    `detail_info` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '詳細情報,成功時はファイル名等を格納、失敗時はエラー内容を格納',
                    `ip_address` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '接続IPアドレス,ログインユーザーのIPアドレスを格納',
                    `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '作成日時,DEFAULT_GENERATED',
                    `create_user` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    `update_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                    `update_user` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    PRIMARY KEY (`id`,`create_at`),
                    KEY `idx_operation_history_on_auth_flg` (`auth_flg`),
                    KEY `idx_operation_history_on_create_at` (`create_at`),
                    KEY `idx_operation_history_on_user_id` (`user_id`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='操作履歴テーブル,ユーザーの操作履歴を保持する。'
                   PARTITION BY RANGE  COLUMNS(create_at)
                  (PARTITION p202006 VALUES LESS THAN ('2020-07-01 00:00:00') COMMENT = '2020-06' ENGINE = InnoDB,
                   PARTITION p202007 VALUES LESS THAN ('2020-08-01 00:00:00') COMMENT = '2020-07' ENGINE = InnoDB,
                   PARTITION p202008 VALUES LESS THAN ('2020-09-01 00:00:00') COMMENT = '2020-08' ENGINE = InnoDB,
                   PARTITION p202009 VALUES LESS THAN ('2020-10-01 00:00:00') COMMENT = '2020-09' ENGINE = InnoDB,
                   PARTITION p202010 VALUES LESS THAN ('2020-11-01 00:00:00') COMMENT = '2020-10' ENGINE = InnoDB,
                   PARTITION p202011 VALUES LESS THAN ('2020-12-01 00:00:00') COMMENT = '2020-11' ENGINE = InnoDB,
                   PARTITION p202012 VALUES LESS THAN ('2021-01-01 00:00:00') COMMENT = '2020-12' ENGINE = InnoDB,
                   PARTITION p202101 VALUES LESS THAN ('2021-02-01 00:00:00') COMMENT = '2021-01' ENGINE = InnoDB,
                   PARTITION p202102 VALUES LESS THAN ('2021-03-01 00:00:00') COMMENT = '2021-02' ENGINE = InnoDB,
                   PARTITION p202103 VALUES LESS THAN ('2021-04-01 00:00:00') COMMENT = '2021-03' ENGINE = InnoDB,
                   PARTITION p202104 VALUES LESS THAN ('2021-05-01 00:00:00') COMMENT = '2021-04' ENGINE = InnoDB,
                   PARTITION p202105 VALUES LESS THAN ('2021-06-01 00:00:00') COMMENT = '2021-05' ENGINE = InnoDB,
                   PARTITION p202106 VALUES LESS THAN ('2021-07-01 00:00:00') COMMENT = '2021-06' ENGINE = InnoDB,
                   PARTITION p202107 VALUES LESS THAN ('2021-08-01 00:00:00') COMMENT = '2021-07' ENGINE = InnoDB,
                   PARTITION p202108 VALUES LESS THAN ('2021-09-01 00:00:00') COMMENT = '2021-08' ENGINE = InnoDB,
                   PARTITION p202109 VALUES LESS THAN ('2021-10-01 00:00:00') COMMENT = '2021-09' ENGINE = InnoDB,
                   PARTITION p202110 VALUES LESS THAN ('2021-11-01 00:00:00') COMMENT = '2021-10' ENGINE = InnoDB,
                   PARTITION p202111 VALUES LESS THAN ('2021-12-01 00:00:00') COMMENT = '2021-11' ENGINE = InnoDB,
                   PARTITION p202112 VALUES LESS THAN ('2022-01-01 00:00:00') COMMENT = '2021-12' ENGINE = InnoDB,
                   PARTITION p202201 VALUES LESS THAN ('2022-02-01 00:00:00') COMMENT = '2022-01' ENGINE = InnoDB,
                   PARTITION p202202 VALUES LESS THAN ('2022-03-01 00:00:00') COMMENT = '2022-02' ENGINE = InnoDB,
                   PARTITION p202203 VALUES LESS THAN ('2022-04-01 00:00:00') COMMENT = '2022-03' ENGINE = InnoDB,
                   PARTITION p202204 VALUES LESS THAN ('2022-05-01 00:00:00') COMMENT = '2022-04' ENGINE = InnoDB,
                   PARTITION p202205 VALUES LESS THAN ('2022-06-01 00:00:00') COMMENT = '2022-05' ENGINE = InnoDB,
                   PARTITION p202206 VALUES LESS THAN ('2022-07-01 00:00:00') COMMENT = '2022-06' ENGINE = InnoDB,
                   PARTITION p202207 VALUES LESS THAN ('2022-08-01 00:00:00') COMMENT = '2022-07' ENGINE = InnoDB,
                   PARTITION p202208 VALUES LESS THAN ('2022-09-01 00:00:00') COMMENT = '2022-08' ENGINE = InnoDB,
                   PARTITION p202209 VALUES LESS THAN ('2022-10-01 00:00:00') COMMENT = '2022-09' ENGINE = InnoDB,
                   PARTITION p202210 VALUES LESS THAN ('2022-11-01 00:00:00') COMMENT = '2022-10' ENGINE = InnoDB,
                   PARTITION p202211 VALUES LESS THAN ('2022-12-01 00:00:00') COMMENT = '2022-11' ENGINE = InnoDB,
                   PARTITION p202212 VALUES LESS THAN ('2023-01-01 00:00:00') COMMENT = '2022-12' ENGINE = InnoDB,
                   PARTITION p202301 VALUES LESS THAN ('2023-02-01 00:00:00') COMMENT = '2023-01' ENGINE = InnoDB,
                   PARTITION p202302 VALUES LESS THAN ('2023-03-01 00:00:00') COMMENT = '2023-02' ENGINE = InnoDB,
                   PARTITION p202303 VALUES LESS THAN ('2023-04-01 00:00:00') COMMENT = '2023-03' ENGINE = InnoDB,
                   PARTITION p202304 VALUES LESS THAN ('2023-05-01 00:00:00') COMMENT = '2023-04' ENGINE = InnoDB,
                   PARTITION p202305 VALUES LESS THAN ('2023-06-01 00:00:00') COMMENT = '2023-05' ENGINE = InnoDB,
                   PARTITION p202306 VALUES LESS THAN ('2023-07-01 00:00:00') COMMENT = '2023-06' ENGINE = InnoDB,
                   PARTITION p202307 VALUES LESS THAN ('2023-08-01 00:00:00') COMMENT = '2023-07' ENGINE = InnoDB,
                   PARTITION p202308 VALUES LESS THAN ('2023-09-01 00:00:00') COMMENT = '2023-08' ENGINE = InnoDB,
                   PARTITION p202309 VALUES LESS THAN ('2023-10-01 00:00:00') COMMENT = '2023-09' ENGINE = InnoDB,
                   PARTITION p202310 VALUES LESS THAN ('2023-11-01 00:00:00') COMMENT = '2023-10' ENGINE = InnoDB,
                   PARTITION p202311 VALUES LESS THAN ('2023-12-01 00:00:00') COMMENT = '2023-11' ENGINE = InnoDB,
                   PARTITION p202312 VALUES LESS THAN ('2024-01-01 00:00:00') COMMENT = '2023-12' ENGINE = InnoDB,
                   PARTITION p202401 VALUES LESS THAN ('2024-02-01 00:00:00') COMMENT = '2024-01' ENGINE = InnoDB,
                   PARTITION p202402 VALUES LESS THAN ('2024-03-01 00:00:00') COMMENT = '2024-02' ENGINE = InnoDB,
                   PARTITION p202403 VALUES LESS THAN ('2024-04-01 00:00:00') COMMENT = '2024-03' ENGINE = InnoDB,
                   PARTITION p202404 VALUES LESS THAN ('2024-05-01 00:00:00') COMMENT = '2024-04' ENGINE = InnoDB,
                   PARTITION p202405 VALUES LESS THAN ('2024-06-01 00:00:00') COMMENT = '2024-05' ENGINE = InnoDB,
                   PARTITION p202406 VALUES LESS THAN ('2024-07-01 00:00:00') COMMENT = '2024-06' ENGINE = InnoDB,
                   PARTITION p202407 VALUES LESS THAN ('2024-08-01 00:00:00') COMMENT = '2024-07' ENGINE = InnoDB,
                   PARTITION p202408 VALUES LESS THAN ('2024-09-01 00:00:00') COMMENT = '2024-08' ENGINE = InnoDB,
                   PARTITION p202409 VALUES LESS THAN ('2024-10-01 00:00:00') COMMENT = '2024-09' ENGINE = InnoDB,
                   PARTITION p202410 VALUES LESS THAN ('2024-11-01 00:00:00') COMMENT = '2024-10' ENGINE = InnoDB,
                   PARTITION p202411 VALUES LESS THAN ('2024-12-01 00:00:00') COMMENT = '2024-11' ENGINE = InnoDB,
                   PARTITION p202412 VALUES LESS THAN ('2025-01-01 00:00:00') COMMENT = '2024-12' ENGINE = InnoDB,
                   PARTITION p202501 VALUES LESS THAN ('2025-02-01 00:00:00') COMMENT = '2025-01' ENGINE = InnoDB,
                   PARTITION p202502 VALUES LESS THAN ('2025-03-01 00:00:00') COMMENT = '2025-02' ENGINE = InnoDB,
                   PARTITION p202503 VALUES LESS THAN ('2025-04-01 00:00:00') COMMENT = '2025-03' ENGINE = InnoDB,
                   PARTITION p202504 VALUES LESS THAN ('2025-05-01 00:00:00') COMMENT = '2025-04' ENGINE = InnoDB,
                   PARTITION p202505 VALUES LESS THAN ('2025-06-01 00:00:00') COMMENT = '2025-05' ENGINE = InnoDB,
                   PARTITION p202506 VALUES LESS THAN ('2025-07-01 00:00:00') COMMENT = '2025-06' ENGINE = InnoDB,
                   PARTITION p202507 VALUES LESS THAN ('2025-08-01 00:00:00') COMMENT = '2025-07' ENGINE = InnoDB,
                   PARTITION p202508 VALUES LESS THAN ('2025-09-01 00:00:00') COMMENT = '2025-08' ENGINE = InnoDB,
                   PARTITION p202509 VALUES LESS THAN ('2025-10-01 00:00:00') COMMENT = '2025-09' ENGINE = InnoDB,
                   PARTITION p202510 VALUES LESS THAN ('2025-11-01 00:00:00') COMMENT = '2025-10' ENGINE = InnoDB,
                   PARTITION p202511 VALUES LESS THAN ('2025-12-01 00:00:00') COMMENT = '2025-11' ENGINE = InnoDB,
                   PARTITION p202512 VALUES LESS THAN ('2026-01-01 00:00:00') COMMENT = '2025-12' ENGINE = InnoDB,
                   PARTITION p202601 VALUES LESS THAN ('2026-02-01 00:00:00') COMMENT = '2026-01' ENGINE = InnoDB,
                   PARTITION p202602 VALUES LESS THAN ('2026-03-01 00:00:00') COMMENT = '2026-02' ENGINE = InnoDB,
                   PARTITION p202603 VALUES LESS THAN ('2026-04-01 00:00:00') COMMENT = '2026-03' ENGINE = InnoDB,
                   PARTITION p202604 VALUES LESS THAN ('2026-05-01 00:00:00') COMMENT = '2026-04' ENGINE = InnoDB,
                   PARTITION p202605 VALUES LESS THAN ('2026-06-01 00:00:00') COMMENT = '2026-05' ENGINE = InnoDB,
                   PARTITION p202606 VALUES LESS THAN ('2026-07-01 00:00:00') COMMENT = '2026-06' ENGINE = InnoDB,
                   PARTITION p202607 VALUES LESS THAN ('2026-08-01 00:00:00') COMMENT = '2026-07' ENGINE = InnoDB)";

            DB::connection()->getPdo()->exec($ddl);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('operation_history7');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeLongTermDocumentPac51815Data extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('
                DELETE l FROM long_term_document l,(SELECT MIN( id ) AS id,circular_id,mst_company_id,completed_at FROM long_term_document GROUP BY circular_id,mst_company_id,completed_at  HAVING count( * ) > 1 ) b 
                WHERE l.id <> b.id  AND l.circular_id = b.circular_id AND l.completed_at = b.completed_at AND l.mst_company_id = b.mst_company_id
        ');
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

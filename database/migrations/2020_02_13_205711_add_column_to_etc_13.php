<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToEtc13 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // SaleRel
        Schema::table('sale_relations', function (Blueprint $table) {
            $table->string('amzn_reference_id')->after('user_comment')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // SaleRel
        if (Schema::hasColumn('sale_relations', 'amzn_reference_id')) {
            Schema::table('sale_relations', function (Blueprint $table) {
                $table->dropColumn('amzn_reference_id');
            });
        }
    }
}

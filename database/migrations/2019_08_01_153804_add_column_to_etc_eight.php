<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToEtcEight extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Sale
        Schema::table('sales', function (Blueprint $table) {            
            $table->boolean('is_huzaioki')->after('plan_time')->nullable()->default(null);
        });
        
        //SaleRel
        Schema::table('sale_relations', function (Blueprint $table) {            
            $table->text('huzai_comment')->after('destination')->nullable()->default(null);
            $table->integer('adjust_price')->after('all_price')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('sales', 'is_huzaioki')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('is_huzaioki');
            });
        }
        
        if (Schema::hasColumn('sale_relations', 'huzai_comment')) {
            Schema::table('sale_relations', function (Blueprint $table) {
                $table->dropColumn('huzai_comment');
            });
        }
        
        if (Schema::hasColumn('sale_relations', 'adjust_price')) {
            Schema::table('sale_relations', function (Blueprint $table) {
                $table->dropColumn('adjust_price');
            });
        }
    }
}

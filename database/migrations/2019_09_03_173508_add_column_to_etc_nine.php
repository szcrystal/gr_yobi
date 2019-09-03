<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToEtcNine extends Migration
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
            $table->integer('seinou_huzai')->after('add_point')->nullable()->default(0);
            $table->integer('seinou_sunday')->after('seinou_huzai')->nullable()->default(0);
        });

		//Sale
        Schema::table('sale_relations', function (Blueprint $table) {
            $table->integer('seinou_huzai')->after('add_point')->nullable()->default(0);         
            $table->integer('seinou_sunday')->after('seinou_huzai')->nullable()->default(0);
           
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    	if (Schema::hasColumn('sales', 'seinou_huzai')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('seinou_huzai');
            });
        }
        if (Schema::hasColumn('sales', 'seinou_sunday')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('seinou_sunday');
            });
        }
        
        if (Schema::hasColumn('sale_relations', 'seinou_huzai')) {
            Schema::table('sale_relations', function (Blueprint $table) {
                $table->dropColumn('seinou_huzai');
            });
        }
        if (Schema::hasColumn('sale_relations', 'seinou_sunday')) {
            Schema::table('sale_relations', function (Blueprint $table) {
                $table->dropColumn('seinou_sunday');
            });
        }
        
    }
}

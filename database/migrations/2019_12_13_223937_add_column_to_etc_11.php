<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToEtc11 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Category
        Schema::table('categories', function (Blueprint $table) {
            $table->string('main_img')->after('slug')->nullable()->default(null);
        });
        
        Schema::table('sales', function (Blueprint $table) {
            $table->index(['item_id', 'created_at']);
            //$table->primary(['item_id', 'created_at']);
        });
        
        Schema::table('items', function (Blueprint $table) {
            $table->index('cate_id');
            $table->index('subcate_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Category
        if (Schema::hasColumn('categories', 'main_img')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('main_img');
            });
        }
        
        if (Schema::hasTable('sales')) {
            Schema::table('sales', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexesFound = $sm->listTableIndexes('sales');

                if(array_key_exists("sales_item_id_created_at_index", $indexesFound)) {
                    $table->dropIndex(['item_id_created_at']); // 'geo_state_index'インデックスを削除
                }
                
//                if(array_key_exists("sales_created_at_index", $indexesFound))
//                    $table->dropIndex(['created_at']);
            });
        }
        
        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexesFound = $sm->listTableIndexes('items');
                
                if(array_key_exists("items_cate_id_index", $indexesFound))
                    $table->dropIndex(['cate_id']); // 'geo_state_index'インデックスを削除
                
                if(array_key_exists("items_subcate_id_index", $indexesFound))
                    $table->dropIndex(['subcate_id']);
            });
        }
    }
}

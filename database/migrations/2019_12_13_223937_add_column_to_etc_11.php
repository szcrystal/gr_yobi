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
        
        //TopSetting
        Schema::table('top_settings', function (Blueprint $table) {
            $table->string('search_words')->after('contents')->nullable()->default(null);
        });
        
        //ItemUpper
        Schema::table('item_uppers', function (Blueprint $table) {
            $table->boolean('is_more')->after('open_status')->nullable()->default(1);
        });
        
        
        //Sales Index
        Schema::table('sales', function (Blueprint $table) {
            //$table->primary(['id', 'item_id', 'created_at']);
            $table->index(['item_id', 'created_at']);
            
        });
        
        //Items Index
        /*
        Schema::table('items', function (Blueprint $table) {
            $table->index('cate_id');
            $table->index('subcate_id');
            $table->index(['open_status', 'is_potset']);
        });
        */
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
        
        // TopSetting
        if (Schema::hasColumn('top_settings', 'search_words')) {
            Schema::table('top_settings', function (Blueprint $table) {
                $table->dropColumn('search_words');
            });
        }
        
        // ItemUpper
        if (Schema::hasColumn('item_uppers', 'is_more')) {
            Schema::table('item_uppers', function (Blueprint $table) {
                $table->dropColumn('is_more');
            });
        }
        
        
        //Sales Index
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
        
        //Items Index
        /*
        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexesFound = $sm->listTableIndexes('items');
                
                if(array_key_exists("items_cate_id_index", $indexesFound))
                    $table->dropIndex(['cate_id']); // 'geo_state_index'インデックスを削除
                
                if(array_key_exists("items_subcate_id_index", $indexesFound))
                    $table->dropIndex(['subcate_id']);
                    
                if(array_key_exists("items_open_status_is_potset_index", $indexesFound)) {
                    $table->dropIndex(['open_status_is_potset']); // 'geo_state_index'インデックスを削除
                }
            });
        }
        */
    }
}

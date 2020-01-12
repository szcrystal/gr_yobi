<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToEtc12 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Item
        Schema::table('items', function (Blueprint $table) {
            $table->integer('pot_type')->after('catchcopy')->nullable()->default(1);
        });
        
        //Items Index
        Schema::table('items', function (Blueprint $table) {
            $table->index('cate_id');
            $table->index('subcate_id');
            $table->index(['open_status', 'pot_type']);
        });
        
        //DataRanking Table Create
        Schema::create('data_rankings', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('sale_id')->nullable()->default(NULL);
            $table->integer('item_id')->nullable()->default(NULL);
            $table->integer('cate_id')->nullable()->default(NULL);
            $table->integer('subcate_id')->nullable()->default(NULL);
            $table->integer('pot_type')->nullable()->default(NULL);
            
            $table->integer('sale_count')->nullable()->default(0);
            $table->integer('sale_price')->nullable()->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Item
        if (Schema::hasColumn('items', 'pot_type')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropColumn('pot_type');
            });
        }
        
        //Items Index
        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexesFound = $sm->listTableIndexes('items');
                
                if(array_key_exists("items_cate_id_index", $indexesFound))
                    $table->dropIndex(['cate_id']); // 'geo_state_index'インデックスを削除
                
                if(array_key_exists("items_subcate_id_index", $indexesFound))
                    $table->dropIndex(['subcate_id']);
                    
                if(array_key_exists("items_open_status_pot_type_index", $indexesFound)) {
                    $table->dropIndex(['open_status', 'pot_type']); // 'geo_state_index'インデックスを削除
                }
            });
        }
        
        
        //DataRanking Table Drop
        Schema::dropIfExists('data_rankings');
        
        
    }
}

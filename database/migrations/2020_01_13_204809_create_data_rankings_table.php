<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataRankingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_rankings', function (Blueprint $table) {
            $table->increments('id');
                
            $table->integer('sale_id')->nullable()->default(NULL);
            $table->integer('item_id')->nullable()->default(NULL);
            $table->integer('cate_id')->nullable()->default(NULL);
            $table->integer('subcate_id')->nullable()->default(NULL);
            $table->integer('pot_type')->nullable()->default(NULL);
            
            $table->integer('sale_count')->nullable()->default(0);
            $table->integer('sale_price')->nullable()->default(0);
            
            $table->boolean('is_cancel')->nullable()->default(0); // ここのデータ量は極力減らしたいのでキャンセルはデータ削除の方向で
            
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
        Schema::dropIfExists('data_rankings');
    }
}

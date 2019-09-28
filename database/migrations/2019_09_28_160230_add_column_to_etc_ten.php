<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToEtcTen extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Sale
        Schema::table('settings', function (Blueprint $table) {
            $table->integer('post_block')->after('snap_block_c')->nullable()->default(5);
            //$table->integer('seinou_sunday')->after('seinou_huzai')->nullable()->default(0);
        });

		//Sale
//        Schema::table('sale_relations', function (Blueprint $table) {
//            $table->integer('seinou_huzai')->after('add_point')->nullable()->default(0);         
//            $table->integer('seinou_sunday')->after('seinou_huzai')->nullable()->default(0);
//           
//        });


    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('settings', 'post_block')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('post_block');
            });
        }
    }
}

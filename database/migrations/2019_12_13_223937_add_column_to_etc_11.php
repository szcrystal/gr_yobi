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
    }
}

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
        //Setting
        Schema::table('settings', function (Blueprint $table) {
            $table->integer('post_block')->after('snap_block_c')->nullable()->default(5);
            $table->integer('rank_term')->after('rewrite_time')->nullable()->default(30);
            $table->integer('rank_term_ueki')->after('rank_term')->nullable()->default(30);
        });

		//Top Setting
//        Schema::table('top_settings', function (Blueprint $table) {
//        	$table->string('post_meta_title')->after('meta_keyword')->nullable()->default(NULL);
//            $table->text('post_meta_description')->after('post_meta_title')->nullable()->default(NULL);
//            $table->string('post_meta_keyword')->after('post_meta_description')->nullable()->default(NULL);
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
        
        if (Schema::hasColumn('settings', 'rank_term')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('rank_term');
            });
        }
        
        if (Schema::hasColumn('settings', 'rank_term_ueki')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('rank_term_ueki');
            });
        }
        
        /*
        if (Schema::hasColumn('settings', 'rank_term_ueki')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('rank_term_ueki');
            });
        }
        
        if (Schema::hasColumn('settings', 'rank_term_ueki')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('rank_term_ueki');
            });
        }
        
        if (Schema::hasColumn('settings', 'rank_term_ueki')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('rank_term_ueki');
            });
        }
        
        if (Schema::hasColumn('settings', 'rank_term_ueki')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('rank_term_ueki');
            });
        }
        */
        
    }
}

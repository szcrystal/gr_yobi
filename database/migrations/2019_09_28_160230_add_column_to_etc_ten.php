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
            
            $table->string('twitter_id')->after('fix_other')->nullable()->default(NULL);
            $table->string('fb_app_id')->after('twitter_id')->nullable()->default(NULL);
            $table->string('instagram_id')->after('twitter_id')->nullable()->default(NULL);
        });

		//Top Setting
        Schema::table('top_settings', function (Blueprint $table) {
        	$table->string('post_meta_title')->after('meta_keyword')->nullable()->default(NULL);
            $table->text('post_meta_description')->after('post_meta_title')->nullable()->default(NULL);
            $table->string('post_meta_keyword')->after('post_meta_description')->nullable()->default(NULL);
        });


    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    	//Setting
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
        
        if (Schema::hasColumn('settings', 'twitter_id')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('twitter_id');
            });
        }
        
        if (Schema::hasColumn('settings', 'fb_app_id')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('fb_app_id');
            });
        }
        
        //Top Setting
        if (Schema::hasColumn('top_settings', 'post_meta_title')) {
            Schema::table('top_settings', function (Blueprint $table) {
                $table->dropColumn('post_meta_title');
            });
        }
        
        if (Schema::hasColumn('top_settings', 'post_meta_description')) {
            Schema::table('top_settings', function (Blueprint $table) {
                $table->dropColumn('post_meta_description');
            });
        }
        
        if (Schema::hasColumn('top_settings', 'post_meta_keyword')) {
            Schema::table('top_settings', function (Blueprint $table) {
                $table->dropColumn('post_meta_keyword');
            });
        }
        
        
    }
}

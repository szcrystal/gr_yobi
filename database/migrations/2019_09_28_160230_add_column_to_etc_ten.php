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
            $table->string('instagram_id')->after('fb_app_id')->nullable()->default(NULL);
            
            $table->string('btn_color_1')->after('kare_ensure')->nullable()->default(NULL);
            $table->string('btn_color_2')->after('btn_color_1')->nullable()->default(NULL);
        });

		//Top Setting
        Schema::table('top_settings', function (Blueprint $table) {
        	$table->string('post_meta_title')->after('meta_keyword')->nullable()->default(NULL);
            $table->text('post_meta_description')->after('post_meta_title')->nullable()->default(NULL);
            $table->string('post_meta_keyword')->after('post_meta_description')->nullable()->default(NULL);
        });

        //Item
        Schema::table('items', function (Blueprint $table) {
            $table->integer('once_price')->after('is_once')->nullable()->default(NULL);
        });
        
        //Sale
        Schema::table('sales', function (Blueprint $table) {
            $table->integer('is_once_down')->after('is_huzaioki')->nullable()->default(NULL);
        });
        
        //SendMailFlags
        Schema::table('send_mail_flags', function (Blueprint $table) {
            $table->integer('add_point')->after('information_foot')->nullable()->default(NULL);
        });
        
        //PostRelation
        Schema::table('post_relations', function (Blueprint $table) {
            $table->string('relate_post_ids')->after('big_title')->nullable()->default(NULL);
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
        
        if (Schema::hasColumn('settings', 'instagram_id')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('instagram_id');
            });
        }
        
        if (Schema::hasColumn('settings', 'btn_color')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('btn_color');
            });
        }
        
        if (Schema::hasColumn('settings', 'btn_color_1')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('btn_color_1');
            });
        }
        
        if (Schema::hasColumn('settings', 'btn_color_2')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('btn_color_2');
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
        
        //Item
        if (Schema::hasColumn('items', 'once_price')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropColumn('once_price');
            });
        }
        
        //Sale
        if (Schema::hasColumn('sales', 'is_once_down')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('is_once_down');
            });
        }
        
        //SendMailFlag
        if (Schema::hasColumn('send_mail_flags', 'add_point')) {
            Schema::table('send_mail_flags', function (Blueprint $table) {
                $table->dropColumn('add_point');
            });
        }
        
        //PostRelation
        if (Schema::hasColumn('post_relations', 'relate_post_ids')) {
            Schema::table('post_relations', function (Blueprint $table) {
                $table->dropColumn('relate_post_ids');
            });
        }
    }
}

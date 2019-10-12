<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_categories', function (Blueprint $table) {
            $table->increments('id');
            
            $table->string('name')->nullable()->default(NULL);
            $table->string('link_name')->nullable()->default(NULL);
            $table->string('slug')->unique()->nullable()->default(NULL);
            
            $table->boolean('is_top')->nullable()->default(0);
            $table->string('postcate_img_path')->nullable()->default(NULL);
            $table->string('postcate_title')->nullable()->default(NULL);
            $table->text('postcate_text')->nullable()->default(NULL);
            
            $table->string('meta_title')->nullable()->default(NULL);
            $table->text('meta_description')->nullable()->default(NULL);
            $table->string('meta_keyword')->nullable()->default(NULL);
            
            $table->longText('contents')->nullable()->default(NULL);
            
            $table->integer('view_count')->nullable()->default(0);
            $table->integer('sort_num')->nullable()->default(NULL);
        
            $table->timestamps();
        });
        
        
        DB::table('post_categories')->insert([
            'name' => 'インフォメーション',
            'link_name' => 'インフォメーション',
            'slug' => 'information',
            'created_at' => date('Y-m-d H:i:s', time()),
            'updated_at' => date('Y-m-d H:i:s', time()),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('post_categories');
    }
}

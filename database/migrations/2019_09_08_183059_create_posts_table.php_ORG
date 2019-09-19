<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('rel_id');
            
            $table->string('block')->nullable()->default(NULL);
            $table->string('img_path')->nullable()->default(NULL);
            $table->string('url')->nullable()->default(NULL);
            $table->string('title')->nullable()->default(NULL);
            $table->longText('detail')->nullable()->default(NULL); //textにするかどうするか
            
            $table->integer('sort_num')->nullable()->default(NULL);
            $table->boolean('is_section')->nullable()->default(NULL);
            
            $table->integer('mid_title_id')->nullable()->default(NULL);
            
            //$table->boolean('is_mid_section')->nullable()->default(NULL);
            
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
        Schema::dropIfExists('posts');
    }
}

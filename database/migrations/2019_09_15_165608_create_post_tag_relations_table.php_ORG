<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostTagRelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_tag_relations', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('postrel_id')->nullable()->default(NULL);
            $table->integer('tag_id')->nullable()->default(NULL);
            $table->integer('sort_num')->nullable()->default(NULL);
            
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
        Schema::dropIfExists('post_tag_relations');
    }
}

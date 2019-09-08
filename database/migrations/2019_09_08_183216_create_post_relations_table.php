<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostRelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_relations', function (Blueprint $table) {
            $table->increments('id');
            
            //$table->integer('parent_id')->nullable()->default(NULL);
            $table->integer('cate_id')->nullable()->default(NULL);
            $table->string('type_code')->nullable()->default(NULL);
            
            $table->boolean('open_status')->nullable()->default(0);
            $table->boolean('is_index')->nullable()->default(0);
            
            
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
        Schema::dropIfExists('post_relations');
    }
}

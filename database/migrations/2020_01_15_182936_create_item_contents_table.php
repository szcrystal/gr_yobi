<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_contents', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('item_id')->nullable()->default(NULL);
            
            $table->text('exp_first')->nullable()->default(NULL);
            $table->longText('explain')->nullable()->default(NULL);
            
            $table->longText('about_ship')->nullable()->default(NULL);
            
            $table->longText('contents')->nullable()->default(NULL);
            
            $table->longText('caution')->nullable()->default(NULL);
            
            $table->longText('free_space')->nullable()->default(NULL);
            
            $table->string('meta_title')->nullable()->default(NULL);
            $table->text('meta_description')->nullable()->default(NULL);
            $table->string('meta_keyword')->nullable()->default(NULL);
            
            $table->string('upper_title')->nullable()->default(NULL);
            $table->text('upper_text')->nullable()->default(NULL);
            
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
        Schema::dropIfExists('item_contents');
    }
}

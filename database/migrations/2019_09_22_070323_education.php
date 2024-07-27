<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("education", function(Blueprint $table){
            $table->id();
            $table->bigInteger("user_id");
            $table->string("level")->nullable();
            $table->string("institution")->nullable();
            $table->string("certification")->nullable();
            $table->date("from")->nullable();
            $table->date("to")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("education");
    }
};

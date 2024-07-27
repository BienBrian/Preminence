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
        Schema::create("church", function(Blueprint $table){
            $table->id();
            $table->bigInteger("user_id");
            $table->bigInteger("communities")->nullable();
            $table->bigInteger("departments")->nullable();
            $table->integer("joined");
            $table->text("gifts")->nullable();
            $table->string("previous_church")->nullable();
            $table->string("position")->nullable();
            $table->text("remarks")->nullable();
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
        Schema::dropIfExists("church");
    }
};

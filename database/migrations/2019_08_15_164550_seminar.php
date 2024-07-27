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
        Schema::create("seminars", function(Blueprint $table){
            $table->id();
            $table->string("title");
            $table->text("description");
            $table->string("theme");
            $table->datetime("start");
            $table->datetime("end");
            $table->string("location");
            $table->double("cost");
            $table->string("entry");
            $table->string("banner");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seminars');
    }
};

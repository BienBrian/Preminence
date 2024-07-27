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
        Schema::create("people", function(Blueprint $table){
            $table->id();
            $table->string("name");
            $table->text("description")->nullable();
            $table->integer("leader")->nullable();
            $table->string("banner")->nullable();
            $table->integer("user_group");
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
        Schema::dropIfExists("people");
    }
};

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
        Schema::create("groups", function(Blueprint $table){
            $table->id();
            $table->integer("activity");
            $table->string("name");
            $table->double("amount");
            $table->double("paid")->nullable()->default('0');
            $table->integer("status");
            $table->integer("creator");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('groups');
    }
};

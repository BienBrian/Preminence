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
        Schema::create("pledges", function(Blueprint $table){
            $table->id();
            $table->integer("activity");
            $table->integer("groups");
            $table->integer("user_id");
            $table->double("paid");
            $table->double("amount");
            $table->integer("status");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("pledges");
    }
};

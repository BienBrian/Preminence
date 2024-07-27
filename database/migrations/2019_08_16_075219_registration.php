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
        Schema::create("registration", function(Blueprint $table){
            $table->id();
            $table->bigInteger("user_id");
            $table->bigInteger("event_id");
            $table->bigInteger("event_type");
            $table->string("report");
            $table->integer("status");
            $table->date("rdate");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('registration');
    }
};

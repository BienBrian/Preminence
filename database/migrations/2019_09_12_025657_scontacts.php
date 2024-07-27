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
        Schema::create("scontacts", function(Blueprint $table){
            $table->id();
            $table->bigInteger("user_id");
            $table->text("town")->nullable();
            $table->string("secondary_phone")->nullable();
            $table->text("address")->nullable();
            $table->text("resident")->nullable();
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
        Schema::dropIfExists("scontacts");
    }
};

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
        Schema::create("contacts", function(Blueprint $table){
            $table->id();
            $table->bigInteger("user_id");
            $table->datetime("baptism")->nullable();
            $table->string("phone");
            $table->string("city");
            $table->string("country");
            $table->string("gender");
            $table->string("dob");
            $table->string("facebook")->nullable();
            $table->string("whatsapp")->nullable();
            $table->string("instagram")->nullable();
            $table->string("twitter")->nullable();
            $table->string("gmail")->nullable();
            $table->string("youtube")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("contacts");
    }
};

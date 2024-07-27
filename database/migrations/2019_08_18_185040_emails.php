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
        Schema::create("emails", function(Blueprint $table){
            $table->id();
            $table->bigInteger("user_id");
            $table->string("subject")->nullable();
            $table->text("message");
            $table->datetime("sent");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("emails");
    }
};

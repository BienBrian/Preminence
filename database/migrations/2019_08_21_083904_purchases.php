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
        Schema::create("purchases", function(Blueprint $table){
            $table->id();
            $table->bigInteger("product_id");
            $table->bigInteger("user_id");
            $table->double("price");
            $table->integer("items");
            $table->double("amount");
            $table->integer("status");
            $table->datetime("date_bought");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchases');
    }
};

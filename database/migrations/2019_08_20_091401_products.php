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
        Schema::create("products", function(Blueprint $table){
            $table->id();
            $table->string("image");
            $table->string("name");
            $table->double("price");
            $table->integer("category")->nullable();
            $table->integer("items");
            $table->integer("available");
            $table->text("description");
            $table->datetime("date_posted");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("products");
    }
};

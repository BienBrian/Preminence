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
        Schema::create("budget_items", function(Blueprint $table){
            $table->id();
            $table->bigInteger("budget_id");
            $table->double("amount");
            $table->bigInteger("source");
            $table->integer("choice");
            $table->datetime("from")->nulluble();
            $table->datetime("to")->nullable();
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
        Schema::dropIfExists("budget_items");
    }
};

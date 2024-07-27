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
        Schema::create("attendance", function(Blueprint $table){
            $table->id();
            $table->integer("attendance_group");
            $table->integer("attended_for");
            $table->integer("attendance_id");
            $table->integer("attendance");
            $table->datetime("day");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("attendance");
    }
};

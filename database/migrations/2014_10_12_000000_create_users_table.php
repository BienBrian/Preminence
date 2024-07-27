<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstname');
            $table->string('lastname');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone')->nullable();
            $table->string('password');
            $table->integer('status');
            $table->integer('role');
            $table->rememberToken();
            $table->timestamps();
        });

        // Insert some stuff
        \DB::table('users')->insert(
            array(
                'firstname' => 'James',
                'lastname' => 'Githiora',
                'email' => 'jaygithiora@gmail.com',
                'password' => \Hash::make("12345"),
                'status' =>1,
                'role'=>1,
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};

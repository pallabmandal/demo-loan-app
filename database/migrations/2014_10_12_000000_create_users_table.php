<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
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

            $table->string('first_name');
            $table->string('last_name');
            $table->string('image')->default('user/profile/default.png');
            $table->date('dob')->unique();
            $table->string('gender')->index();

            $table->unsignedBigInteger('role_id');
            $table->foreign('role_id')->references('id')->on('roles');

            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();

            $table->integer('country_code');

            $table->string('phone')->unique();
            $table->timestamp('phone_verified_at')->nullable();

            $table->string('password')->nullable();

            $table->rememberToken();
            
            $table->dateTime('last_login')->nullable();

            $table->integer('status')->default('1');
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
        Schema::dropIfExists('users');
    }
}

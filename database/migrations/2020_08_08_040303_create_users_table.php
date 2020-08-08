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
            $table->string('full_name',50);
            $table->enum('type', ['user', 'admin']);
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone', 100)->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('sex')->nullable();
            $table->string('api_token');
            $table->integer('id_user_card')->nullable();
            $table->string('authorization_ref')->nullable();
            $table->double('wallet')->default(0.00);
            $table->rememberToken();
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

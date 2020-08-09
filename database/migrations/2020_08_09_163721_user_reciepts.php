<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserReciepts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('user_reciepts', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('reciept_code')->nullable();
            $table->string('description')->nullable();
            $table->double('amount')->nullable();
            $table->string('bank')->nullable();
            $table->integer('account')->nullable();
            $table->string('currency')->nullable();
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
        //
    }
}

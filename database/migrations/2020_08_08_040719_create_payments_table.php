<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('reference');
            $table->string('access_code');
            $table->string('transaction_date')->nullable();
            $table->integer('via');
            $table->double('amount');
            $table->string('authorization_url');
            $table->string('card_number')->nullable();
            $table->string('status')->nullable();
            $table->text('log')->nullable();
            $table->string('domain')->nullable();
            $table->string('bank')->nullable();
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
        Schema::dropIfExists('payments');
    }
}

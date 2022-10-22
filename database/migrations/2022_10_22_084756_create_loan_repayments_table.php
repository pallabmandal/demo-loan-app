<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanRepaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedBigInteger('loan_id');
            $table->foreign('loan_id')->references('id')->on('loans');

            $table->integer('week');
            $table->double('repay_amount', 8,2);

            $table->date('repay_date');
            $table->date('paid_on')->nullable();

            $table->double('total', 8,2);
            $table->double('paid', 8,2);
            $table->double('balance', 8,2);

            $table->integer('status')->default('1');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_repayments');
    }
}

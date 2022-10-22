<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanRepaymentPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_repayment_payments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedBigInteger('loan_id');
            $table->foreign('loan_id')->references('id')->on('loans');

            $table->string('payment_amount');
            $table->string('payment_currency');
            $table->string('payment_id')->nullable();
            $table->string('payment_session_id')->nullable();
            $table->string('payment_status')->nullable();
            
            $table->date('payment_date')->nullable();

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
        Schema::dropIfExists('loan_repayment_payments');
    }
}

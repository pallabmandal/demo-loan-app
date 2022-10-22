<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanRepaymentPaymentDetialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_repayment_payment_detials', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('loan_id');
            $table->foreign('loan_id')->references('id')->on('loans');

            $table->unsignedBigInteger('loan_repayment_id');
            $table->foreign('loan_repayment_id')->references('id')->on('loan_repayments');

            $table->unsignedBigInteger('loan_repayment_payment_id');
            $table->foreign('loan_repayment_payment_id')->references('id')->on('loan_repayment_payments');

            $table->double('total', 8,2);
            $table->double('due', 8,2);
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
        Schema::dropIfExists('loan_repayment_payment_detials');
    }
}

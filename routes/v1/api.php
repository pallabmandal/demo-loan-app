<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'v1'], function () {
	Route::prefix('auth')->group(function () {
	   Route::post('login', [App\Http\Controllers\v1\Auth\AuthController::class, 'login']);
	   Route::post('register', [App\Http\Controllers\v1\Auth\AuthController::class, 'register']);

	   Route::prefix('password')->group(function () {
	   		Route::post('create', [App\Http\Controllers\v1\Auth\ResetPasswordController::class, 'create']);
			Route::post('reset', [App\Http\Controllers\v1\Auth\ResetPasswordController::class, 'reset']);
	   });

	});

	Route::prefix('borrower')->group(function () {
		Route::middleware(['jwtclienttoken'])->group(function () {
			Route::prefix('loans')->group(function () {
				Route::post('get', [App\Http\Controllers\v1\Borrower\Loan\LoanController::class, 'index']);
				Route::post('show', [App\Http\Controllers\v1\Borrower\Loan\LoanController::class, 'show']);
				Route::post('create', [App\Http\Controllers\v1\Borrower\Loan\LoanController::class, 'create']);
				Route::post('repay', [App\Http\Controllers\v1\Borrower\Loan\LoanController::class, 'repay']);
			});
		});
	});
	Route::prefix('admin')->group(function () {
		Route::middleware(['jwtadmintoken'])->group(function () {
			Route::prefix('loans')->group(function () {
				Route::post('update', [App\Http\Controllers\v1\Admin\Loan\LoanController::class, 'updateLoanStatus']);
				Route::post('get', [App\Http\Controllers\v1\Admin\Loan\LoanController::class, 'index']);
				Route::post('show', [App\Http\Controllers\v1\Admin\Loan\LoanController::class, 'show']);
			});
		});
	});

});
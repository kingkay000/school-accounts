<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\ExceptionController;
use App\Http\Controllers\Api\V1\MatchingController;
use App\Http\Controllers\Api\V1\ReceiptVerificationController;
use App\Http\Controllers\Api\V1\TaxController;
use App\Http\Controllers\Api\V1\ValidationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('/v1')->group(function () {
    Route::post('/receipt-webhook', [ReceiptVerificationController::class, 'webhook']);
    Route::post('/bank-webhook', [\App\Http\Controllers\Api\V1\BankTransactionController::class, 'store']);

    Route::post('/documents', [DocumentController::class, 'store']);
    Route::post('/documents/{attachment}/classify', [DocumentController::class, 'classify']);
    Route::post('/matching/confirm', [MatchingController::class, 'confirm']);
    Route::post('/transactions/{transaction}/validate', [ValidationController::class, 'validateTransaction']);
    Route::post('/transactions/{transaction}/tax-assess', [TaxController::class, 'assess']);
    Route::get('/exceptions', [ExceptionController::class, 'index']);
    Route::post('/exceptions/{exception}/resolve', [ExceptionController::class, 'resolve']);
});

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
 use App\Http\Controllers\Api\{
    AccountController,
    CategoryController,
    TransactionEntryController,
    ProductController,
    TransactionItemController,
    CompanyController,
    CustomerController,
    StockLevelController,
    StockMovementController,
    DebtController,
    AccountUserController,
    MessageController,
    ConversationController
};

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('accounts', AccountController::class);
Route::apiResource('categories', CategoryController::class);
Route::apiResource('transactions', TransactionEntryController::class);
Route::apiResource('products', ProductController::class);
Route::apiResource('transaction-items', TransactionItemController::class);
Route::apiResource('companies', CompanyController::class);
Route::apiResource('customers', CustomerController::class);
Route::apiResource('stock-levels', StockLevelController::class);
Route::apiResource('stock-movements', StockMovementController::class);
Route::apiResource('debts', DebtController::class);
Route::apiResource('account-users', AccountUserController::class);
Route::apiResource('massages', MessageController::class);
Route::apiResource('conversations', ConversationController::class);


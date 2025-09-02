<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AccountController, CategoryController, TransactionEntryController, ProductController,
    TransactionItemController, CompanyController, CustomerController, StockLevelController,
    StockMovementController, DebtController, AccountUserController, MessageController, ConversationController, AuthController
};

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class,'register']);
    Route::post('login', [AuthController::class,'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class,'logout']);
        Route::get('me', [AuthController::class,'me']);
        Route::post('update-password', [AuthController::class,'updatePassword']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('accounts/export', [AccountController::class, 'export']);
    Route::post('accounts/import', [AccountController::class, 'import']);
    Route::post('accounts/bulk',   [AccountController::class, 'bulkUpsert']);
    Route::post('accounts/{account}/restore', [AccountController::class, 'restore']);
    Route::apiResource('accounts', AccountController::class);

    Route::get('categories/export', [CategoryController::class, 'export']);
    Route::post('categories/import', [CategoryController::class, 'import']);
    Route::post('categories/bulk',   [CategoryController::class, 'bulkUpsert']);
    Route::post('categories/{category}/restore', [CategoryController::class, 'restore']);
    Route::apiResource('categories', CategoryController::class);

    Route::get('products/export', [ProductController::class, 'export']);
    Route::post('products/import', [ProductController::class, 'import']);
    Route::post('products/bulk',   [ProductController::class, 'bulkUpsert']);
    Route::post('products/{product}/restore', [ProductController::class, 'restore']);
    Route::apiResource('products', ProductController::class);

    Route::get('companies/export', [CompanyController::class, 'export']);
    Route::post('companies/import', [CompanyController::class, 'import']);
    Route::post('companies/bulk',   [CompanyController::class, 'bulkUpsert']);
    Route::post('companies/{company}/restore', [CompanyController::class, 'restore']);
    Route::apiResource('companies', CompanyController::class);

    Route::get('customers/export', [CustomerController::class, 'export']);
    Route::post('customers/import', [CustomerController::class, 'import']);
    Route::post('customers/bulk',   [CustomerController::class, 'bulkUpsert']);
    Route::post('customers/{customer}/restore', [CustomerController::class, 'restore']);
    Route::apiResource('customers', CustomerController::class);

    Route::get('transaction-entries/export', [TransactionEntryController::class, 'export']);
    Route::post('transaction-entries/import', [TransactionEntryController::class, 'import']);
    Route::post('transaction-entries/bulk',   [TransactionEntryController::class, 'bulkUpsert']);
    Route::post('transaction-entries/{transaction_entry}/restore', [TransactionEntryController::class, 'restore']);
    Route::apiResource('transaction-entries', TransactionEntryController::class);

    Route::get('transaction-items/export', [TransactionItemController::class, 'export']);
    Route::post('transaction-items/import', [TransactionItemController::class, 'import']);
    Route::post('transaction-items/bulk',   [TransactionItemController::class, 'bulkUpsert']);
    Route::post('transaction-items/{transaction_item}/restore', [TransactionItemController::class, 'restore']);
    Route::apiResource('transaction-items', TransactionItemController::class);

    Route::get('stock-levels/export', [StockLevelController::class, 'export']);
    Route::post('stock-levels/import', [StockLevelController::class, 'import']);
    Route::post('stock-levels/{stock_level}/restore', [StockLevelController::class, 'restore']);
    Route::apiResource('stock-levels', StockLevelController::class);
    Route::delete('stock-levels/{stockLevel?}', [StockLevelController::class, 'destroy']);
    Route::post('stock-levels/bulk-upsert', [StockLevelController::class, 'bulkUpsert']);
    Route::post('stock-levels/bulk',        [StockLevelController::class, 'bulkUpsert']);

    Route::get('stock-movements/export', [StockMovementController::class, 'export']);
    Route::post('stock-movements/import', [StockMovementController::class, 'import']);
    Route::post('stock-movements/bulk',   [StockMovementController::class, 'bulkUpsert']);
    Route::post('stock-movements/{stock_movement}/restore', [StockMovementController::class, 'restore']);
    Route::apiResource('stock-movements', StockMovementController::class);

    Route::get('debts/export', [DebtController::class, 'export']);
    Route::post('debts/import', [DebtController::class, 'import']);
    Route::post('debts/bulk',   [DebtController::class, 'bulkUpsert']);
    Route::post('debts/{debt}/restore', [DebtController::class, 'restore']);
    Route::apiResource('debts', DebtController::class);

    Route::get('account-users/export', [AccountUserController::class, 'export']);
    Route::post('account-users/import', [AccountUserController::class, 'import']);
    Route::post('account-users/bulk',   [AccountUserController::class, 'bulkUpsert']);
    Route::post('account-users/{account_user}/restore', [AccountUserController::class, 'restore']);
    Route::apiResource('account-users', AccountUserController::class);

    Route::get('messages/export', [MessageController::class, 'export']);
    Route::post('messages/import', [MessageController::class, 'import']);
    Route::post('messages/bulk',   [MessageController::class, 'bulkUpsert']);
    Route::post('messages/{message}/restore', [MessageController::class, 'restore']);
    Route::apiResource('messages', MessageController::class);

    Route::get('conversations/export', [ConversationController::class, 'export']);
    Route::post('conversations/import', [ConversationController::class, 'import']);
    Route::post('conversations/bulk',   [ConversationController::class, 'bulkUpsert']);
    Route::post('conversations/{conversation}/restore', [ConversationController::class, 'restore']);
    Route::apiResource('conversations', ConversationController::class);
});

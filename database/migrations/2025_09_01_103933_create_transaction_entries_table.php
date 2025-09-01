<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transaction_entry', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('remoteId')->nullable();
            $table->string('localId')->nullable();
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->integer('amount')->default(0);
            $table->string('typeEntry')->default('DEBIT');
            $table->string('dateTransaction')->nullable();
            $table->string('status')->nullable();
            $table->string('entityName')->nullable();
            $table->string('entityId')->nullable();
            $table->string('accountId')->nullable();
            $table->string('categoryId')->nullable();
            $table->string('companyId')->nullable();
            $table->string('customerId')->nullable();
            $table->string('debtId')->nullable();
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent();
            $table->timestamp('deletedAt')->nullable();
            $table->timestamp('syncAt')->nullable();
            $table->integer('version')->default(0);
            $table->string('createdBy')->nullable();
            $table->boolean('isDirty')->default(true);

            $table->index('dateTransaction', 'idx_txn_date');
            $table->index('accountId', 'idx_txn_account');
            $table->index('categoryId', 'idx_txn_category');
            $table->index('companyId', 'idx_txn_company');
            $table->index('customerId', 'idx_txn_customer');
            $table->index('isDirty', 'idx_txn_dirty');
            $table->index('deletedAt', 'idx_txn_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_entry');
    }
};

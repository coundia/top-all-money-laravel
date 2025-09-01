<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transaction_item', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('transactionId')->nullable();
            $table->string('productId')->nullable();
            $table->string('remoteId')->nullable();
            $table->string('localId')->nullable();
            $table->string('label')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('unitId')->nullable();
            $table->integer('unitPrice')->nullable();
            $table->integer('total')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent();
            $table->timestamp('deletedAt')->nullable();
            $table->string('account')->nullable();
            $table->timestamp('syncAt')->nullable();
            $table->string('code')->nullable();
            $table->string('createdBy')->nullable();
            $table->integer('version')->default(0);
            $table->boolean('isDirty')->default(true);

            $table->index('transactionId', 'idx_item_txn');
            $table->index('productId', 'idx_item_product');
            $table->index('unitId', 'idx_item_unit');
            $table->index('isDirty', 'idx_item_dirty');
            $table->index('deletedAt', 'idx_item_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_item');
    }
};

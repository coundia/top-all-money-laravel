<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('remoteId')->nullable();
            $table->string('localId')->nullable();
            $table->string('code')->nullable();
            $table->string('account')->nullable();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('barcode')->nullable();
            $table->string('unitId')->nullable();
            $table->string('categoryId')->nullable();
            $table->integer('defaultPrice')->default(0);
            $table->text('statuses')->nullable();
            $table->integer('purchasePrice')->default(0);
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent();
            $table->timestamp('deletedAt')->nullable();
            $table->timestamp('syncAt')->nullable();
            $table->string('createdBy')->nullable();
            $table->integer('version')->default(0);
            $table->boolean('isDirty')->default(true);

            $table->index('barcode', 'idx_product_barcode');
            $table->index('unitId', 'idx_product_unit');
            $table->index('categoryId', 'idx_product_category');
            $table->index('isDirty', 'idx_product_dirty');
            $table->index('deletedAt', 'idx_product_deleted');
        });

        DB::statement('CREATE INDEX IF NOT EXISTS uq_product_code_active ON product(code) WHERE deletedAt IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('product');
    }
};

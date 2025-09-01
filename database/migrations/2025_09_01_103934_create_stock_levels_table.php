<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_level', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('remoteId')->nullable();
            $table->string('code')->nullable();
            $table->string('localId')->nullable();

            $table->integer('stockOnHand')->nullable();
            $table->integer('stockAllocated')->nullable();

            $table->uuid('productVariantId');
            $table->uuid('companyId');

            $table->timestamp('syncAt')->nullable();
            $table->timestamp('deletedAt')->nullable();

            $table->integer('version')->default(0);
            $table->string('account')->nullable();
            $table->boolean('isDirty')->default(true);
            $table->uuid('createdBy')->nullable();

            // camelCase timestamps used by the model
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent();

            // FKs — use conventional plural table names
            $table->foreign('productVariantId')
                ->references('id')->on('product')
                ->onDelete('cascade');

            $table->foreign('companyId')
                ->references('id')->on('company')
                ->onDelete('cascade');

            $table->index(['productVariantId','companyId'], 'IDX_stocklevel_prod_company');
            $table->index('companyId', 'IDX_stocklevel_company');
            $table->index('productVariantId', 'IDX_stocklevel_product');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_level');
    }
};

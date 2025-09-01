<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_movement', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type_stock_movement')->nullable();
            $table->string('code')->nullable();
            $table->string('remoteId')->nullable();
            $table->string('localId')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('companyId')->nullable();
            $table->string('productVariantId')->nullable();
            $table->string('orderLineId')->nullable();
            $table->string('discriminator')->nullable();
            $table->string('account')->nullable();
            $table->timestamp('syncAt')->nullable();
            $table->integer('version')->default(0);
            $table->boolean('isDirty')->default(true);
            $table->string('createdBy')->nullable();
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent();

            $table->index('companyId', 'IDX_stockmove_company');
            $table->index('productVariantId', 'IDX_stockmove_product');
            $table->index('orderLineId', 'IDX_stockmove_orderline');
            $table->index('type_stock_movement', 'IDX_stockmove_type');
            $table->index('createdAt', 'IDX_stockmove_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movement');
    }
};

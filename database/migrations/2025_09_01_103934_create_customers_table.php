<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('remoteId')->nullable();
            $table->string('localId')->nullable();
            $table->string('code')->nullable();
            $table->string('firstName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('fullName')->nullable();
            $table->integer('balance')->default(0);
            $table->integer('balanceDebt')->default(0);
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->nullable();
            $table->string('companyId')->nullable();
            $table->string('addressLine1')->nullable();
            $table->string('addressLine2')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('country')->nullable();
            $table->string('postalCode')->nullable();
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent();
            $table->timestamp('deletedAt')->nullable();
            $table->timestamp('syncAt')->nullable();
            $table->string('createdBy')->nullable();
            $table->string('account')->nullable();
            $table->integer('version')->default(0);
            $table->boolean('isDirty')->default(true);

            $table->index('fullName', 'idx_customer_fullname');
            $table->index('companyId', 'idx_customer_company');
            $table->index('deletedAt', 'idx_customer_deleted');
            $table->index('isDirty', 'idx_customer_dirty');
        });

        DB::statement('CREATE INDEX IF NOT EXISTS uq_customer_code_active ON customer(code) WHERE deletedAt IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('customer');
    }
};

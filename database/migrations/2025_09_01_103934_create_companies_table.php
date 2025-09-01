<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('company', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('remoteId')->nullable();
            $table->string('localId')->nullable();
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('taxId')->nullable();
            $table->string('currency')->nullable();
            $table->string('address')->nullable();
            $table->string('addressLine2')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('country')->nullable();
            $table->string('postalCode')->nullable();
            $table->boolean('isDefault')->default(false);
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent();
            $table->timestamp('deletedAt')->nullable();
            $table->timestamp('syncAt')->nullable();
            $table->string('createdBy')->nullable();
            $table->integer('version')->default(0);
            $table->boolean('isDirty')->default(true);

            $table->index('name', 'idx_company_name');
            $table->index('phone', 'idx_company_phone');
            $table->index('email', 'idx_company_email');
            $table->index('deletedAt', 'idx_company_deleted');
            $table->index('isDirty', 'idx_company_dirty');
        });

        DB::statement('CREATE INDEX IF NOT EXISTS uq_company_code_active ON company(code) WHERE deletedAt IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('company');
    }
};

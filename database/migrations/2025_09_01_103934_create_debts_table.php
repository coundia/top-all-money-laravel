<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    public function up(): void
    {
        Schema::create('debt', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('remoteId')->nullable();
            $table->string('localId')->nullable();
            $table->string('code')->nullable();
            $table->text('notes')->nullable();
            $table->integer('balance')->default(0);
            $table->integer('balanceDebt')->default(0);
            $table->string('dueDate')->nullable();
            $table->text('statuses')->nullable();
            $table->string('account')->nullable();
            $table->string('customerId')->nullable();
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent();
            $table->timestamp('deletedAt')->nullable();
            $table->timestamp('syncAt')->nullable();
            $table->string('createdBy')->nullable();
            $table->integer('version')->default(0);
            $table->boolean('isDirty')->default(true);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('debt');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('account', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('remoteId')->nullable();
            $table->string('localId')->nullable();
            $table->integer('balance')->default(0);
            $table->integer('balance_prev')->default(0);
            $table->integer('balance_blocked')->default(0);
            $table->integer('balance_init')->default(0);
            $table->integer('balance_goal')->default(0);
            $table->integer('balance_limit')->default(0);
            $table->string('dateStartAccount')->nullable();
            $table->string('dateEndAccount')->nullable();
            $table->string('typeAccount')->nullable();
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->nullable();
            $table->string('currency')->nullable();
            $table->boolean('isDefault')->default(false);
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent();
            $table->timestamp('deletedAt')->nullable();
            $table->timestamp('syncAt')->nullable();
            $table->integer('version')->default(0);
            $table->boolean('isShared')->default(false);
            $table->string('createdBy')->nullable();
            $table->boolean('isDirty')->default(true);

            $table->index('code', 'idx_account_code');
            $table->index('isDirty', 'idx_account_dirty');
            $table->index('deletedAt', 'idx_account_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account');
    }
};

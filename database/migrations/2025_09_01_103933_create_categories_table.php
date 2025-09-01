<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('category', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('remoteId')->nullable();
            $table->string('localId')->nullable();
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->string('typeEntry')->default('DEBIT');
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent();
            $table->timestamp('deletedAt')->nullable();
            $table->timestamp('syncAt')->nullable();
            $table->boolean('isShared')->default(false);
            $table->string('createdBy')->nullable();
            $table->string('account')->nullable();
            $table->integer('version')->default(0);
            $table->boolean('isDirty')->default(true);

            $table->index('code', 'idx_category_code');
            $table->index('isDirty', 'idx_category_dirty');
            $table->index('deletedAt', 'idx_category_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category');
    }
};

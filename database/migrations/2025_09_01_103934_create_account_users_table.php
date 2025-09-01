<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('account_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->nullable();
            $table->string('account')->nullable();
            $table->string('user')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('identify')->nullable();
            $table->string('role')->nullable();
            $table->string('status')->nullable();
            $table->string('invitedBy')->nullable();
            $table->timestamp('invitedAt')->useCurrent();
            $table->timestamp('acceptedAt')->nullable();
            $table->timestamp('revokedAt')->nullable();
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent();
            $table->timestamp('deletedAt')->nullable();
            $table->timestamp('syncAt')->nullable();
            $table->integer('version')->default(0);
            $table->boolean('isDirty')->default(true);
            $table->string('remoteId')->nullable();
            $table->string('createdBy')->nullable();
            $table->string('localId')->nullable();

            $table->index('account', 'idx_accusers_account');
            $table->index('user', 'idx_accusers_user');
            $table->index('status', 'idx_accusers_status');
            $table->index('updatedAt', 'idx_accusers_updated');
            $table->index('isDirty', 'idx_accusers_dirty');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_users');
    }
};

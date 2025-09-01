<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conversation', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title')->nullable();
            $table->string('createdBy')->nullable();
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent();
            $table->string('remoteId')->nullable();
            $table->string('localId')->nullable();
            $table->timestamp('syncAt')->nullable();
            $table->integer('version')->default(0);
            $table->boolean('isDirty')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation');
    }
};

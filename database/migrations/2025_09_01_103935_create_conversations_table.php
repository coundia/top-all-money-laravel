<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title')->nullable();
            $table->string('status')->nullable();
            $table->string('createdBy')->nullable();
            $table->string('remoteId')->nullable();
            $table->string('localId')->nullable();
            $table->timestamp('syncAt')->nullable();
            $table->integer('version')->default(0);
            $table->boolean('isDirty')->default(true);
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};

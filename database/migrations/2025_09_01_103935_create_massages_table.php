<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('content')->nullable();
            $table->text('sender')->nullable();
            $table->text('status')->nullable();
            $table->string('createdBy')->nullable();
            $table->string('conversation_id')->nullable();
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
        Schema::dropIfExists('messages');
    }
};

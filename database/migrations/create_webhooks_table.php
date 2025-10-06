<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained()->cascadeOnDelete();
            $table->string('node_id');
            $table->uuid('webhook_id')->unique();
            $table->string('path')->unique();
            $table->enum('method', ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])->default('POST');
            $table->boolean('active')->default(true);
            $table->json('response_mode')->nullable();  // immediate, wait, last_node
            $table->integer('request_count')->default(0);
            $table->timestamp('last_called_at')->nullable();
            $table->timestamps();
            
            $table->index(['webhook_id', 'active']);
            $table->index('path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhooks');
    }
};
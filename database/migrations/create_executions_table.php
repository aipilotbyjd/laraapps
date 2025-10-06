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
        Schema::create('executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['waiting', 'running', 'success', 'failed', 'cancelled'])->default('waiting');
            $table->enum('mode', ['manual', 'trigger', 'webhook', 'schedule', 'retry'])->default('manual');
            $table->json('input_data')->nullable();
            $table->json('output_data')->nullable();
            $table->text('error_message')->nullable();
            $table->json('error_stack')->nullable();
            $table->integer('retry_count')->default(0);
            $table->foreignId('parent_execution_id')->nullable();  // For retries
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('waiting_till')->nullable();  // For wait nodes
            $table->integer('duration_ms')->nullable();
            $table->timestamps();
            
            $table->index(['workflow_id', 'status', 'created_at']);
            $table->index(['status', 'waiting_till']);
            $table->index('mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('executions');
    }
};
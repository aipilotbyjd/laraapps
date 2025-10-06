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
        Schema::create('waiting_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('execution_id')->constrained()->cascadeOnDelete();
            $table->string('node_id');
            $table->enum('wait_type', ['time', 'webhook', 'condition'])->default('time');
            $table->timestamp('resume_at')->nullable();
            $table->json('resume_data')->nullable();
            $table->json('context_data');  // Store execution state
            $table->timestamps();
            
            $table->index(['wait_type', 'resume_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waiting_executions');
    }
};
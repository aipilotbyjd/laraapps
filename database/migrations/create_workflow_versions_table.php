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
        Schema::create('workflow_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained()->cascadeOnDelete();
            $table->integer('version_number');
            $table->json('nodes');
            $table->json('connections');
            $table->json('settings')->nullable();
            $table->string('created_by')->nullable();
            $table->text('change_notes')->nullable();
            $table->timestamps();
            
            $table->unique(['workflow_id', 'version_number']);
            $table->index('workflow_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_versions');
    }
};
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
        Schema::create('bulk_id_card_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_id')->unique(); // Unique job identifier
            $table->string('title'); // Job title/description
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->integer('total_records')->default(0);
            $table->integer('processed_records')->default(0);
            $table->integer('failed_records')->default(0);
            $table->decimal('progress_percentage', 5, 2)->default(0);
            
            // Generation criteria
            $table->enum('generation_type', ['all', 'facility', 'workplace', 'custom_selection', 'status']);
            $table->json('generation_criteria')->nullable(); // Store filters/criteria
            
            // File information
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->integer('file_size')->nullable(); // in bytes
            $table->timestamp('generated_at')->nullable();
            
            // User and timing
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable(); // File cleanup time
            
            // Error handling
            $table->text('error_message')->nullable();
            $table->json('failed_records_list')->nullable(); // List of failed record IDs
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['status', 'user_id']);
            $table->index('generation_type');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_id_card_jobs');
    }
};

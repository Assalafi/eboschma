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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_id')->unique(); // Auto-generated unique ticket ID
            $table->string('boschma_no')->nullable(); // Beneficiary Boschma number
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            
            // Foreign keys
            $table->foreignId('facility_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('ticket_category_id')->constrained()->onDelete('restrict');
            $table->char('assigned_to', 36)->nullable();
            $table->foreign('assigned_to')->references('id')->on('staff')->onDelete('set null');
            $table->char('created_by', 36); // UUID for staff
            $table->foreign('created_by')->references('id')->on('staff')->onDelete('cascade');
            
            // Ticket details
            $table->text('complaint');
            $table->text('description')->nullable();
            $table->string('department')->nullable();
            $table->integer('sla_hours')->default(24); // Service Level Agreement in hours
            
            // Status and priority
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            
            // Timestamps
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('due_date')->nullable(); // Based on SLA
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};

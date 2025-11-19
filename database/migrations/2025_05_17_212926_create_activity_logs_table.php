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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('user_type')->nullable(); // User or Staff
            $table->string('user_id'); // ID of the user who performed the action
            $table->string('user_email')->nullable(); // Email of the user for easy reference
            $table->string('action'); // The action performed (create, update, delete, assign)
            $table->string('module'); // Which module was affected (role, permission, staff)
            $table->string('affected_id')->nullable(); // ID of the affected entity
            $table->string('affected_name')->nullable(); // Name of the affected entity
            $table->text('details')->nullable(); // JSON details of changes
            $table->string('ip_address')->nullable(); // IP address of the user
            $table->string('user_agent')->nullable(); // Browser/client info
            $table->timestamps();
            
            // Add indexes for faster queries
            $table->index(['user_type', 'user_id']);
            $table->index('action');
            $table->index('module');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};

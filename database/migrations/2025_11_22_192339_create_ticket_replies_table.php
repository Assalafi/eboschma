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
        Schema::create('ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->char('user_id', 36);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->text('message');
            $table->string('attachment_path')->nullable(); // File upload path
            $table->string('attachment_name')->nullable(); // Original file name
            $table->enum('reply_type', ['response', 'note', 'internal'])->default('response');
            $table->boolean('is_internal')->default(false); // Internal notes vs customer replies
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_replies');
    }
};

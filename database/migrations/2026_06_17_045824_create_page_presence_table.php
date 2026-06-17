<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_presence', function (Blueprint $table) {
            $table->id();
            $table->string('guard_type', 10)->default('staff'); // 'staff' or 'web'
            $table->string('user_id', 36);
            $table->string('user_name', 255);
            $table->string('user_role', 100)->nullable();
            $table->string('user_phone', 30)->nullable();
            $table->string('page', 255)->default('/crm');
            $table->timestamp('last_seen_at');
            $table->timestamps();

            $table->unique(['user_id', 'page'], 'presence_user_page_unique');
            $table->index('last_seen_at');
            $table->index('page');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_presence');
    }
};

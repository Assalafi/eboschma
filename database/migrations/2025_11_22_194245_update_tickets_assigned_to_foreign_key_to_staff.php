<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Already handled in the initial create_tickets_table migration.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Already handled in the initial create_tickets_table migration.
    }
};

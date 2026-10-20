<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'is_active')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('email_verified_at')->index();
            });
        }

        if (!Schema::hasColumn('staff', 'is_active')) {
            Schema::table('staff', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('password')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'is_active')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }

        if (Schema::hasColumn('staff', 'is_active')) {
            Schema::table('staff', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};

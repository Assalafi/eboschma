<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beneficiary_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Seed existing categories
        DB::table('beneficiary_categories')->insert([
            ['name' => 'GL/STEP', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Civil Servant', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Organized Private Sector', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Others', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Change category column from ENUM to VARCHAR to allow dynamic values
        DB::statement("ALTER TABLE beneficiaries MODIFY COLUMN category VARCHAR(255) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE beneficiaries MODIFY COLUMN category ENUM('GL/STEP', 'Organized Private Sector', 'Others') NULL");
        Schema::dropIfExists('beneficiary_categories');
    }
};

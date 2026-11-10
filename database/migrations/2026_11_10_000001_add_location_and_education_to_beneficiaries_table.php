<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            if (!Schema::hasColumn('beneficiaries', 'ward')) {
                $table->string('ward')->nullable()->after('lga');
            }

            if (!Schema::hasColumn('beneficiaries', 'community')) {
                $table->string('community')->nullable()->after('ward');
            }

            if (!Schema::hasColumn('beneficiaries', 'educational_status')) {
                $table->string('educational_status')->nullable()->after('occupation');
            }
        });
    }

    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('beneficiaries', 'educational_status')) {
                $columns[] = 'educational_status';
            }
            if (Schema::hasColumn('beneficiaries', 'community')) {
                $columns[] = 'community';
            }
            if (Schema::hasColumn('beneficiaries', 'ward')) {
                $columns[] = 'ward';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};

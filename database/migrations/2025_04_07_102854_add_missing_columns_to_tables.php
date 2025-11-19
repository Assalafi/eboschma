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
        // Add missing columns to department_activity table
        Schema::table('department_activity', function (Blueprint $table) {
            if (!Schema::hasColumn('department_activity', 'code_id')) {
                $table->string('code_id')->nullable();
            }
            if (!Schema::hasColumn('department_activity', 'code')) {
                $table->string('code')->nullable();
            }
            if (!Schema::hasColumn('department_activity', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('department_activity', 'receipt')) {
                $table->string('receipt')->nullable();
            }
            if (!Schema::hasColumn('department_activity', 'payee')) {
                $table->string('payee')->nullable();
            }
            if (!Schema::hasColumn('department_activity', 'amount')) {
                $table->decimal('amount', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('department_activity', 'amount_type')) {
                $table->enum('amount_type', ['Dr', 'Cr'])->default('Dr');
            }
            if (!Schema::hasColumn('department_activity', 'enrollee_amount')) {
                $table->decimal('enrollee_amount', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('department_activity', 'enrollee_rate')) {
                $table->decimal('enrollee_rate', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('department_activity', 'date')) {
                $table->date('date')->nullable();
            }
            if (!Schema::hasColumn('department_activity', 'session')) {
                $table->string('session')->nullable();
            }
            if (!Schema::hasColumn('department_activity', 'sector')) {
                $table->string('sector')->default('basic');
            }
            if (!Schema::hasColumn('department_activity', 'remark')) {
                $table->text('remark')->nullable();
            }
            if (!Schema::hasColumn('department_activity', 'user')) {
                $table->string('user')->nullable();
            }
            if (!Schema::hasColumn('department_activity', 'status')) {
                $table->string('status')->default('active');
            }
        });

        // Add missing columns to departments table
        Schema::table('departments', function (Blueprint $table) {
            if (!Schema::hasColumn('departments', 'name')) {
                $table->string('name');
            }
            if (!Schema::hasColumn('departments', 'enrollee_amount')) {
                $table->decimal('enrollee_amount', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('departments', 'enrollee_rate')) {
                $table->decimal('enrollee_rate', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('departments', 'image')) {
                $table->string('image')->nullable();
            }
            if (!Schema::hasColumn('departments', 'user')) {
                $table->string('user')->nullable();
            }
            if (!Schema::hasColumn('departments', 'status')) {
                $table->string('status')->default('active');
            }
        });

        // Add missing columns to economic_codes table
        Schema::table('economic_codes', function (Blueprint $table) {
            if (!Schema::hasColumn('economic_codes', 'code')) {
                $table->string('code');
            }
            if (!Schema::hasColumn('economic_codes', 'code_id')) {
                $table->string('code_id');
            }
            if (!Schema::hasColumn('economic_codes', 'description')) {
                $table->text('description');
            }
            if (!Schema::hasColumn('economic_codes', 'level')) {
                $table->integer('level')->default(1);
            }
            if (!Schema::hasColumn('economic_codes', 'sector')) {
                $table->string('sector')->default('basic');
            }
            if (!Schema::hasColumn('economic_codes', 'user')) {
                $table->string('user')->nullable();
            }
            if (!Schema::hasColumn('economic_codes', 'status')) {
                $table->string('status')->default('active');
            }
        });

        // Add missing columns to approved_budget table
        Schema::table('approved_budget', function (Blueprint $table) {
            if (!Schema::hasColumn('approved_budget', 'code_id')) {
                $table->string('code_id');
            }
            if (!Schema::hasColumn('approved_budget', 'code')) {
                $table->string('code');
            }
            if (!Schema::hasColumn('approved_budget', 'amount')) {
                $table->decimal('amount', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('approved_budget', 'sector')) {
                $table->string('sector')->default('basic');
            }
            if (!Schema::hasColumn('approved_budget', 'session')) {
                $table->string('session')->nullable();
            }
            if (!Schema::hasColumn('approved_budget', 'user')) {
                $table->string('user')->nullable();
            }
            if (!Schema::hasColumn('approved_budget', 'status')) {
                $table->string('status')->default('active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Since we're checking for column existence in up(), 
        // we'll only drop columns that exist in down()
        
        // Remove columns from department_activity table
        Schema::table('department_activity', function (Blueprint $table) {
            $columns = ['code_id', 'code', 'description', 'receipt', 'payee',
                'amount', 'amount_type', 'enrollee_amount', 'enrollee_rate',
                'date', 'session', 'sector', 'remark', 'user', 'status'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('department_activity', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Remove columns from departments table
        Schema::table('departments', function (Blueprint $table) {
            $columns = ['name', 'enrollee_amount', 'enrollee_rate',
                'image', 'user', 'status'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('departments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Remove columns from economic_codes table
        Schema::table('economic_codes', function (Blueprint $table) {
            $columns = ['code', 'code_id', 'description', 'level',
                'sector', 'user', 'status'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('economic_codes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Remove columns from approved_budget table
        Schema::table('approved_budget', function (Blueprint $table) {
            $columns = ['code_id', 'code', 'amount', 'sector',
                'session', 'user', 'status'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('approved_budget', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

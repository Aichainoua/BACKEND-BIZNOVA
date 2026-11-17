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
        Schema::table('project_fundings', function (Blueprint $table): void {
            $table->decimal('initial_investment_total', 15, 2)->default(0);
        });

        Schema::table('project_reports', function (Blueprint $table): void {
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->char('currency', 3)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_reports', function (Blueprint $table): void {
            $table->dropColumn(['period_start', 'period_end', 'currency']);
        });

        Schema::table('project_fundings', function (Blueprint $table): void {
            $table->dropColumn('initial_investment_total');
        });
    }
};

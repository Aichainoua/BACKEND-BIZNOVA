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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sector_id');
            $table->string('name', 150);
            $table->date('launch_date');
            $table->unsignedSmallInteger('horizon_months')->default(12);
            $table->string('country', 100)->nullable();
            $table->char('currency', 3)->default('XAF');
            $table->string('activity_mode', 30);
            $table->timestamps();

            $table->foreign('sector_id')->references('id')->on('sectors')->restrictOnDelete();
        });

        Schema::create('project_fundings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('personal_contribution', 15, 2)->default(0);
            $table->decimal('non_loan_funding', 15, 2)->default(0);
            $table->decimal('loan_amount', 15, 2)->nullable();
            $table->decimal('annual_interest_rate', 7, 4)->nullable();
            $table->unsignedSmallInteger('loan_duration_months')->nullable();
            $table->string('repayment_frequency', 30)->nullable();
            $table->unsignedSmallInteger('deferment_months')->nullable();
            $table->timestamps();
        });

        Schema::create('project_activity_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('line_type', 30);
            $table->string('designation', 150);
            $table->decimal('monthly_revenue', 15, 2)->nullable();
            $table->decimal('monthly_cost_of_goods_sold', 15, 2)->nullable();
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->decimal('monthly_volume', 12, 2)->nullable();
            $table->decimal('unit_direct_cost', 15, 2)->nullable();
            $table->decimal('monthly_subscription_price', 15, 2)->nullable();
            $table->unsignedInteger('new_subscribers_per_month')->nullable();
            $table->unsignedInteger('active_subscribers')->nullable();
            $table->timestamps();
        });

        Schema::create('project_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('nature', 20);
            $table->string('label', 150);
            $table->decimal('monthly_amount', 15, 2);
            $table->unsignedSmallInteger('start_month')->nullable();
            $table->unsignedSmallInteger('end_month')->nullable();
            $table->timestamps();
        });

        Schema::create('project_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->dateTime('generated_at');
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->json('data_snapshot')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_reports');
        Schema::dropIfExists('project_expenses');
        Schema::dropIfExists('project_activity_lines');
        Schema::dropIfExists('project_fundings');
        Schema::dropIfExists('projects');
    }
};

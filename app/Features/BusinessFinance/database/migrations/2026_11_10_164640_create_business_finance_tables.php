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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sector_id');
            $table->string('name', 150);
            $table->char('currency', 3)->default('XAF');
            $table->decimal('opening_balance', 15, 2);
            $table->date('opening_balance_date');
            $table->timestamps();

            $table->foreign('sector_id')->references('id')->on('sectors')->restrictOnDelete();
        });

        Schema::create('financial_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('operation_date');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'id']);
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('label', 80);
            $table->timestamps();

            $table->unique(['company_id', 'label']);
            $table->unique(['company_id', 'id']);
        });

        Schema::create('operation_tag', function (Blueprint $table) {
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('operation_id');
            $table->foreignId('tag_id');
            $table->dateTime('associated_at');

            $table->primary(['operation_id', 'tag_id']);
            $table->foreign(['company_id', 'operation_id'])
                ->references(['company_id', 'id'])
                ->on('financial_operations')
                ->cascadeOnDelete();
            $table->foreign(['company_id', 'tag_id'])
                ->references(['company_id', 'id'])
                ->on('tags')
                ->cascadeOnDelete();
        });

        Schema::create('monthly_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            $table->unique(['company_id', 'year', 'month']);
        });

        Schema::create('monthly_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->dateTime('generated_at');
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
        Schema::dropIfExists('monthly_reports');
        Schema::dropIfExists('monthly_budgets');
        Schema::dropIfExists('operation_tag');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('financial_operations');
        Schema::dropIfExists('companies');
    }
};

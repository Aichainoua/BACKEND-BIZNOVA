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
        Schema::create('sectors', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('code', 30)->unique();
            $table->string('label', 120);
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('sector_id')->nullable();
            $table->string('type', 20);
            $table->string('label', 100);
            $table->boolean('is_active')->default(true);

            $table->foreign('sector_id')->references('id')->on('sectors')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
        Schema::dropIfExists('sectors');
    }
};

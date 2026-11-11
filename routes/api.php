<?php

use App\Features\BusinessFinance\Http\Controllers\CompanyController;
use App\Features\BusinessFinance\Http\Controllers\FinancialOperationController;
use App\Features\BusinessFinance\Http\Controllers\MonthlyBudgetController;
use App\Features\BusinessFinance\Http\Controllers\TagController;
use App\Features\Identity\Http\Controllers\AuthController;
use App\Features\Identity\Http\Controllers\ProfileController;
use App\Features\ProjectAnalysis\Http\Controllers\ProjectController;
use App\Features\ReferenceData\Http\Controllers\ReferenceDataController;
use App\Features\System\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', HealthController::class)->name('system.health');

    Route::prefix('auth')->name('auth.')->group(function (): void {
        Route::middleware('throttle:auth')->group(function (): void {
            Route::post('/register', [AuthController::class, 'register'])->name('register');
            Route::post('/login', [AuthController::class, 'login'])->name('login');
        });

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
            Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
            Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        });
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/sectors', [ReferenceDataController::class, 'sectors'])->name('sectors.index');
        Route::get('/categories', [ReferenceDataController::class, 'categories'])->name('categories.index');
        Route::apiResource('projects', ProjectController::class);
        Route::apiResource('companies', CompanyController::class);
        Route::apiResource('companies.operations', FinancialOperationController::class);
        Route::apiResource('companies.tags', TagController::class);
        Route::apiResource('companies.budgets', MonthlyBudgetController::class)
            ->parameters(['budgets' => 'monthlyBudget']);
    });
});

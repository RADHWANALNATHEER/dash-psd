<?php

use App\Http\Controllers\Api\DesignApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// REST API لإنتاج التصاميم برمجيًا (مرحلة 2) - مصادقة Token-based عبر Sanctum
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::post('/designs', [DesignApiController::class, 'store'])->name('api.designs.store');
    Route::get('/designs/{id}', [DesignApiController::class, 'show'])->name('api.designs.show');
});

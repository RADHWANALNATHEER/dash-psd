<?php

use App\Http\Controllers\DesignController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TemplateController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('templates', TemplateController::class)->except(['show']);

    Route::get('designs/create', [DesignController::class, 'create'])->name('designs.create');
    Route::post('designs', [DesignController::class, 'store'])->name('designs.store');
    Route::get('designs/gallery', [DesignController::class, 'gallery'])->name('designs.gallery');
    Route::delete('designs/{design}', [DesignController::class, 'destroy'])->name('designs.destroy');
});

require __DIR__.'/auth.php';

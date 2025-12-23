<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard', [BookController::class, 'index'])->name('dashboard');
    Route::get('/books/search', [BookController::class, 'search'])->name('books.search');
    Route::get('/books/{olid}', [BookController::class, 'show'])->name('books.show');
    Route::post('/books/{olid}/summary', [BookController::class, 'generateSummary'])->name('books.summary');

});

require __DIR__.'/auth.php';

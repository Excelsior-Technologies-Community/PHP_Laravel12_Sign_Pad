<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SignatureController;

Route::get('/signature', [SignatureController::class, 'index'])->name('signature.index');
Route::get('/signature/create', [SignatureController::class, 'create'])->name('signature.create');
Route::post('/signature', [SignatureController::class, 'store'])->name('signature.store');

// ✅ NEW DELETE ROUTE
Route::delete('/signature/{id}', [SignatureController::class, 'destroy'])->name('signature.delete');

Route::get('/', function () {
    return view('welcome');
});

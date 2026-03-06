<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SignatureController;


// New route for index page
Route::get('/signature', [SignatureController::class, 'index'])->name('signature.index');

Route::get('/signature/create',[SignatureController::class,'create'])->name('signature.create');

Route::post('/signature',[SignatureController::class,'store'])->name('signature.store');

Route::get('/', function () {
    return view('welcome');
});

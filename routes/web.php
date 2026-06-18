<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SignatureController;

/*
|--------------------------------------------------------------------------
| Standalone signature flow (your original routes, unchanged)
|--------------------------------------------------------------------------
*/
Route::get('/signature', [SignatureController::class, 'index'])->name('signature.index');
Route::get('/signature/create', [SignatureController::class, 'create'])->name('signature.create');
Route::post('/signature', [SignatureController::class, 'store'])->name('signature.store');

// ✅ NEW DELETE ROUTE
Route::delete('/signature/{id}', [SignatureController::class, 'destroy'])->name('signature.delete');

// ✅ NEW: download a single signer's certificate PDF
Route::get('/signature/{id}/pdf', [SignatureController::class, 'exportPdf'])->name('signature.pdf');

/*
|--------------------------------------------------------------------------
| Multi-signer workflow (new)
|--------------------------------------------------------------------------
| /create and /requests/create are placed BEFORE the {uuid} wildcard routes
| on purpose -- otherwise Laravel would try to match the word "create" as
| if it were a {uuid} value.
*/
Route::get('/signature/requests', [SignatureController::class, 'requestIndex'])->name('signature.request.index');
Route::get('/signature/requests/create', [SignatureController::class, 'createRequest'])->name('signature.request.create');
Route::post('/signature/requests', [SignatureController::class, 'storeRequest'])->name('signature.request.store');
Route::get('/signature/requests/{uuid}', [SignatureController::class, 'showRequest'])->name('signature.request.show');
Route::get('/signature/requests/{uuid}/pdf', [SignatureController::class, 'exportRequestPdf'])->name('signature.request.pdf');

// Each signer's personal signing link, e.g. /sign/3f9a1c2e-....
Route::get('/sign/{signatureUuid}', [SignatureController::class, 'showRequestSignPage'])->name('signature.request.sign');
Route::post('/sign/{signatureUuid}', [SignatureController::class, 'storeRequestSignature'])->name('signature.request.sign.store');

Route::get('/', function () {
    return view('welcome');
});
<?php

use App\Http\Controllers\JurisprudenciaController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/jurisprudencia')->name('home');

Route::get('/js/script.js', function () {
    $path = public_path('js/script.js');

    abort_unless(file_exists($path), 404);

    return response()->file($path, [
        'Content-Type' => 'application/javascript; charset=UTF-8',
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->name('assets.script');

Route::get('/jurisprudencia', [JurisprudenciaController::class, 'index'])->name('jurisprudencia.index');
Route::get('/jurisprudencia/api/search', [JurisprudenciaController::class, 'search'])->name('jurisprudencia.search');
Route::get('/jurisprudencia/processos/{id}', [JurisprudenciaController::class, 'show'])->name('jurisprudencia.show');

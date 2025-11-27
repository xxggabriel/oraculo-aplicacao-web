<?php

use App\Http\Controllers\Api\JurimetriaController;
use Illuminate\Support\Facades\Route;

Route::get('/jurimetria/ping', [JurimetriaController::class, 'ping'])->name('api.jurimetria.ping');
Route::get('/jurimetria/search', [JurimetriaController::class, 'search'])->name('api.jurimetria.search');
Route::get('/jurimetria/aggregations', [JurimetriaController::class, 'aggregations'])->name('api.jurimetria.aggregations');

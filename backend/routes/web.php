<?php

use Illuminate\Support\Facades\Route;

// This deployment is an API only — the UI is a separat
// small JSON status instead of the framework's default
Route::get('/', fn () => response()->json([
    'service' => config('app.name').' API',
    'status' => 'ok',
    'app' => env('FRONTEND_URL'),
]));

Route::get('/login', function () {
    return response()->json(['message' => 'Unauthenticated'], 401);
})->name('login');
<?php

use Illuminate\Support\Facades\Route;

// This deployment is an API only — the UI is a separate app. The root returns a
// small JSON status instead of the framework's default landing page.
Route::get('/', fn () => response()->json([
    'service' => config('app.name').' API',
    'status' => 'ok',
    'app' => env('FRONTEND_URL'),
]));

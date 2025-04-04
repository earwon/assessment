<?php

use Illuminate\Support\Facades\Route;

Route::get('/api/sanctum/csrf-cookie', function() {
    return response()->json(['message' => 'CSRF token set']);
});

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

require __DIR__.'/auth.php';

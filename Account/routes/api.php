<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function (Request $request) {
    return response()->json(['message' => 'running ...']);
});

Route::get('/run', function (Request $request) {
    return response()->json(['message' => 'true']);
})->middleware('auth:sanctum');

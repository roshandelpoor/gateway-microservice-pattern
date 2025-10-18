<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GatewayController;

Route::match([
    'get', 
    'post', 
    'put', 
    'patch', 
    'delete'
], '/{service}/{path?}', [
    GatewayController::class, 
    'forward'
])
    ->where([
        'service' => 'orders|payments|accounts', 
        'path' => '.*'
    ]);
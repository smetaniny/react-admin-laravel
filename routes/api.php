<?php

use Illuminate\Support\Facades\Route;
use Smetaniny\ReactAdminLaravel\Controllers\ReactAdminController;

/*
|--------------------------------------------------------------------------
| React Admin API Routes
|--------------------------------------------------------------------------
|
| Маршруты для React Admin API
|
*/

$config = config(
    'react-admin.routes', [
    'prefix' => 'api/admin',
    'middleware' => ['api', 'auth:sanctum'],
    'name' => 'react-admin.',
    ]
);

Route::group(
    $config, function () {
        // Метаданные ресурса
        Route::get('{resource}/metadata', [ReactAdminController::class, 'metadata'])
        ->name('metadata');

        // CRUD маршруты для ресурсов
        Route::apiResource('{resource}', ReactAdminController::class)
        ->parameters(['{resource}' => 'id']);
    }
);

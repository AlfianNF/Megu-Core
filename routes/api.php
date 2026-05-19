<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CrudController;
use App\Http\Controllers\UploadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('login', [AuthController::class, 'login']);

Route::middleware(['auth:api'])->group(function () {

    $services = config('service') ?? []; 

    foreach ($services as $service) {
        $method = strtolower($service['type']);
        $path = ltrim($service['end_point'], '/');

        if ($method === 'post' && $path === 'login') {
            continue;
        }

        Route::$method($path, [CrudController::class, 'callService'])
             ->defaults('serviceName', $service['class']);
    }
    
    Route::get('logout', [AuthController::class, 'logout']);
    
    Route::post('upload-tmp', [UploadController::class, 'uploadTmp'])->name("upload");
    Route::post('upload', [UploadController::class, 'uploadFile'])->name("uploadFile");
    
    Route::get('file/{model}/{field}/{id}', [UploadController::class, 'getFile']);
    Route::get('tumb-file/{model}/{field}/{id}', [UploadController::class, 'getTumbnailFile']);
    
    Route::prefix('{model}')->group(function () {
        Route::get('/', [CrudController::class, 'index']);
        Route::get('/export', [CrudController::class, 'export']);
        Route::get('{id}', [CrudController::class, 'show']);
        Route::post('/', [CrudController::class, 'create']);
        Route::put('/', [CrudController::class, 'update']);
        Route::delete('/', [CrudController::class, 'delete']);
    });

    Route::get('crud/lang', [CrudController::class, 'lang']);
    Route::get('crud/modules', [CrudController::class, 'listModule']);

});

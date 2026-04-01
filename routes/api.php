<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('login', [AuthController::class, 'login']);

Route::middleware(['framework.auth'])->group(function () {

    Route::get('logout', [AuthController::class, 'logout']);
    
    Route::prefix('{model}')->group(function () {
        Route::get('/', [CrudController::class, 'index']);
        Route::get('dataset', [CrudController::class, 'dataset']);
        Route::get('generate', [CrudController::class, 'generate']);
        Route::get('{id}', [CrudController::class, 'show']);
        
        Route::post('/', [CrudController::class, 'create']);
        Route::put('/', [CrudController::class, 'update']);
        Route::delete('/', [CrudController::class, 'delete']);
        
        Route::patch('remove', [CrudController::class, 'remove']);
        Route::patch('restore', [CrudController::class, 'restore']);
    });

    Route::get('crud/lang', [CrudController::class, 'lang']);
    Route::get('crud/modules', [CrudController::class, 'listModule']);


});
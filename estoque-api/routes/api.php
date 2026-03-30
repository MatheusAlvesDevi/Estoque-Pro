<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\EntryProductController;
use App\Http\Controllers\ExitProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// AUTENTICAÇÃO (sem proteção)
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:api');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum', 'throttle:api');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//PRODUCTS (protegido)
Route::prefix('products')->middleware('auth:sanctum', 'throttle:api')->group(function () {
    Route::get('/',[ProductController::class,'index']); //listar
    Route::post('/', [ProductController::class, 'store']); //criar
    Route::get('{id}', [ProductController::class, 'show']); //buscar por ID
    Route::put('{id}', [ProductController::class, 'update']); //atualizar
    Route::delete('{id}', [ProductController::class, 'destroy']); //deletar
});

//ENTRIES (protegido)
Route::prefix('entryProduct')->middleware('auth:sanctum', 'throttle:api')->group(function () {
    Route::get('/',[EntryProductController::class,'index']); 
    Route::post('/', [EntryProductController::class, 'store']);
    Route::get('{id}', [EntryProductController::class, 'show']);
    Route::put('{id}', [EntryProductController::class, 'update']);
    Route::delete('{id}', [EntryProductController::class, 'destroy']);
});

//EXIT (protegido)
Route::prefix('exitProduct')->middleware('auth:sanctum', 'throttle:api')->group(function () {
    Route::get('/',[ExitProductController::class,'index']);
    Route::post('/', [ExitProductController::class, 'store']);
    Route::get('{id}', [ExitProductController::class, 'show']);
    Route::put('{id}', [ExitProductController::class, 'update']);
    Route::delete('{id}', [ExitProductController::class, 'destroy']);
});

//SUPPLIES (protegido)
Route::prefix('supply')->middleware('auth:sanctum', 'throttle:api')->group(function () {
    Route::get('/',[SupplierController::class,'index']);
    Route::post('/', [SupplierController::class, 'store']);
    Route::get('{id}', [SupplierController::class, 'show']);
    Route::put('{id}', [SupplierController::class, 'update']);
    Route::delete('{id}', [SupplierController::class, 'destroy']);
});

//USERS (protegido)
Route::prefix('users')->middleware('auth:sanctum', 'throttle:api')->group(function () {
    Route::get('/',[UserController::class,'index']);
    Route::post('/', [UserController::class, 'store']);
    Route::get('{id}', [UserController::class, 'show']);
    Route::put('{id}', [UserController::class, 'update']);
    Route::delete('{id}', [UserController::class, 'destroy']);
});
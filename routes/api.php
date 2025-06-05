<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LivroController;



Route::prefix('v1')->group(function () {
    Route::post('auth/token', [AuthController::class, 'login']);


    Route::middleware('auth:sanctum')->group(function () {
        Route::get('livros', [LivroController::class, 'index']);
        Route::post('livros', [LivroController::class, 'store']);
        Route::post('livros/{livroId}/importar-indices-xml', [LivroController::class, 'import']);
    });
});
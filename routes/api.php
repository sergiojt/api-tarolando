<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('user', [Auth::class, 'store']);
Route::get('user/google/{id}', [AuthController::class, 'findGoogle']);
Route::get('/eventos/importar', [EventoController::class, 'importarEventos']);
Route::group(['middleware' => ['JWTToken']], function () {
    Route::apiResource('eventos', EventoController::class);
    Route::post('/eventos/{id}/curtir', [EventoController::class, 'toggleCurtir']);
});

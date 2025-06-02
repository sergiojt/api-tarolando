<?php

use App\Http\Controllers\AmigoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\MessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
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
Route::post('user', [AuthController::class, 'store']);
Route::get('user/google/{id}', [AuthController::class, 'findGoogle']);
Route::get('/eventos/importar', [EventoController::class, 'importarEventos']);
Route::get('/eventos/importar/bar/{data}', [EventoController::class, 'importarEventosDoJson']);
Route::get('/eventos/importar/sympla', [EventoController::class, 'importSympla']);
Route::group(['middleware' => ['JWTToken']], function () {
    Route::get('/user/termo', [AuthController::class, 'termo']);
    Route::get('user/{id}', [AuthController::class, 'show']);
    Route::put('user/{id}', [AuthController::class, 'update']);
    Route::apiResource('eventos', EventoController::class);
    Route::post('/eventos/{id}/curtir', [EventoController::class, 'toggleCurtir']);
    Route::post('/eventos/{evento}/checkin', [EventoController::class, 'checkin']);
    Route::apiResource('amigos', AmigoController::class);
    Route::get('/messages/{id}', [MessageController::class, 'show']);
    Route::post('/messages', [MessageController::class, 'store']);
});

Broadcast::routes(['middleware' => ['JWTToken']]);

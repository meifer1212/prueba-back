<?php

use App\Http\Controllers\Api\BodegaController;
use App\Http\Controllers\Api\InventarioController;
use App\Http\Controllers\Api\ProductoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/

Route::get('/bodegas', [BodegaController::class, 'index']); // lista todas las bodegas ordenadas por nombre
Route::post('/bodegas', [BodegaController::class, 'store']); // crea una nueva bodega
Route::post('/productos', [ProductoController::class, 'store']); // crea un producto y asigna stock inicial en la bodega por default
Route::get('/productos/total-desc', [ProductoController::class, 'indexByTotalDesc']); // lista productos ordenados por total de ventas descendente
Route::post('/inventarios', [InventarioController::class, 'store']); // crea un nuevo inventario
Route::post('/inventarios/trasladar', [InventarioController::class, 'trasladar']); // traslada stock entre bodegas

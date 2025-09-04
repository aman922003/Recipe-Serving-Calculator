<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UnitConverterController;
use App\Http\Controllers\RecipeServingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', [UnitConverterController::class, 'index']);
Route::post('/', [UnitConverterController::class, 'convert'])->name('unit-converter.convert');

Route::get('/recipe-serving', [RecipeServingController::class, 'index']);
Route::post('/recipe-serving/calculate', [RecipeServingController::class, 'calculate'])->name('recipe-serving.calculate');

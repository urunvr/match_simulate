<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeamController;

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

Route::get('/', [TeamController::class, 'index']);
Route::get('/generate-fixtures', [TeamController::class, 'generateSchedule'])->name('generate_fixtures');
Route::get('/show', [TeamController::class, 'show'])->name('show');
Route::get('/play-week', [TeamController::class, 'playNextWeek'])->name('play-week');
Route::get('/simulate-league', [TeamController::class, 'simulateLeague'])->name('simulate-league');
Route::get('/reset', [TeamController::class, 'resetData'])->name('reset');

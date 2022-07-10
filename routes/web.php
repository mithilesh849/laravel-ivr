<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IvrController;



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


Route::get('/', function () {
    return view('welcome');
});

//ivr routes
Route::get('welcome', [IvrController::class, 'showWelcome'])->name('welcome');
Route::get('askName', [IvrController::class, 'askName'])->name('askName');
Route::any('showGenderOption', [IvrController::class, 'showGenderOption'])->name('showGenderOption');
Route::any('showInterest', [IvrController::class, 'showInterest'])->name('showInterest');
Route::any('saveInterest', [IvrController::class, 'saveInterest'])->name('saveInterest');
// test callback url
Route::any('saveRecording', [IvrController::class, 'saveRecording'])->name('saveRecording');






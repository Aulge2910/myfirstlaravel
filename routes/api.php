<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\blogPostController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/login', [   UserController::class, 'loginApi']);

Route::post('/create-post   ', [   blogPostController::class, 'storeNewPostApi'])->middleware('auth:sanctum');
//token based

Route::delete('/delete-post/{post}', [   blogPostController::class, 'deleteApi'])->middleware('auth:sanctum', 'can:delete,post');

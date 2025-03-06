<?php

use App\Events\chatMessage;

use Illuminate\Http\Request;
use  App\Http\Controllers\Test;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\followController;
use App\Http\Controllers\blogPostController;

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

// //user related route
//第一种写法
// Route::get('/admins-only', function(){
//     if(Gate::allows('visitAdminPages')){
//         return 'only admin able to view this page';
//     };

//     return 'you cant view this page';

// });

//第二种写法
Route::get('/admins-only', function(){
    return 'only admin able to view this page';
})->middleware('can:visitAdminPages');

//by default is named as login
Route::get('/', [userController::class,"showCorrectHomePage"])->name('login');
Route::post("/register", [UserController::class, 'register']);
Route::post("/login", [UserController::class, 'login']);
Route::post("/logout", [UserController::class, 'logout']);
Route::get('/manage-avatar', [userController::class,"showAvatarForm"])->middleware('MustBeLoggedIn');
Route::post('/manage-avatar', [userController::class,"storeAvatar"])->middleware('MustBeLoggedIn');

//blog post related route
//middleware run before http request before the showcreateform func
Route::get('/create-post', [blogPostController::class,"showCreateForm"])->middleware('MustBeLoggedIn');
Route::post('/create-post', [blogPostController::class,"storeNewPost"])->middleware('MustBeLoggedIn');
Route::get('/post/{post}', [blogPostController::class,"viewSinglePost"]);
Route::delete('/post/{post}', [blogPostController::class,"delete"])->middleware('can:delete,post');
Route::get('/post/{post}/edit', [blogPostController::class,"showEditForm"])->middleware('can:update,post');
Route::put('/post/{post}', [blogPostController::class,"actuallyUpdate"])->middleware('can:update,post');
Route::get('/search/{term}', [blogPostController::class,"search"]);

//have to match with class parameter
Route::get('/profile/{user:username}', [UserController::class, "profile"])->middleware('MustBeLoggedIn');
Route::get('/profile/{user:username}/follower', [UserController::class, "profileFollower"])->middleware('MustBeLoggedIn');
Route::get('/profile/{user:username}/following', [UserController::class, "profileFollowing"])->middleware('MustBeLoggedIn');


Route::middleware('cache.headers:public;max_age=20;etag')->group(function(){
    Route::get('/profile/{user:username}/raw', [UserController::class, "profileRaw"]);
    Route::get('/profile/{user:username}/follower/raw', [UserController::class, "profileFollowerRaw"]);
    Route::get('/profile/{user:username}/following/raw', [UserController::class, "profileFollowingRaw"]);


});




//follow related route
Route::post('/create-follow/{user:username}', [followController::class, "createFollow"])->middleware('MustBeLoggedIn');
Route::post('/remove-follow/{user:username}', [followController::class, "removeFollow"])->middleware('MustBeLoggedIn');



//check route
Route::post('/send-chat-message', function (Request $request){
 $formFields = $request->validate([
    'textvalue' => 'required'
  ]);

  if (!trim(strip_tags($formFields['textvalue']))) {
    return response()->noContent();
  }

  broadcast(new ChatMessage(['username' =>auth()->user()->username, 'textvalue' => strip_tags($request->textvalue), 'avatar' => auth()->user()->avatar]))->toOthers();
  return response()->noContent();


})->middleware('MustBeLoggedIn');

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\BirdsController;
use App\Http\Controllers\LikesController;
use App\Http\Controllers\FollowersController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', [UsersController::class, 'register']);
Route::post('login', [UsersController::class, 'login']);
Route::get('getuserbyusername/{loggedUsername}/{requestedUsername}', [UsersController::class, 'getUserByUsername']);
Route::get('searchuserbyusername/{username}', [UsersController::class, 'searchUserByUsername']);
Route::post('edituser', [UsersController::class, 'editUser']);
Route::post('changepassword', [UsersController::class, 'changePassword']);
Route::post('addbird', [BirdsController::class, 'addBird']);
Route::post('editbird', [BirdsController::class, 'editBird']);
Route::get('getbird/{id}/{requestingUser}',[BirdsController::class,'getBird']);
Route::get('getallbirds/{requestingUser}',[BirdsController::class,'getAllBirds']);
Route::get('getallbirdsexceptyours/{requestingUser}',[BirdsController::class,'getAllBirdsExceptYours']);
Route::get('getbirdsbyuser/{user}/{requestingUser}',[BirdsController::class,'getBirdsByUser']);
Route::post('getbirdswithfilter',[BirdsController::class,'getBirdsWithFilter']);
Route::post('getbirdswithfilterexceptyours',[BirdsController::class,'getBirdsWithFilterExceptYours']);
Route::post('getbirdsbyusernamewithdistance',[BirdsController::class,'getBirdsByUsernameWithDistance']);
Route::get('deletebird/{id}',[BirdsController::class,'deleteBird']);
Route::post('addlike',[LikesController::class,'addLike']);
Route::post('removelike',[LikesController::class,'removeLike']);
Route::post('userputlike',[LikesController::class,'userPutLike']);
Route::post('addfollower', [FollowersController::class, 'addFollower']);
Route::post('removefollower', [FollowersController::class, 'removeFollower']);
Route::get('getfollowersbyusername/{username}', [FollowersController::class, 'getFollowersByUsername']);
Route::get('getfollowedbyusername/{username}', [FollowersController::class, 'getFollowedByUsername']);
Route::get('isusernamefollowing/{follower}/{followed}', [FollowersController::class, 'isUsernameFollowing']);
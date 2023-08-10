<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Followers;

class FollowersController extends Controller
{
    function addFollower(Request $req){
        $follower = new Followers;
        $follower->usernameFollower = $req->input('usernameFollower');
        $follower->usernameFollowed = $req->input('usernameFollowed');
        $follower->save();
        return $follower;
    }

    function getFollowersByUsername($username){
        return Followers::leftJoin('users', 'followers.usernameFollower', '=', 'users.username')
        ->select('*')
        ->where('followers.usernameFollowed', '=', $username)
        ->get();
    }

    function getFollowedByUsername($username){
        return Followers::leftJoin('users', 'followers.usernameFollowed', '=', 'users.username')
        ->select('*')
        ->where('followers.usernameFollower', '=', $username)
        ->get();
    }
}

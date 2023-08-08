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
        return Followers::select("*")->where("usernameFollowed", $username)->get();
    }
}

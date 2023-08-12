<?php

namespace App\Http\Controllers;

use App\Models\Users;
use Illuminate\Http\Request;
use App\Models\Followers;

class FollowersController extends Controller
{
    function addFollower(Request $req){
        $follower = new Followers;
        $follower->usernameFollower = $req->input('usernameFollower');
        $follower->usernameFollowed = $req->input('usernameFollowed');

        $oldFollowers = Users::select("followers")->where("username", $req->input('usernameFollowed'))->first();

        if ($oldFollowers) {
            $newFollowers = $oldFollowers->followers + 1;
        
            Users::where('username', $req->input('usernameFollowed'))->update([
                'followers' => $newFollowers,
            ]);
        }
        
        $follower->save();
        return $follower;
    }

    function removeFollower(Request $req){
        $followers = Followers::where("usernameFollower", $req->input('usernameFollower'))->where("usernameFollowed", $req->input('usernameFollowed'))->delete();

        $oldFollowers = Users::select("followers")->where("username", $req->input('usernameFollowed'))->first();

        if ($oldFollowers) {
            $newFollowers = $oldFollowers->followers - 1;
        
            Users::where('username', $req->input('usernameFollowed'))->update([
                'followers' => $newFollowers,
            ]);
        }

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

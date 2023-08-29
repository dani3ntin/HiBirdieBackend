<?php

namespace App\Http\Controllers;

use App\Models\Users;
use Illuminate\Http\Request;
use App\Models\Followers;

class FollowersController extends Controller
{
    function addFollower(Request $req){
        $alreadyFollowing = Followers::where('usernameFollower', $req->input('usernameFollower'))->where('usernameFollowed', $req->input('usernameFollowed'))->exists();
        if($alreadyFollowing)
            return "Already following";
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
        $follower = Followers::where("usernameFollower", $req->input('usernameFollower'))->where("usernameFollowed", $req->input('usernameFollowed'))->delete();

        $oldFollowers = Users::select("followers")->where("username", $req->input('usernameFollowed'))->first();

        if ($oldFollowers) {
            $newFollowers = $oldFollowers->followers - 1;
        
            Users::where('username', $req->input('usernameFollowed'))->update([
                'followers' => $newFollowers,
            ]);
        }

        return $follower;
    }

    function getFollowersByUsername($username, $requestingUsername){
        $results = Followers::leftJoin('users', 'followers.usernameFollower', '=', 'users.username')
        ->select('*')
        ->where('followers.usernameFollowed', '=', $username)
        ->get();

        $followersController = new FollowersController;
        foreach ($results as $result) {
            $isUsernameFollowing = $followersController->isUsernameFollowing($requestingUsername, $result->usernameFollowed);
            $result->isLoggedUserFollowing = $isUsernameFollowing;
        }

        return $results;
    }

    function getFollowedByUsername($username){
        return Followers::leftJoin('users', 'followers.usernameFollowed', '=', 'users.username')
        ->select('*')
        ->where('followers.usernameFollower', '=', $username)
        ->get();
    }

    function isUsernameFollowing($follower, $followed){
        return Followers::where('usernameFollower', $follower)->where('usernameFollowed', $followed)->exists();
    }
}

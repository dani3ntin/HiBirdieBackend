<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Users;
use App\Models\Birds;
use App\Models\Followers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use App\Http\Controllers\FollowersController;

class UsersController extends Controller
{
    function register(Request $req){
        $users = new Users;
        $users->username = $req->input('username');
        $users->name = $req->input('name');
        $users->email = $req->input('email');
        $users->password = $req->input('password');
        $users->xPosition = $req->input('xPosition');
        $users->yPosition = $req->input('yPosition');
        $users->likes = 0;
        $users->followers = 0;
        //$users->password = Hash::make($req->input('password'));
        if($req->file('photo') == null){
            $users->profilePic = 'personPic/default.png';
        }else{
            $users->profilePic = $req->file('photo')->store("personPic/");
        }
        if($req->input('state') == null){
            $users->state = '';
        }else{
            $users->state = $req->input('state');
        }
        $users->save();
        return $users;
    }

    function checkLogin($user, $password){
        if(!$user || $password != $user->password)
            return false;
        return true;
    }

    function login(Request $req){
        $userFoundWithId = Users::where("username", $req->input)->first();
        $userFoundWithEmail = Users::where("email", $req->input)->first();
        //if(!$utente || !Hash::check($req->password, $utente->password)){  //questo è quello giusto per fare il login, fa l'hash delle password
        if(!$this->checkLogin($userFoundWithId, $req->password) && !$this->checkLogin($userFoundWithEmail, $req->password))
            return ["error"=>"wrong username or password"];
        if($userFoundWithId) 
            return $userFoundWithId;
        return $userFoundWithEmail;
    }

    function getUserByUsername($loggedUsername, $requestedUsername){
        $user = Users::select("*")->where("username", $requestedUsername)->get()[0];
        $path = $user->profilePic;

        if (!Storage::exists($path)) {
            abort(404);
        }

        $image = Storage::get($path);
        $mimeType = Storage::mimeType($path);

        $latestSightByThisUser = Birds::select("sightingDate")->where("user", $user->username)->orderBy("sightingDate", "desc")->first();
        if($latestSightByThisUser){
            $latestSightByThisUser = $latestSightByThisUser->sightingDate;
        }

        $followersController = new FollowersController;
        $isUsernameFollowing = $followersController->isUsernameFollowing($loggedUsername, $requestedUsername);

        return new Response($image, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline',
            'imageInfos' => json_encode([
                'username' => $user->username,
                'name' => $user->name, 
                'email' => $user->email,
                'state' => $user->state,
                'latestSight' => $latestSightByThisUser,
                'likes' => $user->likes,
                'followers' => $user->followers,
                'xPosition' => $user->xPosition,
                'yPosition' => $user->yPosition,
                'isLoggedUserFollowing' => $isUsernameFollowing,
            ]),
        ]);
    }

    function searchUserByUsername($username){
        return Users::where('username', 'like', '%'.$username.'%')->get();
    }

    function editUser(Request $req){
        if($req->file('photo') == null){
            Users::where('username', $req->input('username'))->update([
                'name' => $req->input('name'),
                'state' => $req->input('state'),
                'xPosition' => $req->input('xPosition'),
                'yPosition' => $req->input('yPosition'),
            ]);
        }else{
            Users::where('username', $req->input('username'))->update([
                'name' => $req->input('name'),
                'state' => $req->input('state'),
                'profilePic' => $req->file('photo')->store("personPic/"),
                'xPosition' => $req->input('xPosition'),
                'yPosition' => $req->input('yPosition'),
            ]);
        }
        return Users::where('username', $req->input('username'))->get();
    }

    function changePassword(Request $req){
        $result = Users::where('username', $req->username)
            ->update([
                'password' => Hash::make($req->input('password'))
            ]);
        if($result) 
            return true;
        return false;
    }
}

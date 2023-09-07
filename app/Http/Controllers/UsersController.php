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
    function getValidpersonIconPicName(){
        $files = Storage::files('personIconPic');
        $fileNames = [];

        foreach ($files as $file) {
            $fileName = pathinfo($file, PATHINFO_FILENAME);
            $fileNames[] = $fileName;
        }
        if($fileNames == [])
            return 0;
        $maxValue = max($fileNames);
        return $maxValue + 1;
    }

    function compressImage($imagePath){
        if (!extension_loaded('gd')){
            return $imagePath;
        }
        $originalImage = imagecreatefromjpeg($imagePath);
        $quality = 15;
        $compressedImagePath = 'immagine_compressa.jpg';
        imagejpeg($originalImage, $compressedImagePath, $quality);
        imagedestroy($originalImage);
        return $compressedImagePath;
    }

    function register(Request $req){
        $users = new Users;
        $users->username = $req->input('username');
        $users->name = $req->input('name');
        $users->email = $req->input('email');
        //$users->password = $req->input('password');
        $users->xPosition = $req->input('xPosition');
        $users->yPosition = $req->input('yPosition');
        $users->likes = 0;
        $users->followers = 0;
        $users->password = Hash::make($req->input('password'));
        $users->profilePic = 'personPic/default.png';
        $users->iconPic = 'personPic/default.png';
        if($req->input('state') == null){
            $users->state = '';
        }else{
            $users->state = $req->input('state');
        }
        $users->save();
        return $users;
    }

    function checkLogin($user, $password){
        if(!$user || !Hash::check($password, $user->password))
            return false;
        return true;
    }

    function login(Request $req){
        $userFoundWithId = Users::where("username", $req->input)->first();
        $userFoundWithEmail = Users::where("email", $req->input)->first();
        if(!$this->checkLogin($userFoundWithId, $req->password) && !$this->checkLogin($userFoundWithEmail, $req->password))
            return ["error"=>"wrong username or password"];
        if($userFoundWithId) 
            return $userFoundWithId;
        return $userFoundWithEmail;
    }

    function isUsernameAlreadyUsed($username){
        $user = Users::select("*")->where("username", $username)->get();
        if(count($user) > 0) return ["response" => 1];
        return ["response" => 0];
    }

    function getUserIconByUsername($username){
        $user = Users::select("*")->where("username", $username)->get()[0];
        $path = $user->iconPic;
        if($path == null) 
            return null;
        $image = Storage::get($path);
        $mimeType = Storage::mimeType($path);
        return new Response($image, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline',
        ]);
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

    function searchUserByUsername($username, $requestingUsername){
        $results = Users::where('username', 'like', '%'.$username.'%')
        ->where('username', '<>', $requestingUsername)
        ->get();

        $followersController = new FollowersController;
        foreach ($results as $result) {
            $isUsernameFollowing = $followersController->isUsernameFollowing($requestingUsername, $result->username);
            $result->isLoggedUserFollowing = $isUsernameFollowing;
        }

        return $results;
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
            $compressedImagePath = $this->compressImage($req->file('photo')->path());
            $iconPath = "personIconPic/".$this->getValidpersonIconPicName().".jpeg";
            Storage::put($iconPath, file_get_contents($compressedImagePath));
            Users::where('username', $req->input('username'))->update([
                'name' => $req->input('name'),
                'state' => $req->input('state'),
                'profilePic' => $req->file('photo')->store("personPic/"),
                'iconPic' => $iconPath,
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

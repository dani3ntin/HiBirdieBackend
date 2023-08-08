<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Users;
use App\Models\Birds;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;

class UsersController extends Controller
{
    function register(Request $req){
        $users = new Users;
        $users->id = $req->input('id');
        $users->name = $req->input('name');
        $users->email = $req->input('email');
        $users->password = $req->input('password');
        //$users->password = Hash::make($req->input('password'));
        $user->profilePic = $req->file('photo')->store("personPic/");
        $users->state = $req->input('state');
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

    function getUserByUsername($username){
        $user = Users::select("*")->where("username", $username)->get()[0];
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

        return new Response($image, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline',
            'imageInfos' => json_encode([
                'username' => $user->username,
                'name' => $user->name, 
                'email'=> $user->email,
                'state'=> $user->state,
                'latestSight' => $latestSightByThisUser,
            ]),
        ]);
    }
}

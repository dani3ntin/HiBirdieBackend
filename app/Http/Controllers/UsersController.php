<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Users;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    function register(Request $req){
        $users = new Users;
        $users->id = $req->input('id');
        $users->name = $req->input('name');
        $users->email = $req->input('email');
        $users->password = $req->input('password');
        //$users->password = Hash::make($req->input('password'));
        $users->state = $req->input('state');
        $users->likes = $req->input('likes');
        $users->save();
        return $users;
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Likes;

class LikesController extends Controller
{
    function addLike(Request $req){
        $like = new Likes;
        $like->user = $req->input('user');
        $like->bird = $req->input('bird');
        $like->save();
        return $like;
    }

    function removeLike(Request $req){
        $like = Likes::where("user", $req->input('user'))->where("bird", $req->input('bird'))->delete();
        if ($like) {
            return "like deleted";
        } 
        return "like not found";
    }

    function userPutLike(Request $req){
        $like = Likes::where("user", $req->input('user'))->where("bird", $req->input('bird'))->get();
        if ($like->isEmpty()) {
            return false;
        }
        return true;
    }
}

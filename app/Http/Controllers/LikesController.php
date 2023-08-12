<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Likes;
use App\Models\Users;
use App\Models\Birds;

class LikesController extends Controller
{
    function addLike(Request $req){
        $like = new Likes;
        $like->user = $req->input('user');
        $like->bird = $req->input('bird');

        $userLiked = Birds::select("user")->where("id", $req->input('bird'))->first();
        $oldLikes = Users::select("likes")->where("username", $userLiked->user)->first();

        if ($oldLikes) {
            $newLikes = $oldLikes->likes + 1;
        
            Users::where('username', $userLiked->user)->update([
                'likes' => $newLikes,
            ]);
        }
        
        $like->save();
        return $like;
    }

    function removeLike(Request $req){
        $like = Likes::where("user", $req->input('user'))->where("bird", $req->input('bird'))->delete();

        $userLiked = Birds::select("user")->where("id", $req->input('bird'))->first();
        $oldLikes = Users::select("likes")->where("username", $userLiked->user)->first();

        if ($oldLikes) {
            $newLikes = $oldLikes->likes - 1;
        
            Users::where('username', $userLiked->user)->update([
                'likes' => $newLikes,
            ]);
        }

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

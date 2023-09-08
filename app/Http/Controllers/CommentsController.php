<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comments;

class CommentsController extends Controller
{
    function addComment(Request $req){
        $comment = new Comments;
        $comment->bird = $req->input('bird');
        $comment->user = $req->input('user');
        $comment->commentText = $req->input('commentText');
        $comment->date = now();
        $comment->save();
        return $comment;
    }

    function getCommentsByBird($bird){
        return Comments::where('bird', '=', $bird)->get();
    }
}

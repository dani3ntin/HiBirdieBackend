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

    function getCommentsByBird($bird, $requestingUsername){
        $results = Comments::select('id', 'bird', 'user', 'commentText', 'date', 'name', 'likes', 'followers', 'state')
            ->join('users', 'comments.user', '=', 'users.username')
            ->where('comments.bird', '=', $bird)
            ->orderBy('comments.date', 'asc')
            ->get();

        $followersController = new FollowersController;
        foreach ($results as $result) {
            $isUsernameFollowing = $followersController->isUsernameFollowing($requestingUsername, $result->user);
            $result->isLoggedUserFollowing = $isUsernameFollowing;
        }

        return $results;
    }
}

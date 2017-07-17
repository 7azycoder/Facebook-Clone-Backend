<?php

namespace App\Http\Controllers;

use App\User;
use App\Jobs\MessageJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Post;
use App\Comment;

class CommentsController extends Controller
{
    
    public function __construct()
    {
        
    }

    public function createComment(Request $request)
    {
        $this->validate($request, [
        'content' => 'required',
        'post_id' => 'required',
        ]);

        $content = $request->input('content');  
        $post_id = $request->input('post_id');
        $user = Auth::user();
        $comment = new Comment;
        $comment->post_id = $post_id;
        $comment->user_id = $user->id;
        $comment->content = $content;
        $comment->save();

        return response()
                ->json(['success' => 'Comment Added Successfully'],200);
    
    }

    public function getComments(Request $request)
    {
        $user = Auth::user();
        $post_id = $request->header('post_id');
        $comments = Comment::where([['user_id','=',$user->id], ['post_id','=', $post_id]])->get();

        return response()->json(['comments' => $comments]);
    }

    public function updateComment(Request $request, $id)
    {
        $user = Auth::user();
        $content = $request->input('content');
        $comment = Comment::where('id', $id)->firstOrFail();
        $comment->content = $content;
        $comment->save();

        return response('Comment Updated Successfully', 200)
                  ->header('Content-Type', 'text/plain');
    }

    public function deleteComment(Request $request, $id)
    {
        $user = Auth::user();
        $comment = Comment::where('id', $id)->firstOrFail();
        $comment->delete();

        return response('Comment Deleted Successfully', 200)
                  ->header('Content-Type', 'text/plain');
    }

    public function getCommentById(Request $request, $id)
    {
        $user = Auth::user();
        $comment = Comment::where('id', $id)->firstOrFail();

        return response()->json(['comment' => $comment]);
    }

}
<?php

namespace App\Http\Controllers;

use App\User;
use App\Jobs\MessageJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Post;

class PostsController extends Controller
{
    
    public function __construct()
    {
        
    }


    public function createPost(Request $request)
    {
        $this->validate($request, [
        'content' => 'required',
        ]);

        $user = Auth::user();
        $content = $request->input('content');  
        $post = new Post;
        $post->user_id = $user->id;
        $post->content = $content;
        $post->save();

        return response()
                ->json(['success' => 'Post Created Successfully'],200);
    }

    public function getPosts(Request $request)
    {
        $user = Auth::user();
        $posts = Post::where('user_id', $user->id)->get();
        return response()->json(['posts' => $posts],200);
    }

    public function updatePost(Request $request, $id)
    {
        $user = Auth::user();
        $content = $request->input('content');
        $post = Post::where('id', $id)->first();
        if($post){
            $post->content = $content;
            $post->save();
            return response()
                ->json(['success' => 'Post Updated Successfully'],200);
        }

        return response()
                ->json(['error' => 'Post not found'],400);
    }

    public function deletePost(Request $request, $id)
    {
        $user = Auth::user();
        $post = Post::where('id', $id)->first();
        if($post){
            $post->delete();
            return response()
                ->json(['success' => 'Post Deleted Successfully'],200);
        }

        return response()
                ->json(['error' => 'Post not found'],400);

    }

    // public function getPostById(Request $request, $id)
    // {
    //     $post = Post::where('id', $id)->first();
    //     if($post){
    //         return response()->json(['post' => $post],200);
    //     }
    //     return response()
    //             ->json(['error' => 'Post not found'],400);
    // }

}
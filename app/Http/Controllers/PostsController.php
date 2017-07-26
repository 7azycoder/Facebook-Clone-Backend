<?php

namespace App\Http\Controllers;

use App\User;
use App\Jobs\MessageJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Post;
use App\Comment;
use App\FriendRequest;
use Carbon\Carbon;

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

      $postIds = [];
      $allUser = [];
      $allPosts = [];
      $currentUser = User::where('id',$user->id)->first();

      if($posts){
          // get all posts ids of user
          foreach ($posts as $post) {
              $postIds[] = $post->id;
          }

          // get all comments on all post ids
          $comments = Comment::whereIn('post_id', $postIds)->orderBy('created_at', 'ASC')->get();
          
          foreach ($posts as $post) {
              $commentsForPost = [];
              foreach ($comments as $comment) {
                  if($post->id === $comment->post_id){
                      $commentsForPost[] = $comment->id;
                  }
              }

              $allPosts[] = [
                  'id'  => $post->id,
                  'updated_at' => $this->formattedDate($post->updated_at) ,
                  'content' => $post->content,
                  'comments' => $commentsForPost
              ];
          }
      }

      return response()
        ->json(['posts' => $allPosts],200);
    }

    public function updatePost(Request $request, $id)
    {
      $user = Auth::user();
      $content = $request->input('content');
      $post = Post::where('id', $id)->first();
      
      if($post){
        if($post->user_id == $user->id) {
          // user is the author of post
          // user can update the post
          $post->content = $content;
          $post->save();
          return response()
            ->json(['success' => 'Post Updated Successfully'],200);
        } else {
          // user is not the author of post
          // user cannot update this post
          return response()
            ->json(['error' => 'You are not authorised to update this post'],400);
        }
      }

      return response()
        ->json(['error' => 'Post not found'],400);
    }

    public function deletePost(Request $request, $id)
    {
      $user = Auth::user();
      $post = Post::where('id', $id)->first();
      
      if($post){
        if($post->user_id == $user->id) {
          // user is the author of post
          // user can delete the post
          $post->delete();
          return response()
            ->json(['success' => 'Post deleted successfully'],200);
        } else {
          // user is not the author of post
          // user cannot delete this post 
          return response()
            ->json(['error' => 'You are not authorised to delete this post'],400);
        }
      }

      return response()
        ->json(['error' => 'Post not found'],400);

    }

    public function getPostsByUserId(Request $request, $user_id)
    {
      $user = Auth::user();

      if($user->id == $user_id){
        // get current user posts
        $posts = Post::where('user_id', $user->id)->get();
        $postIds = [];
        $allUser = [];
        $allPosts = [];

        if($posts){
            // get all posts ids of user
            foreach ($posts as $post) {
                $postIds[] = $post->id;
            }

            // get all comments on all post ids
            $comments = Comment::whereIn('post_id', $postIds)->orderBy('created_at', 'ASC')->get();
            
            foreach ($posts as $post) {
                $commentsForPost = [];
                foreach ($comments as $comment) {
                    if($post->id === $comment->post_id){
                        $commentsForPost[] = $comment->id;
                    }
                }

                $allPosts[] = [
                    'id'  => $post->id,
                    'updated_at' => $this->formattedDate($post->updated_at) ,
                    'content' => $post->content,
                    'isEditable'=> true,
                    'comments' => $commentsForPost
                ];
            }
        }

        return response()
          ->json(['posts' => $allPosts],200);
      }
      else{
        // check if this user is friend of current user
        $friendCheck = $this->isFriends($user->id , $user_id);
        if($friendCheck){
          // they are friends .. show the friend's posts
          $posts = Post::where('user_id', $user_id)->get();
          $postIds = [];
          $allUser = [];
          $allPosts = [];

          if($posts){
              // get all posts ids of user
              foreach ($posts as $post) {
                  $postIds[] = $post->id;
              }

              // get all comments on all post ids
              $comments = Comment::whereIn('post_id', $postIds)->orderBy('created_at', 'ASC')->get();
              
              foreach ($posts as $post) {
                  $commentsForPost = [];
                  foreach ($comments as $comment) {
                      if($post->id === $comment->post_id){
                          $commentsForPost[] = $comment->id;
                      }
                  }

                  $allPosts[] = [
                      'id'  => $post->id,
                      'updated_at' => $this->formattedDate($post->updated_at) ,
                      'content' => $post->content,
                      'isEditable' => false,
                      'comments' => $commentsForPost
                  ];
              }
          }

          return response()
            ->json(['posts' => $allPosts],200);
        }
        else{
          // not friends .. display error message
          return response()
            ->json(['error' => 'Not authorised to view posts of this user'],400);
        }
      }
     
    }

    public function isFriends($user_id_1 , $user_id_2)
    {
      $friendRequest1 = FriendRequest::where([
      ['from_user_id', '=', $user_id_1],
      ['to_user_id', '=', $user_id_2],
      ['status','=','accepted']
      ])->first();

      if($friendRequest1){
          return true;
      }

      $friendRequest2 = FriendRequest::where([
      ['from_user_id', '=', $user_id_2],
      ['to_user_id', '=', $user_id_1],
      ['status','=','accepted']
      ])->first();

      if($friendRequest2){
          return true;
      }

      return false;
    }

    public function formattedDate($dateString){
        $carbon = new Carbon($dateString);
        // $time = microtime(true)
        $localUpdatedDate = $carbon;//->timezone('Asia/Kolkata');
        return $localUpdatedDate->diffForHumans();
    }


}
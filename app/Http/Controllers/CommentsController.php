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

    public function createComment(Request $request, $post_id)
    {
      $this->validate($request, [
      'content' => 'required'
      ]);

      $user = Auth::user();
      $content = $request->input('content'); 

      $post = Post::where('id',$post_id)->get();
      if($post){
        if($post->user_id == $user->id)
        {
          // user is the author of post
          // save comment
          $comment = new Comment;
          $comment->post_id = $post_id;
          $comment->user_id = $user->id;
          $comment->content = $content;
          $comment->save();

          return response()
            ->json(['success' => 'Comment Added Successfully'],200);
        } 
        else
        {
          // user is not the author of post
          // check if user is friend of author
          $friendCheck = $this->isFriends($user->id, $post->user_id);

          if($friendCheck)
          {
            // user is friend of author
            // user can comment on post
            $comment = new Comment;
            $comment->post_id = $post_id;
            $comment->user_id = $user->id;
            $comment->content = $content;
            $comment->save();

            return response()
              ->json(['success' => 'Comment Added Successfully'],200);
          } 
          else
          {
            // user is not friend of author
            // user cannot comment on post
            return response()
              ->json(['error' => 'Cannot comment on this post since you are not friends'],400);
          }
        }
      } else
      {
        return response()
          ->json(['error' => 'Post does not exist'],400);
      }

    }

    public function getComments(Request $request, $post_id)
    {
      $user = Auth::user();

      $post = Post::where('id',$post_id)->get();
      if($post)
      {
        if($post->user_id == $user->id)
        {
          // user is the author of post
          // return him all the commentts for the requested post
          $comments = Comment::where('post_id', $post_id)->get();

          return response()
            ->json(['comments' => $comments],200);
        } else
        {
          // user is not the author of post
          // check if user is friend of author
          $friendCheck = $this->isFriends($user->id, $post->user_id);

          if($friendCheck)
          {
            // user is friend of author
            // return him all the commentts for the requested post
            $comments = Comment::where('post_id', $post_id)->get();

            return response()
              ->json(['comments' => $comments],200);
          } else
          {
            // user is not friend of author
            // user cannot read comments
            return response()
              ->json(['error' => 'You are not authorised to read comments for this post'],400);
          }

        }
      } else
      {
        return response()
          ->json(['error' => 'No comments found since the requested post does not exist'],400);
      }

    }

    public function updateComment(Request $request, $id)
    {
      $user = Auth::user();
      $content = $request->input('content');
      $comment = Comment::where('id', $id)->first();
      if($comment)
      {
        // comment exists 
        // check if user has permission to update it
        if($comment->user_id == $user->id)
        {
          // user is the author of comment
          // user has permission tot update it
          $comment->content = $content;
          $comment->save()
          return response()
            ->json(['success' => 'Comment updated successfully'],200);
        } else {
          // user is not the author of comment.
          // user cannot delete it
          return response()
            ->json(['error' => 'You are not authorised to update this comment'],400);
        }
      }

      return response()
          ->json(['error' => 'Comment not found'],400);
    
    }

    public function deleteComment(Request $request, $id)
    {
      $user = Auth::user();
      $comment = Comment::where('id', $id)->first();
      if($comment)
      {
        // comment exists 
        // check if user has permission to delete it
        if($comment->user_id == $user->id)
        {
          // user is the author of comment
          // user has permission to delete comment
          $comment->delete()
          return response()
            ->json(['success' => 'Comment deleted successfully'],200);
        } else {
          // user is not the author of comment. 
          // user cannot delete it
          return response()
            ->json(['error' => 'You are not authorised to delete this comment'],400);
        }
      }

      return response()
          ->json(['error' => 'Comment not found'],400);

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

}
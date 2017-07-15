<?php

namespace App\Http\Controllers;

use App\User;
use App\Post;
use App\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function signUp(Request $request)
    {
        $this->validate($request, [
        'name' => 'required|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:6|max:255',
        ]);

        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');

        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($password);
        $user->save();

        return response()
              ->json(['success' => 'User Signed Up Successfully'],200);
    }

    public function login(Request $request)
    {
        $this->validate($request, [
        'email' => 'required|email',
        ]);

        $email = $request->input('email');
        $password = $request->input('password');
        $user = User::where('email', $email)->first();

        if($user){
          $hashedPassword = $user->password;
          if (Hash::check($password, $hashedPassword)) {
              // password match
              $token = md5(microtime().rand());
              $user->token = $token;
              $user->save();
              return response()
                    ->json(['access_token' => $token],200);
          }

          return response()
                ->json(['error' => 'Wrong Password'],400);
        }

        return response()
              ->json(['error' => 'Email not found'],400);
    }


    public function logout(Request $request)
    {
        $user = Auth::user();
        $user->token = NULL;
        $user->save();

        return response()
              ->json(['success' => 'User Logged Out Successfully'],200);
    }

    public function getCurrentUser(Request $request)
    {
        $user = Auth::user();
        $postIds = [];
        $allUser = [];
        $allPosts = [];
        $currentUser = User::where('id',$user->id)->first();
        if($currentUser){
            $posts = Post::where('user_id', $user->id)->orderBy('updated_at', 'DESC')->get();
            if($posts){
                // get all posts ids of user
                foreach ($posts as $post) {
                    $postIds[] = $post->id;
                }

                // get all comments on all post ids
                $comments = Comment::whereIn('post_id', $postIds)->orderBy('updated_at', 'ASC')->get();
                if(!empty($comments)) {
                    foreach ($comments as $comment) {
                        $allUser[] = $comment->user_id;
                    }
                    $allUser = array_unique($allUser);
                    $userNames = User::select(['id','name'])->whereIn('id', $allUser)->get();
                    $temp = [];
                    foreach ($userNames as $row) {
                        $temp[$row['id']] = $row['name'];
                    }
                    // a map for id => username
                    $userNames = $temp;


                    foreach ($comments as $comment) {
                        $comment_id = $comment['id'];
                        $post_id = $comment['post_id'];
                        $user_id = $comment['user_id'];
                        $commentText = $comment['content'];
                    }
                }

                
                foreach ($posts as $post) {
                    $commentsForPost = [];
                    foreach ($comments as $comment) {
                        if($post->id === $comment->post_id){
                            $commentsForPost[] = [
                                'id' => $comment->id,
                                'name' => $userNames[$comment->user_id],
                                'updated_at'=> $comment->updated_at,
                                'content' => $comment->content
                            ];
                        }
                    }

                    $allPosts[] = [
                        'id'  => $post->id,
                        'updated_at' => $post->updated_at,
                        'content' => $post->content,
                        'comments' => $commentsForPost
                    ];
                }
            }

            return response()->json(['user' => ['id' => $currentUser->id, 'name' => $currentUser->name, 'posts' => $allPosts]],200);
        }
        return response()->json(['error' => 'User Not Found'],400);
    }

    public function getUserById(Request $request, $user_id)
    {
        $user = Auth::user();
        $user = User::where('id', $user_id)->first();
        if($user){
            return response()->json(['user' => $user],200);
        }

        return response()->json(['error' => "User Not Found"],400);

    }

    public function deleteCurrentUser(Request $request)
    {
        $user = Auth::user();
        $user->delete();
        return response()
              ->json(['success' => 'User Deleted Successfully'],200);
    }

    public function searchUserByName(Request $request, $name)
    {
        $name = rawurldecode($name);
        $users = User::where('name','like', '%' . $name . '%')->get();
        return response()
                  ->json(['users' => $users],200);

    }
}

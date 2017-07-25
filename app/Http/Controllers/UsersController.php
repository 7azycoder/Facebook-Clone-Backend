<?php

namespace App\Http\Controllers;

use App\User;
use App\Post;
use App\Comment;
use App\FriendRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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

        $password = $request->input('password');

        $user = new User;
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = Hash::make($password);
        $user->save();

        return response()
              ->json(['success' => 'User Signed Up Successfully'],200);
    }

    public function login(Request $request)
    {
        $this->validate($request, [
        'email' => 'required|email',
        'password' => 'required'
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
              ->json(['error' => 'No user found with that email'],400);
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
                $comments = Comment::whereIn('post_id', $postIds)->orderBy('created_at', 'ASC')->get();
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
                                'user_id' => $comment->user_id,
                                'name' => $userNames[$comment->user_id],
                                'updated_at'=> $this->formattedDate($comment->updated_at),
                                'content' => $comment->content,
                                'isEditable' => $comment->user_id == $currentUser->id ? true:false
                            ];
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

            return response()->json(['user' => ['id' => $currentUser->id, 'name' => $currentUser->name, 'posts' => $allPosts]],200);
        }
        return response()->json(['error' => 'User Not Found'],400);
    }

    public function getOtherUserById(Request $request, $user_id)
    {
        $currentUser = Auth::user();
        $postIds = [];
        $allUser = [];
        $allPosts = [];
        $user = User::where('id',$user_id)->first();
        if($user){
            // same user is logged in 
            // send user complete info
            $posts = Post::where('user_id', $user->id)->orderBy('updated_at', 'DESC')->get();
            if($posts){
                // get all posts ids of user
                foreach ($posts as $post) {
                    $postIds[] = $post->id;
                }

                // get all comments on all post ids
                $comments = Comment::whereIn('post_id', $postIds)->orderBy('created_at', 'ASC')->get();
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
                                'user_id' => $comment->user_id,
                                'name' => $userNames[$comment->user_id],
                                'updated_at'=> $this->formattedDate($comment->updated_at),
                                'content' => $comment->content,
                                'isEditable' => $comment->user_id == $currentUser->id ? true:false
                            ];
                        }
                    }

                    $allPosts[] = [
                        'id'  => $post->id,
                        'updated_at' => $this->formattedDate($post->updated_at),
                        'content' => $post->content,
                        'comments' => $commentsForPost
                    ];
                }
            }

            $isFriends = false;
            $status = $this->friendShipStatus($user->id, $currentUser->id);
            if($status === "accepted"){
                $isFriends = true;
            }

            return response()->json(['user' => ['id' => $user->id, 'name' => $user->name, 'posts' => $allPosts ,'isFriends' => $isFriends , 'status' => $status]],200);
        }

        return response()->json(['error' => 'User Not Found'],400);

    }

    public function friendShipStatus($user_id_1 , $user_id_2)
    {
        $friendRequest1 = FriendRequest::where([
        ['from_user_id', '=', $user_id_1],
        ['to_user_id', '=', $user_id_2]
        ])->first();

        if($friendRequest1){
            return $friendRequest1->status;
        }

        $friendRequest2 = FriendRequest::where([
        ['from_user_id', '=', $user_id_2],
        ['to_user_id', '=', $user_id_1],
        ])->first();

        if($friendRequest2){
            return $friendRequest2->status;
        }

        return "NoRequestSent";
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

        $allUsers = [];
        foreach ($users as $user) {
            $allUsers[] = [
                'id' => $user->id,
                'name' => $user->name,
            ];
        }

        return response()
                  ->json(['users' => $allUsers],200);

    }

    public function formattedDate($dateString){
        $carbon = new Carbon($dateString);
        // $time = microtime(true)
        $localUpdatedDate = $carbon;//->timezone('Asia/Kolkata');
        return $localUpdatedDate->diffForHumans();
    }
}

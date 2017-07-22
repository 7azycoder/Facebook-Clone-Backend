<?php

namespace App\Http\Controllers;

use App\User;
use App\Jobs\MessageJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Message;

class MessagesController extends Controller
{
    
    public function __construct()
    {
        //
    }


    public function sendMessage(Request $request, $to_user_id)
    {
      $user = Auth::user();
      $from_user_id = $user->id;
      $content = $request->input('content');

      //check if user is a friend
      $friendCheck = $this->isFriends($from_user_id, $to_user_id);
      if($friendCheck) {
        // user is a friend
        $this->dispatch(new MessageJob($from_user_id,$to_user_id,$content));
        return response()
          ->json(['success' => 'Message sent successfully'],200);
      } else {
        return response()
          ->json(['error' => 'You are not authorised to send message to this person'],400); 
      }  
    }

    public function getMessages(Request $request, $to_user_id)
    {
      $user = Auth::User();
      $from_user_id = $user->id; 

      $friendCheck = $this->isFriends($from_user_id, $to_user_id);
      if($friendCheck) {
        // user is a friend
        $messages1 = Message::where([
        ['from_user_id', '=', $from_user_id],
        ['to_user_id', '=', $to_user_id],
        ]);

        $messages2 = Message::where([
        ['from_user_id', '=', $to_user_id],
        ['to_user_id', '=', $from_user_id],
        ])
        ->union($messages1)
        ->orderBy('created_at','asc')
        ->get();

        return response()
          ->json(['messages' => $messages2],200);
        
      } else {
        return response()
          ->json(['error' => 'You are not authorised to read messages to this person'],400); 
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

}
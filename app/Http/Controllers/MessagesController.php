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


    public function sendMessage(Request $request)
    {
        $user = Auth::user();
        $from_user_id = $user->id;
        $to_user_id = $request->input('to_user_id');
        $content = $request->input('content');
        $this->dispatch(new MessageJob($from_user_id,$to_user_id,$content));
        return response()
                  ->json(['success' => 'Message Sent'],200);
    }

    public function getMessages(Request $request, $to_user_id)
    {
        // $this->validate($request, [
        // 'to_user_id' => 'required',
        // 'content' => 'required|min:1',
        // ]);

        $user = Auth::User();
        $from_user_id = $user->id; 

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

    }

}
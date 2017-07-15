<?php

namespace App\Http\Controllers;

use App\User;
use App\FriendRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RequestsController extends Controller
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

    public function sendRequest(Request $request, $to_user_id)
    {
        $user = Auth::user();
        $from_user_id = $user->id;

        $friendRequest1 = FriendRequest::where([
        ['from_user_id', '=', $from_user_id],
        ['to_user_id', '=', $to_user_id],
        ])->first();

        $friendRequest2 = FriendRequest::where([
        ['from_user_id', '=', $to_user_id],
        ['to_user_id', '=', $from_user_id],
        ])->first();


        if($friendRequest1 || $friendRequest2)
        {
            return response('Request Exists', 400)
                  ->header('Content-Type', 'text/plain');
        } else {
            $friendRequest = new FriendRequest;
            $friendRequest->from_user_id = $from_user_id;
            $friendRequest->to_user_id = $to_user_id;
            $friendRequest->save();

            return response('Request Sent Successfully', 200)
                  ->header('Content-Type', 'text/plain');
        }
        
    }

    public function cancelRequest(Request $request, $from_user_id)
    {
        $user = Auth::user();
        $from_user_id = $user->id;

        $friendRequest1 = FriendRequest::where([
        ['from_user_id', '=', $from_user_id],
        ['to_user_id', '=', $to_user_id],
        ])->first();

        if($friendRequest1){
            $friendRequest1->delete();
            return response('Request Deleted Successfully', 200)
                  ->header('Content-Type', 'text/plain');
        }

        $friendRequest2 = FriendRequest::where([
        ['from_user_id', '=', $to_user_id],
        ['to_user_id', '=', $from_user_id],
        ])->first();

        if($friendRequest2){
            $friendRequest2->delete();
            return response('Request Rejected Successfully', 200)
                  ->header('Content-Type', 'text/plain');
        }


        return response('No request was sent. Hence not cancelled', 400)
                  ->header('Content-Type', 'text/plain');

    }

    public function acceptRequest(Request $request, $from_user_id)
    {
        $user = Auth::user();
        $to_user_id = $user->id;

        $friendRequest = FriendRequest::where([
        ['from_user_id', '=', $to_user_id],
        ['to_user_id', '=', $from_user_id],
        ])->first();

        if($friendRequest){
            $friendRequest->status = 'accepted';
            $friendRequest->save();
            return response('Request Accepted Successfully', 200)
                  ->header('Content-Type', 'text/plain');
        } else{
            return response('No Such Request Found. Hence not accepted', 400)
                  ->header('Content-Type', 'text/plain');
        }
        
    }


    public function isFriend(Request $request){

    }
}
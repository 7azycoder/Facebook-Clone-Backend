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

    public function getRequests(Request $request)
    {
        $user = Auth::User();
        $to_user_id = $user->id;
        $friendRequests = FriendRequest::where([['to_user_id', '=', $to_user_id],['status', '=', 'pending']])->get();

        $allUserIds = [];
    
        foreach ($friendRequests as $friendRequest) {
            $allUserIds[] = $friendRequest->from_user_id;
        }

        $allUserIds = array_unique($allUserIds);
        $userNames = User::select(['id','name'])->whereIn('id', $allUserIds)->get();
        $temp = [];
        foreach ($userNames as $row) {
            $temp[$row['id']] = $row['name'];
        }
        // a map for id => username
        $userNames = $temp;

        $allFriendRequests = [];
        foreach ($friendRequests as $friendRequest) {
            $allFriendRequests[] = [
                'id' => $friendRequest->id,
                'from_id' => $friendRequest->from_user_id,
                'name' => $userNames[$friendRequest->from_user_id]
            ];
        }

        return response()
              ->json(['requests' => $allFriendRequests],200);

    }

    public function sendRequest(Request $request)
    {
        $user = Auth::user();
        $from_user_id = $user->id;
        $to_user_id = $request->input('to_user_id');

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
            return response()
              ->json(['error' => 'Request Exists'],400);
        } else {
            $friendRequest = new FriendRequest;
            $friendRequest->from_user_id = $from_user_id;
            $friendRequest->to_user_id = $to_user_id;
            $friendRequest->save();

            return response()
              ->json(['success' => 'Request Sent Successfully'],200);
        }
        
    }

    public function deleteRequest(Request $request, $id)
    {
        $user = Auth::user();
        $friendRequest = FriendRequest::where('id','=',$id)->first();

        if($friendRequest){
            $friendRequest->delete();
            return response()
              ->json(['success' => 'Friend Request Deleted'],200);
        }

        return response()
              ->json(['error' => 'No Friend Request Found'],400);
    }

    public function confirmRequest(Request $request, $id)
    {
        $user = Auth::user();
        $friendRequest = FriendRequest::where('id',$id)->first();

        if($friendRequest){
            $friendRequest->status = 'accepted';
            $friendRequest->save();
            return response()
              ->json(['success' => 'Friend Added'],200);
        }

        return response()
              ->json(['error' => 'No Friend Request Found'],400);
    }

    // public function declineRequest(Request $request, $id)
    // {
    //     $user = Auth::user();
    //     $friendRequest = FriendRequest::where('id',$id)->first();

    //     if($friendRequest){
    //         $friendRequest->status = 'declined';
    //         $friendRequest->save();
    //         return response()
    //           ->json(['success' => 'Friend Request Declined'],200);
    //     }

    //     return response()
    //           ->json(['error' => 'No Friend Request Found'],400);
    // }
}
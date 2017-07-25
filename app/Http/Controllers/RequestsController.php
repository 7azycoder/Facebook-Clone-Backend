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
          return response()
            ->json(['error' => 'Request Already Exists'],400);
      } else {
          $friendRequest = new FriendRequest;
          $friendRequest->from_user_id = $from_user_id;
          $friendRequest->to_user_id = $to_user_id;
          $friendRequest->save();

          return response()
            ->json(['success' => 'Request Sent Successfully'],200);
      }
        
    }

    public function cancelSentRequest(Request $request, $to_user_id)
    {
      $user = Auth::user();
      $friendRequest = FriendRequest::where([
        ['from_user_id','=',$user->id],
        ['to_user_id', '=', $to_user_id],
        ['status','=','pending']
        ])->first();

      if($friendRequest){
        $friendRequest->delete();
        return response()
          ->json(['success' => 'Friend Request Cancelled'],200);
      }

      return response()
        ->json(['error' => 'No Friend Request Found'],400);
    }

    public function rejectReceivedRequest(Request $request, $from_user_id)
    {
      $user = Auth::user();
      $friendRequest = FriendRequest::where([
        ['from_user_id','=',$from_user_id],
        ['to_user_id', '=', $user->id],
        ['status','=','pending']
        ])->first();

      if($friendRequest){
        $friendRequest->delete();
        return response()
          ->json(['success' => 'Friend Request Rejected'],200);
      }

      return response()
        ->json(['error' => 'No Friend Request Found'],400);
    }

    public function unFriend(Request $request, $user_id)
    {
      $user = Auth::user();
      $friendRequest1 = FriendRequest::where([
        ['from_user_id','=',$user_id],
        ['to_user_id', '=', $user->id],
        ['status','=','accepted']
        ])->first();

      if($friendRequest1){
        $friendRequest1->delete();
        return response()
          ->json(['success' => 'Friend Deleted'],200);
      }

      $friendRequest2 = FriendRequest::where([
        ['from_user_id','=',$user->id],
        ['to_user_id', '=', $user_id],
        ['status','=','accepted']
        ])->first();

      if($friendRequest2){
        $friendRequest2->delete();
        return response()
          ->json(['success' => 'Friend Deleted'],200);
      }

      return response()
        ->json(['error' => 'No Friend Found'],400);
    }


    public function confirmReceivedRequest(Request $request, $from_user_id)
    {
      $user = Auth::user();
      $friendRequest = FriendRequest::where([
        ['from_user_id','=',$from_user_id],
        ['to_user_id', '=', $user->id],
        ['status','=','pending']
        ])->first();

      if($friendRequest){
        $friendRequest->status = 'accepted';
        $friendRequest->save();
        return response()
          ->json(['success' => 'Friend Added'],200);
      }

      return response()
            ->json(['error' => 'No Friend Request Found'],400);
    }
}
<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Auth;
use App\Message;

class MessageJob extends Job
{

    protected $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
        // $user = Auth::user();
        // print_r($user);
        // print_r($request);
        // print_r($message);
        print_r($this->message->content);
    }


    public function handle()
    {
        // $newMessage = $this->message;
        echo 'handle called';
        // $newMessage->save();
    }
}
<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Auth;
use App\Message;

class MessageJob extends Job
{
    protected $from_user_id;
    protected $to_user_id;
    protected $content;

    public function __construct($from_user_id,$to_user_id,$content)
    {
        $this->from_user_id = $from_user_id;
        $this->to_user_id = $to_user_id;
        $this->content = $content;
    }


    public function handle()
    {
        $message = new Message;
        $message->to_user_id = $this->to_user_id;
        $message->from_user_id = $this->from_user_id;
        $message->content = $this->content;
        $message->save();
    }
}
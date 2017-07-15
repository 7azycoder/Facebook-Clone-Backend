<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    public $timestamps = false;

    protected $fillable =  ['from_user_id','to_user_id','content'];

    // protected $hidden = ['created_at', 'updated_at'];
}
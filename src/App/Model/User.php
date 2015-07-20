<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class User extends Eloquent {

    protected $table = 'user';

    protected $fillable = ['email', 'first_name', 'last_name', 'gender', 'facebook_id'];

    public function Photo()
    {
    	return $this->hasOne('Photo');
    }
}
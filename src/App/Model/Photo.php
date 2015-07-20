<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Photo extends Eloquent {

    protected $table = 'photo';

    protected $fillable = ['user_id', 'link', 'title', 'description'];

    public function user()
    {
        return $this->belongsTo('User');
    }
}
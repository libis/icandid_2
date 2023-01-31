<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Query extends Model
{
    //
    protected $hidden = ['user_id','updated_at'];

    public function getQueryAttribute($value) {
        return json_decode($value);
    }
    
    public function user() {
        return $this->belongsTo(User::class);
    }    
}

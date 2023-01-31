<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    //

    public function users() {

        return $this->belongsToMany(User::class, 'user_resource');
    }

    public function roles() {
        return $this->belongsToMany(Role::class, 'role_resource');
    }

}

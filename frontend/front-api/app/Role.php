<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    //
    public function users() {
        return $this->belongsToMany(User::class, 'user_role');
    }

    public function resources() {
        return $this->belongsToMany(Resource::class, 'role_resource');
    }

    public function datasets() {
        return $this->belongsToMany(Dataset::class, 'role_dataset');
    }
}

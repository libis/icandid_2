<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Eshelf extends Model
{
    //

    protected $hidden = ['user_id', 'updated_at', 'created_at'];

    protected static function boot()
    {
        parent::boot();

        self::deleting(function($model) { // before delete() method call this
            $model->items()->each(function($es) {
            $es->delete(); 
            });    
        });
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function items() {
        return $this->hasMany(Item::class);
    }    
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dataset extends Model
{
    //

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->from == "") {
                $date = new \DateTime();
                $model->from = $date->format('Y-m-d');
            }
            if ($model->until == "") {
                $model->until = "2060-12-31";
            }
        });


    }

    public function users() {
        return $this->belongsToMany(User::class, 'user_dataset');
    }

    public function roles() {
        return $this->belongsToMany(Role::class, 'role_dataset');
    }

    public function labels() {
        return $this->belongsToMany(Label::class, 'dataset_label');
    }

    public function languages() {
        return $this->belongsToMany(Language::class, 'dataset_language');
    }

    public function __toString() {
        return $this->internalident;
    }
}

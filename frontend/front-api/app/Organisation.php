<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    public function groups() {
        return $this->hasMany(OrganisationGroup::class)->orderBy('name', 'asc');
    }
        
}

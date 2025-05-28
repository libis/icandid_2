<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrganisationGroup extends Model
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
    protected $hidden = ["organisation_id"];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    public function organisation() {
        return $this->belongsTo(Organisation::class);
    }  
    
    public function faculty() {
        return $this->hasMany(Faculty::class)->orderBy('name', 'asc');
    }
}

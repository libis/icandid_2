<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
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
    protected $hidden = ["organisation_group_id"];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    public function organisationgroup() {
        return $this->belongsTo(OrganisationGroup::class);
    }  
    
}

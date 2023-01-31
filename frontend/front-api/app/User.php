<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'eppn'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['created_at','updated_at'
        
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->apikey = sha1(uniqid());
        });

        static::updating(function ($model) {
            if (trim($model->apikey) == "") {
                $model->apikey = sha1(uniqid());
            }
        });

        self::deleting(function($model) { // before delete() method call this
            $model->eshelves()->each(function($es) {
               $es->delete(); 
            });
            $model->queries()->each(function($es) {
                $es->delete(); 
            });
            $model->queue()->each(function($es) {
                $es->delete(); 
            });
            $model->resources()->each(function($es) use ($model) {
                $model->resources()->detach($es->id); 
            });
            $model->roles()->each(function($es) use ($model)  {
                $model->roles()->detach($es->id); 
            });
            $model->datasets()->each(function($es) use ($model) {
                $model->datasets()->detach($es->id); 
            });
        });
    }

    public function eshelves() {
        return $this->hasMany(Eshelf::class);
    }

    public function queries() {
        return $this->hasMany(Query::class);
    }

    public function queue() {
        return $this->hasMany(Queue::class);
    }

    public function resources() {
        return $this->belongsToMany(Resource::class, 'user_resource');
    }

    public function roles() {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    public function datasets() {
        return $this->belongsToMany(Dataset::class, 'user_dataset');
    }


    public function access() {
        $acc = array_map(function($d) { return $d->internalident; },$this->datasets()->getResults()->where('available',1)->all());
        foreach ($this->roles()->getResults()->all() as $r) {
            $acc = array_merge($acc, array_map(function($d) { return $d->internalident; }, $r->datasets()->getResults()->where('available',1)->all()));
        }
        return array_values(array_unique($acc));
    }

    public function getPermissions() {
        $p = [];
        $p['datasets'] = $this->datasets()->with('labels')->with('languages')->getResults()->where('available',1)->all();
        foreach ($this->roles()->getResults()->all() as $r) {
            $p['datasets'] = array_merge($p['datasets'], $r->datasets()->with('labels')->with('languages')->getResults()->where('available',1)->all());
        }
        $p['datasets'] = array_values(array_unique($p['datasets']));

        $p['resources'] = $this->resources()->getResults()->all();
        foreach ($this->roles()->getResults()->all() as $r) {
            $p['resources'] = array_merge($p['resources'], $r->resources()->getResults()->all());
        }
        $p['resources'] = array_unique($p['resources']);

        foreach($p["resources"] as $k => $v) {
            unset($p["resources"][$k]["id"]);
            unset($p["resources"][$k]["created_at"]);
            unset($p["resources"][$k]["updated_at"]);
            unset($p["resources"][$k]["pivot"]);
        }

        return $p;
    }
        
}

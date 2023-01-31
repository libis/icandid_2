<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Item extends Model
{
    //

    protected $hidden = ["eshelf_id", "created_at", "updated_at"];

    public function getValueAttribute($value) {
        if (is_object($value)) {
            return $value;
        }
        $r = json_decode($value);
        if ($r == Null) {
            return $value;
        } else {
            return $r;
        }
    }

    public function eshelf() {
        return $this->belongsTo(Eshelf::class);
    }    
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    //

    public function datasets() {
        return $this->belongsToMany(Dataset::class, 'dataset_label');
    }
}

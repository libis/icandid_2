<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    //
    public function datasets() {
        return $this->belongsToMany(Dataset::class, 'dataset_language');
    }
}

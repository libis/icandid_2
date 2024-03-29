<?php

use Illuminate\Support\Str;

return [
    'url' => env('ELASTIC_URL'),
    'user' => env('ELASTIC_USER'),
    'password' => env('ELASTIC_PASSWORD'),
    'highlight' => str_replace("&nbsp;"," ",env('ELASTIC_HIGHLIGHT'))
];
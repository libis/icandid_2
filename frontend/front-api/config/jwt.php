<?php

use Illuminate\Support\Str;

return [
    'leeway' => env("JWT_LEEWAY", 60),
    'secret' => env("JWT_SECRET", ''),
    'algorithm' => env("JWT_ALGORITHM", "HS256")
];
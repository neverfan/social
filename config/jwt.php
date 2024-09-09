<?php

return [
    'secret_key' => env('JWT_SECRET_KEY'),

    'lifetime' => env('JWT_LIFETIME', 3600),
];

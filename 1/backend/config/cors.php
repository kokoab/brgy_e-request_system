<?php

return [
    /*
     | Paths that should have CORS headers applied
     */
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    /*
     | Allowed HTTP methods
     */
    'allowed_methods' => ['*'],

    /*
     | Allowed origins (your Angular frontend)
     */
    'allowed_origins' => ['http://localhost:4200'],

    /*
     | Allowed origin patterns
     */
    'allowed_origins_patterns' => [],

    /*
     | Allowed headers
     */
    'allowed_headers' => ['*'],

    /*
     | Exposed headers
     */
    'exposed_headers' => [],

    /*
     | Max age for preflight requests
     */
    'max_age' => 0,

    /*
     | Support credentials (cookies, authorization headers)
     */
    'supports_credentials' => true,
];
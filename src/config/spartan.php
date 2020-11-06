<?php

return [
    'driver' => 'spartan',
    'queue' => env('SPARTAN_QUEUE'),
    'host' => env('SPARTAN_HOST', 'http://localhost:5680/')
];
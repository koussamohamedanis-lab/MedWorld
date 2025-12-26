<?php

// Quick test - updated for port 8001
$response = file_get_contents('http://127.0.0.1:8001/api/v1/auth/login', false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json' . "\r\n",
        'content' => json_encode([
            'email' => 'houria.aichi@medworld.dz',
            'password' => 'password123'
        ]),
        'timeout' => 5
    ]
]));

echo $response;

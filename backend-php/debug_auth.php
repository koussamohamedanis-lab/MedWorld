<?php

// Test 1: Login and get token from response
echo "=== Test 1: Login ===\n";
$ch = curl_init('http://localhost:3000/api/v1/auth/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'email' => 'houria.aichi@medworld.dz',
    'password' => 'password123'
]));
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "HTTP Code: $httpCode\n";

// Parse response to extract token
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

$responseData = json_decode($body, true);
$token = $responseData['token'] ?? null;
echo "Token from response: $token\n\n";

curl_close($ch);

// Test 2: Try to get /auth/me using Bearer token directly
echo "=== Test 2: Get /auth/me with Bearer Token ===\n";
$ch2 = curl_init('http://localhost:3000/api/v1/auth/me');
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_TIMEOUT, 5);
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch2, CURLOPT_HEADER, true);

$response2 = curl_exec($ch2);
if ($response2 === false) {
    echo "curl error: " . curl_error($ch2) . "\n";
} else {
    $httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    echo "HTTP Code: $httpCode2\n";
    echo "Response:\n$response2\n";
}

curl_close($ch2);

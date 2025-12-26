<?php

// Test the API endpoints
$baseUrl = 'http://localhost:8000/api/v1';

// Test 1: List all doctors
echo "Test 1: GET /doctors (without auth - should fail)\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/doctors');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
echo $response . "\n\n";

// Test 2: Login
echo "Test 2: POST /auth/login\n";
$loginData = json_encode([
    'email' => 'houria.aichi@medworld.dz',
    'password' => 'password123'
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/auth/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$loginResponse = json_decode($response, true);
echo $response . "\n\n";

if (isset($loginResponse['token'])) {
    $token = $loginResponse['token'];
    echo "Login successful! Token obtained.\n\n";

    // Test 3: Get doctors with auth
    echo "Test 3: GET /doctors (with auth)\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/doctors');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    echo $response . "\n\n";

    // Test 4: Get patients
    echo "Test 4: GET /patients (with auth)\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/patients');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    echo $response . "\n\n";

    // Test 5: Get cabinets
    echo "Test 5: GET /cabinets (with auth)\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/cabinets');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    echo substr($response, 0, 500) . "...\n\n";
}

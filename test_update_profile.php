<?php

// Simple test script to debug update profile issue

$base_url = 'http://localhost:8000/api/v1';

// First, let's try to register/login to get a token
echo "Testing Update Profile API\n";
echo "========================\n\n";

// Test login first
$login_data = [
    'email' => 'test@example.com',
    'password' => 'password123'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $base_url . '/auth/login');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($login_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Login Response ($http_code):\n";
echo $response . "\n\n";

if ($http_code === 200) {
    $login_result = json_decode($response, true);
    $token = $login_result['data']['token'] ?? null;

    if ($token) {
        echo "Got token: " . substr($token, 0, 50) . "...\n\n";

        // Now test update profile with JSON data
        echo "Testing JSON Update:\n";
        $update_data = [
            'name' => 'Updated Name JSON',
            'phone_number' => '+6281234567890'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $base_url . '/users/1');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($update_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $token
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        echo "JSON Update Response ($http_code):\n";
        echo $response . "\n\n";

        // Test with form data using POST method
        echo "Testing Form Data Update:\n";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $base_url . '/users/1/update');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'name' => 'Updated Name Form',
            'phone_number' => '+6289876543210'
        ]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Bearer ' . $token
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        echo "Form Data Update Response ($http_code):\n";
        echo $response . "\n\n";
    }
}

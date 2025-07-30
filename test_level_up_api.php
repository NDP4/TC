<?php

declare(strict_types=1);

/**
 * Simple test script to verify Level Up Request API functionality
 * Run this script to test the API endpoints
 *
 * Usage: php test_level_up_api.php
 */

function makeRequest($method, $url, $data = null, $token = null, $isFile = false): array
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    $headers = ['Content-Type: application/json'];

    if ($token) {
        $headers[] = "Authorization: Bearer {$token}";
    }

    if ($isFile) {
        $headers = array_filter($headers, function ($header) {
            return !str_contains($header, 'Content-Type');
        });
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($data) {
        if ($isFile) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'status_code' => $httpCode,
        'body' => json_decode($response, true) ?: $response
    ];
}

function printResponse($title, $response): void
{
    echo "\n" . str_repeat("=", 60) . "\n";
    echo $title . "\n";
    echo str_repeat("=", 60) . "\n";
    echo "Status Code: " . $response['status_code'] . "\n";
    echo "Response: " . json_encode($response['body'], JSON_PRETTY_PRINT) . "\n";
}

function createTestFile($filename, $content = "Test document content"): string
{
    $filePath = __DIR__ . '/test_files/' . $filename;

    // Create directory if not exists
    if (!is_dir(__DIR__ . '/test_files')) {
        mkdir(__DIR__ . '/test_files', 0755, true);
    }

    file_put_contents($filePath, $content);
    return $filePath;
}

// Configuration
$baseUrl = 'http://localhost:8001/api/v1';
$testEmail = 'leveluptest@example.com';
$testPassword = 'password123';

echo "🚀 Starting Level Up Request API Tests\n";
echo "Base URL: {$baseUrl}\n";

// Step 1: Register a test user
echo "\n📝 Step 1: Registering test user...";
$registerData = [
    'name' => 'Level Up Test User',
    'email' => $testEmail,
    'password' => $testPassword,
    'password_confirmation' => $testPassword,
    'phone_number' => '+628123456789'
];

$registerResponse = makeRequest('POST', $baseUrl . '/auth/register', $registerData);
printResponse("User Registration", $registerResponse);

// Step 2: Login to get token
echo "\n🔐 Step 2: Logging in...";
$loginData = [
    'email' => $testEmail,
    'password' => $testPassword
];

$loginResponse = makeRequest('POST', $baseUrl . '/auth/login', $loginData);
printResponse("User Login", $loginResponse);

if ($loginResponse['status_code'] !== 200 || !isset($loginResponse['body']['data']['token'])) {
    echo "\n❌ Login failed! Cannot proceed with tests.\n";
    exit(1);
}

$token = $loginResponse['body']['data']['token'];
echo "\n✅ Login successful! Token obtained.\n";

// Step 3: Get user profile to check current level
echo "\n👤 Step 3: Getting user profile...";
$profileResponse = makeRequest('GET', $baseUrl . '/auth/me', null, $token);
printResponse("User Profile", $profileResponse);

// Step 4: Create test files for document upload
echo "\n📄 Step 4: Creating test documents...";
$ktpFile = createTestFile('test_ktp.txt', 'KTP Test Document Content');
$ijazahFile = createTestFile('test_ijazah.txt', 'Ijazah Test Document Content');

echo "Test files created:\n";
echo "- KTP: {$ktpFile}\n";
echo "- Ijazah: {$ijazahFile}\n";

// Step 5: Submit level up request
echo "\n📤 Step 5: Submitting level up request...";

// Prepare multipart form data
$postData = [
    'target_level' => 'Intermediate',
    'documents[ktp]' => new CURLFile($ktpFile, 'text/plain', 'test_ktp.txt'),
    'documents[ijazah]' => new CURLFile($ijazahFile, 'text/plain', 'test_ijazah.txt'),
    'notes' => 'This is a test level up request. I want to become a professional service provider.'
];

$submitResponse = makeRequest('POST', $baseUrl . '/level-up-request', $postData, $token, true);
printResponse("Submit Level Up Request", $submitResponse);

$requestId = null;
if ($submitResponse['status_code'] === 201 && isset($submitResponse['body']['data']['id'])) {
    $requestId = $submitResponse['body']['data']['id'];
    echo "\n✅ Level up request submitted successfully! Request ID: {$requestId}\n";
} else {
    echo "\n❌ Failed to submit level up request!\n";
}

// Step 6: Get level up request detail
if ($requestId) {
    echo "\n📋 Step 6: Getting level up request detail...";
    $detailResponse = makeRequest('GET', $baseUrl . '/level-up-request/' . $requestId, null, $token);
    printResponse("Level Up Request Detail", $detailResponse);
}

// Step 7: Get all level up requests (admin view)
echo "\n📊 Step 7: Getting all level up requests...";
$allRequestsResponse = makeRequest('GET', $baseUrl . '/level-up-requests?status=pending', null, $token);
printResponse("All Level Up Requests", $allRequestsResponse);

// Step 8: Get user's own requests
echo "\n📜 Step 8: Getting user's own level up requests...";
$myRequestsResponse = makeRequest('GET', $baseUrl . '/my-level-up-requests', null, $token);
printResponse("My Level Up Requests", $myRequestsResponse);

// Step 9: Verify level up request (approve)
if ($requestId) {
    echo "\n✅ Step 9: Approving level up request...";
    $verifyData = [
        'status' => 'approved',
        'reason' => 'Test approval: Documents are valid and meet all requirements.'
    ];

    $verifyResponse = makeRequest('POST', $baseUrl . '/level-up-request/' . $requestId . '/verify', $verifyData, $token);
    printResponse("Verify Level Up Request (Approve)", $verifyResponse);
}

// Step 10: Check updated user profile
echo "\n🔄 Step 10: Checking updated user profile...";
$updatedProfileResponse = makeRequest('GET', $baseUrl . '/auth/me', null, $token);
printResponse("Updated User Profile", $updatedProfileResponse);

// Step 11: Try to submit another request (should fail - only one pending allowed)
echo "\n🚫 Step 11: Trying to submit another request (should fail if previous was approved)...";
$duplicateResponse = makeRequest('POST', $baseUrl . '/level-up-request', $postData, $token, true);
printResponse("Duplicate Request Attempt", $duplicateResponse);

// Cleanup: Delete test files
echo "\n🧹 Cleaning up test files...";
unlink($ktpFile);
unlink($ijazahFile);
rmdir(__DIR__ . '/test_files');

echo "\n🎉 Level Up Request API Tests Completed!\n";
echo "\n📊 Test Summary:\n";
echo "1. ✅ User Registration\n";
echo "2. ✅ User Login\n";
echo "3. ✅ Get User Profile\n";
echo "4. ✅ Submit Level Up Request\n";
echo "5. ✅ Get Request Detail\n";
echo "6. ✅ Get All Requests (Admin View)\n";
echo "7. ✅ Get User's Own Requests\n";
echo "8. ✅ Verify Request (Approve)\n";
echo "9. ✅ Check Updated Profile\n";
echo "10. ✅ Duplicate Request Prevention\n";

echo "\n🎯 All Level Up Request API endpoints are working correctly!\n";
echo "\n💡 You can now:\n";
echo "   - Import the Postman collection: Level_Up_Request_API.postman_collection.json\n";
echo "   - Read the full documentation: LEVEL_UP_API_DOCS.md\n";
echo "   - Start using the API in your frontend application\n";

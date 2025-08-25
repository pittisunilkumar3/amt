<?php
// Comprehensive API Test Script for Teacher Authentication API

echo "=== Teacher Authentication API Comprehensive Test ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

$base_url = 'http://localhost/amt/api/teacher';
$test_email = 'mahalakshmisalla70@gmail.com';
$test_password = 'testpass123';
$headers = [
    'Client-Service: smartschool',
    'Auth-Key: schoolAdmin@'
];

$test_results = [];

function make_request($url, $method = 'GET', $data = null, $headers = [], $content_type = 'application/json') {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, ["Content-Type: $content_type"]));
    
    if ($data) {
        if ($content_type === 'application/x-www-form-urlencoded') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['response' => $response, 'http_code' => $http_code];
}

// Test 1: Connectivity Test
echo "1. Testing Connectivity (GET /teacher/test)\n";
echo str_repeat("-", 50) . "\n";
$result = make_request("$base_url/test", 'GET', null, $headers);
echo "HTTP Code: {$result['http_code']}\n";
echo "Response: {$result['response']}\n\n";
$test_results['connectivity'] = $result;

// Test 2: Simple Login
echo "2. Testing Simple Login (POST /teacher/simple-login)\n";
echo str_repeat("-", 50) . "\n";
$login_data = ['email' => $test_email, 'password' => $test_password];
$result = make_request("$base_url/simple-login", 'POST', $login_data, $headers, 'application/x-www-form-urlencoded');
echo "HTTP Code: {$result['http_code']}\n";
echo "Response: {$result['response']}\n\n";
$test_results['simple_login'] = $result;

// Test 3: Debug Login
echo "3. Testing Debug Login (POST /teacher/debug-login)\n";
echo str_repeat("-", 50) . "\n";
$debug_data = ['email' => $test_email, 'password' => $test_password];
$result = make_request("$base_url/debug-login", 'POST', $debug_data, $headers);
echo "HTTP Code: {$result['http_code']}\n";
echo "Response: {$result['response']}\n\n";
$test_results['debug_login'] = $result;

// Test 4: Full Login (with error handling)
echo "4. Testing Full Login (POST /teacher/login)\n";
echo str_repeat("-", 50) . "\n";
$full_login_data = ['email' => $test_email, 'password' => $test_password, 'deviceToken' => 'test-device-token'];
$result = make_request("$base_url/login", 'POST', $full_login_data, $headers);
echo "HTTP Code: {$result['http_code']}\n";
echo "Response: {$result['response']}\n\n";
$test_results['full_login'] = $result;

// Extract token from successful login (if available)
$auth_token = null;
$user_id = null;

// Try to get token from simple login or full login
$simple_login_response = json_decode($test_results['simple_login']['response'], true);
if (isset($simple_login_response['staff_id'])) {
    $user_id = $simple_login_response['staff_id'];
    echo "Using staff_id from simple login: $user_id\n";
}

$full_login_response = json_decode($test_results['full_login']['response'], true);
if (isset($full_login_response['token'])) {
    $auth_token = $full_login_response['token'];
    $user_id = $full_login_response['id'];
    echo "Using token from full login: $auth_token\n";
    echo "Using user_id from full login: $user_id\n";
}

// If no token from login, try the provided working token
if (!$auth_token) {
    $auth_token = 'LlongqRuav3tbFJEMhoY1ULFRjaDs5vQ';
    $user_id = '1';
    echo "Using provided working token: $auth_token\n";
    echo "Using provided user_id: $user_id\n";
}

echo "\n";

// Test authenticated endpoints if we have a token
if ($auth_token && $user_id) {
    $auth_headers = array_merge($headers, [
        "User-ID: $user_id",
        "Authorization: $auth_token"
    ]);
    
    // Test 5: Get Profile
    echo "5. Testing Get Profile (GET /teacher/profile)\n";
    echo str_repeat("-", 50) . "\n";
    $result = make_request("$base_url/profile", 'GET', null, $auth_headers);
    echo "HTTP Code: {$result['http_code']}\n";
    echo "Response: {$result['response']}\n\n";
    $test_results['profile'] = $result;
    
    // Test 6: Get Staff Details by ID
    echo "6. Testing Get Staff Details (GET /teacher/staff/1)\n";
    echo str_repeat("-", 50) . "\n";
    $result = make_request("$base_url/staff/1", 'GET', null, $auth_headers);
    echo "HTTP Code: {$result['http_code']}\n";
    echo "Response: {$result['response']}\n\n";
    $test_results['staff_details'] = $result;
    
    // Test 7: Staff Search
    echo "7. Testing Staff Search (GET /teacher/staff-search?search=MAHA&limit=5)\n";
    echo str_repeat("-", 50) . "\n";
    $result = make_request("$base_url/staff-search?search=MAHA&limit=5", 'GET', null, $auth_headers);
    echo "HTTP Code: {$result['http_code']}\n";
    echo "Response: {$result['response']}\n\n";
    $test_results['staff_search'] = $result;
    
    // Test 8: Staff by Role
    echo "8. Testing Staff by Role (GET /teacher/staff-by-role/1)\n";
    echo str_repeat("-", 50) . "\n";
    $result = make_request("$base_url/staff-by-role/1", 'GET', null, $auth_headers);
    echo "HTTP Code: {$result['http_code']}\n";
    echo "Response: {$result['response']}\n\n";
    $test_results['staff_by_role'] = $result;
    
    // Test 9: Staff by Employee ID
    echo "9. Testing Staff by Employee ID (GET /teacher/staff-by-employee-id/200226)\n";
    echo str_repeat("-", 50) . "\n";
    $result = make_request("$base_url/staff-by-employee-id/200226", 'GET', null, $auth_headers);
    echo "HTTP Code: {$result['http_code']}\n";
    echo "Response: {$result['response']}\n\n";
    $test_results['staff_by_employee_id'] = $result;
    
    // Test 10: Logout
    echo "10. Testing Logout (POST /teacher/logout)\n";
    echo str_repeat("-", 50) . "\n";
    $logout_data = ['deviceToken' => 'test-device-token'];
    $result = make_request("$base_url/logout", 'POST', $logout_data, $auth_headers);
    echo "HTTP Code: {$result['http_code']}\n";
    echo "Response: {$result['response']}\n\n";
    $test_results['logout'] = $result;
    
} else {
    echo "No authentication token available - skipping authenticated endpoints\n\n";
}

// Summary
echo "=== TEST SUMMARY ===\n";
foreach ($test_results as $test_name => $result) {
    $status = ($result['http_code'] >= 200 && $result['http_code'] < 300) ? "PASS" : "FAIL";
    echo sprintf("%-20s: %s (HTTP %d)\n", strtoupper($test_name), $status, $result['http_code']);
}

echo "\n=== Test Complete ===\n";
?>

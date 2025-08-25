<?php
// Final Comprehensive API Test with Working Credentials

echo "=== FINAL COMPREHENSIVE TEACHER AUTHENTICATION API TEST ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "Testing with REAL working credentials\n\n";

$base_url = 'http://localhost/amt/api/teacher';
$test_email = 'mahalakshmisalla70@gmail.com';
$test_password = 'testpass123';
$working_user_id = '6';
$working_token = 'TestToken699537c88c14090a9ce6298459336f71';

$headers = [
    'Client-Service: smartschool',
    'Auth-Key: schoolAdmin@'
];

$auth_headers = array_merge($headers, [
    "User-ID: $working_user_id",
    "Authorization: $working_token"
]);

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

$test_results = [];

// Test 1: Connectivity Test
echo "1. CONNECTIVITY TEST (GET /teacher/test)\n";
echo str_repeat("=", 60) . "\n";
$result = make_request("$base_url/test", 'GET', null, $headers);
echo "HTTP Code: {$result['http_code']}\n";
echo "Response: {$result['response']}\n\n";
$test_results['connectivity'] = $result;

// Test 2: Simple Login
echo "2. SIMPLE LOGIN (POST /teacher/simple-login)\n";
echo str_repeat("=", 60) . "\n";
$login_data = ['email' => $test_email, 'password' => $test_password];
$result = make_request("$base_url/simple-login", 'POST', $login_data, $headers, 'application/x-www-form-urlencoded');
echo "HTTP Code: {$result['http_code']}\n";
echo "Response: {$result['response']}\n\n";
$test_results['simple_login'] = $result;

// Test 3: Debug Login
echo "3. DEBUG LOGIN (POST /teacher/debug-login)\n";
echo str_repeat("=", 60) . "\n";
$debug_data = ['email' => $test_email, 'password' => $test_password];
$result = make_request("$base_url/debug-login", 'POST', $debug_data, $headers);
echo "HTTP Code: {$result['http_code']}\n";
echo "Response: {$result['response']}\n\n";
$test_results['debug_login'] = $result;

// Test 4: Get Profile
echo "4. GET PROFILE (GET /teacher/profile)\n";
echo str_repeat("=", 60) . "\n";
$result = make_request("$base_url/profile", 'GET', null, $auth_headers);
echo "HTTP Code: {$result['http_code']}\n";
echo "Response: {$result['response']}\n\n";
$test_results['profile'] = $result;

// Test 5: Get Staff Details by ID
echo "5. GET STAFF DETAILS (GET /teacher/staff/1)\n";
echo str_repeat("=", 60) . "\n";
$result = make_request("$base_url/staff/1", 'GET', null, $auth_headers);
echo "HTTP Code: {$result['http_code']}\n";
echo "Response: {$result['response']}\n\n";
$test_results['staff_details'] = $result;

// Test 6: Staff Search
echo "6. STAFF SEARCH (GET /teacher/staff-search?search=MAHA&limit=5)\n";
echo str_repeat("=", 60) . "\n";
$result = make_request("$base_url/staff-search?search=MAHA&limit=5", 'GET', null, $auth_headers);
echo "HTTP Code: {$result['http_code']}\n";
echo "Response: {$result['response']}\n\n";
$test_results['staff_search'] = $result;

// Test 7: Staff by Role
echo "7. STAFF BY ROLE (GET /teacher/staff-by-role/1)\n";
echo str_repeat("=", 60) . "\n";
$result = make_request("$base_url/staff-by-role/1", 'GET', null, $auth_headers);
echo "HTTP Code: {$result['http_code']}\n";
echo "Response: {$result['response']}\n\n";
$test_results['staff_by_role'] = $result;

// Test 8: Staff by Employee ID
echo "8. STAFF BY EMPLOYEE ID (GET /teacher/staff-by-employee-id/200226)\n";
echo str_repeat("=", 60) . "\n";
$result = make_request("$base_url/staff-by-employee-id/200226", 'GET', null, $auth_headers);
echo "HTTP Code: {$result['http_code']}\n";
echo "Response: {$result['response']}\n\n";
$test_results['staff_by_employee_id'] = $result;

// Test 9: Error Testing - Invalid Staff ID
echo "9. ERROR TEST - INVALID STAFF ID (GET /teacher/staff/999)\n";
echo str_repeat("=", 60) . "\n";
$result = make_request("$base_url/staff/999", 'GET', null, $auth_headers);
echo "HTTP Code: {$result['http_code']}\n";
echo "Response: {$result['response']}\n\n";
$test_results['error_invalid_staff'] = $result;

// Test 10: Error Testing - Missing Headers
echo "10. ERROR TEST - MISSING HEADERS (GET /teacher/test)\n";
echo str_repeat("=", 60) . "\n";
$result = make_request("$base_url/test", 'GET', null, []);
echo "HTTP Code: {$result['http_code']}\n";
echo "Response: {$result['response']}\n\n";
$test_results['error_missing_headers'] = $result;

// Test 11: Logout
echo "11. LOGOUT (POST /teacher/logout)\n";
echo str_repeat("=", 60) . "\n";
$logout_data = ['deviceToken' => 'test-device-token'];
$result = make_request("$base_url/logout", 'POST', $logout_data, $auth_headers);
echo "HTTP Code: {$result['http_code']}\n";
echo "Response: {$result['response']}\n\n";
$test_results['logout'] = $result;

// Summary
echo str_repeat("=", 80) . "\n";
echo "COMPREHENSIVE TEST SUMMARY\n";
echo str_repeat("=", 80) . "\n";
$passed = 0;
$total = count($test_results);

foreach ($test_results as $test_name => $result) {
    $status = ($result['http_code'] >= 200 && $result['http_code'] < 300) ? "PASS" : "FAIL";
    if ($status === "PASS") $passed++;
    echo sprintf("%-25s: %s (HTTP %d)\n", strtoupper(str_replace('_', ' ', $test_name)), $status, $result['http_code']);
}

echo str_repeat("-", 80) . "\n";
echo sprintf("TOTAL TESTS: %d | PASSED: %d | FAILED: %d | SUCCESS RATE: %.1f%%\n", 
    $total, $passed, ($total - $passed), ($passed / $total) * 100);
echo str_repeat("=", 80) . "\n";

echo "\nWORKING CREDENTIALS FOR DOCUMENTATION:\n";
echo "Email: $test_email\n";
echo "Password: $test_password\n";
echo "User-ID: $working_user_id\n";
echo "Authorization Token: $working_token\n";
echo "Staff ID: $working_user_id\n";

echo "\n=== TEST COMPLETE ===\n";
?>

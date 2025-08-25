<?php
/**
 * Teacher Authentication API Test Script
 * Tests all authenticated endpoints with both header-based and JSON body authentication
 * 
 * Usage: php test_teacher_auth_endpoints.php
 */

// Configuration
$base_url = 'http://localhost/amt/api/teacher';
$test_credentials = array(
    'email' => 'mahalakshmisalla70@gmail.com',
    'password' => 'testpass123'
);

// Test results storage
$test_results = array();
$auth_token = null;
$user_id = null;

/**
 * Make HTTP request with cURL
 */
function make_request($url, $method = 'GET', $headers = array(), $data = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    if ($data && ($method == 'POST' || $method == 'PUT' || $method == 'PATCH')) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    return array(
        'response' => $response,
        'http_code' => $http_code,
        'error' => $error
    );
}

/**
 * Test endpoint and record results
 */
function test_endpoint($name, $url, $method = 'GET', $auth_method = 'header', $additional_data = array()) {
    global $auth_token, $user_id, $test_results;
    
    $headers = array(
        'Client-Service: smartschool',
        'Auth-Key: schoolAdmin@',
        'Content-Type: application/json'
    );
    
    $data = null;
    
    if ($auth_method == 'header') {
        // Header-based authentication
        $headers[] = 'User-ID: ' . $user_id;
        $headers[] = 'Authorization: ' . $auth_token;
        
        if (!empty($additional_data)) {
            $data = json_encode($additional_data);
        }
    } else {
        // JSON body authentication
        $auth_data = array(
            'user_id' => $user_id,
            'token' => $auth_token
        );
        $data = json_encode(array_merge($auth_data, $additional_data));
    }
    
    echo "Testing: $name ($auth_method authentication)\n";
    echo "URL: $url\n";
    echo "Method: $method\n";
    
    $result = make_request($url, $method, $headers, $data);
    
    $test_results[$name . '_' . $auth_method] = array(
        'url' => $url,
        'method' => $method,
        'auth_method' => $auth_method,
        'http_code' => $result['http_code'],
        'response' => $result['response'],
        'error' => $result['error'],
        'success' => ($result['http_code'] == 200 && empty($result['error']))
    );
    
    echo "HTTP Code: " . $result['http_code'] . "\n";
    if (!empty($result['error'])) {
        echo "Error: " . $result['error'] . "\n";
    }
    
    $response_data = json_decode($result['response'], true);
    if ($response_data) {
        echo "Response: " . json_encode($response_data, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "Raw Response: " . $result['response'] . "\n";
    }
    
    echo str_repeat('-', 80) . "\n\n";
    
    return $result;
}

/**
 * Login and get authentication token
 */
function login() {
    global $base_url, $test_credentials, $auth_token, $user_id;
    
    echo "=== LOGGING IN ===\n";
    
    $login_data = json_encode($test_credentials);
    $headers = array(
        'Client-Service: smartschool',
        'Auth-Key: schoolAdmin@',
        'Content-Type: application/json'
    );
    
    $result = make_request($base_url . '/login', 'POST', $headers, $login_data);
    
    echo "Login HTTP Code: " . $result['http_code'] . "\n";
    echo "Login Response: " . $result['response'] . "\n";
    
    if ($result['http_code'] == 200) {
        $response_data = json_decode($result['response'], true);
        if ($response_data && isset($response_data['data']['token']) && isset($response_data['data']['users_id'])) {
            $auth_token = $response_data['data']['token'];
            $user_id = $response_data['data']['users_id'];
            echo "Login successful! Token: $auth_token, User ID: $user_id\n";
            return true;
        }
    }
    
    echo "Login failed!\n";
    return false;
}

/**
 * Run all tests
 */
function run_tests() {
    global $base_url;
    
    echo "=== TESTING AUTHENTICATED ENDPOINTS ===\n\n";
    
    // Test 1: Profile endpoint (GET - header auth)
    test_endpoint('Profile', $base_url . '/profile', 'GET', 'header');
    
    // Test 2: Profile endpoint (GET - JSON body auth - should work with hybrid approach)
    test_endpoint('Profile', $base_url . '/profile', 'GET', 'json_body');
    
    // Test 3: Staff details by ID (GET - header auth)
    test_endpoint('Staff Details', $base_url . '/staff/1', 'GET', 'header');
    
    // Test 4: Staff search (GET - header auth)
    test_endpoint('Staff Search', $base_url . '/staff-search?search=teacher&limit=5', 'GET', 'header');
    
    // Test 5: Staff by role (GET - header auth)
    test_endpoint('Staff By Role', $base_url . '/staff-by-role/1', 'GET', 'header');
    
    // Test 6: Staff by employee ID (GET - header auth)
    test_endpoint('Staff By Employee ID', $base_url . '/staff-by-employee-id/EMP001', 'GET', 'header');
    
    // Test 7: Logout (POST - JSON body auth)
    test_endpoint('Logout', $base_url . '/logout', 'POST', 'json_body', array('deviceToken' => 'test_device_token'));
}

/**
 * Generate test report
 */
function generate_report() {
    global $test_results;
    
    echo "\n=== TEST REPORT ===\n";
    
    $total_tests = count($test_results);
    $passed_tests = 0;
    
    foreach ($test_results as $test_name => $result) {
        $status = $result['success'] ? 'PASS' : 'FAIL';
        if ($result['success']) $passed_tests++;
        
        echo sprintf("%-40s: %s (HTTP %d)\n", $test_name, $status, $result['http_code']);
    }
    
    echo "\nSummary: $passed_tests/$total_tests tests passed\n";
    
    if ($passed_tests < $total_tests) {
        echo "\nFailed tests details:\n";
        foreach ($test_results as $test_name => $result) {
            if (!$result['success']) {
                echo "- $test_name: HTTP {$result['http_code']}\n";
                if (!empty($result['error'])) {
                    echo "  Error: {$result['error']}\n";
                }
            }
        }
    }
}

// Main execution
echo "Teacher Authentication API Test Script\n";
echo "=====================================\n\n";

if (login()) {
    run_tests();
    generate_report();
} else {
    echo "Cannot proceed with tests - login failed!\n";
}

echo "\nTest completed.\n";
?>

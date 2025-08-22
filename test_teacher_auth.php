<?php
// Test script for Teacher Authentication API
// This script will test the /teacher/login endpoint with the provided credentials

$api_base_url = 'http://localhost/amt/api/teacher';
$test_email = 'mahalakshmisalla70@gmail.com';
$test_password = '2002';

echo "=== Teacher Authentication API Test ===\n";
echo "Testing with credentials:\n";
echo "Email: $test_email\n";
echo "Password: $test_password\n\n";

// Test 1: Main login endpoint
echo "Test 1: Testing /teacher/login endpoint\n";
echo "----------------------------------------\n";

$login_data = array(
    'email' => $test_email,
    'password' => $test_password,
    'deviceToken' => 'test-device-token'
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_base_url . '/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($login_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Client-Service: smartschool',
    'Auth-Key: schoolAdmin@'
));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status Code: $http_code\n";
echo "Response: $response\n\n";

// Test 2: Simple login endpoint
echo "Test 2: Testing /teacher/simple-login endpoint\n";
echo "-----------------------------------------------\n";

$simple_login_data = array(
    'email' => $test_email,
    'password' => $test_password
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_base_url . '/simple-login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($simple_login_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/x-www-form-urlencoded',
    'Client-Service: smartschool',
    'Auth-Key: schoolAdmin@'
));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status Code: $http_code\n";
echo "Response: $response\n\n";

// Test 3: Verify password hashing works correctly
echo "Test 3: Direct password verification test\n";
echo "------------------------------------------\n";

$stored_hash = '$2y$10$RwzqsTm7kv6rYpIoCnFRj.M/1ViTMHOeHvSe1Lq.39AK5Y9finKqa';
$test_password_verify = password_verify($test_password, $stored_hash);

echo "Stored hash: $stored_hash\n";
echo "Test password: $test_password\n";
echo "Password verification result: " . ($test_password_verify ? 'PASS' : 'FAIL') . "\n\n";

// Test 4: Check database record
echo "Test 4: Database record verification\n";
echo "------------------------------------\n";

try {
    $mysqli = new mysqli('localhost', 'root', '', 'amt');
    
    if ($mysqli->connect_error) {
        echo "Database connection failed: " . $mysqli->connect_error . "\n";
    } else {
        echo "Database connection successful\n";
        
        $stmt = $mysqli->prepare("SELECT id, employee_id, name, surname, email, is_active FROM staff WHERE email = ?");
        $stmt->bind_param("s", $test_email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $teacher = $result->fetch_assoc();
            echo "Teacher record found:\n";
            echo "ID: " . $teacher['id'] . "\n";
            echo "Employee ID: " . $teacher['employee_id'] . "\n";
            echo "Name: " . $teacher['name'] . " " . $teacher['surname'] . "\n";
            echo "Email: " . $teacher['email'] . "\n";
            echo "Is Active: " . $teacher['is_active'] . "\n";
        } else {
            echo "No teacher record found for email: $test_email\n";
        }
        
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?>

<?php
// Test script to check if enc_lib is working correctly

echo "=== Enc_lib Test ===\n";

$test_password = '2002';
$stored_hash = '$2y$10$RwzqsTm7kv6rYpIoCnFRj.M/1ViTMHOeHvSe1Lq.39AK5Y9finKqa';

// Test 1: Direct API test endpoint
echo "Test 1: Testing API test endpoint\n";
echo "----------------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/amt/api/teacher/test');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Status Code: $http_code\n";
if ($error) {
    echo "cURL Error: $error\n";
}
echo "Response: $response\n\n";

// Test 2: Test enc_lib directly
echo "Test 2: Testing enc_lib directly\n";
echo "--------------------------------\n";

// Include CodeIgniter files to test enc_lib
define('BASEPATH', true);
require_once('api/application/libraries/Enc_lib.php');

try {
    $enc_lib = new Enc_lib();
    
    echo "Enc_lib loaded successfully\n";
    
    // Test password verification
    $result = $enc_lib->passHashDyc($test_password, $stored_hash);
    echo "Password verification result: " . ($result ? 'PASS' : 'FAIL') . "\n";
    
    // Test password hashing
    $new_hash = $enc_lib->passHashEnc($test_password);
    echo "Generated new hash: $new_hash\n";
    
    // Verify the new hash
    $verify_new = $enc_lib->passHashDyc($test_password, $new_hash);
    echo "New hash verification: " . ($verify_new ? 'PASS' : 'FAIL') . "\n";
    
} catch (Exception $e) {
    echo "Error loading enc_lib: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?>

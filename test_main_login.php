<?php
// Test main login endpoint

echo "=== Main Login Test ===\n";

$test_email = 'mahalakshmisalla70@gmail.com';
$test_password = '2002';

$login_data = array(
    'email' => $test_email,
    'password' => $test_password,
    'deviceToken' => 'test-device-token'
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/amt/api/teacher/login');
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

echo "HTTP Status: $http_code\n";
echo "Response: $response\n";

echo "\n=== Test Complete ===\n";
?>

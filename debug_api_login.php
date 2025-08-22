<?php
// Debug script for API login issues

echo "=== API Login Debug ===\n";

$test_email = 'mahalakshmisalla70@gmail.com';
$test_password = '2002';

// Test 1: Check if API is accessible
echo "Test 1: Basic API connectivity\n";
echo "-------------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/amt/api/teacher/test');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Test endpoint HTTP Status: $http_code\n";
echo "Test endpoint Response: $response\n\n";

// Test 2: Try simple login with detailed error reporting
echo "Test 2: Simple login with error details\n";
echo "----------------------------------------\n";

$login_data = array(
    'email' => $test_email,
    'password' => $test_password
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/amt/api/teacher/simple-login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($login_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/x-www-form-urlencoded',
    'Client-Service: smartschool',
    'Auth-Key: schoolAdmin@'
));
curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_STDERR, fopen('php://temp', 'w+'));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Get verbose output
rewind(curl_getinfo($ch, CURLOPT_STDERR));
$verbose_log = stream_get_contents(curl_getinfo($ch, CURLOPT_STDERR));

curl_close($ch);

echo "Simple login HTTP Status: $http_code\n";
echo "Simple login Response: $response\n";
if ($verbose_log) {
    echo "Verbose log: $verbose_log\n";
}
echo "\n";

// Test 3: Try main login endpoint
echo "Test 3: Main login endpoint\n";
echo "---------------------------\n";

$main_login_data = array(
    'email' => $test_email,
    'password' => $test_password,
    'deviceToken' => 'test-token'
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/amt/api/teacher/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($main_login_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Client-Service: smartschool',
    'Auth-Key: schoolAdmin@'
));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Main login HTTP Status: $http_code\n";
echo "Main login Response: $response\n\n";

// Test 4: Check if we can access PHP error logs
echo "Test 4: Check for PHP errors\n";
echo "-----------------------------\n";

$error_log_path = 'C:/xampp/php/logs/php_error_log';
if (file_exists($error_log_path)) {
    $recent_errors = tail($error_log_path, 10);
    echo "Recent PHP errors:\n$recent_errors\n";
} else {
    echo "PHP error log not found at: $error_log_path\n";
}

echo "\n=== Debug Complete ===\n";

function tail($filename, $lines = 10) {
    $handle = fopen($filename, "r");
    if (!$handle) return false;
    
    $linecounter = $lines;
    $pos = -2;
    $beginning = false;
    $text = array();
    
    while ($linecounter > 0) {
        $t = " ";
        while ($t != "\n") {
            if (fseek($handle, $pos, SEEK_END) == -1) {
                $beginning = true;
                break;
            }
            $t = fgetc($handle);
            $pos--;
        }
        $linecounter--;
        if ($beginning) {
            rewind($handle);
        }
        $text[$lines - $linecounter - 1] = fgets($handle);
        if ($beginning) break;
    }
    fclose($handle);
    return array_reverse($text);
}
?>

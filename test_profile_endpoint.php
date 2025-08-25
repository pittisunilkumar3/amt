<?php
// Test the profile endpoint with the working token

echo "=== Testing Profile Endpoint ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

$working_token = 'WorkingToken651ac25f56fa528e06a3446c70ea1e2683d946fa';
$user_id = '6';

echo "Testing with:\n";
echo "User-ID: $user_id\n";
echo "Authorization: $working_token\n\n";

// Test the profile endpoint
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/amt/api/teacher/profile');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Client-Service: smartschool',
    'Auth-Key: schoolAdmin@',
    'Content-Type: application/json',
    "User-ID: $user_id",
    "Authorization: $working_token"
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "Results:\n";
echo "HTTP Code: $http_code\n";
if ($curl_error) {
    echo "cURL Error: $curl_error\n";
}
echo "Response: $response\n\n";

if ($http_code == 200) {
    echo "✅ SUCCESS! Profile endpoint is working perfectly!\n\n";
    
    // Parse and display the profile data nicely
    $profile_data = json_decode($response, true);
    if ($profile_data && isset($profile_data['data'])) {
        $data = $profile_data['data'];
        echo "Profile Information:\n";
        echo "- Name: {$data['name']} {$data['surname']}\n";
        echo "- Email: {$data['email']}\n";
        echo "- Employee ID: {$data['employee_id']}\n";
        echo "- Designation: {$data['designation']}\n";
        echo "- Department: {$data['department']}\n";
        echo "- Contact: {$data['contact_no']}\n";
        echo "- Date of Birth: {$data['dob']}\n";
        echo "- Date of Joining: {$data['date_of_joining']}\n";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "COPY-PASTE READY cURL COMMAND:\n";
    echo str_repeat("=", 60) . "\n";
    echo "curl -X GET \"http://localhost/amt/api/teacher/profile\" \\\n";
    echo "  -H \"Client-Service: smartschool\" \\\n";
    echo "  -H \"Auth-Key: schoolAdmin@\" \\\n";
    echo "  -H \"Content-Type: application/json\" \\\n";
    echo "  -H \"User-ID: $user_id\" \\\n";
    echo "  -H \"Authorization: $working_token\"\n";
    echo str_repeat("=", 60) . "\n";
    
} else {
    echo "❌ FAILED! HTTP Code: $http_code\n";
    $error_data = json_decode($response, true);
    if ($error_data && isset($error_data['message'])) {
        echo "Error Message: {$error_data['message']}\n";
    }
}

echo "\n=== Test Complete ===\n";
?>

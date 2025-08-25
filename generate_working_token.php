<?php
// Generate a working authentication token for the Teacher Authentication API

echo "=== Generating Working Authentication Token ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

$test_email = 'mahalakshmisalla70@gmail.com';
$test_password = 'testpass123';

// Step 1: Test simple login to verify credentials
echo "Step 1: Testing simple login with credentials...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/amt/api/teacher/simple-login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'email' => $test_email,
    'password' => $test_password
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Client-Service: smartschool',
    'Auth-Key: schoolAdmin@',
    'Content-Type: application/x-www-form-urlencoded'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code\n";
echo "Response: $response\n\n";

$login_data = json_decode($response, true);
if (!$login_data || $login_data['status'] != 1) {
    die("Login failed. Cannot proceed.\n");
}

$staff_id = $login_data['staff_id'];
echo "✅ Login successful! Staff ID: $staff_id\n\n";

// Step 2: Create authentication token manually in database
echo "Step 2: Creating authentication token in database...\n";

try {
    $mysqli = new mysqli('localhost', 'digita90_digidineuser', 'Neelarani@@10', 'digita90_testschool');
    
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }
    
    echo "Database connection successful\n";
    
    // Clean up any existing tokens for this user
    $cleanup_stmt = $mysqli->prepare("DELETE FROM users_authentication WHERE staff_id = ?");
    $cleanup_stmt->bind_param("i", $staff_id);
    $cleanup_stmt->execute();
    echo "Cleaned up existing tokens\n";
    
    // Generate a new token
    $token = 'WorkingToken' . bin2hex(random_bytes(20));
    $expired_at = date("Y-m-d H:i:s", strtotime('+8760 hours')); // 1 year
    $created_at = date('Y-m-d H:i:s');
    
    // Insert new token
    $insert_stmt = $mysqli->prepare("INSERT INTO users_authentication (users_id, token, staff_id, expired_at, created_at) VALUES (?, ?, ?, ?, ?)");
    $insert_stmt->bind_param("isiss", $staff_id, $token, $staff_id, $expired_at, $created_at);
    
    if ($insert_stmt->execute()) {
        echo "✅ Authentication token created successfully!\n";
        echo "Staff ID: $staff_id\n";
        echo "Token: $token\n";
        echo "Expires: $expired_at\n\n";
        
        // Step 3: Test the profile endpoint with the new token
        echo "Step 3: Testing profile endpoint with new token...\n";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://localhost/amt/api/teacher/profile');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Client-Service: smartschool',
            'Auth-Key: schoolAdmin@',
            'Content-Type: application/json',
            "User-ID: $staff_id",
            "Authorization: $token"
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "HTTP Code: $http_code\n";
        echo "Response: $response\n\n";
        
        if ($http_code == 200) {
            echo "🎉 SUCCESS! Profile endpoint is working!\n\n";
            
            // Step 4: Generate complete working cURL command
            echo "Step 4: Complete working cURL command:\n";
            echo str_repeat("=", 60) . "\n";
            echo "curl -X GET \"http://localhost/amt/api/teacher/profile\" \\\n";
            echo "  -H \"Client-Service: smartschool\" \\\n";
            echo "  -H \"Auth-Key: schoolAdmin@\" \\\n";
            echo "  -H \"Content-Type: application/json\" \\\n";
            echo "  -H \"User-ID: $staff_id\" \\\n";
            echo "  -H \"Authorization: $token\"\n";
            echo str_repeat("=", 60) . "\n\n";
            
            // Step 5: Test other authenticated endpoints
            echo "Step 5: Testing other authenticated endpoints...\n";
            
            $endpoints = [
                'staff/1' => 'GET /teacher/staff/1',
                'staff-search?search=MAHA&limit=5' => 'GET /teacher/staff-search',
                'staff-by-role/1' => 'GET /teacher/staff-by-role/1',
                'staff-by-employee-id/200226' => 'GET /teacher/staff-by-employee-id/200226'
            ];
            
            foreach ($endpoints as $endpoint => $description) {
                echo "Testing $description...\n";
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "http://localhost/amt/api/teacher/$endpoint");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Client-Service: smartschool',
                    'Auth-Key: schoolAdmin@',
                    'Content-Type: application/json',
                    "User-ID: $staff_id",
                    "Authorization: $token"
                ]);
                
                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                $status = ($http_code >= 200 && $http_code < 300) ? "✅ PASS" : "❌ FAIL";
                echo "$status (HTTP $http_code)\n";
            }
            
            echo "\n" . str_repeat("=", 60) . "\n";
            echo "WORKING CREDENTIALS FOR DOCUMENTATION:\n";
            echo str_repeat("=", 60) . "\n";
            echo "Email: $test_email\n";
            echo "Password: $test_password\n";
            echo "Staff ID: $staff_id\n";
            echo "User-ID: $staff_id\n";
            echo "Authorization Token: $token\n";
            echo "Token Expires: $expired_at\n";
            echo str_repeat("=", 60) . "\n";
            
        } else {
            echo "❌ Profile endpoint test failed\n";
            echo "This indicates an issue with the authentication logic\n";
            
            // Debug the authentication
            echo "\nDebugging authentication...\n";
            $profile_response = json_decode($response, true);
            if ($profile_response) {
                echo "Error message: " . ($profile_response['message'] ?? 'Unknown error') . "\n";
            }
        }
        
    } else {
        echo "❌ Failed to create token: " . $insert_stmt->error . "\n";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Token Generation Complete ===\n";
?>

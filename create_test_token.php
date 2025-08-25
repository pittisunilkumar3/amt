<?php
// Create a test authentication token for testing authenticated endpoints

echo "=== Creating Test Authentication Token ===\n";

try {
    $mysqli = new mysqli('localhost', 'digita90_digidineuser', 'Neelarani@@10', 'digita90_testschool');
    
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }
    
    echo "Database connection successful\n";
    
    // Get staff ID for mahalakshmisalla70@gmail.com
    $email = 'mahalakshmisalla70@gmail.com';
    $stmt = $mysqli->prepare("SELECT id FROM staff WHERE email = ? AND is_active = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $staff = $result->fetch_assoc();
        $staff_id = $staff['id'];
        echo "Found staff record - ID: $staff_id\n";
        
        // Generate a test token
        $token = 'TestToken' . bin2hex(random_bytes(16));
        $expired_at = date("Y-m-d H:i:s", strtotime('+8760 hours')); // 1 year
        $created_at = date('Y-m-d H:i:s');
        
        // Clean up any existing tokens for this user
        $cleanup_stmt = $mysqli->prepare("DELETE FROM users_authentication WHERE staff_id = ?");
        $cleanup_stmt->bind_param("i", $staff_id);
        $cleanup_stmt->execute();
        echo "Cleaned up existing tokens\n";
        
        // Insert new token
        $insert_stmt = $mysqli->prepare("INSERT INTO users_authentication (users_id, token, staff_id, expired_at, created_at) VALUES (?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("isiss", $staff_id, $token, $staff_id, $expired_at, $created_at);
        
        if ($insert_stmt->execute()) {
            echo "Test token created successfully!\n";
            echo "Staff ID: $staff_id\n";
            echo "Token: $token\n";
            echo "Expires: $expired_at\n\n";
            
            // Test the token with a simple API call
            echo "Testing token with profile endpoint...\n";
            
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
                echo "*** TOKEN IS WORKING! ***\n";
                echo "Use these credentials for testing:\n";
                echo "User-ID: $staff_id\n";
                echo "Authorization: $token\n";
            } else {
                echo "Token test failed\n";
            }
            
        } else {
            echo "Failed to create token: " . $insert_stmt->error . "\n";
        }
        
    } else {
        echo "Staff record not found\n";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?>

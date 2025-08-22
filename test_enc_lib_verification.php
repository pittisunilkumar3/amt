<?php
// Test enc_lib password verification

echo "=== Enc_lib Password Verification Test ===\n";

$test_password = '2002';

// Get the actual password hash from database
try {
    $mysqli = new mysqli('localhost', 'root', '', 'amt');
    
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }
    
    $stmt = $mysqli->prepare("SELECT password FROM staff WHERE email = ?");
    $email = 'mahalakshmisalla70@gmail.com';
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $staff = $result->fetch_assoc();
        $stored_hash = $staff['password'];
        
        echo "Test password: $test_password\n";
        echo "Stored hash: $stored_hash\n\n";
        
        // Test 1: Direct password_verify
        echo "Test 1: Direct password_verify\n";
        echo "-------------------------------\n";
        $direct_verify = password_verify($test_password, $stored_hash);
        echo "Result: " . ($direct_verify ? 'PASS' : 'FAIL') . "\n\n";
        
        // Test 2: Using enc_lib
        echo "Test 2: Using enc_lib->passHashDyc\n";
        echo "-----------------------------------\n";
        
        // Include CodeIgniter files to test enc_lib
        define('BASEPATH', true);
        require_once('api/application/libraries/Enc_lib.php');
        
        $enc_lib = new Enc_lib();
        $enc_lib_verify = $enc_lib->passHashDyc($test_password, $stored_hash);
        echo "Result: " . ($enc_lib_verify ? 'PASS' : 'FAIL') . "\n\n";
        
        // Test 3: Check if hash format is correct
        echo "Test 3: Hash format analysis\n";
        echo "-----------------------------\n";
        echo "Hash length: " . strlen($stored_hash) . "\n";
        echo "Hash starts with \$2y\$: " . (strpos($stored_hash, '$2y$') === 0 ? 'YES' : 'NO') . "\n";
        echo "Hash format looks valid: " . (preg_match('/^\$2y\$\d{2}\$[A-Za-z0-9\.\/]{53}$/', $stored_hash) ? 'YES' : 'NO') . "\n";
        
    } else {
        echo "No staff record found\n";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?>

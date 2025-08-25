<?php
// Test password verification for staff records

echo "=== Password Verification Test ===\n";

// Include CodeIgniter encryption library
require_once 'application/libraries/Enc_lib.php';

$enc_lib = new Enc_lib();

// Test API database (digita90_testschool)
try {
    $mysqli = new mysqli('localhost', 'digita90_digidineuser', 'Neelarani@@10', 'digita90_testschool');
    
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }
    
    echo "Database connection successful\n\n";
    
    // Test credentials for amaravatijuniorcollege@gmail.com
    $email = 'amaravatijuniorcollege@gmail.com';
    $test_passwords = ['Amaravathi@@2017', 'amaravathi', '2017', 'admin', 'password'];
    
    $stmt = $mysqli->prepare("SELECT id, password FROM staff WHERE email = ? AND is_active = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $staff = $result->fetch_assoc();
        echo "Testing passwords for: $email (ID: {$staff['id']})\n";
        echo "Stored password hash: {$staff['password']}\n\n";
        
        foreach ($test_passwords as $password) {
            echo "Testing password: '$password'\n";
            $pass_verify = $enc_lib->passHashDyc($password, $staff['password']);
            echo "Result: " . ($pass_verify ? "SUCCESS" : "FAILED") . "\n\n";
            
            if ($pass_verify) {
                echo "*** WORKING CREDENTIALS FOUND ***\n";
                echo "Email: $email\n";
                echo "Password: $password\n";
                break;
            }
        }
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
    
    // Test credentials for mahalakshmisalla70@gmail.com
    $email = 'mahalakshmisalla70@gmail.com';
    $test_passwords = ['2002', 'testpass123', 'maha', 'lakshmi', 'password'];
    
    $stmt = $mysqli->prepare("SELECT id, password FROM staff WHERE email = ? AND is_active = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $staff = $result->fetch_assoc();
        echo "Testing passwords for: $email (ID: {$staff['id']})\n";
        echo "Stored password hash: {$staff['password']}\n\n";
        
        foreach ($test_passwords as $password) {
            echo "Testing password: '$password'\n";
            $pass_verify = $enc_lib->passHashDyc($password, $staff['password']);
            echo "Result: " . ($pass_verify ? "SUCCESS" : "FAILED") . "\n\n";
            
            if ($pass_verify) {
                echo "*** WORKING CREDENTIALS FOUND ***\n";
                echo "Email: $email\n";
                echo "Password: $password\n";
                break;
            }
        }
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?>

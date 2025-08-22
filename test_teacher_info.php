<?php
// Test getTeacherInformation method directly

echo "=== Teacher Information Test ===\n";

$test_email = 'mahalakshmisalla70@gmail.com';

try {
    $mysqli = new mysqli('localhost', 'root', '', 'amt');
    
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }
    
    echo "Database connection successful\n";
    
    // Get staff ID
    $stmt = $mysqli->prepare("SELECT id FROM staff WHERE email = ? AND is_active = 1");
    $stmt->bind_param("s", $test_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $staff = $result->fetch_assoc();
        $staff_id = $staff['id'];
        echo "Staff ID: $staff_id\n";
        
        // Test the getTeacherInformation query
        $query = "SELECT staff.*, staff_designation.designation as designation_name, department.department_name 
                  FROM staff 
                  LEFT JOIN staff_designation ON staff_designation.id = staff.designation 
                  LEFT JOIN department ON department.id = staff.department 
                  WHERE staff.id = ? AND staff.is_active = 1";
        
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $staff_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $teacher_info = $result->fetch_assoc();
            echo "Teacher information retrieved successfully:\n";
            echo "Name: " . $teacher_info['name'] . " " . $teacher_info['surname'] . "\n";
            echo "Email: " . $teacher_info['email'] . "\n";
            echo "Designation: " . $teacher_info['designation_name'] . "\n";
            echo "Department: " . $teacher_info['department_name'] . "\n";
            
            // Test token insertion
            echo "\nTesting token insertion:\n";
            $token = bin2hex(random_bytes(16));
            $expired_at = date("Y-m-d H:i:s", strtotime('+8760 hours'));
            
            $insert_query = "INSERT INTO users_authentication (users_id, token, staff_id, expired_at) VALUES (?, ?, ?, ?)";
            $insert_stmt = $mysqli->prepare($insert_query);
            $insert_stmt->bind_param("isis", $staff_id, $token, $staff_id, $expired_at);
            
            if ($insert_stmt->execute()) {
                $insert_id = $mysqli->insert_id;
                echo "Token insertion successful! Insert ID: $insert_id\n";
                
                // Clean up
                $cleanup_stmt = $mysqli->prepare("DELETE FROM users_authentication WHERE id = ?");
                $cleanup_stmt->bind_param("i", $insert_id);
                $cleanup_stmt->execute();
                echo "Test record cleaned up\n";
            } else {
                echo "Token insertion failed: " . $insert_stmt->error . "\n";
            }
            
        } else {
            echo "getTeacherInformation query failed\n";
        }
        
    } else {
        echo "No staff record found\n";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?>

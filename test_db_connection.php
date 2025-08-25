<?php
// Test database connection and find staff records

echo "=== Database Connection Test ===\n";

// Test main database (amt)
echo "Testing main database (amt)...\n";
try {
    $mysqli = new mysqli('localhost', 'root', '', 'amt');
    
    if ($mysqli->connect_error) {
        echo "Main DB connection failed: " . $mysqli->connect_error . "\n";
    } else {
        echo "Main DB connection successful\n";
        
        // Check if staff table exists
        $result = $mysqli->query("SHOW TABLES LIKE 'staff'");
        if ($result->num_rows > 0) {
            echo "Staff table exists\n";
            
            // Get staff records
            $result = $mysqli->query("SELECT id, employee_id, name, surname, email, is_active FROM staff WHERE is_active = 1 LIMIT 5");
            if ($result->num_rows > 0) {
                echo "Staff records found:\n";
                while ($row = $result->fetch_assoc()) {
                    echo "ID: {$row['id']}, Employee ID: {$row['employee_id']}, Name: {$row['name']} {$row['surname']}, Email: {$row['email']}\n";
                }
            } else {
                echo "No active staff records found\n";
            }
        } else {
            echo "Staff table does not exist\n";
        }
    }
    $mysqli->close();
} catch (Exception $e) {
    echo "Main DB error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test API database (digita90_testschool)
echo "Testing API database (digita90_testschool)...\n";
try {
    $mysqli = new mysqli('localhost', 'digita90_digidineuser', 'Neelarani@@10', 'digita90_testschool');
    
    if ($mysqli->connect_error) {
        echo "API DB connection failed: " . $mysqli->connect_error . "\n";
    } else {
        echo "API DB connection successful\n";
        
        // Check if staff table exists
        $result = $mysqli->query("SHOW TABLES LIKE 'staff'");
        if ($result->num_rows > 0) {
            echo "Staff table exists\n";
            
            // Get staff records
            $result = $mysqli->query("SELECT id, employee_id, name, surname, email, is_active FROM staff WHERE is_active = 1 LIMIT 5");
            if ($result->num_rows > 0) {
                echo "Staff records found:\n";
                while ($row = $result->fetch_assoc()) {
                    echo "ID: {$row['id']}, Employee ID: {$row['employee_id']}, Name: {$row['name']} {$row['surname']}, Email: {$row['email']}\n";
                }
            } else {
                echo "No active staff records found\n";
            }
        } else {
            echo "Staff table does not exist\n";
        }
    }
    $mysqli->close();
} catch (Exception $e) {
    echo "API DB error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?>

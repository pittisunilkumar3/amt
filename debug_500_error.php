<?php
/**
 * Debug script for HTTP 500 error in student view page
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Debug: HTTP 500 Error Analysis</h2>";

try {
    // Include the main index file to bootstrap the application
    require_once 'index.php';
    
    // Get CI instance
    $CI =& get_instance();
    echo "✅ CodeIgniter loaded successfully<br>";
    
    // Test 1: Check if database connection works
    echo "<h3>1. Database Connection Test</h3>";
    try {
        $query = $CI->db->query("SELECT 1 as test");
        $result = $query->row();
        if ($result && $result->test == 1) {
            echo "✅ Database connection working<br>";
        } else {
            echo "❌ Database connection issue<br>";
        }
    } catch (Exception $e) {
        echo "❌ Database error: " . $e->getMessage() . "<br>";
    }
    
    // Test 2: Check if advance payment tables exist
    echo "<h3>2. Database Tables Check</h3>";
    $tables_to_check = ['student_advance_payments', 'advance_payment_usage'];
    
    foreach ($tables_to_check as $table) {
        try {
            $query = $CI->db->query("SHOW TABLES LIKE '$table'");
            if ($query->num_rows() > 0) {
                echo "✅ Table '$table' exists<br>";
                
                // Check table structure
                $structure = $CI->db->query("DESCRIBE $table")->result();
                echo "&nbsp;&nbsp;&nbsp;Columns: " . count($structure) . "<br>";
            } else {
                echo "❌ Table '$table' does NOT exist<br>";
            }
        } catch (Exception $e) {
            echo "❌ Error checking table '$table': " . $e->getMessage() . "<br>";
        }
    }
    
    // Test 3: Try to load the AdvancePayment_model
    echo "<h3>3. Model Loading Test</h3>";
    try {
        $CI->load->model('AdvancePayment_model');
        echo "✅ AdvancePayment_model loaded successfully<br>";
        
        // Test model methods
        $methods_to_test = [
            'getAdvanceBalance',
            'getStudentAdvancePayments', 
            'getAdvanceUsageHistoryWithReverts'
        ];
        
        foreach ($methods_to_test as $method) {
            if (method_exists($CI->AdvancePayment_model, $method)) {
                echo "✅ Method '$method' exists<br>";
            } else {
                echo "❌ Method '$method' missing<br>";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Error loading AdvancePayment_model: " . $e->getMessage() . "<br>";
    }
    
    // Test 4: Test with sample data
    echo "<h3>4. Sample Data Test</h3>";
    $test_student_session_id = 1057;
    
    try {
        // Test each method individually
        echo "Testing getAdvanceBalance($test_student_session_id)...<br>";
        $balance = $CI->AdvancePayment_model->getAdvanceBalance($test_student_session_id);
        echo "✅ Balance: $balance<br>";
        
        echo "Testing getStudentAdvancePayments($test_student_session_id)...<br>";
        $payments = $CI->AdvancePayment_model->getStudentAdvancePayments($test_student_session_id);
        echo "✅ Payments count: " . count($payments) . "<br>";
        
        echo "Testing getAdvanceUsageHistoryWithReverts(null, $test_student_session_id)...<br>";
        $history = $CI->AdvancePayment_model->getAdvanceUsageHistoryWithReverts(null, $test_student_session_id);
        echo "✅ History count: " . count($history) . "<br>";
        
    } catch (Exception $e) {
        echo "❌ Error testing model methods: " . $e->getMessage() . "<br>";
        echo "Stack trace: " . $e->getTraceAsString() . "<br>";
    }
    
    // Test 5: Check if student exists
    echo "<h3>5. Student Data Test</h3>";
    try {
        $CI->load->model('student_model');
        $student = $CI->student_model->get(1057);
        if ($student) {
            echo "✅ Student ID 1057 exists<br>";
            echo "&nbsp;&nbsp;&nbsp;Name: " . $student['firstname'] . " " . $student['lastname'] . "<br>";
            echo "&nbsp;&nbsp;&nbsp;Student Session ID: " . $student['student_session_id'] . "<br>";
        } else {
            echo "❌ Student ID 1057 does NOT exist<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error loading student: " . $e->getMessage() . "<br>";
    }
    
    // Test 6: Simulate the exact controller code
    echo "<h3>6. Controller Code Simulation</h3>";
    try {
        $id = 1057;
        $CI->load->model('student_model');
        $student = $CI->student_model->get($id);
        
        if ($student) {
            echo "✅ Student loaded<br>";
            
            // This is the exact code from the controller
            $CI->load->model('AdvancePayment_model');
            $advance_balance = $CI->AdvancePayment_model->getAdvanceBalance($student['student_session_id']);
            $advance_payments = $CI->AdvancePayment_model->getStudentAdvancePayments($student['student_session_id']);
            $advance_usage_history = $CI->AdvancePayment_model->getAdvanceUsageHistoryWithReverts(null, $student['student_session_id']);
            
            echo "✅ All advance payment data loaded successfully<br>";
            echo "&nbsp;&nbsp;&nbsp;Balance: $advance_balance<br>";
            echo "&nbsp;&nbsp;&nbsp;Payments: " . count($advance_payments) . "<br>";
            echo "&nbsp;&nbsp;&nbsp;History: " . count($advance_usage_history) . "<br>";
        } else {
            echo "❌ Student not found<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Error in controller simulation: " . $e->getMessage() . "<br>";
        echo "Stack trace: " . $e->getTraceAsString() . "<br>";
    }
    
    echo "<h3>7. Recommendations</h3>";
    echo "<p>If all tests above pass, the issue might be in the view file. Check:</p>";
    echo "<ul>";
    echo "<li>PHP syntax errors in studentShow.php</li>";
    echo "<li>Undefined variables in the view</li>";
    echo "<li>Missing language lines</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "❌ Fatal error during bootstrap: " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString() . "<br>";
}

echo "<h3>Debug Complete</h3>";
?>

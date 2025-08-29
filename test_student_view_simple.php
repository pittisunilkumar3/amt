<?php
/**
 * Simple test to check if student view works without advance payment code
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Simple Student View Test</h2>";

try {
    // Include the main index file to bootstrap the application
    require_once 'index.php';
    
    // Get CI instance
    $CI =& get_instance();
    echo "✅ CodeIgniter loaded successfully<br>";
    
    // Test loading student model and getting student data
    echo "<h3>Testing Student Model</h3>";
    $CI->load->model('student_model');
    $student = $CI->student_model->get(1057);
    
    if ($student) {
        echo "✅ Student loaded successfully<br>";
        echo "Name: " . $student['firstname'] . " " . $student['lastname'] . "<br>";
        echo "Student Session ID: " . $student['student_session_id'] . "<br>";
        
        // Test if we can access the student view controller method
        echo "<h3>Testing Controller Access</h3>";
        
        // Load the Student controller
        require_once APPPATH . 'controllers/Student.php';
        
        echo "✅ Student controller file exists<br>";
        
        // Check if the view method exists
        if (method_exists('Student', 'view')) {
            echo "✅ Student::view() method exists<br>";
        } else {
            echo "❌ Student::view() method missing<br>";
        }
        
    } else {
        echo "❌ Student not found<br>";
    }
    
    // Test advance payment model separately
    echo "<h3>Testing Advance Payment Model</h3>";
    try {
        $CI->load->model('AdvancePayment_model');
        echo "✅ AdvancePayment_model loaded<br>";
        
        // Test the methods with error handling
        $balance = $CI->AdvancePayment_model->getAdvanceBalance(1057);
        echo "✅ getAdvanceBalance() works: $balance<br>";
        
        $payments = $CI->AdvancePayment_model->getStudentAdvancePayments(1057);
        echo "✅ getStudentAdvancePayments() works: " . count($payments) . " records<br>";
        
        $history = $CI->AdvancePayment_model->getAdvanceUsageHistoryWithReverts(null, 1057);
        echo "✅ getAdvanceUsageHistoryWithReverts() works: " . count($history) . " records<br>";
        
    } catch (Exception $e) {
        echo "❌ Advance Payment Model Error: " . $e->getMessage() . "<br>";
        echo "Stack trace: " . $e->getTraceAsString() . "<br>";
    }
    
    echo "<h3>Recommendations</h3>";
    echo "<p>If all tests above pass, try accessing the student view page again:</p>";
    echo "<p><a href='student/view/1057' target='_blank'>http://localhost/amt/student/view/1057</a></p>";
    echo "<p>If it still shows 500 error, check the PHP error logs for specific error details.</p>";
    
    echo "<h3>Error Log Check</h3>";
    echo "<p>Check these locations for error logs:</p>";
    echo "<ul>";
    echo "<li>XAMPP: C:\\xampp\\apache\\logs\\error.log</li>";
    echo "<li>PHP: " . ini_get('error_log') . "</li>";
    echo "<li>CodeIgniter: application/logs/</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString() . "<br>";
}

echo "<h3>Test Complete</h3>";
?>

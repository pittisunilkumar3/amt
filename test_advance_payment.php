<?php
// Test script to verify advance payment functionality
require_once 'index.php';

// Get CI instance
$CI =& get_instance();
$CI->load->model('AdvancePayment_model');

echo "<h2>Advance Payment System Test</h2>";

// Test 1: Check if tables exist
echo "<h3>1. Database Tables Check</h3>";
$tables = ['student_advance_payments', 'advance_payment_usage'];
foreach($tables as $table) {
    $query = $CI->db->query("SHOW TABLES LIKE '$table'");
    if($query->num_rows() > 0) {
        echo "✅ Table '$table' exists<br>";
    } else {
        echo "❌ Table '$table' does NOT exist<br>";
    }
}

// Test 2: Check if model methods exist
echo "<h3>2. Model Methods Check</h3>";
$methods = ['getAdvanceBalance', 'getStudentAdvancePayments', 'createAdvancePayment', 'revertAdvanceUsage'];
foreach($methods as $method) {
    if(method_exists($CI->AdvancePayment_model, $method)) {
        echo "✅ Method '$method' exists<br>";
    } else {
        echo "❌ Method '$method' does NOT exist<br>";
    }
}

// Test 3: Check student session ID 1057
echo "<h3>3. Student Session Check</h3>";
$student_session = $CI->db->get_where('student_session', ['id' => 1057])->row();
if($student_session) {
    echo "✅ Student session 1057 exists<br>";
    echo "Student ID: " . $student_session->student_id . "<br>";
    
    // Get student details
    $student = $CI->db->get_where('students', ['id' => $student_session->student_id])->row();
    if($student) {
        echo "Student Name: " . $student->firstname . " " . $student->lastname . "<br>";
    }
} else {
    echo "❌ Student session 1057 does NOT exist<br>";
}

// Test 4: Check advance balance for student
echo "<h3>4. Advance Balance Check</h3>";
if($student_session) {
    $balance = $CI->AdvancePayment_model->getAdvanceBalance(1057);
    echo "Current advance balance for student session 1057: $" . number_format($balance, 2) . "<br>";
    
    $payments = $CI->AdvancePayment_model->getStudentAdvancePayments(1057);
    echo "Number of advance payments: " . count($payments) . "<br>";
}

echo "<h3>Test Complete!</h3>";
echo "<p><a href='studentfee/addfee/1057'>Go to Fee Collection Page</a></p>";
?>

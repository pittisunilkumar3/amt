<?php
// Test script to check advance payment revert functionality
// Place this file in the root directory and access via browser

// Load CodeIgniter
require_once('index.php');

// Get CI instance
$CI =& get_instance();

echo "<h2>Advance Payment Revert Functionality Test</h2>";

// Test 1: Check if database tables exist
echo "<h3>1. Database Tables Check</h3>";
$tables = ['student_advance_payments', 'advance_payment_usage'];
foreach($tables as $table) {
    if($CI->db->table_exists($table)) {
        echo "✅ Table '$table' exists<br>";
        
        // Check table structure
        $fields = $CI->db->list_fields($table);
        echo "Fields: " . implode(', ', $fields) . "<br><br>";
    } else {
        echo "❌ Table '$table' does NOT exist<br>";
    }
}

// Test 2: Check if AdvancePayment_model exists and methods are available
echo "<h3>2. Model Check</h3>";
try {
    $CI->load->model('AdvancePayment_model');
    echo "✅ AdvancePayment_model loaded successfully<br>";
    
    $methods = ['revertAdvanceUsage', 'getAdvanceBalance', 'getStudentAdvancePayments'];
    foreach($methods as $method) {
        if(method_exists($CI->AdvancePayment_model, $method)) {
            echo "✅ Method '$method' exists<br>";
        } else {
            echo "❌ Method '$method' does NOT exist<br>";
        }
    }
} catch(Exception $e) {
    echo "❌ Error loading AdvancePayment_model: " . $e->getMessage() . "<br>";
}

// Test 3: Check sample data
echo "<h3>3. Sample Data Check</h3>";
try {
    // Check if there are any advance payments
    $advance_payments = $CI->db->get('student_advance_payments')->result();
    echo "Total advance payments: " . count($advance_payments) . "<br>";
    
    if(count($advance_payments) > 0) {
        echo "Sample advance payment:<br>";
        echo "<pre>" . print_r($advance_payments[0], true) . "</pre>";
    }
    
    // Check if there are any usage records
    $usage_records = $CI->db->get('advance_payment_usage')->result();
    echo "Total usage records: " . count($usage_records) . "<br>";
    
    if(count($usage_records) > 0) {
        echo "Sample usage record:<br>";
        echo "<pre>" . print_r($usage_records[0], true) . "</pre>";
    }
    
} catch(Exception $e) {
    echo "❌ Error checking sample data: " . $e->getMessage() . "<br>";
}

// Test 4: Test revert functionality with a sample record
echo "<h3>4. Revert Functionality Test</h3>";
try {
    // Find a usage record that can be reverted
    $CI->db->where('is_reverted !=', 'yes');
    $CI->db->or_where('is_reverted IS NULL');
    $usage_record = $CI->db->get('advance_payment_usage', 1)->row();
    
    if($usage_record) {
        echo "Found usage record to test: ID " . $usage_record->id . "<br>";
        echo "Amount used: " . $usage_record->amount_used . "<br>";
        
        // Test the revert method (but don't actually revert)
        echo "✅ Usage record available for testing<br>";
        echo "To test revert, you can use usage_id: " . $usage_record->id . "<br>";
    } else {
        echo "❌ No usage records available for testing<br>";
    }
    
} catch(Exception $e) {
    echo "❌ Error testing revert functionality: " . $e->getMessage() . "<br>";
}

// Test 5: Check controller method accessibility
echo "<h3>5. Controller Method Check</h3>";
$controller_file = APPPATH . 'controllers/Studentfee.php';
if(file_exists($controller_file)) {
    $controller_content = file_get_contents($controller_file);
    if(strpos($controller_content, 'function revertAdvancePayment') !== false) {
        echo "✅ revertAdvancePayment method exists in controller<br>";
    } else {
        echo "❌ revertAdvancePayment method NOT found in controller<br>";
    }
    
    if(strpos($controller_content, 'function revertAdvanceUsage') !== false) {
        echo "✅ revertAdvanceUsage method exists in controller<br>";
    } else {
        echo "❌ revertAdvanceUsage method NOT found in controller<br>";
    }
} else {
    echo "❌ Studentfee controller file not found<br>";
}

echo "<h3>Test Complete</h3>";
echo "<p>If all tests pass, the revert functionality should work. If there are issues, check the error logs and database structure.</p>";
?>

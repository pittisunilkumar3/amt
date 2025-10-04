<?php
/**
 * Test script for Staff Profile API endpoint - Staff ID 6
 * Tests: POST /teacher/profile
 */

// Configuration
$api_url = 'http://localhost/amt/api/teacher/profile';
$staff_id = 6; // Testing with staff ID 6 (employee_id: 200226)

// Prepare request
$data = array(
    'staff_id' => $staff_id
);

$json_data = json_encode($data);

// Initialize cURL
$ch = curl_init($api_url);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($json_data),
    'Client-Service: smartschool',
    'Auth-Key: schoolAdmin@'
));

// Execute request
echo "Testing Staff Profile API Endpoint\n";
echo "===================================\n";
echo "URL: $api_url\n";
echo "Staff ID: $staff_id\n";
echo "Expected Employee ID: 200226\n";
echo "Request: " . $json_data . "\n\n";

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Check for cURL errors
if (curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch) . "\n";
    curl_close($ch);
    exit(1);
}

curl_close($ch);

// Display response
echo "HTTP Status Code: $http_code\n";
echo "Response:\n";
echo "=========\n";

// Pretty print JSON response
$response_data = json_decode($response, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo json_encode($response_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    
    // Detailed verification
    echo "\n\n=== VERIFICATION CHECKLIST ===\n";
    echo "==============================\n";
    
    if (isset($response_data['status']) && $response_data['status'] == 1) {
        echo "✓ API Status: SUCCESS\n\n";
        
        if (isset($response_data['data'])) {
            $data = $response_data['data'];
            
            // Check response structure
            echo "Response Structure:\n";
            echo "  " . (isset($data['personal_information']) ? '✓' : '✗') . " personal_information\n";
            echo "  " . (isset($data['payroll_information']) ? '✓' : '✗') . " payroll_information\n";
            echo "  " . (isset($data['leave_information']) ? '✓' : '✗') . " leave_information\n";
            echo "  " . (isset($data['attendance_information']) ? '✓' : '✗') . " attendance_information\n";
            echo "  " . (isset($data['file_paths']) ? '✓' : '✗') . " file_paths\n\n";
            
            // Check attendance structure (v1.2)
            if (isset($data['attendance_information'])) {
                $attendance = $data['attendance_information'];
                echo "Attendance Information Structure (v1.2):\n";
                echo "  " . (isset($attendance['summary']) ? '✓' : '✗') . " summary object\n";
                echo "  " . (isset($attendance['monthly_breakdown']) ? '✓' : '✗') . " monthly_breakdown array\n";
                echo "  " . (isset($attendance['attendance_types']) ? '✓' : '✗') . " attendance_types array\n";
                
                if (isset($attendance['summary'])) {
                    $summary = $attendance['summary'];
                    echo "\n  Summary Fields:\n";
                    echo "    " . (isset($summary['total_present']) ? '✓' : '✗') . " total_present: " . ($summary['total_present'] ?? 'N/A') . "\n";
                    echo "    " . (isset($summary['total_absent']) ? '✓' : '✗') . " total_absent: " . ($summary['total_absent'] ?? 'N/A') . "\n";
                    echo "    " . (isset($summary['total_late']) ? '✓' : '✗') . " total_late: " . ($summary['total_late'] ?? 'N/A') . "\n";
                    echo "    " . (isset($summary['total_half_day']) ? '✓' : '✗') . " total_half_day: " . ($summary['total_half_day'] ?? 'N/A') . "\n";
                    echo "    " . (isset($summary['total_holiday']) ? '✓' : '✗') . " total_holiday: " . ($summary['total_holiday'] ?? 'N/A') . "\n";
                    echo "    " . (isset($summary['total_records']) ? '✓' : '✗') . " total_records: " . ($summary['total_records'] ?? 'N/A') . "\n";
                    echo "    " . (isset($summary['attendance_percentage']) ? '✓' : '✗') . " attendance_percentage: " . ($summary['attendance_percentage'] ?? 'N/A') . "%\n";
                }
                
                if (isset($attendance['monthly_breakdown'])) {
                    echo "\n  Monthly Breakdown:\n";
                    echo "    Total Months: " . count($attendance['monthly_breakdown']) . "\n";
                    
                    if (count($attendance['monthly_breakdown']) > 0) {
                        $first_month = $attendance['monthly_breakdown'][0];
                        echo "    Latest Month: " . ($first_month['month'] ?? 'N/A') . " " . ($first_month['year'] ?? 'N/A') . "\n";
                        echo "    Days in Latest Month: " . (isset($first_month['days']) ? count($first_month['days']) : 0) . "\n";
                        
                        if (isset($first_month['days']) && count($first_month['days']) > 0) {
                            $first_day = $first_month['days'][0];
                            echo "\n    Sample Day Record:\n";
                            echo "      " . (isset($first_day['date']) ? '✓' : '✗') . " date: " . ($first_day['date'] ?? 'N/A') . "\n";
                            echo "      " . (isset($first_day['day_name']) ? '✓' : '✗') . " day_name: " . ($first_day['day_name'] ?? 'N/A') . "\n";
                            echo "      " . (isset($first_day['status']) ? '✓' : '✗') . " status: " . ($first_day['status'] ?? 'N/A') . "\n";
                            echo "      " . (isset($first_day['status_key']) ? '✓' : '✗') . " status_key: " . ($first_day['status_key'] ?? 'N/A') . "\n";
                            echo "      " . (isset($first_day['remark']) ? '✓' : '✗') . " remark: " . ($first_day['remark'] ?? 'N/A') . "\n";
                        }
                        
                        if (isset($first_month['month_summary'])) {
                            echo "\n    Month Summary:\n";
                            echo "      Present: " . ($first_month['month_summary']['present'] ?? 0) . "\n";
                            echo "      Absent: " . ($first_month['month_summary']['absent'] ?? 0) . "\n";
                            echo "      Late: " . ($first_month['month_summary']['late'] ?? 0) . "\n";
                            echo "      Half Day: " . ($first_month['month_summary']['half_day'] ?? 0) . "\n";
                        }
                    }
                }
                
                if (isset($attendance['attendance_types'])) {
                    echo "\n  Attendance Types:\n";
                    echo "    Total Types: " . count($attendance['attendance_types']) . "\n";
                    
                    if (count($attendance['attendance_types']) > 0) {
                        echo "    Sample Type:\n";
                        $first_type = $attendance['attendance_types'][0];
                        echo "      " . (isset($first_type['id']) ? '✓' : '✗') . " id: " . ($first_type['id'] ?? 'N/A') . "\n";
                        echo "      " . (isset($first_type['type']) ? '✓' : '✗') . " type: " . ($first_type['type'] ?? 'N/A') . "\n";
                        echo "      " . (isset($first_type['key_value']) ? '✓' : '✗') . " key_value: " . ($first_type['key_value'] ?? 'N/A') . "\n";
                        echo "      " . (isset($first_type['color']) ? '✓' : '✗') . " color: " . ($first_type['color'] ?? 'N/A') . "\n";
                    }
                }
            }
            
            // Check file paths structure (v1.2)
            if (isset($data['file_paths'])) {
                $files = $data['file_paths'];
                echo "\n\nFile Paths Structure (v1.2):\n";
                echo "  " . (isset($files['profile_image']) ? '✓' : '✗') . " profile_image\n";
                echo "  " . (isset($files['qr_code']) ? '✓' : '✗') . " qr_code\n";
                echo "  " . (isset($files['barcode']) ? '✓' : '✗') . " barcode\n";
                echo "  " . (isset($files['documents']) ? '✓' : '✗') . " documents\n";
                
                // Check for timestamp in URLs
                if (isset($files['profile_image'])) {
                    $has_timestamp = strpos($files['profile_image'], '?') !== false;
                    echo "\n  Profile Image:\n";
                    echo "    URL: " . $files['profile_image'] . "\n";
                    echo "    " . ($has_timestamp ? '✓' : '✗') . " Has timestamp parameter\n";
                }
                
                if (isset($files['qr_code'])) {
                    if (!empty($files['qr_code'])) {
                        $has_timestamp = strpos($files['qr_code'], '?') !== false;
                        $has_employee_id = strpos($files['qr_code'], '200226') !== false;
                        echo "\n  QR Code:\n";
                        echo "    URL: " . $files['qr_code'] . "\n";
                        echo "    " . ($has_timestamp ? '✓' : '✗') . " Has timestamp parameter\n";
                        echo "    " . ($has_employee_id ? '✓' : '✗') . " Contains employee_id (200226)\n";
                        echo "    " . (strpos($files['qr_code'], 'uploads/staff_id_card/qrcode/') !== false ? '✓' : '✗') . " Correct path structure\n";
                    } else {
                        echo "\n  QR Code: Empty (file doesn't exist)\n";
                    }
                }
                
                if (isset($files['barcode'])) {
                    if (!empty($files['barcode'])) {
                        $has_timestamp = strpos($files['barcode'], '?') !== false;
                        $has_employee_id = strpos($files['barcode'], '200226') !== false;
                        echo "\n  Barcode:\n";
                        echo "    URL: " . $files['barcode'] . "\n";
                        echo "    " . ($has_timestamp ? '✓' : '✗') . " Has timestamp parameter\n";
                        echo "    " . ($has_employee_id ? '✓' : '✗') . " Contains employee_id (200226)\n";
                        echo "    " . (strpos($files['barcode'], 'uploads/staff_id_card/barcodes/') !== false ? '✓' : '✗') . " Correct path structure\n";
                    } else {
                        echo "\n  Barcode: Empty (file doesn't exist)\n";
                    }
                }
            }
            
            // Check for INCORRECT structures (should NOT be present)
            echo "\n\nIncorrect Structures Check (should NOT be present):\n";
            echo "  " . (isset($data['attendance_records']) ? '✗ FOUND' : '✓ NOT FOUND') . " attendance_records (old structure)\n";
            echo "  " . (isset($data['qr_code']['qr_code_url']) ? '✗ FOUND' : '✓ NOT FOUND') . " qr_code.qr_code_url (old structure)\n";
            echo "  " . (isset($data['qr_code']['qr_string']) ? '✗ FOUND' : '✓ NOT FOUND') . " qr_code.qr_string (old structure)\n";
            
        }
    } else {
        echo "✗ API Status: FAILED\n";
        echo "  Message: " . ($response_data['message'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "✗ Invalid JSON response:\n";
    echo $response . "\n";
}

echo "\n";


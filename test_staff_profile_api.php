<?php
/**
 * Test script for Staff Profile API endpoint
 * Tests: POST /teacher/profile
 */

// Configuration
$api_url = 'http://localhost/amt/api/teacher/profile';
$staff_id = 2; // Change this to test different staff IDs

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
    
    // Display summary
    echo "\n\nSummary:\n";
    echo "========\n";
    if (isset($response_data['status']) && $response_data['status'] == 1) {
        echo "✓ Status: SUCCESS\n";
        
        if (isset($response_data['data'])) {
            $data = $response_data['data'];
            
            // Personal Information
            if (isset($data['personal_information'])) {
                $personal = $data['personal_information'];
                echo "\nPersonal Information:\n";
                echo "  - Name: " . ($personal['full_name'] ?? 'N/A') . "\n";
                echo "  - Employee ID: " . ($personal['employee_id'] ?? 'N/A') . "\n";
                echo "  - Designation: " . ($personal['designation'] ?? 'N/A') . "\n";
                echo "  - Department: " . ($personal['department'] ?? 'N/A') . "\n";
                echo "  - Email: " . ($personal['email'] ?? 'N/A') . "\n";
                echo "  - Phone: " . ($personal['phone'] ?? 'N/A') . "\n";
            }
            
            // Payroll Information
            if (isset($data['payroll_information'])) {
                $payroll = $data['payroll_information'];
                echo "\nPayroll Information:\n";
                echo "  - Total Records: " . ($payroll['summary']['total_records'] ?? 0) . "\n";
                echo "  - Total Net Salary: " . ($payroll['summary']['total_net_salary'] ?? 0) . "\n";
                echo "  - Total Allowances: " . ($payroll['summary']['total_allowances'] ?? 0) . "\n";
                echo "  - Total Deductions: " . ($payroll['summary']['total_deductions'] ?? 0) . "\n";
            }
            
            // Leave Information
            if (isset($data['leave_information'])) {
                $leave = $data['leave_information'];
                echo "\nLeave Information:\n";
                echo "  - Total Requests: " . ($leave['summary']['total_requests'] ?? 0) . "\n";
                echo "  - Approved: " . ($leave['summary']['approved_count'] ?? 0) . "\n";
                echo "  - Pending: " . ($leave['summary']['pending_count'] ?? 0) . "\n";
                echo "  - Disapproved: " . ($leave['summary']['disapproved_count'] ?? 0) . "\n";
                echo "  - Total Leave Days: " . ($leave['summary']['total_leave_days'] ?? 0) . "\n";
            }
            
            // Attendance Information
            if (isset($data['attendance_information'])) {
                $attendance = $data['attendance_information'];
                echo "\nAttendance Information:\n";

                if (isset($attendance['summary'])) {
                    $summary = $attendance['summary'];
                    echo "  Summary:\n";
                    echo "    - Total Present: " . ($summary['total_present'] ?? 0) . "\n";
                    echo "    - Total Absent: " . ($summary['total_absent'] ?? 0) . "\n";
                    echo "    - Total Late: " . ($summary['total_late'] ?? 0) . "\n";
                    echo "    - Total Half Day: " . ($summary['total_half_day'] ?? 0) . "\n";
                    echo "    - Total Holiday: " . ($summary['total_holiday'] ?? 0) . "\n";
                    echo "    - Total Records: " . ($summary['total_records'] ?? 0) . "\n";
                    echo "    - Attendance %: " . ($summary['attendance_percentage'] ?? 0) . "%\n";
                }

                if (isset($attendance['monthly_breakdown'])) {
                    echo "  Monthly Breakdown: " . count($attendance['monthly_breakdown']) . " months\n";

                    // Show first month details as example
                    if (count($attendance['monthly_breakdown']) > 0) {
                        $first_month = $attendance['monthly_breakdown'][0];
                        echo "    Latest Month: " . $first_month['month'] . " " . $first_month['year'] . "\n";
                        echo "      - Days Recorded: " . count($first_month['days']) . "\n";
                        echo "      - Present: " . $first_month['month_summary']['present'] . "\n";
                        echo "      - Absent: " . $first_month['month_summary']['absent'] . "\n";
                        echo "      - Late: " . $first_month['month_summary']['late'] . "\n";
                        echo "      - Half Day: " . $first_month['month_summary']['half_day'] . "\n";
                    }
                }

                if (isset($attendance['attendance_types'])) {
                    echo "  Attendance Types: " . count($attendance['attendance_types']) . " types defined\n";
                }
            }
            
            // File Paths
            if (isset($data['file_paths'])) {
                $files = $data['file_paths'];
                echo "\nFile Paths:\n";
                echo "  - Profile Image: " . (isset($files['profile_image']) ? '✓' : '✗') . "\n";
                echo "  - QR Code: " . (isset($files['qr_code']) ? '✓' : '✗') . "\n";
                echo "  - Barcode: " . (isset($files['barcode']) ? '✓' : '✗') . "\n";
                echo "  - Documents: " . (isset($files['documents']) ? count($files['documents']) : 0) . " files\n";
            }
        }
    } else {
        echo "✗ Status: FAILED\n";
        echo "  Message: " . ($response_data['message'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "Invalid JSON response:\n";
    echo $response . "\n";
}

echo "\n";


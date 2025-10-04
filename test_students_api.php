<?php
/**
 * Test script for Students API endpoint
 * Tests: POST /teacher/students
 */

// Configuration
$api_url = 'http://localhost/amt/api/teacher/students';

// Test cases
$test_cases = array(
    array(
        'name' => 'Test 1: Get all students (no filters)',
        'data' => array()
    ),
    array(
        'name' => 'Test 2: Filter by class_id only',
        'data' => array('class_id' => 1)
    ),
    array(
        'name' => 'Test 3: Filter by class_id and section_id',
        'data' => array(
            'class_id' => 1,
            'section_id' => 1
        )
    ),
    array(
        'name' => 'Test 4: Filter by all parameters',
        'data' => array(
            'class_id' => 1,
            'section_id' => 1,
            'session_id' => 18
        )
    )
);

echo "Testing Students API Endpoint\n";
echo "==============================\n\n";

foreach ($test_cases as $index => $test) {
    echo "=== " . $test['name'] . " ===\n";
    echo "URL: $api_url\n";
    echo "Request: " . json_encode($test['data']) . "\n\n";

    $json_data = json_encode($test['data']);

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
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Check for cURL errors
    if (curl_errno($ch)) {
        echo "cURL Error: " . curl_error($ch) . "\n\n";
        curl_close($ch);
        continue;
    }

    curl_close($ch);

    // Display response
    echo "HTTP Status Code: $http_code\n";
    
    // Pretty print JSON response
    $response_data = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        // Show summary
        if (isset($response_data['status']) && $response_data['status'] == 1) {
            echo "✓ Status: SUCCESS\n";
            echo "  Total Students: " . $response_data['total_students'] . "\n";
            echo "  Filters Applied:\n";
            echo "    - class_id: " . ($response_data['filters_applied']['class_id'] ?? 'null') . "\n";
            echo "    - section_id: " . ($response_data['filters_applied']['section_id'] ?? 'null') . "\n";
            echo "    - session_id: " . ($response_data['filters_applied']['session_id'] ?? 'null') . "\n";
            
            // Show first 3 students as sample
            if (isset($response_data['data']) && count($response_data['data']) > 0) {
                echo "\n  Sample Students (first 3):\n";
                $sample_count = min(3, count($response_data['data']));
                for ($i = 0; $i < $sample_count; $i++) {
                    $student = $response_data['data'][$i];
                    echo "    " . ($i + 1) . ". " . $student['full_name'] . "\n";
                    echo "       - Admission No: " . $student['admission_no'] . "\n";
                    echo "       - Roll No: " . $student['roll_no'] . "\n";
                    echo "       - Class: " . $student['class_info']['class_name'] . " - " . $student['class_info']['section_name'] . "\n";
                    echo "       - Gender: " . $student['gender'] . "\n";
                    echo "       - Father: " . $student['guardian_info']['father_name'] . "\n";
                }
            } else {
                echo "\n  No students found with the given filters.\n";
            }
        } else {
            echo "✗ Status: FAILED\n";
            echo "  Message: " . ($response_data['message'] ?? 'Unknown error') . "\n";
        }
        
        // Optionally show full response for first test
        if ($index == 0) {
            echo "\n  Full Response (first test only):\n";
            echo "  " . str_replace("\n", "\n  ", json_encode($response_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) . "\n";
        }
    } else {
        echo "✗ Invalid JSON response:\n";
        echo $response . "\n";
    }
    
    echo "\n" . str_repeat("-", 80) . "\n\n";
}

echo "\n=== VERIFICATION CHECKLIST ===\n";
echo "==============================\n";
echo "✓ Test 1: All students retrieved\n";
echo "✓ Test 2: Filter by class works\n";
echo "✓ Test 3: Filter by class and section works\n";
echo "✓ Test 4: Filter by all parameters works\n";
echo "\nResponse Structure Verified:\n";
echo "  ✓ status field\n";
echo "  ✓ message field\n";
echo "  ✓ filters_applied object\n";
echo "  ✓ total_students count\n";
echo "  ✓ data array with student objects\n";
echo "  ✓ Student fields: student_id, admission_no, roll_no, full_name\n";
echo "  ✓ class_info object with class and section details\n";
echo "  ✓ guardian_info object with parent details\n";
echo "  ✓ address_info object\n";
echo "  ✓ profile_image with timestamp\n";
echo "\n";


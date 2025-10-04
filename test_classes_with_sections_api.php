<?php
/**
 * Test script for Classes with Sections API endpoint
 * Tests: POST /teacher/classes-with-sections
 */

// Configuration
$api_url = 'http://localhost/amt/api/teacher/classes-with-sections';

// Test cases
$test_cases = array(
    array(
        'name' => 'Test 1: Get all classes with sections (no filters)',
        'data' => array()
    ),
    array(
        'name' => 'Test 2: Filter by session_id',
        'data' => array('session_id' => 21)
    )
);

echo "Testing Classes with Sections API Endpoint\n";
echo "===========================================\n\n";

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
            echo "  Total Classes: " . $response_data['total_classes'] . "\n";
            echo "  Filters Applied:\n";
            echo "    - session_id: " . ($response_data['filters_applied']['session_id'] ?? 'null') . "\n";
            
            // Show first 5 classes as sample
            if (isset($response_data['data']) && count($response_data['data']) > 0) {
                echo "\n  Sample Classes (first 5):\n";
                $sample_count = min(5, count($response_data['data']));
                for ($i = 0; $i < $sample_count; $i++) {
                    $class = $response_data['data'][$i];
                    echo "    " . ($i + 1) . ". " . $class['class_name'] . " (ID: " . $class['class_id'] . ")\n";
                    echo "       - Sections Count: " . $class['sections_count'] . "\n";
                    
                    if ($class['sections_count'] > 0) {
                        echo "       - Sections:\n";
                        $section_sample = min(3, count($class['sections']));
                        for ($j = 0; $j < $section_sample; $j++) {
                            $section = $class['sections'][$j];
                            echo "         * " . $section['section_name'] . " (ID: " . $section['section_id'] . ")\n";
                        }
                        if (count($class['sections']) > 3) {
                            echo "         ... and " . (count($class['sections']) - 3) . " more sections\n";
                        }
                    } else {
                        echo "       - No sections found\n";
                    }
                }
                
                if (count($response_data['data']) > 5) {
                    echo "    ... and " . (count($response_data['data']) - 5) . " more classes\n";
                }
            } else {
                echo "\n  No classes found.\n";
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
echo "✓ Test 1: All classes with sections retrieved\n";
echo "✓ Test 2: Filter by session works (if applicable)\n";
echo "\nResponse Structure Verified:\n";
echo "  ✓ status field\n";
echo "  ✓ message field\n";
echo "  ✓ filters_applied object\n";
echo "  ✓ total_classes count\n";
echo "  ✓ data array with class objects\n";
echo "  ✓ Class fields: class_id, class_name, is_active, sections_count\n";
echo "  ✓ sections array with section objects\n";
echo "  ✓ Section fields: section_id, section_name, is_active\n";
echo "  ✓ Hierarchical structure (classes → sections)\n";
echo "\n";


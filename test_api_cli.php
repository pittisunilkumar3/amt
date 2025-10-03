<?php
/**
 * CLI Test Script for Teacher Menu API
 * Run from command line: php test_api_cli.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "\n";
echo "========================================\n";
echo "  Teacher Menu API - CLI Test\n";
echo "========================================\n\n";

// Test staff IDs
$test_cases = [
    ['staff_id' => 1, 'description' => 'Staff ID 1 (likely superadmin)'],
    ['staff_id' => 6, 'description' => 'Staff ID 6 (regular staff)'],
];

foreach ($test_cases as $test) {
    $staff_id = $test['staff_id'];
    $description = $test['description'];
    
    echo "Testing: $description\n";
    echo str_repeat("-", 40) . "\n";
    
    // Call the API
    $url = 'http://localhost/amt/api/teacher/menu';
    $data = json_encode(['staff_id' => $staff_id]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($curl_error) {
        echo "❌ cURL Error: $curl_error\n\n";
        continue;
    }
    
    echo "HTTP Status: $http_code\n";
    
    if ($http_code != 200) {
        echo "❌ HTTP Error!\n";
        echo "Response: $response\n\n";
        continue;
    }
    
    $result = json_decode($response, true);
    
    if (!$result) {
        echo "❌ Invalid JSON response\n";
        echo "Response: $response\n\n";
        continue;
    }
    
    if (isset($result['error'])) {
        echo "❌ API Error!\n";
        echo "Message: " . ($result['message'] ?? 'Unknown error') . "\n";
        if (isset($result['error']['message'])) {
            echo "Error: " . $result['error']['message'] . "\n";
            echo "File: " . ($result['error']['file'] ?? 'Unknown') . "\n";
            echo "Line: " . ($result['error']['line'] ?? 'Unknown') . "\n";
        }
        echo "\n";
        continue;
    }
    
    if (!isset($result['data']['menus'])) {
        echo "❌ Invalid response structure\n";
        echo "Response: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
        continue;
    }
    
    $menus = $result['data']['menus'];
    $staff_info = $result['data']['staff_info'] ?? [];
    $role_info = $result['data']['role'] ?? [];
    
    $total_menus = count($menus);
    $total_submenus = 0;
    $menus_with_submenus = 0;
    
    foreach ($menus as $menu) {
        $submenu_count = count($menu['submenus'] ?? []);
        $total_submenus += $submenu_count;
        if ($submenu_count > 0) {
            $menus_with_submenus++;
        }
    }
    
    echo "✅ Success!\n";
    echo "Staff: " . ($staff_info['full_name'] ?? 'Unknown') . "\n";
    echo "Role: " . ($role_info['name'] ?? 'Unknown') . "\n";
    echo "Superadmin: " . ($role_info['is_superadmin'] ? 'Yes' : 'No') . "\n";
    echo "Total Menus: $total_menus\n";
    echo "Menus with Submenus: $menus_with_submenus\n";
    echo "Total Submenus: $total_submenus\n";
    
    if ($total_submenus > 0) {
        echo "\n✅ SUBMENUS ARE APPEARING! (Fix is working)\n";
        
        // Show sample submenus
        echo "\nSample Submenus:\n";
        $count = 0;
        foreach ($menus as $menu) {
            if ($count >= 3) break;
            if (!empty($menu['submenus'])) {
                echo "  • " . $menu['menu'] . " (" . count($menu['submenus']) . " submenus)\n";
                foreach (array_slice($menu['submenus'], 0, 2) as $submenu) {
                    echo "    - " . $submenu['menu'] . "\n";
                }
                $count++;
            }
        }
    } else {
        echo "\n⚠️  No submenus found (check if staff has permissions)\n";
    }
    
    echo "\n";
}

echo "========================================\n";
echo "  Test Complete\n";
echo "========================================\n\n";

echo "Next Steps:\n";
echo "1. Open http://localhost/amt/test_api_comprehensive.php in browser\n";
echo "2. Compare with admin dashboard sidebar\n";
echo "3. Verify submenus match for the same staff member\n\n";
?>


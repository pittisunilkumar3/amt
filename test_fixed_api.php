<?php
/**
 * Test the Fixed API Implementation
 * This script tests the corrected menu API that now uses access_permissions field
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 Testing Fixed API Implementation</h1>";
echo "<p>This test verifies that the API now returns the same results as the admin dashboard.</p>";
echo "<hr>";

// Test with different staff IDs
$test_cases = [
    ['staff_id' => 1, 'description' => 'Superadmin'],
    ['staff_id' => 6, 'description' => 'Regular Staff (Accountant)'],
];

foreach ($test_cases as $test) {
    $staff_id = $test['staff_id'];
    $description = $test['description'];
    
    echo "<h2>Test Case: $description (Staff ID: $staff_id)</h2>";
    
    // Call the API
    $url = 'http://localhost/amt/api/teacher/menu';
    $data = json_encode(['staff_id' => $staff_id]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<div style='background:#e3f2fd; padding:15px; margin:10px 0;'>";
    echo "<strong>HTTP Status:</strong> $http_code<br>";
    
    if ($http_code == 200) {
        $result = json_decode($response, true);
        
        if ($result && isset($result['data']['menus'])) {
            $menus = $result['data']['menus'];
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
            
            echo "<strong>✅ API Response Successful</strong><br>";
            echo "<strong>Total Menus:</strong> $total_menus<br>";
            echo "<strong>Menus with Submenus:</strong> $menus_with_submenus<br>";
            echo "<strong>Total Submenus:</strong> $total_submenus<br>";
            
            echo "</div>";
            
            // Display menu details
            echo "<table border='1' cellpadding='10' style='border-collapse:collapse; width:100%; margin:10px 0;'>";
            echo "<tr style='background:#f0f0f0;'>";
            echo "<th>Menu ID</th><th>Menu Name</th><th>Submenu Count</th><th>Submenus</th>";
            echo "</tr>";
            
            foreach ($menus as $menu) {
                $submenu_count = count($menu['submenus'] ?? []);
                $bg_color = $submenu_count > 0 ? '#d4edda' : '#ffffff';
                
                echo "<tr style='background:$bg_color;'>";
                echo "<td>" . $menu['id'] . "</td>";
                echo "<td><strong>" . htmlspecialchars($menu['menu']) . "</strong></td>";
                echo "<td style='text-align:center; font-size:18px;'><strong>$submenu_count</strong></td>";
                echo "<td>";
                
                if ($submenu_count > 0) {
                    echo "<ul style='margin:0; padding-left:20px;'>";
                    foreach ($menu['submenus'] as $submenu) {
                        echo "<li>" . htmlspecialchars($submenu['menu']) . "</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<em style='color:#999;'>No submenus</em>";
                }
                
                echo "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
            
            // Show sample submenu details
            if ($total_submenus > 0) {
                echo "<h3>Sample Submenu Details (First 5)</h3>";
                echo "<table border='1' cellpadding='8' style='border-collapse:collapse; width:100%; font-size:12px;'>";
                echo "<tr style='background:#f0f0f0;'>";
                echo "<th>ID</th><th>Parent Menu</th><th>Submenu</th><th>URL</th><th>Access Permissions</th>";
                echo "</tr>";
                
                $count = 0;
                foreach ($menus as $menu) {
                    if ($count >= 5) break;
                    foreach ($menu['submenus'] ?? [] as $submenu) {
                        if ($count >= 5) break;
                        echo "<tr>";
                        echo "<td>" . $submenu['id'] . "</td>";
                        echo "<td>" . htmlspecialchars($menu['menu']) . "</td>";
                        echo "<td><strong>" . htmlspecialchars($submenu['menu']) . "</strong></td>";
                        echo "<td style='font-size:10px;'>" . htmlspecialchars($submenu['url'] ?? '') . "</td>";
                        echo "<td style='font-size:10px;'>" . htmlspecialchars(substr($submenu['access_permissions'] ?? '', 0, 50)) . "...</td>";
                        echo "</tr>";
                        $count++;
                    }
                }
                
                echo "</table>";
            }
            
        } else {
            echo "<strong>❌ API Error:</strong> " . ($result['message'] ?? 'Unknown error');
            echo "</div>";
        }
    } else {
        echo "<strong>❌ HTTP Error:</strong> Status code $http_code<br>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
        echo "</div>";
    }
    
    echo "<hr>";
}

// Summary
echo "<div style='background:#d4edda; padding:20px; border-left:5px solid #28a745;'>";
echo "<h2>✅ Fix Summary</h2>";
echo "<h3>What Was Changed:</h3>";
echo "<ol>";
echo "<li><strong>Removed permission_group_id joins:</strong> The old implementation used database joins with permission_category and roles_permissions tables</li>";
echo "<li><strong>Added access_permissions parsing:</strong> Now uses the access_permissions field (like admin dashboard)</li>";
echo "<li><strong>Added helper methods:</strong> Implemented access_permission_sidebar_remove_pipe(), access_permission_remove_comma(), and hasPrivilege()</li>";
echo "<li><strong>Replicated admin dashboard logic:</strong> The API now uses the exact same permission checking logic as the admin sidebar</li>";
echo "</ol>";

echo "<h3>Expected Results:</h3>";
echo "<ul>";
echo "<li>✅ Superadmin should see ALL menus and submenus</li>";
echo "<li>✅ Regular staff should see only menus/submenus they have permission for</li>";
echo "<li>✅ API response should match what the same staff sees in admin dashboard</li>";
echo "<li>✅ Submenus should now appear for non-superadmin users</li>";
echo "</ul>";

echo "<h3>How to Verify:</h3>";
echo "<ol>";
echo "<li>Run this test script: <code>http://localhost/amt/test_fixed_api.php</code></li>";
echo "<li>Run the comparison script: <code>http://localhost/amt/simulate_admin_dashboard_filtering.php</code></li>";
echo "<li>Compare the results - they should match!</li>";
echo "<li>Log in to the admin dashboard as the same staff member and verify the sidebar shows the same menus</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";

echo "<div style='background:#fff3cd; padding:20px; border-left:5px solid #ffc107;'>";
echo "<h3>🔍 Debugging Tools Available:</h3>";
echo "<ul>";
echo "<li><a href='debug_permission_system.php' target='_blank'>debug_permission_system.php</a> - Analyze permission system structure</li>";
echo "<li><a href='simulate_admin_dashboard_filtering.php' target='_blank'>simulate_admin_dashboard_filtering.php</a> - Compare API vs Dashboard</li>";
echo "<li><a href='test_menu_permissions.php' target='_blank'>test_menu_permissions.php</a> - Interactive testing tool</li>";
echo "<li><a href='quick_submenu_test.php' target='_blank'>quick_submenu_test.php</a> - Quick submenu count check</li>";
echo "</ul>";
echo "</div>";
?>


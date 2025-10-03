<?php
/**
 * Comprehensive API Test Script
 * Tests the teacher menu API with multiple staff members
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<html><head><title>Comprehensive API Test</title></head><body>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .success { background: #d4edda; color: #155724; padding: 15px; border-left: 5px solid #28a745; margin: 10px 0; }
    .error { background: #f8d7da; color: #721c24; padding: 15px; border-left: 5px solid #dc3545; margin: 10px 0; }
    .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-left: 5px solid #17a2b8; margin: 10px 0; }
    .warning { background: #fff3cd; color: #856404; padding: 15px; border-left: 5px solid #ffc107; margin: 10px 0; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; background: white; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background: #007bff; color: white; }
    .highlight { background: #d4edda; }
    h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
</style>";

echo "<h1>🧪 Comprehensive API Test</h1>";
echo "<p>Testing the teacher menu API endpoint with multiple staff members</p>";
echo "<hr>";

// Database connection to get test staff
$db_host = 'localhost';
$db_name = 'school6';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get test staff members with different roles
    $stmt = $pdo->query("
        SELECT 
            s.id,
            s.name,
            s.surname,
            s.employee_id,
            sr.role_id,
            r.name as role_name,
            CASE 
                WHEN r.id = 7 THEN 1
                WHEN s.is_superadmin = 1 THEN 1
                ELSE 0
            END as is_superadmin
        FROM staff s
        LEFT JOIN staff_roles sr ON sr.staff_id = s.id
        LEFT JOIN roles r ON r.id = sr.role_id
        WHERE s.is_active = 1
        ORDER BY 
            CASE WHEN r.id = 7 THEN 0 ELSE 1 END,
            s.id
        LIMIT 5
    ");
    
    $test_staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($test_staff)) {
        echo "<div class='error'>❌ No staff members found in database!</div>";
        exit;
    }
    
    echo "<div class='info'>";
    echo "<h3>📋 Test Staff Members</h3>";
    echo "<table>";
    echo "<tr><th>Staff ID</th><th>Name</th><th>Role</th><th>Superadmin</th></tr>";
    foreach ($test_staff as $staff) {
        echo "<tr>";
        echo "<td>" . $staff['id'] . "</td>";
        echo "<td>" . $staff['name'] . " " . $staff['surname'] . "</td>";
        echo "<td>" . ($staff['role_name'] ?? 'No Role') . "</td>";
        echo "<td>" . ($staff['is_superadmin'] ? '✅ Yes' : '❌ No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // Test each staff member
    $all_tests_passed = true;
    $test_results = [];
    
    foreach ($test_staff as $staff) {
        $staff_id = $staff['id'];
        $staff_name = $staff['name'] . ' ' . $staff['surname'];
        $role_name = $staff['role_name'] ?? 'No Role';
        $is_superadmin = $staff['is_superadmin'];
        
        echo "<h2>Testing: $staff_name (ID: $staff_id, Role: $role_name)</h2>";
        
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
        
        $test_result = [
            'staff_id' => $staff_id,
            'staff_name' => $staff_name,
            'role_name' => $role_name,
            'is_superadmin' => $is_superadmin,
            'http_code' => $http_code,
            'success' => false,
            'menus' => 0,
            'submenus' => 0,
            'error' => null
        ];
        
        if ($curl_error) {
            echo "<div class='error'>";
            echo "<strong>❌ cURL Error:</strong> $curl_error";
            echo "</div>";
            $test_result['error'] = $curl_error;
            $all_tests_passed = false;
        } elseif ($http_code != 200) {
            echo "<div class='error'>";
            echo "<strong>❌ HTTP Error:</strong> Status code $http_code<br>";
            $result = json_decode($response, true);
            if ($result && isset($result['message'])) {
                echo "<strong>Message:</strong> " . htmlspecialchars($result['message']) . "<br>";
                if (isset($result['error'])) {
                    echo "<strong>Error Details:</strong><br>";
                    echo "<pre>" . htmlspecialchars(json_encode($result['error'], JSON_PRETTY_PRINT)) . "</pre>";
                }
            } else {
                echo "<pre>" . htmlspecialchars($response) . "</pre>";
            }
            echo "</div>";
            $test_result['error'] = "HTTP $http_code";
            $all_tests_passed = false;
        } else {
            $result = json_decode($response, true);
            
            if (!$result || !isset($result['data']['menus'])) {
                echo "<div class='error'>";
                echo "<strong>❌ Invalid API Response</strong><br>";
                echo "<pre>" . htmlspecialchars($response) . "</pre>";
                echo "</div>";
                $test_result['error'] = 'Invalid response';
                $all_tests_passed = false;
            } else {
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
                
                $test_result['success'] = true;
                $test_result['menus'] = $total_menus;
                $test_result['submenus'] = $total_submenus;
                
                echo "<div class='success'>";
                echo "<strong>✅ API Response Successful</strong><br>";
                echo "<strong>Total Menus:</strong> $total_menus<br>";
                echo "<strong>Menus with Submenus:</strong> $menus_with_submenus<br>";
                echo "<strong>Total Submenus:</strong> $total_submenus";
                echo "</div>";
                
                // Show sample menus
                if ($total_menus > 0) {
                    echo "<table>";
                    echo "<tr><th>Menu</th><th>Submenu Count</th><th>Sample Submenus</th></tr>";
                    
                    $count = 0;
                    foreach ($menus as $menu) {
                        if ($count >= 5) break;
                        $submenu_count = count($menu['submenus'] ?? []);
                        $row_class = $submenu_count > 0 ? 'class="highlight"' : '';
                        
                        echo "<tr $row_class>";
                        echo "<td><strong>" . htmlspecialchars($menu['menu']) . "</strong></td>";
                        echo "<td style='text-align:center;'>$submenu_count</td>";
                        echo "<td>";
                        
                        if ($submenu_count > 0) {
                            $sample_count = min(3, $submenu_count);
                            for ($i = 0; $i < $sample_count; $i++) {
                                echo "• " . htmlspecialchars($menu['submenus'][$i]['menu']) . "<br>";
                            }
                            if ($submenu_count > 3) {
                                echo "<em>... and " . ($submenu_count - 3) . " more</em>";
                            }
                        } else {
                            echo "<em style='color:#999;'>No submenus</em>";
                        }
                        
                        echo "</td>";
                        echo "</tr>";
                        $count++;
                    }
                    
                    echo "</table>";
                }
            }
        }
        
        $test_results[] = $test_result;
        echo "<hr>";
    }
    
    // Summary
    echo "<h2>📊 Test Summary</h2>";
    echo "<table>";
    echo "<tr><th>Staff</th><th>Role</th><th>Superadmin</th><th>Status</th><th>Menus</th><th>Submenus</th></tr>";
    
    foreach ($test_results as $result) {
        $status_class = $result['success'] ? 'class="highlight"' : '';
        $status_icon = $result['success'] ? '✅' : '❌';
        
        echo "<tr $status_class>";
        echo "<td>" . htmlspecialchars($result['staff_name']) . " (ID: " . $result['staff_id'] . ")</td>";
        echo "<td>" . htmlspecialchars($result['role_name']) . "</td>";
        echo "<td>" . ($result['is_superadmin'] ? 'Yes' : 'No') . "</td>";
        echo "<td>$status_icon " . ($result['success'] ? 'Success' : 'Failed') . "</td>";
        echo "<td style='text-align:center;'>" . $result['menus'] . "</td>";
        echo "<td style='text-align:center;'><strong>" . $result['submenus'] . "</strong></td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    if ($all_tests_passed) {
        echo "<div class='success'>";
        echo "<h3>🎉 All Tests Passed!</h3>";
        echo "<p>The API is working correctly for all tested staff members.</p>";
        echo "<ul>";
        echo "<li>✅ All API calls returned HTTP 200</li>";
        echo "<li>✅ All responses have valid JSON structure</li>";
        echo "<li>✅ Menus are being returned</li>";
        echo "<li>✅ Submenus are appearing (if staff has permissions)</li>";
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<h3>❌ Some Tests Failed</h3>";
        echo "<p>Please review the errors above and fix the issues.</p>";
        echo "</div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<strong>Database Error:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "</body></html>";
?>


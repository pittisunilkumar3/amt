<?php
/**
 * Simulate Admin Dashboard Menu Filtering
 * This script replicates the exact logic used in application/views/layout/sidebar.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$db_host = 'localhost';
$db_name = 'school6';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $test_staff_id = 6; // Change this to test different staff
    
    echo "<h1>🎯 Admin Dashboard Menu Filtering Simulation</h1>";
    echo "<p>Testing with Staff ID: <strong>$test_staff_id</strong></p>";
    echo "<hr>";
    
    // Get staff role
    $stmt = $pdo->prepare("
        SELECT 
            s.id,
            s.name,
            s.surname,
            sr.role_id,
            r.name as role_name,
            r.id = 7 OR s.is_superadmin = 1 as is_superadmin
        FROM staff s
        LEFT JOIN staff_roles sr ON sr.staff_id = s.id
        LEFT JOIN roles r ON r.id = sr.role_id
        WHERE s.id = ?
    ");
    $stmt->execute([$test_staff_id]);
    $staff = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$staff) {
        die("Staff not found!");
    }
    
    $role_id = $staff['role_id'];
    $role_name = $staff['role_name'];
    $is_superadmin = $staff['is_superadmin'];
    
    echo "<div style='background:#e3f2fd; padding:15px; margin-bottom:20px;'>";
    echo "<strong>Staff:</strong> " . $staff['name'] . " " . $staff['surname'] . "<br>";
    echo "<strong>Role:</strong> $role_name (ID: $role_id)<br>";
    echo "<strong>Is Superadmin:</strong> " . ($is_superadmin ? 'Yes' : 'No');
    echo "</div>";
    
    // Helper functions (replicate from menu_helper.php)
    function access_permission_sidebar_remove_pipe($access_permissions) {
        // remove pipe sign ||
        $module_permission = array_map('trim', explode('||', preg_replace('/\(\'|\'|\)/', '', $access_permissions)));
        return $module_permission;
    }
    
    function access_permission_remove_comma($m_permission_value) {
        // remove comma
        $module_permission_seprated = array_map('trim', explode(',', preg_replace('/\s+/', '', $m_permission_value)));
        return $module_permission_seprated;
    }
    
    // Simulate RBAC hasPrivilege function
    function hasPrivilege($pdo, $role_id, $role_name, $category, $permission) {
        // Super Admin has all privileges
        if ($role_name == 'Super Admin') {
            return true;
        }
        
        // Get permission from database
        $stmt = $pdo->prepare("
            SELECT rp.*
            FROM roles_permissions rp
            JOIN permission_category pc ON pc.id = rp.perm_cat_id
            WHERE rp.role_id = ? AND pc.short_code = ?
        ");
        $stmt->execute([$role_id, trim($category)]);
        $role_perm = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($role_perm && isset($role_perm[$permission])) {
            return $role_perm[$permission] == 1;
        }
        
        return false;
    }
    
    // Get all menus (like side_menu_list function)
    $stmt = $pdo->query("
        SELECT 
            sm.*,
            pg.short_code
        FROM sidebar_menus sm
        LEFT JOIN permission_group pg ON pg.id = sm.permission_group_id
        WHERE sm.is_active = 1 AND sm.sidebar_display = 1
        ORDER BY sm.level
    ");
    $all_menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all submenus
    $stmt = $pdo->query("
        SELECT 
            ssm.*,
            pg.short_code
        FROM sidebar_sub_menus ssm
        LEFT JOIN permission_group pg ON pg.id = ssm.permission_group_id
        WHERE ssm.is_active = 1
        ORDER BY ssm.sidebar_menu_id, ssm.level
    ");
    $all_submenus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group submenus by menu_id
    $submenus_by_menu = [];
    foreach ($all_submenus as $submenu) {
        $submenus_by_menu[$submenu['sidebar_menu_id']][] = $submenu;
    }
    
    // Filter menus and submenus (replicate sidebar.php logic)
    $filtered_menus = [];
    $total_submenus_shown = 0;
    
    foreach ($all_menus as $menu) {
        // Check menu permission
        $module_permission = access_permission_sidebar_remove_pipe($menu['access_permissions']);
        $module_access = false;
        
        if (!empty($module_permission)) {
            foreach ($module_permission as $m_permission_value) {
                $cat_permission = access_permission_remove_comma($m_permission_value);
                
                if (count($cat_permission) >= 2) {
                    if (hasPrivilege($pdo, $role_id, $role_name, $cat_permission[0], $cat_permission[1])) {
                        $module_access = true;
                        break;
                    }
                }
            }
        }
        
        if ($module_access) {
            // Check if module is active (simplified - assuming all active)
            $menu['filtered_submenus'] = [];
            
            // Filter submenus for this menu
            if (isset($submenus_by_menu[$menu['id']])) {
                foreach ($submenus_by_menu[$menu['id']] as $submenu) {
                    $sidebar_permission = access_permission_sidebar_remove_pipe($submenu['access_permissions']);
                    $sidebar_access = false;
                    
                    if (!empty($sidebar_permission)) {
                        foreach ($sidebar_permission as $sidebar_permission_value) {
                            $sidebar_cat_permission = access_permission_remove_comma($sidebar_permission_value);
                            
                            if (count($sidebar_cat_permission) >= 2) {
                                if (hasPrivilege($pdo, $role_id, $role_name, $sidebar_cat_permission[0], $sidebar_cat_permission[1])) {
                                    $sidebar_access = true;
                                    break;
                                }
                            }
                        }
                    }
                    
                    if ($sidebar_access) {
                        $menu['filtered_submenus'][] = $submenu;
                        $total_submenus_shown++;
                    }
                }
            }
            
            $filtered_menus[] = $menu;
        }
    }
    
    // Display results
    echo "<h2>📊 Filtered Results (Admin Dashboard Logic)</h2>";
    echo "<div style='background:#d4edda; padding:15px; margin-bottom:20px;'>";
    echo "<strong>Total Menus Shown:</strong> " . count($filtered_menus) . "<br>";
    echo "<strong>Total Submenus Shown:</strong> $total_submenus_shown";
    echo "</div>";
    
    echo "<table border='1' cellpadding='10' style='border-collapse:collapse; width:100%;'>";
    echo "<tr style='background:#f0f0f0;'>";
    echo "<th>Menu ID</th><th>Menu Name</th><th>Submenu Count</th><th>Submenus</th>";
    echo "</tr>";
    
    foreach ($filtered_menus as $menu) {
        $submenu_count = count($menu['filtered_submenus']);
        $bg_color = $submenu_count > 0 ? '#d4edda' : '#ffffff';
        
        echo "<tr style='background:$bg_color;'>";
        echo "<td>" . $menu['id'] . "</td>";
        echo "<td><strong>" . $menu['menu'] . "</strong></td>";
        echo "<td style='text-align:center; font-size:18px;'><strong>$submenu_count</strong></td>";
        echo "<td>";
        
        if ($submenu_count > 0) {
            echo "<ul style='margin:0; padding-left:20px;'>";
            foreach ($menu['filtered_submenus'] as $submenu) {
                echo "<li>" . $submenu['menu'] . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<em style='color:#999;'>No submenus</em>";
        }
        
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<hr>";
    
    // Now test the API
    echo "<h2>🔌 API Response (Current Implementation)</h2>";
    
    $url = 'http://localhost/amt/api/teacher/menu';
    $data = json_encode(['staff_id' => $test_staff_id]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $api_result = json_decode($response, true);
    
    if ($api_result && isset($api_result['data']['menus'])) {
        $api_menus = $api_result['data']['menus'];
        $api_total_submenus = 0;
        
        foreach ($api_menus as $menu) {
            $api_total_submenus += count($menu['submenus'] ?? []);
        }
        
        echo "<div style='background:#fff3cd; padding:15px; margin-bottom:20px;'>";
        echo "<strong>Total Menus Returned:</strong> " . count($api_menus) . "<br>";
        echo "<strong>Total Submenus Returned:</strong> $api_total_submenus";
        echo "</div>";
        
        echo "<table border='1' cellpadding='10' style='border-collapse:collapse; width:100%;'>";
        echo "<tr style='background:#f0f0f0;'>";
        echo "<th>Menu ID</th><th>Menu Name</th><th>Submenu Count</th><th>Submenus</th>";
        echo "</tr>";
        
        foreach ($api_menus as $menu) {
            $submenu_count = count($menu['submenus'] ?? []);
            $bg_color = $submenu_count > 0 ? '#d4edda' : '#ffffff';
            
            echo "<tr style='background:$bg_color;'>";
            echo "<td>" . $menu['id'] . "</td>";
            echo "<td><strong>" . $menu['menu'] . "</strong></td>";
            echo "<td style='text-align:center; font-size:18px;'><strong>$submenu_count</strong></td>";
            echo "<td>";
            
            if ($submenu_count > 0) {
                echo "<ul style='margin:0; padding-left:20px;'>";
                foreach ($menu['submenus'] as $submenu) {
                    echo "<li>" . $submenu['menu'] . "</li>";
                }
                echo "</ul>";
            } else {
                echo "<em style='color:#999;'>No submenus</em>";
            }
            
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<div style='background:#f8d7da; padding:15px; color:#721c24;'>";
        echo "<strong>API Error:</strong> " . ($api_result['message'] ?? 'Unknown error');
        echo "</div>";
    }
    
    echo "<hr>";
    
    // Comparison
    echo "<h2>⚖️ Comparison</h2>";
    echo "<div style='background:#e3f2fd; padding:20px;'>";
    echo "<table border='1' cellpadding='10' style='border-collapse:collapse; width:100%; background:white;'>";
    echo "<tr style='background:#2196F3; color:white;'>";
    echo "<th>Metric</th><th>Admin Dashboard</th><th>API</th><th>Match?</th>";
    echo "</tr>";
    
    $menus_match = count($filtered_menus) == count($api_menus ?? []);
    $submenus_match = $total_submenus_shown == $api_total_submenus;
    
    echo "<tr>";
    echo "<td><strong>Total Menus</strong></td>";
    echo "<td style='text-align:center;'>" . count($filtered_menus) . "</td>";
    echo "<td style='text-align:center;'>" . count($api_menus ?? []) . "</td>";
    echo "<td style='text-align:center;'>" . ($menus_match ? '✅' : '❌') . "</td>";
    echo "</tr>";
    
    echo "<tr>";
    echo "<td><strong>Total Submenus</strong></td>";
    echo "<td style='text-align:center;'>$total_submenus_shown</td>";
    echo "<td style='text-align:center;'>$api_total_submenus</td>";
    echo "<td style='text-align:center;'>" . ($submenus_match ? '✅' : '❌') . "</td>";
    echo "</tr>";
    
    echo "</table>";
    
    if (!$menus_match || !$submenus_match) {
        echo "<div style='background:#f8d7da; padding:15px; margin-top:20px; color:#721c24;'>";
        echo "<h3>❌ MISMATCH DETECTED!</h3>";
        echo "<p><strong>The API is not returning the same results as the admin dashboard.</strong></p>";
        echo "<p><strong>Root Cause:</strong> The API uses <code>permission_group_id</code> joins, but the admin dashboard uses <code>access_permissions</code> field parsing.</p>";
        echo "<p><strong>Solution:</strong> The API needs to replicate the exact logic used in the admin dashboard (parsing <code>access_permissions</code> field).</p>";
        echo "</div>";
    } else {
        echo "<div style='background:#d4edda; padding:15px; margin-top:20px; color:#155724;'>";
        echo "<h3>✅ PERFECT MATCH!</h3>";
        echo "<p>The API is returning the same results as the admin dashboard.</p>";
        echo "</div>";
    }
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background:#f8d7da; padding:15px; color:#721c24;'>";
    echo "<strong>Error:</strong> " . $e->getMessage();
    echo "</div>";
}
?>


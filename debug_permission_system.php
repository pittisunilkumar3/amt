<?php
/**
 * Comprehensive Permission System Debugger
 * This script analyzes the permission system to understand why submenus aren't showing
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
    
    // Test with a non-superadmin staff member
    $test_staff_id = 6; // Change this to test different staff
    
    echo "<h1>🔍 Permission System Debugger</h1>";
    echo "<p>Testing with Staff ID: <strong>$test_staff_id</strong></p>";
    echo "<hr>";
    
    // Step 1: Get staff info and role
    echo "<h2>Step 1: Staff Information</h2>";
    $stmt = $pdo->prepare("
        SELECT 
            s.id,
            s.name,
            s.surname,
            s.employee_id,
            s.is_active,
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
        WHERE s.id = ?
    ");
    $stmt->execute([$test_staff_id]);
    $staff = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$staff) {
        die("<div style='color:red;'>Staff member not found!</div>");
    }
    
    echo "<table border='1' cellpadding='8' style='border-collapse:collapse;'>";
    echo "<tr><th>Field</th><th>Value</th></tr>";
    foreach ($staff as $key => $value) {
        echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
    }
    echo "</table>";
    
    $role_id = $staff['role_id'];
    $is_superadmin = $staff['is_superadmin'];
    
    echo "<hr>";
    
    // Step 2: Check what permissions this role has
    echo "<h2>Step 2: Role Permissions (roles_permissions table)</h2>";
    $stmt = $pdo->prepare("
        SELECT 
            rp.id,
            rp.role_id,
            rp.perm_cat_id,
            rp.can_view,
            rp.can_add,
            rp.can_edit,
            rp.can_delete,
            pc.name as permission_name,
            pc.short_code as permission_code,
            pg.id as perm_group_id,
            pg.name as perm_group_name,
            pg.short_code as perm_group_code
        FROM roles_permissions rp
        JOIN permission_category pc ON pc.id = rp.perm_cat_id
        LEFT JOIN permission_group pg ON pg.id = pc.perm_group_id
        WHERE rp.role_id = ?
        ORDER BY pg.name, pc.name
    ");
    $stmt->execute([$role_id]);
    $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Total Permissions:</strong> " . count($permissions) . "</p>";
    echo "<table border='1' cellpadding='8' style='border-collapse:collapse; font-size:12px;'>";
    echo "<tr style='background:#f0f0f0;'>";
    echo "<th>Group</th><th>Permission</th><th>Code</th><th>View</th><th>Add</th><th>Edit</th><th>Delete</th>";
    echo "</tr>";
    
    $permission_groups = [];
    foreach ($permissions as $perm) {
        if ($perm['can_view'] == 1) {
            $permission_groups[$perm['perm_group_id']] = $perm['perm_group_code'];
        }
        
        echo "<tr>";
        echo "<td>" . ($perm['perm_group_name'] ?? 'N/A') . "</td>";
        echo "<td>" . $perm['permission_name'] . "</td>";
        echo "<td><code>" . $perm['permission_code'] . "</code></td>";
        echo "<td style='text-align:center;'>" . ($perm['can_view'] ? '✅' : '❌') . "</td>";
        echo "<td style='text-align:center;'>" . ($perm['can_add'] ? '✅' : '❌') . "</td>";
        echo "<td style='text-align:center;'>" . ($perm['can_edit'] ? '✅' : '❌') . "</td>";
        echo "<td style='text-align:center;'>" . ($perm['can_delete'] ? '✅' : '❌') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    
    // Step 3: Check sidebar_menus structure
    echo "<h2>Step 3: Sidebar Menus Structure</h2>";
    $stmt = $pdo->query("
        SELECT 
            sm.id,
            sm.menu,
            sm.permission_group_id,
            sm.access_permissions,
            sm.is_active,
            pg.short_code as perm_group_code
        FROM sidebar_menus sm
        LEFT JOIN permission_group pg ON pg.id = sm.permission_group_id
        WHERE sm.is_active = 1
        ORDER BY sm.level
        LIMIT 5
    ");
    $sample_menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Sample Menus (first 5):</strong></p>";
    echo "<table border='1' cellpadding='8' style='border-collapse:collapse; font-size:12px;'>";
    echo "<tr style='background:#f0f0f0;'>";
    echo "<th>ID</th><th>Menu</th><th>Permission Group ID</th><th>Group Code</th><th>Access Permissions</th>";
    echo "</tr>";
    
    foreach ($sample_menus as $menu) {
        echo "<tr>";
        echo "<td>" . $menu['id'] . "</td>";
        echo "<td><strong>" . $menu['menu'] . "</strong></td>";
        echo "<td>" . ($menu['permission_group_id'] ?? 'NULL') . "</td>";
        echo "<td>" . ($menu['perm_group_code'] ?? 'NULL') . "</td>";
        echo "<td style='font-size:10px;'>" . htmlspecialchars(substr($menu['access_permissions'], 0, 100)) . "...</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    
    // Step 4: Check sidebar_sub_menus structure
    echo "<h2>Step 4: Sidebar Sub-Menus Structure</h2>";
    $stmt = $pdo->query("
        SELECT 
            ssm.id,
            ssm.sidebar_menu_id,
            ssm.menu,
            ssm.permission_group_id,
            ssm.access_permissions,
            ssm.is_active,
            pg.short_code as perm_group_code,
            sm.menu as parent_menu
        FROM sidebar_sub_menus ssm
        LEFT JOIN permission_group pg ON pg.id = ssm.permission_group_id
        LEFT JOIN sidebar_menus sm ON sm.id = ssm.sidebar_menu_id
        WHERE ssm.is_active = 1
        ORDER BY ssm.sidebar_menu_id, ssm.level
        LIMIT 10
    ");
    $sample_submenus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Sample Submenus (first 10):</strong></p>";
    echo "<table border='1' cellpadding='8' style='border-collapse:collapse; font-size:12px;'>";
    echo "<tr style='background:#f0f0f0;'>";
    echo "<th>ID</th><th>Parent Menu</th><th>Submenu</th><th>Perm Group ID</th><th>Group Code</th><th>Access Permissions</th>";
    echo "</tr>";
    
    foreach ($sample_submenus as $submenu) {
        echo "<tr>";
        echo "<td>" . $submenu['id'] . "</td>";
        echo "<td>" . $submenu['parent_menu'] . "</td>";
        echo "<td><strong>" . $submenu['menu'] . "</strong></td>";
        echo "<td>" . ($submenu['permission_group_id'] ?? 'NULL') . "</td>";
        echo "<td>" . ($submenu['perm_group_code'] ?? 'NULL') . "</td>";
        echo "<td style='font-size:10px;'>" . htmlspecialchars(substr($submenu['access_permissions'], 0, 80)) . "...</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    
    // Step 5: Test the API query for menus
    echo "<h2>Step 5: Test API Query for Menus</h2>";
    
    if ($is_superadmin) {
        $stmt = $pdo->prepare("SELECT * FROM sidebar_menus WHERE is_active = 1 ORDER BY level");
        $stmt->execute();
    } else {
        $stmt = $pdo->prepare("
            SELECT DISTINCT sm.* 
            FROM sidebar_menus sm
            JOIN permission_category pc ON sm.permission_group_id = pc.perm_group_id
            JOIN roles_permissions rp ON pc.id = rp.perm_cat_id
            WHERE rp.role_id = ? AND rp.can_view = 1 AND sm.is_active = 1
            ORDER BY sm.level
        ");
        $stmt->execute([$role_id]);
    }
    
    $api_menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p><strong>Menus returned by API query:</strong> " . count($api_menus) . "</p>";
    
    echo "<hr>";
    
    // Step 6: Test the API query for submenus
    echo "<h2>Step 6: Test API Query for Submenus</h2>";
    
    if (count($api_menus) > 0) {
        $test_menu = $api_menus[0];
        echo "<p>Testing with first menu: <strong>" . $test_menu['menu'] . "</strong> (ID: " . $test_menu['id'] . ")</p>";
        
        if ($is_superadmin) {
            $stmt = $pdo->prepare("
                SELECT * FROM sidebar_sub_menus 
                WHERE sidebar_menu_id = ? AND is_active = 1 
                ORDER BY level
            ");
            $stmt->execute([$test_menu['id']]);
        } else {
            $stmt = $pdo->prepare("
                SELECT DISTINCT ssm.* 
                FROM sidebar_sub_menus ssm
                JOIN permission_category pc ON ssm.permission_group_id = pc.perm_group_id
                JOIN roles_permissions rp ON pc.id = rp.perm_cat_id
                WHERE rp.role_id = ? AND rp.can_view = 1 AND ssm.sidebar_menu_id = ? AND ssm.is_active = 1
                ORDER BY ssm.level
            ");
            $stmt->execute([$role_id, $test_menu['id']]);
        }
        
        $api_submenus = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<p><strong>Submenus returned by API query:</strong> " . count($api_submenus) . "</p>";
        
        if (count($api_submenus) > 0) {
            echo "<table border='1' cellpadding='8' style='border-collapse:collapse;'>";
            echo "<tr style='background:#d4edda;'><th>ID</th><th>Submenu</th><th>URL</th></tr>";
            foreach ($api_submenus as $submenu) {
                echo "<tr>";
                echo "<td>" . $submenu['id'] . "</td>";
                echo "<td>" . $submenu['menu'] . "</td>";
                echo "<td>" . $submenu['url'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div style='background:#f8d7da; padding:15px; color:#721c24;'>";
            echo "⚠️ <strong>NO SUBMENUS FOUND!</strong> This is the problem.";
            echo "</div>";
            
            // Debug: Check if submenus exist without permission filter
            $stmt = $pdo->prepare("
                SELECT * FROM sidebar_sub_menus 
                WHERE sidebar_menu_id = ? AND is_active = 1
            ");
            $stmt->execute([$test_menu['id']]);
            $all_submenus = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<p><strong>Total submenus for this menu (without permission filter):</strong> " . count($all_submenus) . "</p>";
            
            if (count($all_submenus) > 0) {
                echo "<p style='color:red;'><strong>Issue Found:</strong> Submenus exist but are being filtered out by permission check!</p>";
                echo "<p><strong>Checking why submenus are filtered:</strong></p>";
                
                foreach ($all_submenus as $submenu) {
                    echo "<div style='border:1px solid #ddd; padding:10px; margin:10px 0;'>";
                    echo "<strong>Submenu:</strong> " . $submenu['menu'] . "<br>";
                    echo "<strong>Permission Group ID:</strong> " . ($submenu['permission_group_id'] ?? 'NULL') . "<br>";
                    
                    if ($submenu['permission_group_id']) {
                        // Check if role has permission for this group
                        $stmt = $pdo->prepare("
                            SELECT pc.name, pc.short_code, rp.can_view
                            FROM permission_category pc
                            LEFT JOIN roles_permissions rp ON pc.id = rp.perm_cat_id AND rp.role_id = ?
                            WHERE pc.perm_group_id = ?
                        ");
                        $stmt->execute([$role_id, $submenu['permission_group_id']]);
                        $perm_check = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        echo "<strong>Permission categories in this group:</strong><br>";
                        echo "<ul>";
                        foreach ($perm_check as $pc) {
                            $has_view = $pc['can_view'] == 1 ? '✅ HAS VIEW' : '❌ NO VIEW';
                            echo "<li>" . $pc['name'] . " (" . $pc['short_code'] . ") - $has_view</li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "<strong style='color:orange;'>⚠️ No permission_group_id set!</strong><br>";
                        echo "<strong>Access Permissions:</strong> " . htmlspecialchars($submenu['access_permissions']) . "<br>";
                    }
                    echo "</div>";
                }
            }
        }
    }
    
    echo "<hr>";
    
    // Step 7: Summary and recommendations
    echo "<h2>Step 7: Analysis Summary</h2>";
    echo "<div style='background:#fff3cd; padding:20px; border-left:5px solid #ffc107;'>";
    echo "<h3>🔍 Key Findings:</h3>";
    echo "<ol>";
    echo "<li><strong>Permission System:</strong> The system uses TWO different permission approaches:";
    echo "<ul>";
    echo "<li><code>permission_group_id</code> + <code>roles_permissions</code> table (database-level)</li>";
    echo "<li><code>access_permissions</code> field with string parsing (application-level)</li>";
    echo "</ul>";
    echo "</li>";
    echo "<li><strong>Admin Dashboard:</strong> Uses <code>access_permissions</code> field and RBAC library</li>";
    echo "<li><strong>API Implementation:</strong> Uses <code>permission_group_id</code> joins</li>";
    echo "<li><strong>Potential Issue:</strong> If submenus don't have <code>permission_group_id</code> set, they won't appear in API</li>";
    echo "</ol>";
    
    echo "<h3>💡 Recommendations:</h3>";
    echo "<ol>";
    echo "<li>Check if <code>permission_group_id</code> is NULL for submenus</li>";
    echo "<li>Consider using <code>access_permissions</code> field instead (like admin dashboard)</li>";
    echo "<li>Or ensure all submenus have proper <code>permission_group_id</code> values</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='background:#f8d7da; padding:15px; color:#721c24;'>";
    echo "<strong>Database Error:</strong> " . $e->getMessage();
    echo "</div>";
}
?>


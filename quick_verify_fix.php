<?php
/**
 * Quick Verification Script
 * Run this immediately to verify the fix is working
 */

echo "<html><head><title>Quick Fix Verification</title></head><body>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .success { background: #d4edda; color: #155724; padding: 15px; border-left: 5px solid #28a745; margin: 10px 0; }
    .error { background: #f8d7da; color: #721c24; padding: 15px; border-left: 5px solid #dc3545; margin: 10px 0; }
    .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-left: 5px solid #17a2b8; margin: 10px 0; }
    .warning { background: #fff3cd; color: #856404; padding: 15px; border-left: 5px solid #ffc107; margin: 10px 0; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; background: white; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background: #007bff; color: white; }
    .highlight { background: #d4edda; font-weight: bold; }
</style>";

echo "<h1>🚀 Quick Fix Verification</h1>";
echo "<p>Testing the fixed API endpoint...</p>";
echo "<hr>";

// Test with a non-superadmin user
$test_staff_id = 6; // Change this if needed

echo "<div class='info'>";
echo "<strong>Testing with Staff ID:</strong> $test_staff_id<br>";
echo "<strong>API Endpoint:</strong> POST http://localhost/amt/api/teacher/menu";
echo "</div>";

// Call the API
$url = 'http://localhost/amt/api/teacher/menu';
$data = json_encode(['staff_id' => $test_staff_id]);

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
    echo "<div class='error'>";
    echo "<strong>❌ cURL Error:</strong> $curl_error";
    echo "</div>";
    exit;
}

if ($http_code != 200) {
    echo "<div class='error'>";
    echo "<strong>❌ HTTP Error:</strong> Status code $http_code<br>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    echo "</div>";
    exit;
}

$result = json_decode($response, true);

if (!$result || !isset($result['data']['menus'])) {
    echo "<div class='error'>";
    echo "<strong>❌ Invalid API Response</strong><br>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    echo "</div>";
    exit;
}

// Analyze results
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

// Display results
echo "<div class='success'>";
echo "<h2>✅ API Response Successful!</h2>";
echo "<strong>Staff:</strong> " . ($staff_info['full_name'] ?? 'Unknown') . "<br>";
echo "<strong>Role:</strong> " . ($role_info['name'] ?? 'Unknown') . "<br>";
echo "<strong>Is Superadmin:</strong> " . ($role_info['is_superadmin'] ? 'Yes' : 'No') . "<br>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>📊 Summary Statistics</h3>";
echo "<table>";
echo "<tr><th>Metric</th><th>Value</th><th>Status</th></tr>";
echo "<tr><td>Total Menus</td><td><strong>$total_menus</strong></td><td>" . ($total_menus > 0 ? '✅' : '❌') . "</td></tr>";
echo "<tr><td>Menus with Submenus</td><td><strong>$menus_with_submenus</strong></td><td>" . ($menus_with_submenus > 0 ? '✅' : '⚠️') . "</td></tr>";
echo "<tr class='highlight'><td>Total Submenus</td><td><strong>$total_submenus</strong></td><td>" . ($total_submenus > 0 ? '✅ FIXED!' : '❌ ISSUE') . "</td></tr>";
echo "</table>";
echo "</div>";

// Check if fix is working
if ($total_submenus > 0) {
    echo "<div class='success'>";
    echo "<h2>🎉 FIX VERIFIED!</h2>";
    echo "<p><strong>The API is now returning submenus correctly!</strong></p>";
    echo "<p>Before the fix, non-superadmin users would see 0 submenus. Now you're seeing <strong>$total_submenus submenus</strong>.</p>";
    echo "</div>";
} else {
    echo "<div class='warning'>";
    echo "<h2>⚠️ No Submenus Found</h2>";
    echo "<p>This could mean:</p>";
    echo "<ul>";
    echo "<li>The staff member has no permissions for any submenus</li>";
    echo "<li>The menus don't have submenus in the database</li>";
    echo "<li>There might still be an issue with the fix</li>";
    echo "</ul>";
    echo "<p><strong>Recommendation:</strong> Test with a superadmin user (staff_id with role_id = 7) to verify submenus exist in the database.</p>";
    echo "</div>";
}

// Display detailed menu breakdown
echo "<h3>📋 Detailed Menu Breakdown</h3>";
echo "<table>";
echo "<tr><th>Menu ID</th><th>Menu Name</th><th>Submenu Count</th><th>Sample Submenus</th></tr>";

foreach ($menus as $menu) {
    $submenu_count = count($menu['submenus'] ?? []);
    $row_class = $submenu_count > 0 ? 'class="highlight"' : '';
    
    echo "<tr $row_class>";
    echo "<td>" . $menu['id'] . "</td>";
    echo "<td><strong>" . htmlspecialchars($menu['menu']) . "</strong></td>";
    echo "<td style='text-align:center; font-size:18px;'><strong>$submenu_count</strong></td>";
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
}

echo "</table>";

// Next steps
echo "<div class='info'>";
echo "<h3>🔍 Next Steps</h3>";
echo "<ol>";
echo "<li><strong>Compare with Admin Dashboard:</strong> Log in as this staff member and verify the sidebar shows the same menus</li>";
echo "<li><strong>Test with Different Roles:</strong> Try different staff IDs to verify role-based filtering</li>";
echo "<li><strong>Run Full Comparison:</strong> <a href='simulate_admin_dashboard_filtering.php?staff_id=$test_staff_id' target='_blank'>Click here</a> to compare API vs Dashboard</li>";
echo "<li><strong>View Detailed Analysis:</strong> <a href='debug_permission_system.php' target='_blank'>Click here</a> for permission system analysis</li>";
echo "</ol>";
echo "</div>";

// Show sample API request
echo "<div class='info'>";
echo "<h3>📝 Sample API Request</h3>";
echo "<pre style='background:#263238; color:#aed581; padding:15px; border-radius:5px; overflow-x:auto;'>";
echo "curl -X POST http://localhost/amt/api/teacher/menu \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -d '{\"staff_id\": $test_staff_id}'";
echo "</pre>";
echo "</div>";

// Show what changed
echo "<div class='warning'>";
echo "<h3>🔧 What Was Fixed</h3>";
echo "<p><strong>Problem:</strong> The API was using <code>permission_group_id</code> database joins, but many submenus don't have this field set.</p>";
echo "<p><strong>Solution:</strong> Changed to use <code>access_permissions</code> field parsing (like the admin dashboard does).</p>";
echo "<p><strong>Result:</strong> Submenus now appear correctly for all users based on their role permissions.</p>";
echo "<p><strong>Files Modified:</strong> <code>api/application/controllers/Teacher_webservice.php</code></p>";
echo "</div>";

echo "</body></html>";
?>


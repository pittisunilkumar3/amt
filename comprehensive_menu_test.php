<?php
// Comprehensive test for teacher menu API with proper authentication

echo "<h1>Teacher Menu API Test</h1>";

// Configuration
$base_url = "http://localhost/amt/api";
$test_credentials = array(
    'admin' => array('email' => 'admin@admin.com', 'password' => 'admin123'),
    'teacher' => array('email' => 'teacher@school.com', 'password' => 'teacher123'),
    'staff' => array('email' => 'staff@school.com', 'password' => 'staff123')
);

$staff_id = $_GET['staff_id'] ?? 1;

echo "<h2>Testing for Staff ID: {$staff_id}</h2>";

// Step 1: Test the debug endpoint first (no auth required)
echo "<h3>Step 1: Testing Debug Endpoint (No Authentication)</h3>";
$debug_url = "{$base_url}/teacher/debug-menu?staff_id={$staff_id}";
echo "<p>URL: <a href='{$debug_url}' target='_blank'>{$debug_url}</a></p>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $debug_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$debug_response = curl_exec($ch);
$debug_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>Debug Response (HTTP {$debug_http_code}):</p>";
if ($debug_http_code == 200) {
    $debug_result = json_decode($debug_response, true);
    if ($debug_result && isset($debug_result['data'])) {
        echo "<p style='color: green;'>✓ Debug endpoint working</p>";
        echo "<p>Staff Found: " . ($debug_result['data']['debug_info']['staff_exists'] ? 'Yes' : 'No') . "</p>";
        echo "<p>Role Found: " . ($debug_result['data']['debug_info']['role_found'] ? 'Yes' : 'No') . "</p>";
        echo "<p>Menu Count: " . $debug_result['data']['debug_info']['menu_count'] . "</p>";
        
        if ($debug_result['data']['debug_info']['menu_count'] == 0) {
            echo "<div style='background: #ffeeee; padding: 10px; border: 1px solid red;'>";
            echo "<strong>Warning:</strong> No menus found for this staff member. This could be due to:";
            echo "<ul>";
            echo "<li>Staff member has no role assigned</li>";
            echo "<li>Role has no permissions</li>";
            echo "<li>No sidebar menus are configured</li>";
            echo "<li>Menu permission configuration issue</li>";
            echo "</ul>";
            echo "</div>";
        }
    } else {
        echo "<p style='color: red;'>✗ Debug endpoint failed</p>";
        echo "<pre>" . htmlspecialchars($debug_response) . "</pre>";
    }
} else {
    echo "<p style='color: red;'>✗ Debug endpoint failed with HTTP {$debug_http_code}</p>";
    echo "<pre>" . htmlspecialchars($debug_response) . "</pre>";
}

// Step 2: Test with authentication
echo "<h3>Step 2: Testing with Authentication</h3>";

// Try to get staff member's email from database first
$mysqli = new mysqli("localhost", "root", "", "school");
if (!$mysqli->connect_error) {
    $staff_query = "SELECT s.*, u.email 
                    FROM staff s 
                    LEFT JOIN users u ON u.user_id = s.id AND u.role = 'Staff'
                    WHERE s.id = ?";
    $stmt = $mysqli->prepare($staff_query);
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $staff_info = $stmt->get_result()->fetch_assoc();
    
    if ($staff_info && $staff_info['email']) {
        $test_email = $staff_info['email'];
        echo "<p>Found staff email: {$test_email}</p>";
    } else {
        $test_email = 'admin@admin.com'; // Fallback
        echo "<p>Using fallback email: {$test_email}</p>";
    }
    $mysqli->close();
} else {
    $test_email = 'admin@admin.com';
    echo "<p>Database connection failed, using fallback email: {$test_email}</p>";
}

// Login to get token
$login_data = array(
    'email' => $test_email,
    'password' => 'admin123', // You may need to adjust this
    'app_key' => 'demo_app'
);

echo "<h4>Login Request:</h4>";
echo "<pre>" . print_r($login_data, true) . "</pre>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "{$base_url}/teacher/login");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($login_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Client-Service: smartschool',
    'Auth-Key: schoolAdmin@'
));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$login_response = curl_exec($ch);
$login_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h4>Login Response (HTTP {$login_http_code}):</h4>";
echo "<pre>" . htmlspecialchars($login_response) . "</pre>";

$login_result = json_decode($login_response, true);

if ($login_result && isset($login_result['record']['token'])) {
    $token = $login_result['record']['token'];
    $user_id = $login_result['record']['id'];
    
    echo "<p style='color: green;'>✓ Login successful</p>";
    echo "<p>Token: " . substr($token, 0, 20) . "...</p>";
    echo "<p>User ID: {$user_id}</p>";
    
    // Now test the authenticated menu endpoint
    echo "<h4>Menu Request with Authentication:</h4>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "{$base_url}/teacher/menu");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Client-Service: smartschool',
        'Auth-Key: schoolAdmin@',
        'Authorization: ' . $token,
        'User-ID: ' . $user_id
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $menu_response = curl_exec($ch);
    $menu_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<h4>Menu Response (HTTP {$menu_http_code}):</h4>";
    echo "<pre>" . htmlspecialchars($menu_response) . "</pre>";
    
    $menu_result = json_decode($menu_response, true);
    if ($menu_result && $menu_result['status'] == 1) {
        echo "<p style='color: green;'>✓ Menu API successful</p>";
        echo "<p>Total Menus: " . ($menu_result['data']['total_menus'] ?? 0) . "</p>";
        echo "<p>Role: " . ($menu_result['data']['role']['name'] ?? 'Unknown') . "</p>";
        
        if (isset($menu_result['data']['menus']) && !empty($menu_result['data']['menus'])) {
            echo "<h4>Available Menus:</h4>";
            echo "<ul>";
            foreach ($menu_result['data']['menus'] as $menu) {
                echo "<li><strong>" . $menu['menu'] . "</strong>";
                if (!empty($menu['submenus'])) {
                    echo " (" . count($menu['submenus']) . " submenus)";
                }
                echo "</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<p style='color: red;'>✗ Menu API failed</p>";
        if (isset($menu_result['debug_info'])) {
            echo "<h4>Debug Info:</h4>";
            echo "<pre>" . print_r($menu_result['debug_info'], true) . "</pre>";
        }
    }
    
} else {
    echo "<p style='color: red;'>✗ Login failed</p>";
    if ($login_result) {
        echo "<pre>" . print_r($login_result, true) . "</pre>";
    }
}

// Navigation
echo "<hr>";
echo "<h3>Navigation:</h3>";
echo "<p><a href='test_debug_menu.php?staff_id={$staff_id}'>Test Debug Menu</a></p>";
echo "<p><a href='debug_menu_permissions.php?staff_id={$staff_id}'>View Database Debug</a></p>";
echo "<p><a href='test_menu_api.php'>Back to Staff List</a></p>";

// Test with different staff IDs
echo "<p>Test with different Staff IDs: ";
for ($i = 1; $i <= 5; $i++) {
    echo "<a href='?staff_id={$i}' style='margin-right: 10px;'>Staff {$i}</a>";
}
echo "</p>";
?>
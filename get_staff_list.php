<?php
/**
 * Helper script to get staff list for testing
 * Returns JSON list of staff members with their roles
 */

header('Content-Type: application/json');

// Database configuration
$db_host = 'localhost';
$db_name = 'school6';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get staff with their roles
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
        WHERE s.is_active = 1
        ORDER BY 
            CASE WHEN r.id = 7 THEN 0 ELSE 1 END,
            s.name
        LIMIT 50
    ");
    
    $stmt->execute();
    $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert numeric strings to integers
    foreach ($staff as &$member) {
        $member['id'] = (int)$member['id'];
        $member['role_id'] = $member['role_id'] ? (int)$member['role_id'] : null;
        $member['is_active'] = (int)$member['is_active'];
        $member['is_superadmin'] = (int)$member['is_superadmin'];
    }
    
    echo json_encode([
        'status' => 1,
        'message' => 'Staff list retrieved successfully',
        'count' => count($staff),
        'staff' => $staff
    ], JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 0,
        'message' => 'Database error: ' . $e->getMessage(),
        'staff' => []
    ], JSON_PRETTY_PRINT);
}


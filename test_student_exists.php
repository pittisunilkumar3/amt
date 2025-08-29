<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Student Existence Test</h1>";

try {
    // Include the main index file to bootstrap the application
    require_once 'index.php';
    
    // Get CI instance
    $CI =& get_instance();
    echo "✅ CodeIgniter loaded successfully<br>";
    
    // Load student model
    $CI->load->model('student_model');
    echo "✅ Student model loaded<br>";
    
    // Test if student ID 1057 exists
    $student = $CI->student_model->get(1057);
    
    if ($student) {
        echo "✅ Student ID 1057 exists<br>";
        echo "Name: " . $student['firstname'] . " " . $student['lastname'] . "<br>";
        echo "Student Session ID: " . $student['student_session_id'] . "<br>";
        echo "Class: " . $student['class'] . "<br>";
        echo "Section: " . $student['section'] . "<br>";
        
        // Test if we can get student session
        $studentSession = $CI->student_model->getStudentSession(1057);
        if ($studentSession) {
            echo "✅ Student session data available<br>";
            echo "Session: " . $studentSession['session'] . "<br>";
        } else {
            echo "❌ Student session data not available<br>";
        }
        
    } else {
        echo "❌ Student ID 1057 does NOT exist<br>";
        echo "<p>Try with a different student ID. Let's check what students exist:</p>";
        
        // Get first 5 students
        $students = $CI->student_model->get('', 5);
        if ($students) {
            echo "<h3>Available Students:</h3>";
            echo "<ul>";
            foreach ($students as $s) {
                echo "<li>ID: " . $s['id'] . " - " . $s['firstname'] . " " . $s['lastname'] . " (Session ID: " . $s['student_session_id'] . ")</li>";
            }
            echo "</ul>";
        }
    }
    
    // Test database connection
    echo "<h3>Database Test</h3>";
    $query = $CI->db->query("SELECT COUNT(*) as count FROM students");
    $result = $query->row();
    echo "Total students in database: " . $result->count . "<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString() . "<br>";
}

echo "<h3>Recommendations</h3>";
echo "<p>If student 1057 doesn't exist, try with an existing student ID from the list above.</p>";
echo "<p>Example: <code>http://localhost/amt/student/view/[EXISTING_ID]</code></p>";
?>

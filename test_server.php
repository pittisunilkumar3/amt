<?php
// Simple server test
echo "<h1>Server Test</h1>";
echo "<p>PHP is working!</p>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";

// Test if we can access the application directory
if (file_exists('application/config/config.php')) {
    echo "<p>✅ Application directory accessible</p>";
} else {
    echo "<p>❌ Application directory not accessible</p>";
}

// Test if we can include the index file
try {
    if (file_exists('index.php')) {
        echo "<p>✅ index.php exists</p>";
        
        // Try to include it
        ob_start();
        include 'index.php';
        $output = ob_get_clean();
        
        if (strlen($output) > 0) {
            echo "<p>✅ index.php loads successfully</p>";
        } else {
            echo "<p>⚠️ index.php loads but produces no output</p>";
        }
    } else {
        echo "<p>❌ index.php not found</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error loading index.php: " . $e->getMessage() . "</p>";
}

echo "<h2>Next Steps</h2>";
echo "<p>If this page loads successfully, try accessing:</p>";
echo "<ul>";
echo "<li><a href='student/view/1057'>Student View Page</a></li>";
echo "<li><a href='index.php'>Main Application</a></li>";
echo "</ul>";
?>

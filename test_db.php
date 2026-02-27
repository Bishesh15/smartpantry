<?php
/**
 * Database Connection Test
 * Use this file to test your database connection
 */

require_once __DIR__ . '/config/database.php';

echo "<h2>Database Connection Test</h2>";

try {
    $conn = getDB();
    
    if ($conn === null) {
        echo "<p style='color: red;'>❌ Database connection FAILED!</p>";
        echo "<p><strong>Possible issues:</strong></p>";
        echo "<ul>";
        echo "<li>MySQL service is not running</li>";
        echo "<li>Database 'SmartPantryFull' does not exist</li>";
        echo "<li>Wrong username/password in config/database.php</li>";
        echo "</ul>";
        echo "<p><strong>Solution:</strong></p>";
        echo "<ol>";
        echo "<li>Make sure XAMPP MySQL is running</li>";
        echo "<li>Open phpMyAdmin: <a href='http://localhost/phpmyadmin'>http://localhost/phpmyadmin</a></li>";
        echo "<li>Create database named 'SmartPantryFull'</li>";
        echo "<li>Import the schema: database/schema.sql</li>";
        echo "</ol>";
    } else {
        echo "<p style='color: green;'>✅ Database connection SUCCESSFUL!</p>";
        
        // Test query
        $stmt = $conn->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'SmartPantryFull'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $tableCount = $result['count'];
        
        if ($tableCount > 0) {
            echo "<p style='color: green;'>✅ Database 'SmartPantryFull' exists with {$tableCount} tables</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Database 'SmartPantryFull' exists but has no tables</p>";
            echo "<p><strong>You need to import the schema!</strong></p>";
            echo "<p><strong>Steps to import:</strong></p>";
            echo "<ol>";
            echo "<li>Open your MySQL application (phpMyAdmin or MySQL Workbench)</li>";
            echo "<li>Select the 'SmartPantryFull' database</li>";
            echo "<li>Go to Import/SQL tab</li>";
            echo "<li>Choose file: <code>database/schema.sql</code> from your project folder</li>";
            echo "<li>Click Import/Execute</li>";
            echo "</ol>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Go to Application</a></p>";
?>


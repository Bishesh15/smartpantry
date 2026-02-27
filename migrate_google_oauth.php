<?php
/**
 * Migration: Add google_id column to users table
 * Run this file once if you have an existing database without the google_id column.
 * 
 * Usage: Open in browser: http://localhost/smartpantry/migrate_google_oauth.php
 */

require_once __DIR__ . '/config/database.php';

echo "<h2>Smart Pantry - Google OAuth Migration</h2>";

try {
    $db = getDB();
    
    // Check if google_id column already exists
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'google_id'");
    
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE users ADD COLUMN google_id VARCHAR(255) DEFAULT NULL AFTER password_hash");
        $db->exec("ALTER TABLE users ADD INDEX idx_google_id (google_id)");
        echo "<p style='color:green;'>&#10004; Successfully added 'google_id' column to users table.</p>";
    } else {
        echo "<p style='color:blue;'>&#8505; Column 'google_id' already exists. No changes needed.</p>";
    }
    
    echo "<p>Migration complete. You can delete this file now.</p>";
    echo "<p><a href='views/user/login.php'>Go to Login Page</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color:red;'>&#10008; Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

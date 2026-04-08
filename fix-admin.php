<?php
/**
 * SmartPantry — Definitive Admin Fix
 * Visit: http://localhost/smartpantry/fix-admin.php
 * DELETE AFTER USE.
 */

// Direct PDO — no framework
try {
    $pdo = new PDO('mysql:host=localhost;dbname=smartpantry;charset=utf8mb4',
        'root', '1234', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (Exception $e) {
    die('<h2 style="color:red;font-family:sans-serif;">DB Error: ' . $e->getMessage() . '<br><br>
    Fix: open <code>config/database.php</code> and check your password (currently set to 1234)</h2>');
}

// Generate real hashes right now
$pass  = 'admin123';
$hash  = password_hash($pass, PASSWORD_BCRYPT);

// Reset ALL admins password
$pdo->prepare("UPDATE admins SET password = ?")->execute([$hash]);

// Also reset demo users
$pdo->prepare("UPDATE users SET password = ? WHERE username IN ('bishesh','ram_kumar','sita_devi')")->execute([$hash]);

// Double-check
$row      = $pdo->query("SELECT * FROM admins LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$ok       = $row && password_verify($pass, $row['password']);
$rowCount = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();

echo '<!DOCTYPE html><html><head>
<meta charset="UTF-8">
<title>Fix</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{font-family:sans-serif;} code{background:#f3f4f6;padding:2px 6px;border-radius:4px;}</style>
</head><body class="bg-light"><div class="container py-5" style="max-width:650px;">';

echo '<div class="card shadow-sm"><div class="card-body p-4">';
echo '<h3 class="fw-bold mb-3">🔧 SmartPantry Admin Fix</h3>';

// Show what's in admins table
echo '<h6>Admins table (' . $rowCount . ' rows):</h6>';
$admins = $pdo->query("SELECT id, username, LEFT(password,30) AS hash_peek FROM admins")->fetchAll(PDO::FETCH_ASSOC);
echo '<table class="table table-sm table-bordered mb-4"><tr><th>ID</th><th>Username</th><th>Hash (first 30 chars)</th></tr>';
foreach ($admins as $a) {
    echo '<tr><td>'.$a['id'].'</td><td>'.$a['username'].'</td><td><code>'.htmlspecialchars($a['hash_peek']).'…</code></td></tr>';
}
echo '</table>';

if ($ok) {
    echo '<div class="alert alert-success fs-5">
        ✅ <strong>Done! Password updated and verified.</strong><br><br>
        Login: <strong>admin</strong> / <strong>admin123</strong>
    </div>';
    echo '<div class="alert alert-warning">
        <strong>⚠ Now delete this file!</strong><br>
        PowerShell: <code>Remove-Item \'d:\xammp\htdocs\smartpantry\fix-admin.php\' -Force</code>
    </div>';
    echo '<a href="http://localhost/smartpantry/views/admin/login.php" class="btn btn-success btn-lg">Go to Admin Login →</a>';
} else {
    echo '<div class="alert alert-danger">
        ❌ password_verify still fails after update.<br>
        Row found: ' . ($row ? 'YES' : 'NO') . '<br>
        Hash generated: <code>' . htmlspecialchars($hash) . '</code>
    </div>';
    echo '<p>Try running this SQL manually in phpMyAdmin:</p>';
    echo '<pre style="background:#1e293b;color:#a3e635;padding:1rem;border-radius:8px;font-size:.82rem;">USE smartpantry;
UPDATE admins SET password = \'' . addslashes($hash) . '\' WHERE username = \'admin\';</pre>';
}

echo '</div></div></div></body></html>';

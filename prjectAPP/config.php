<?php
// ============================================================
//  config.php — Database connection (PDO)
//  Edit the constants below to match your server settings
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'vision2030_db');
define('DB_USER', 'root');        // ← change to your MySQL user
define('DB_PASS', '');            // ← change to your MySQL password
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST
             . ';dbname=' . DB_NAME
             . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    return $pdo;
}

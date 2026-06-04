<?php
// includes/db.php - Database connection configurations

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$db   = 'orchid_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // If the database has not been setup yet, offer redirection to setup.php
    if ($e->getCode() == 1049) { // Database not found code
        header("Location: setup.php");
        exit();
    }
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>

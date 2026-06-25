<?php
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3306';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$db   = getenv('DB_NAME') ?: 'sap_ptw';

$conn = mysqli_connect($host, $user, $pass, $db, (int) $port);

if (!$conn) {
    die('DB Connection Failed: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
?>
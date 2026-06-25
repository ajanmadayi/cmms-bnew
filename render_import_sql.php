<?php
set_time_limit(0);
ini_set('memory_limit', '512M');

$token = getenv('IMPORT_TOKEN') ?: '';
$provided = $_SERVER['HTTP_X_IMPORT_TOKEN'] ?? ($_GET['token'] ?? '');

if ($token === '' || !hash_equals($token, $provided)) {
    http_response_code(403);
    echo "Forbidden";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "POST the SQL dump body to this endpoint.";
    exit;
}

require_once __DIR__ . '/config/db.php';

$sql = file_get_contents('php://input');
if ($sql === false || trim($sql) === '') {
    http_response_code(400);
    echo "Empty SQL body.";
    exit;
}

mysqli_set_charset($conn, 'utf8mb4');
mysqli_query($conn, 'SET FOREIGN_KEY_CHECKS=0');

if (!mysqli_multi_query($conn, $sql)) {
    http_response_code(500);
    echo "Import failed: " . mysqli_error($conn);
    exit;
}

do {
    if ($result = mysqli_store_result($conn)) {
        mysqli_free_result($result);
    }
} while (mysqli_more_results($conn) && mysqli_next_result($conn));

if (mysqli_errno($conn)) {
    http_response_code(500);
    echo "Import failed: " . mysqli_error($conn);
    exit;
}

mysqli_query($conn, 'SET FOREIGN_KEY_CHECKS=1');
echo "Import complete";

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

$tmp = tempnam(sys_get_temp_dir(), 'cmms-import-');
if ($tmp === false) {
    http_response_code(500);
    echo "Could not create temporary file.";
    exit;
}

$input = fopen('php://input', 'rb');
$output = fopen($tmp, 'wb');
if ($input === false || $output === false) {
    @unlink($tmp);
    http_response_code(500);
    echo "Could not open import stream.";
    exit;
}

$bytes = stream_copy_to_stream($input, $output);
fclose($input);
fclose($output);

if ($bytes === false || $bytes === 0) {
    @unlink($tmp);
    http_response_code(400);
    echo "Empty SQL body.";
    exit;
}

$command = [
    'mysql',
    '--host=' . $host,
    '--port=' . $port,
    '--user=' . $user,
    '--default-character-set=utf8mb4',
    '--force',
    $db,
];

$descriptors = [
    0 => ['file', $tmp, 'rb'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];

$process = proc_open($command, $descriptors, $pipes, null, ['MYSQL_PWD' => $pass]);
if (!is_resource($process)) {
    @unlink($tmp);
    http_response_code(500);
    echo "Could not start mysql client.";
    exit;
}

$stdout = stream_get_contents($pipes[1]);
$stderr = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);

$exitCode = proc_close($process);
@unlink($tmp);

if ($exitCode !== 0) {
    http_response_code(500);
    echo "Import failed with exit code " . $exitCode . "\n" . $stderr . "\n" . $stdout;
    exit;
}

echo "Import complete: " . (int) $bytes . " bytes";

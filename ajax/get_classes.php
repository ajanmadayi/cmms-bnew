<?php
require_once('../config/db.php');

$description = $_POST['description'] ?? '';

if($description == ''){
    echo json_encode([]);
    exit;
}

$stmt = mysqli_prepare($conn,"
    SELECT DISTINCT class
    FROM asset_master
    WHERE description = ?
      AND class <> ''
    ORDER BY class
");

mysqli_stmt_bind_param($stmt,"s",$description);
mysqli_stmt_execute($stmt);

$res = mysqli_stmt_get_result($stmt);

$data = [];
while($r = mysqli_fetch_assoc($res)){
    $data[] = $r;
}

echo json_encode($data);
exit;

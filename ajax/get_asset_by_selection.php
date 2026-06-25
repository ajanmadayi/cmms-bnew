<?php
require_once('../config/db.php');

header('Content-Type: application/json');

$department = $_POST['department'] ?? '';
$ptw_dep    = $_POST['ptw_dep'] ?? '';
$unit_no    = $_POST['unit_no'] ?? '';
$system     = $_POST['system'] ?? '';
$sub_system = $_POST['sub_system'] ?? '';

$sql = "
SELECT asset_id, eqpt_code
FROM asset_master
WHERE department = ?
AND ptw_dep = ?
AND unit_no = ?
AND system = ?
AND sub_system = ?
LIMIT 1
";

$stmt = mysqli_prepare($conn,$sql);
mysqli_stmt_bind_param(
    $stmt,
    "sssss",
    $department,
    $ptw_dep,
    $unit_no,
    $system,
    $sub_system
);

mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if($row=mysqli_fetch_assoc($res)){
    echo json_encode([
        "status"=>"success",
        "asset_id"=>$row['asset_id'],
        "eqpt_code"=>$row['eqpt_code']
    ]);
}else{
    echo json_encode(["status"=>"error"]);
}

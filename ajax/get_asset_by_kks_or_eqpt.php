<?php
session_start();
require_once('../config/db.php');

header('Content-Type: application/json');

$value = trim($_POST['value'] ?? '');

if ($value == '') {
    echo json_encode(["status"=>"error"]);
    exit;
}

/* login department */
$loginDept = $_SESSION['department'] ?? '';
$username  = $_SESSION['username'] ?? '';

/* ===== user1 gets global access ===== */
if ($username === 'user1') {

    $sql = "
        SELECT *
        FROM asset_master
        WHERE kks_tag = ?
           OR eqpt_code = ?
        LIMIT 1
    ";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $value, $value);

} else {

    /* normal users restricted */
    $sql = "
        SELECT *
        FROM asset_master
        WHERE department = ?
          AND (kks_tag = ? OR eqpt_code = ?)
        LIMIT 1
    ";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $loginDept, $value, $value);
}

mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if (!$row = mysqli_fetch_assoc($res)) {
    echo json_encode(["status"=>"error"]);
    exit;
}

echo json_encode([
    "status"      => "success",
    "asset_id"    => $row['asset_id'],
    "department"  => $row['department'],
    "ptw_dep"     => $row['ptw_dep'],
    "unit_no"     => $row['unit_no'],
    "system"      => $row['system'],
    "sub_system"  => $row['sub_system'],
    "description" => $row['description'],
    "class"       => $row['class'],
    "group"       => $row['group'],
    "eqpt_code"   => $row['eqpt_code']
]);

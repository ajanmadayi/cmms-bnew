<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';

$value = trim($_POST['value'] ?? '');
if ($value === '') {
    echo json_encode(["status"=>"fail"]);
    exit;
}

$user_department = $_SESSION['department'] ?? '';
$user_role       = $_SESSION['role'] ?? '';

if ($user_role === 'ADMIN') {

    $sql = "
        SELECT
            asset_id, department, ptw_dep, unit_no,
            system, sub_system, description,
            IFNULL(NULLIF(class,''),'NA') AS class,
            IFNULL(NULLIF(`group`,''),'NA') AS `group`,
            eqpt_code
        FROM asset_master
        WHERE
              TRIM(kks_temp) LIKE CONCAT('%', ?, '%')
           OR TRIM(kks_tag)  LIKE CONCAT('%', ?, '%')
           OR TRIM(eqpt_code) LIKE CONCAT('%', ?, '%')
        ORDER BY asset_id DESC
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss",$value,$value,$value);

} else {

    $sql = "
        SELECT
            asset_id, department, ptw_dep, unit_no,
            system, sub_system, description,
            IFNULL(NULLIF(class,''),'NA') AS class,
            IFNULL(NULLIF(`group`,''),'NA') AS `group`,
            eqpt_code
        FROM asset_master
        WHERE (
              TRIM(kks_temp) LIKE CONCAT('%', ?, '%')
           OR TRIM(kks_tag)  LIKE CONCAT('%', ?, '%')
           OR TRIM(eqpt_code) LIKE CONCAT('%', ?, '%')
        )
        AND department = ?
        ORDER BY asset_id DESC
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss",$value,$value,$value,$user_department);
}

$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    echo json_encode(["status"=>"success"] + $row);
    exit;
}

echo json_encode(["status"=>"fail"]);

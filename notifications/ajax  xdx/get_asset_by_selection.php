<?php
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$p = $_POST;

$res = mysqli_query($conn,"
    SELECT asset_id, eqpt_code
    FROM asset_master
    WHERE department='{$p['department']}'
      AND ptw_dep='{$p['ptw_dep']}'
      AND unit_no='{$p['unit_no']}'
      AND system='{$p['system']}'
      AND sub_system='{$p['sub_system']}'
      AND description='{$p['description']}'
      AND IFNULL(class,'NA')='{$p['class']}'
      AND IFNULL(`group`,'NA')='{$p['group']}'
    LIMIT 1
");

if($r=mysqli_fetch_assoc($res)){
    echo json_encode(["status"=>"success"]+$r);
    exit;
}

echo json_encode(["status"=>"fail"]);

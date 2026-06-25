<?php
require_once __DIR__ . '/../../config/db.php';

$department = $_POST['department'] ?? '';
$ptw_dep    = $_POST['ptw_dep'] ?? '';
$unit_no    = $_POST['unit_no'] ?? '';
$system     = $_POST['system'] ?? '';

$res = mysqli_query($conn,"
    SELECT DISTINCT sub_system
    FROM asset_master
    WHERE department='$department'
      AND ptw_dep='$ptw_dep'
      AND unit_no='$unit_no'
      AND system='$system'
    ORDER BY sub_system
");

$data=[];
while($r=mysqli_fetch_assoc($res)){
    $data[]=$r;
}
echo json_encode($data);

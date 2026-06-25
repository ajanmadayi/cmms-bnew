<?php
require_once('../config/db.php');

$department = $_POST['department'] ?? '';
$ptw_dep    = $_POST['ptw_dep'] ?? '';
$unit_no    = $_POST['unit_no'] ?? '';
$system     = $_POST['system'] ?? '';
$sub_system = $_POST['sub_system'] ?? '';

$res = mysqli_query($conn,"
    SELECT DISTINCT description
    FROM asset_master
    WHERE department='$department'
      AND ptw_dep='$ptw_dep'
      AND unit_no='$unit_no'
      AND system='$system'
      AND sub_system='$sub_system'
    ORDER BY description
");

$data=[];
while($r=mysqli_fetch_assoc($res)){
    $data[]=$r;
}
echo json_encode($data);

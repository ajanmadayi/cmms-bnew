<?php
require_once('../config/db.php');

$department = $_POST['department'] ?? '';
$ptw_dep    = $_POST['ptw_dep'] ?? '';
$unit_no    = $_POST['unit_no'] ?? '';

$res = mysqli_query($conn,"
    SELECT DISTINCT system
    FROM asset_master
    WHERE department='$department'
      AND ptw_dep='$ptw_dep'
      AND unit_no='$unit_no'
    ORDER BY system
");

$data=[];
while($r=mysqli_fetch_assoc($res)){
    $data[]=$r;
}
echo json_encode($data);

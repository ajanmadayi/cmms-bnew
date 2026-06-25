<?php
require_once('../config/db.php');

$department = $_POST['department'] ?? '';
$ptw_dep    = $_POST['ptw_dep'] ?? '';

$res = mysqli_query($conn,"
    SELECT DISTINCT unit_no
    FROM asset_master
    WHERE department='$department'
      AND ptw_dep='$ptw_dep'
    ORDER BY unit_no
");

$data=[];
while($r=mysqli_fetch_assoc($res)){
    $data[]=$r;
}
echo json_encode($data);

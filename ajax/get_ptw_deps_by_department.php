<?php
require_once('../config/db.php');

$department = $_POST['department'] ?? '';
$res = mysqli_query($conn,"
    SELECT DISTINCT ptw_dep
    FROM asset_master
    WHERE department='$department'
    ORDER BY ptw_dep
");

$data=[];
while($r=mysqli_fetch_assoc($res)){
    $data[]=$r;
}
echo json_encode($data);

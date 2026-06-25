<?php
require_once('../config/db.php');
$res = mysqli_query($conn,"SELECT DISTINCT department FROM asset_master ORDER BY department");
$data=[];
while($r=mysqli_fetch_assoc($res)){
    $data[]=$r;
}
echo json_encode($data);

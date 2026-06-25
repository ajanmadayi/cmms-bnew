<?php
require_once __DIR__ . '/../../config/db.php';

$class = $_POST['class'] ?? '';

$res = mysqli_query($conn,"
    SELECT DISTINCT `group`
    FROM asset_master
    WHERE class='$class'
      AND `group` IS NOT NULL
      AND `group`!=''
    ORDER BY `group`
");

$data=[];
while($r=mysqli_fetch_assoc($res)){
    $data[]=$r;
}
echo json_encode($data);

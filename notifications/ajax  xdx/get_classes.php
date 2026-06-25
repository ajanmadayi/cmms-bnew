<?php
require_once __DIR__ . '/../../config/db.php';

$description = $_POST['description'] ?? '';

$res = mysqli_query($conn,"
    SELECT DISTINCT class
    FROM asset_master
    WHERE description='$description'
      AND class IS NOT NULL
      AND class!=''
    ORDER BY class
");

$data=[];
while($r=mysqli_fetch_assoc($res)){
    $data[]=$r;
}
echo json_encode($data);

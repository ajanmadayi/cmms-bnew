<?php
require_once('../config/db.php');

$description = $_POST['description'] ?? '';
$class       = $_POST['class'] ?? '';

if(!$description || !$class){
    echo json_encode([]);
    exit;
}

$sql = "
SELECT DISTINCT `group`
FROM asset_master
WHERE description = ?
AND class = ?
ORDER BY `group`
";

$stmt = mysqli_prepare($conn,$sql);
mysqli_stmt_bind_param($stmt,"ss",$description,$class);
mysqli_stmt_execute($stmt);

$res = mysqli_stmt_get_result($stmt);

$data=[];
while($row=mysqli_fetch_assoc($res)){
    if($row['group']!=''){
        $data[]=$row;
    }
}

echo json_encode($data);

<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
if(!$conn) exit;

$department = $_SESSION['department'];
$eqpt = trim($_GET['eqpt'] ?? '');

$where = "department='$department'";
if($eqpt !== ''){
    $where .= " AND eqpt_code='$eqpt'";
}

$q = mysqli_query($conn,"
    SELECT MONTH(notif_date) m, COUNT(*) c
    FROM notification_master
    WHERE $where
    GROUP BY MONTH(notif_date)
    ORDER BY m
");

$labels = [];
$data   = [];

for($i=1;$i<=12;$i++){
    $labels[$i] = date('M', mktime(0,0,0,$i,1));
    $data[$i]   = 0;
}

while($r=mysqli_fetch_assoc($q)){
    $data[(int)$r['m']] = (int)$r['c'];
}

echo json_encode([
    "labels"=>array_values($labels),
    "data"=>array_values($data)
]);

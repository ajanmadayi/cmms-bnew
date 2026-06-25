<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
if(!$conn) exit;

$data = json_decode(file_get_contents("php://input"), true);

$department = $_SESSION['department'];
$kks  = trim($data['kks'] ?? '');
$date = $data['date'] ?? date('Y-m-d');
$type = $data['type'] ?? 'day';

$where = "department='$department'";

/* KKS / EQPT FILTER */
if($kks !== ''){
    $where .= " AND (
        kks LIKE '%$kks%' OR
        eqpt_code LIKE '%$kks%'
    )";
}

/* DATE FILTER */
if($type === 'day'){
    $where .= " AND notif_date='$date'";
}
elseif($type === 'month'){
    $where .= " AND DATE_FORMAT(notif_date,'%Y-%m')='".substr($date,0,7)."'";
}
else{
    $where .= " AND YEAR(notif_date)='".substr($date,0,4)."'";
}

/* QUERY */
$q = mysqli_query($conn,"
    SELECT notif_date, system, sub_system, eqpt_code,
           valid_from, valid_to, status
    FROM notification_master
    WHERE $where
    ORDER BY notif_date DESC
");

$rows = [];
while($r=mysqli_fetch_assoc($q)){
    $rows[] = $r;
}

echo json_encode([
    "total"=>count($rows),
    "rows"=>$rows
]);

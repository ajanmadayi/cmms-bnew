<?php
session_start();
require_once('../../config/db.php');

header('Content-Type: application/json');

$department = strtoupper($_SESSION['department']);
$department = preg_replace('/_CCR$/','',$department);
$username   = $_SESSION['username'];

$mode  = $_GET['mode'] ?? 'day';
$kks   = $_GET['kks'] ?? '';

$where = "1=1";

/* Department/User logic */
if ($department === 'ALL') {
    $user = mysqli_real_escape_string($conn,$username);
    $where .= " AND created_by='$user'";
} else {
    $dept = mysqli_real_escape_string($conn,$department);
    $where .= " AND department='$dept'";
}

/* KKS filter */
if($kks!=''){
    $kks=mysqli_real_escape_string($conn,$kks);
    $where.=" AND (kks='$kks' OR eqpt_code='$kks')";
}

/* Mode filtering */
if($mode=='day'){
    $date=$_GET['date'] ?? date('Y-m-d');
    $where.=" AND notif_date='$date'";
    $group="HOUR(valid_from)";
    $label="LPAD(HOUR(valid_from),2,'0')";
}
elseif($mode=='month'){
    $month=$_GET['month'] ?? date('Y-m');
    $where.=" AND DATE_FORMAT(notif_date,'%Y-%m')='$month'";
    $group="DAY(notif_date)";
    $label="LPAD(DAY(notif_date),2,'0')";
}
else{
    $year=$_GET['year'] ?? date('Y');
    $where.=" AND YEAR(notif_date)='$year'";
    $group="MONTH(notif_date)";
    $label="MONTHNAME(notif_date)";
}

$sql="
SELECT $label lbl, COUNT(*) c
FROM notification_master
WHERE $where
GROUP BY $group
ORDER BY $group
";

$res=mysqli_query($conn,$sql);

$labels=[];
$data=[];

while($r=mysqli_fetch_assoc($res)){
    $labels[]=$r['lbl'];
    $data[]=$r['c'];
}

echo json_encode([
    'total'=>array_sum($data),
    'labels'=>$labels,
    'data'=>$data
]);

<?php
/* ================= DASHBOARD FILTER (AJAX) ================= */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['logged_in'])) {
    echo json_encode(["total"=>0,"rows"=>[],"chart"=>["labels"=>[],"data"=>[]]]);
    exit;
}

require_once __DIR__ . '/../../config/db.php';
if(!$conn){
    echo json_encode(["total"=>0,"rows"=>[],"chart"=>["labels"=>[],"data"=>[]]]);
    exit;
}

$user_department = $_SESSION['department'];
$user_role       = $_SESSION['role'];

/* ================= INPUT ================= */
$input = json_decode(file_get_contents("php://input"), true);

$kks   = trim($input['kks']   ?? '');
$year  = trim($input['year']  ?? '');
$month = trim($input['month'] ?? '');
$date  = trim($input['date']  ?? '');

/* ================= BASE WHERE ================= */
$where = [];

if ($user_role !== 'ADMIN') {
    $where[] = "department = '".mysqli_real_escape_string($conn,$user_department)."'";
}

/* KKS / EQUIPMENT FILTER */
if ($kks !== '') {
    $kksEsc = mysqli_real_escape_string($conn,$kks);
    $where[] = "(
        eqpt_code LIKE '%$kksEsc%' OR
        kks_temp  LIKE '%$kksEsc%' OR
        kks_tag   LIKE '%$kksEsc%'
    )";
}

/* DATE FILTER */
if ($date !== '') {
    $where[] = "notif_date = '".mysqli_real_escape_string($conn,$date)."'";
} else {
    if ($year !== '') {
        $where[] = "YEAR(notif_date) = '".mysqli_real_escape_string($conn,$year)."'";
    }
    if ($month !== '') {
        $where[] = "MONTH(notif_date) = '".intval($month)."'";
    }
}

$whereSQL = count($where) ? "WHERE ".implode(" AND ",$where) : "";

/* ================= TABLE DATA ================= */
$sql = "
SELECT
    notif_date,
    system,
    sub_system,
    eqpt_code,
    created_on,
    CONCAT(valid_from_date,' ',valid_from_hour,':',valid_from_min) AS valid_from,
    CONCAT(valid_to_date,' ',valid_to_hour,':',valid_to_min)       AS valid_to,
    status
FROM notification_master
$whereSQL
ORDER BY created_on DESC
LIMIT 500
";

$res = mysqli_query($conn,$sql);

$rows = [];
while($r=mysqli_fetch_assoc($res)){
    $rows[] = $r;
}

/* ================= TOTAL ================= */
$total = count($rows);

/* ================= CHART DATA ================= */
/* Month-wise if year selected, else day-wise if month+year, else year-wise */
$labels = [];
$data   = [];

if ($date !== '') {

    // single date â†’ status wise
    $q = mysqli_query($conn,"
        SELECT status, COUNT(*) c
        FROM notification_master
        $whereSQL
        GROUP BY status
    ");
    while($r=mysqli_fetch_assoc($q)){
        $labels[] = $r['status'];
        $data[]   = $r['c'];
    }

} elseif ($month !== '' && $year !== '') {

    // DAY WISE
    $q = mysqli_query($conn,"
        SELECT DAY(notif_date) d, COUNT(*) c
        FROM notification_master
        $whereSQL
        GROUP BY d
        ORDER BY d
    ");
    while($r=mysqli_fetch_assoc($q)){
        $labels[] = sprintf('%02d',$r['d']);
        $data[]   = $r['c'];
    }

} elseif ($year !== '') {

    // MONTH WISE
    $q = mysqli_query($conn,"
        SELECT MONTH(notif_date) m, COUNT(*) c
        FROM notification_master
        $whereSQL
        GROUP BY m
        ORDER BY m
    ");
    while($r=mysqli_fetch_assoc($q)){
        $labels[] = date('F',mktime(0,0,0,$r['m'],1));
        $data[]   = $r['c'];
    }

} else {

    // YEAR WISE
    $q = mysqli_query($conn,"
        SELECT YEAR(notif_date) y, COUNT(*) c
        FROM notification_master
        $whereSQL
        GROUP BY y
        ORDER BY y
    ");
    while($r=mysqli_fetch_assoc($q)){
        $labels[] = $r['y'];
        $data[]   = $r['c'];
    }
}

/* ================= OUTPUT ================= */
echo json_encode([
    "total" => $total,
    "rows"  => $rows,
    "chart" => [
        "labels" => $labels,
        "data"   => $data
    ]
]);
exit;

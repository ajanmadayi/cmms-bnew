<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . '/../config/db.php';
if (!$conn) die("DB Connection Failed");

/* SESSION DATA */
$department = $_SESSION['department'];
$role       = $_SESSION['role'];
$fullname   = $_SESSION['fullname'];
$username   = $_SESSION['username'];

/* normalize department */
$department = strtoupper($department);
$department = preg_replace('/_CCR$/','',$department);

/* FILTER INPUT */
$kks   = trim($_GET['kks'] ?? '');
$day   = $_GET['day'] ?? '';
$month = $_GET['month'] ?? date('m');
$year  = $_GET['year'] ?? date('Y');

/* WHERE */
$where = "1=1";

if ($role !== 'ADMIN') {

    if ($department === 'ALL') {
        $user = mysqli_real_escape_string($conn,$username);
        $where .= " AND created_by='$user'";
    } else {
        $dept = mysqli_real_escape_string($conn,$department);
        $where .= " AND department='$dept'";
    }
}

if ($kks !== '') {
    $kks = mysqli_real_escape_string($conn,$kks);
    $where .= " AND (kks='$kks' OR eqpt_code='$kks')";
}

if ($year)  $where .= " AND YEAR(notif_date)='$year'";
if ($month) $where .= " AND MONTH(notif_date)='$month'";
if ($day)   $where .= " AND DAY(notif_date)='$day'";

/* QUERY */
$sql = "
SELECT notif_no, notif_date, system, sub_system,
       created_by, valid_from, valid_to, status
FROM notification_master
WHERE $where
ORDER BY notif_date DESC
";

$res = mysqli_query($conn,$sql);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Notification List | CMMS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    background:#eeeeee;
    font-family:Segoe UI,Arial;
    margin:0;
}
.header-title{
    font-size:22px;
    font-weight:700;
    letter-spacing:.3px;
}

/* HEADER */
.app-header{
    position:fixed;
    top:0;left:0;right:0;
    height:60px;
    background:#808080;
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:0 20px;
    z-index:1000;
}

.app-header strong{
    font-size:18px;
    letter-spacing:.4px;
}

/* SIDEBAR */
.sidebar{
    position:fixed;
    top:60px;
    left:0;
    width:220px;
    height:calc(100vh - 60px);
    background:#707070;
}

.sidebar a{
    display:block;
    padding:12px 18px;
    color:#fff;
    text-decoration:none;
    font-weight:500;
}

.sidebar a:hover,
.sidebar a.active{
    background:#606060;
}

/* CONTENT */
.content{
    margin-left:220px;
    margin-top:60px;
    padding:22px;
}

/* CARD */
.card-box{
    background:#fff;
    padding:22px;
    border-radius:8px;
    box-shadow:0 4px 14px rgba(0,0,0,.12);
}

/* TABLE */
.table thead th{
    background:#f1f1f1;
    font-size:13px;
    font-weight:600;
}

.table td{
    font-size:13px;
    vertical-align:middle;
}

/* STATUS BADGES */
.badge{
    padding:6px 14px;
    border-radius:20px;
    color:#fff;
    font-size:12px;
    font-weight:600;
    min-width:130px;
    text-align:center;
}

.st1{background:#0d6efd;}   /* Ready */
.st2{background:#198754;}   /* PTW Created */
.st3{background:#dc3545;}   /* Returned */

@media(max-width:900px){
    .sidebar{
        position:relative;
        width:100%;
        height:auto;
    }
    .content{
        margin-left:0;
    }
}
</style>
</head>

<body>

<!-- HEADER -->
<div class="app-header">

    <div style="display:flex;align-items:center;gap:12px;">
        <img src="../assets/images/thdc_logo.png" height="34">
        <strong>CMMS â€“ Notification List</strong>
    </div>

    <div style="display:flex;align-items:center;gap:12px;">
        <img src="../assets/images/steag_logo.png" height="34">
        <?= htmlspecialchars($fullname) ?> |
        <a href="../logout.php" style="color:#fff;text-decoration:none;">Logout</a>
    </div>

</div>

<!-- SIDEBAR -->
<div class="sidebar">
    <a href="../dashboard/issuer_dashboard.php">Dashboard</a>
    <a href="notification_create.php">Create Notification</a>
    <a class="active">Notification List</a>
</div>

<!-- CONTENT -->
<div class="content">
<div class="card-box">

<h5 class="mb-3">Notification List</h5>

<div class="table-responsive">
<table class="table table-hover align-middle">
<thead>
<tr>
<th>No</th>
<th>Date</th>
<th>System</th>
<th>Sub System</th>
<th>Created By</th>
<th>Validity</th>
<th>Status</th>
</tr>
</thead>

<tbody>

<?php if(mysqli_num_rows($res)==0): ?>
<tr>
<td colspan="7" class="text-center text-muted py-4">
No notifications found
</td>
</tr>
<?php endif; ?>

<?php while($r=mysqli_fetch_assoc($res)): ?>
<tr>
<td><?= htmlspecialchars($r['notif_no']) ?></td>
<td><?= htmlspecialchars($r['notif_date']) ?></td>
<td><?= htmlspecialchars($r['system']) ?></td>
<td><?= htmlspecialchars($r['sub_system']) ?></td>
<td><?= htmlspecialchars($r['created_by']) ?></td>

<td>
<?= htmlspecialchars($r['valid_from']) ?><br>
<small class="text-muted">
to <?= htmlspecialchars($r['valid_to']) ?>
</small>
</td>

<td>
<?php
if($r['status']==1)
    echo '<span class="badge st1">READY FOR PTW</span>';
elseif($r['status']==2)
    echo '<span class="badge st2">PTW CREATED</span>';
else
    echo '<span class="badge st3">RETURNED</span>';
?>
</td>
</tr>
<?php endwhile; ?>

</tbody>
</table>
</div>

</div>
</div>

</body>
</html>

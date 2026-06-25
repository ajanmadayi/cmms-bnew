<?php
session_start();

/* ================= SECURITY ================= */
if (!isset($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit;
}
if ($_SESSION['role'] !== 'CCR') {
    header("Location: issuer_dashboard.php");
    exit;
}

/* ================= DATABASE ================= */
require_once __DIR__ . '/../config/db.php';

/* ================= SESSION DATA ================= */
$fullname   = $_SESSION['fullname'];
$department = strtoupper($_SESSION['department']);
$department = preg_replace('/_CCR$/', '', $department);

/* ================= VIEW FILTER ================= */
$view = $_GET['view'] ?? 'pending';

$statusMap = [
    'pending'  => 1,
    'approved' => 2,
    'returned' => 3
];

$where = "department='".mysqli_real_escape_string($conn,$department)."'";
if (isset($statusMap[$view])) {
    $where .= " AND status=".$statusMap[$view];
}

/* ================= FETCH DATA ================= */
$sql = "
SELECT notif_no, notif_date, system, sub_system,
       eqpt_code, created_by, status, ptw_no
FROM notification_master
WHERE $where
ORDER BY notif_date DESC
";
$q = mysqli_query($conn,$sql);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>CCR Dashboard | CMMS</title>

<style>
body{
    margin:0;
    background:#eeeeee;
    font-family:"Segoe UI",Arial,sans-serif;
}

/* ================= HEADER ================= */
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

.app-header img{height:34px}

.header-title{
    font-size:22px;
    font-weight:700;
}

/* ================= SIDEBAR ================= */
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
    color:#f1f1f1;
    text-decoration:none;
    font-weight:500;
}

.sidebar a.active,
.sidebar a:hover{
    background:#606060;
    color:#fff;
}

/* ================= CONTENT ================= */
.content{
    margin-left:220px;
    margin-top:60px;
    padding:25px;
}

/* ================= CARD ================= */
.card{
    background:#fff;
    padding:20px;
    border-radius:8px;
    box-shadow:0 4px 14px rgba(0,0,0,.12);
}

/* ================= TABLE ================= */
table{
    width:100%;
    border-collapse:collapse;
    font-size:14px;
}

th,td{
    padding:10px;
    border-bottom:1px solid #ddd;
}

th{
    background:#f3f4f6;
    text-align:left;
}

tr:hover{
    background:#f9fafb;
}

/* ================= STATUS ================= */
.status{
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
    font-weight:600;
}

.st1{background:#ffc107;color:#000;}
.st2{background:#198754;color:#fff;}
.st3{background:#dc3545;color:#fff;}

/* ================= BUTTONS ================= */
.btn{
    padding:6px 10px;
    color:#fff;
    text-decoration:none;
    border-radius:5px;
    font-size:12px;
}

.btn-blue{background:#0d6efd;}
.btn-blue:hover{background:#0b5ed7;}

.btn-red{background:#dc3545;}
.btn-red:hover{background:#bb2d3b;}

.btn-orange{background:#fd7e14;}
.btn-orange:hover{background:#e46c0a;}
</style>
</head>

<body>

<!-- HEADER -->
<div class="app-header">

    <div style="display:flex;align-items:center;gap:12px;">
        <img src="../assets/images/thdc_logo.png">
        <div class="header-title">CMMS â€“ CCR Dashboard</div>
    </div>

    <div style="display:flex;align-items:center;gap:12px;">
        <img src="../assets/images/steag_logo.png">
        <?= htmlspecialchars($fullname) ?>
        (<?= htmlspecialchars($department) ?> CCR) |
        <a href="../logout.php" style="color:#fff;text-decoration:none;">
            Logout
        </a>
    </div>

</div>

<!-- SIDEBAR -->
<div class="sidebar">
    <a class="<?= $view=='pending'?'active':'' ?>" href="?view=pending">
        Pending PTW
    </a>
    <a class="<?= $view=='approved'?'active':'' ?>" href="?view=approved">
        PTW Created
    </a>
    <a class="<?= $view=='returned'?'active':'' ?>" href="?view=returned">
        Returned
    </a>
    <a href="../notifications/notification_create.php">
        Create Notification
    </a>
</div>

<!-- CONTENT -->
<div class="content">
<div class="card">

<h3 style="margin-top:0;">
Notifications â€” <?= htmlspecialchars($department) ?>
</h3>

<table>
<thead>
<tr>
<th>Notification No</th>
<th>Date</th>
<th>System</th>
<th>Sub System</th>
<th>Equipment</th>
<th>Created By</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php if(mysqli_num_rows($q)==0): ?>
<tr>
<td colspan="8" style="text-align:center;color:#777">
No records found
</td>
</tr>
<?php endif; ?>

<?php while($r=mysqli_fetch_assoc($q)): ?>
<tr>
<td><?= htmlspecialchars($r['notif_no']) ?></td>
<td><?= htmlspecialchars($r['notif_date']) ?></td>
<td><?= htmlspecialchars($r['system']) ?></td>
<td><?= htmlspecialchars($r['sub_system']) ?></td>
<td><?= htmlspecialchars($r['eqpt_code']) ?></td>
<td><?= htmlspecialchars($r['created_by']) ?></td>

<td>
<span class="status st<?= (int)$r['status'] ?>">
<?= $r['status']==1?'READY FOR PTW':
   ($r['status']==2?'PTW CREATED':'RETURNED') ?>
</span>
</td>

<td>
<?php if($r['status']==1): ?>
<a class="btn btn-blue"
   href="../ptw/notification_ptw_create.php?notif_no=<?= urlencode($r['notif_no']) ?>">
Create PTW</a>

<?php elseif($r['status']==2 && $r['ptw_no']): ?>
<a class="btn btn-blue" target="_blank"
   href="../ptw/ptw_print.php?ptw_no=<?= urlencode($r['ptw_no']) ?>">
View PTW</a>

<a class="btn btn-red"
   href="return_notification.php?notif_no=<?= urlencode($r['notif_no']) ?>"
   onclick="return confirm('Return this PTW?');">
Return</a>

<?php elseif($r['status']==3): ?>
<a class="btn btn-orange"
   href="../ptw/notification_ptw_create.php?notif_no=<?= urlencode($r['notif_no']) ?>">
Re-Create PTW</a>
<?php endif; ?>
</td>

</tr>
<?php endwhile; ?>

</tbody>
</table>

</div>
</div>

</body>
</html>

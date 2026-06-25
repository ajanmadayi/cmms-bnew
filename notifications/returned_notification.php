<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . '/../config/db.php';
if (!$conn) die("DB Connection Failed");

$department = $_SESSION['department'];
$role       = $_SESSION['role'];
$fullname   = $_SESSION['fullname'];

/* remove _CCR if present */
$department = strtoupper($department);
$department = preg_replace('/_CCR$/','',$department);

/* ================= FILTERS ================= */
$kks   = trim($_GET['kks'] ?? '');
$year  = $_GET['year'] ?? date('Y');

/* ================= WHERE ================= */
$where = "status = 3"; // RETURNED only

if ($role !== 'ADMIN') {
    $where .= " AND department = '".mysqli_real_escape_string($conn,$department)."'";
}
if ($kks !== '') {
    $kks = mysqli_real_escape_string($conn,$kks);
    $where .= " AND (kks = '$kks' OR eqpt_code = '$kks')";
}
$where .= " AND YEAR(notif_date) = '$year'";

$sql = "
SELECT notif_no, notif_date, system, sub_system,
       created_by, valid_from, valid_to
FROM notification_master
WHERE $where
ORDER BY notif_date DESC
";
$res = mysqli_query($conn,$sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Returned Notifications | CMMS</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{background:#eeeeee;font-family:"Segoe UI",Arial,sans-serif;color:#1f2933}

.app-header{
    height:60px;background:#808080;color:#fff;
    display:flex;align-items:center;justify-content:space-between;
    padding:0 20px;position:fixed;top:0;left:0;right:0;z-index:1000
}
.app-header img{height:34px}

.sidebar{
    position:fixed;top:60px;left:0;width:220px;
    height:calc(100vh - 60px);background:#707070
}
.sidebar a{
    display:block;padding:12px 18px;color:#f1f1f1;
    text-decoration:none;font-weight:500
}
.sidebar a.active,.sidebar a:hover{
    background:#606060;color:#fff
}

.content{margin-left:220px;margin-top:60px;padding:25px}

.card-box{
    background:#fff;border-radius:8px;padding:22px;
    box-shadow:0 4px 14px rgba(0,0,0,.12)
}

.badge-returned{
    background:#6c757d;color:#fff;
    padding:6px 12px;border-radius:20px;
    font-size:12px;font-weight:600
}
</style>
</head>
<body>

<!-- HEADER -->
<div class="app-header">
    <img src="../assets/images/thdc_logo.png">
    <div>CMMS â€“ Returned Notifications</div>
    <div>
        <img src="../assets/images/steag_logo.png">
        <?= htmlspecialchars($fullname) ?> |
        <a href="../logout.php" style="color:#fff">Logout</a>
    </div>
</div>

<!-- SIDEBAR -->
<div class="sidebar">
    <a href="../dashboard/issuer_dashboard.php">Dashboard</a>
    <a href="notification_create.php">Create Notification</a>
    <a href="notification_list.php">Notification List</a>
    <a class="active">Returned</a>
</div>

<!-- CONTENT -->
<div class="content">
<div class="card-box">

<h5 class="mb-3">Returned Notifications</h5>

<form method="get" class="row g-2 mb-3">
    <div class="col-md-6">
        <input type="text" class="form-control" name="kks"
               placeholder="KKS / Equipment Code"
               value="<?= htmlspecialchars($kks) ?>">
    </div>
    <div class="col-md-4">
        <select class="form-select" name="year">
            <?php for($y=date('Y');$y>=2022;$y--): ?>
            <option value="<?= $y ?>" <?= ($year==$y?'selected':'') ?>>
                <?= $y ?>
            </option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="col-md-2">
        <button class="btn btn-dark w-100">Apply</button>
    </div>
</form>

<div class="table-responsive">
<table class="table table-hover align-middle">
<thead>
<tr>
    <th>Notification No</th>
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
No returned notifications found
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
        <small class="text-muted">to <?= htmlspecialchars($r['valid_to']) ?></small>
    </td>
    <td><span class="badge-returned">RETURNED</span></td>
</tr>
<?php endwhile; ?>

</tbody>
</table>
</div>

</div>
</div>
</body>
</html>

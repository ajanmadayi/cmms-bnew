<?php
session_start();

/* ================= SECURITY ================= */
if (
    !isset($_SESSION['logged_in']) ||
    $_SESSION['logged_in'] !== 1 ||
    $_SESSION['role'] !== 'ISSUER'
){
    header("Location: ../login.php");
    exit;
}

/* ================= DB ================= */
require_once('../config/db.php');

/* ================= USER ================= */
$fullname = $_SESSION['fullname'];
$username = $_SESSION['username'];

/* department fix */
$department = strtoupper($_SESSION['department']);
$department = preg_replace('/_CCR$/','',$department);
$department = mysqli_real_escape_string($conn,$department);

/* ================= COUNT FUNCTION ================= */
function cnt($conn,$where){
    $q = mysqli_query($conn,"SELECT COUNT(*) c FROM notification_master WHERE $where");
    return mysqli_fetch_assoc($q)['c'];
}

/* ================= COUNTS ================= */
if ($department === 'ALL') {
    $user = mysqli_real_escape_string($conn,$username);

    $total     = cnt($conn,"created_by='$user'");
    $pending   = cnt($conn,"created_by='$user' AND status=1");
    $ptw_done  = cnt($conn,"created_by='$user' AND status=2");
    $returned  = cnt($conn,"created_by='$user' AND status=3");
} else {
    $total     = cnt($conn,"department='$department'");
    $pending   = cnt($conn,"department='$department' AND status=1");
    $ptw_done  = cnt($conn,"department='$department' AND status=2");
    $returned  = cnt($conn,"department='$department' AND status=3");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Issuer Dashboard | CMMS</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body{
    margin:0;
    background:#eeeeee;
    font-family:"Segoe UI",Arial,sans-serif;
    color:#1f2933;
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
.app-header img{height:34px}

.header-title{
    font-size:24px;
    font-weight:700;
    letter-spacing:.4px;
}

/* SIDEBAR */
.sidebar{
    position:fixed;
    top:60px;left:0;
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

/* CONTENT */
.content{
    margin-left:220px;
    margin-top:60px;
    padding:25px;
}

/* SUMMARY CARDS */
.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
    margin-bottom:20px;
}
.card{
    background:#fff;
    padding:20px;
    border-radius:8px;
    box-shadow:0 4px 14px rgba(0,0,0,.12);
}
.card.total    { border-left:6px solid #0d6efd; }
.card.pending  { border-left:6px solid #fd7e14; }
.card.ptw      { border-left:6px solid #198754; }
.card.returned { border-left:6px solid #dc3545; }

.card h6{
    margin:0;
    color:#6b7280;
    font-size:12px;
    text-transform:uppercase;
}
.count{
    font-size:30px;
    font-weight:700;
    margin-top:6px;
}

/* BOX */
.box{
    background:#fff;
    padding:20px;
    border-radius:8px;
    box-shadow:0 4px 14px rgba(0,0,0,.12);
    margin-bottom:20px;
}

/* FILTER PANEL */
.analysis-grid{
    display:grid;
    grid-template-columns:2fr repeat(3,1fr) auto;
    gap:14px;
    align-items:end;
    background:#f7f7f7;
    padding:15px;
    border-radius:8px;
}

.analysis-grid label{
    font-size:13px;
    font-weight:600;
    color:#444;
}

.analysis-grid input,
.analysis-grid select{
    width:100%;
    padding:7px 8px;
    border:1px solid #c5c5c5;
    border-radius:6px;
}

.analysis-total{
    margin-top:12px;
    font-weight:600;
}

/* BUTTON */
button{
    padding:8px 18px;
    background:#606060;
    color:#fff;
    border:none;
    border-radius:6px;
    cursor:pointer;
}
button:hover{background:#505050}

/* Responsive */
@media(max-width:900px){
    .sidebar{position:relative;width:100%;height:auto}
    .content{margin-left:0}
}
</style>
</head>

<body>

<!-- HEADER -->
<div class="app-header">
    <div style="display:flex;align-items:center;gap:12px;">
        <img src="../assets/images/thdc_logo.png">
        <div class="header-title">CMMS – Issuer Dashboard</div>
    </div>

    <div style="display:flex;align-items:center;gap:12px;">
        <img src="../assets/images/steag_logo.png">
        <?= htmlspecialchars($fullname) ?> |
        <a href="../logout.php" style="color:#fff;text-decoration:none;">Logout</a>
    </div>
</div>

<!-- SIDEBAR -->
<div class="sidebar">
    <a class="active">Dashboard</a>
    <a href="../notifications/notification_create.php">Create Notification</a>
    <a href="../notifications/notification_list.php">Notification List</a>
    <a href="../notifications/returned_notification.php">Returned</a>
</div>

<!-- CONTENT -->
<div class="content">

<div class="cards">
    <div class="card total">
        <h6>Total Notifications</h6>
        <div class="count"><?= $total ?></div>
    </div>

    <div class="card pending">
        <h6>Ready for PTW</h6>
        <div class="count"><?= $pending ?></div>
    </div>

    <div class="card ptw">
        <h6>PTW Created</h6>
        <div class="count"><?= $ptw_done ?></div>
    </div>

    <div class="card returned">
        <h6>Returned</h6>
        <div class="count"><?= $returned ?></div>
    </div>
</div>

<div class="box">
<h5>Notification Analysis</h5>

<div class="analysis-grid">

<div>
<label>KKS / Equipment</label>
<input id="f_kks">
</div>

<div>
<label>View</label>
<select id="mode">
<option value="day">Day-wise</option>
<option value="month">Month-wise</option>
<option value="year">Year-wise</option>
</select>
</div>

<div id="dayBox">
<label>Date</label>
<input type="date" id="f_date" value="<?= date('Y-m-d') ?>">
</div>

<div id="monthBox" style="display:none">
<label>Month</label>
<input type="month" id="f_month" value="<?= date('Y-m') ?>">
</div>

<div id="yearBox" style="display:none">
<label>Year</label>
<select id="f_year">
<?php for($y=date('Y');$y>=2020;$y--) echo "<option>$y</option>"; ?>
</select>
</div>

<div>
<button onclick="loadData()">Apply</button>
</div>

</div>

<div class="analysis-total">
Total Notifications : <span id="totalCount">0</span>
</div>

</div>

<div class="box">
<h5>Statistics</h5>
<canvas id="chart" height="90"></canvas>
</div>

</div>

<script>
let chart;
const ctx=document.getElementById('chart');

mode.addEventListener('change',()=>{
    dayBox.style.display   = mode.value==='day'?'block':'none';
    monthBox.style.display = mode.value==='month'?'block':'none';
    yearBox.style.display  = mode.value==='year'?'block':'none';
});

function loadData(){
    const p=new URLSearchParams({
        kks  : f_kks.value,
        mode : mode.value,
        date : f_date?.value,
        month: f_month?.value,
        year : f_year?.value
    });

    fetch('ajax/dashboard_stats.php?'+p)
    .then(r=>r.json())
    .then(d=>{
        totalCount.innerText=d.total;

        if(chart) chart.destroy();
        chart=new Chart(ctx,{
            type:'bar',
            data:{labels:d.labels,datasets:[{data:d.data,backgroundColor:'#1f3c4f'}]},
            options:{responsive:true,plugins:{legend:{display:false}}}
        });
    });
}

loadData();
</script>

</body>
</html>

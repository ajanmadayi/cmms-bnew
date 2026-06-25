<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . '/../config/db.php';
if(!$conn) die("DB Connection Failed");


$ptw_no = $_GET['ptw_no'] ?? '';
if(!$ptw_no) die("PTW No missing");

$q = mysqli_query($conn,"
    SELECT * FROM ptw_master
    WHERE ptw_no='".mysqli_real_escape_string($conn,$ptw_no)."'
");
$ptw = mysqli_fetch_assoc($q);
if(!$ptw) die("Invalid PTW No");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>PTW â€“ <?= htmlspecialchars($ptw_no) ?></title>

<style>
@page{size:A4 portrait;margin:15mm;}
body{font-family:Arial;margin:0;background:#fff}
.page{max-width:900px;margin:auto;border:1px solid #000;padding:15px}
.header{position:relative;height:80px}
.header img.left{position:absolute;left:0;top:0;height:45px}
.header img.right{position:absolute;right:0;top:10px;height:35px}
.header .center{text-align:center;font-size:13px}
h3{text-align:center;margin:10px 0}
table{width:100%;border-collapse:collapse;font-size:13px}
th,td{border:1px solid #000;padding:6px}
th{background:#f1f1f1;width:30%}
.print-btn{text-align:center;margin:15px}
@media print{
 .print-btn{display:none}
 .page{border:none}
}
</style>
</head>

<body>

<div class="print-btn">
<button onclick="window.print()">ðŸ–¨ Print / Save as PDF</button>
</div>

<div class="page">

<div class="header">
<img src="../includes/thdc_logo.png" class="left">
<img src="../includes/steag_logo.png" class="right">
<div class="center">
<b>THDC INDIA LIMITED</b><br>
(A Joint Venture of Govt. of India & Govt. of U.P.)<br>
Site Office: Khurja, Bulandshahr (UP)
</div>
</div>

<h3>PERMIT TO WORK (PTW)</h3>

<table>
<tr><th>PTW No</th><td><?= $ptw['ptw_no'] ?></td></tr>
<tr><th>Date</th><td><?= $ptw['ptw_date'] ?></td></tr>
<tr><th>System</th><td><?= $ptw['system'] ?></td></tr>
<tr><th>Sub System</th><td><?= $ptw['sub_system'] ?></td></tr>
<tr><th>Description</th><td><?= $ptw['description'] ?></td></tr>
<tr><th>Equipment Code</th><td><?= $ptw['eqpt_code'] ?></td></tr>
<tr><th>Valid From</th><td><?= $ptw['valid_from'] ?></td></tr>
<tr><th>Valid To</th><td><?= $ptw['valid_to'] ?></td></tr>
<tr><th>Job Description</th><td><?= nl2br($ptw['job_description']) ?></td></tr>
<tr><th>Isolation System</th><td><?= $ptw['iso_system'] ?></td></tr>
<tr><th>Isolation Equipment</th><td><?= $ptw['iso_equipment'] ?></td></tr>
<tr><th>Isolation Description</th><td><?= nl2br($ptw['iso_description']) ?></td></tr>
<tr><th>Extra Permits</th><td><?= $ptw['extra_permits'] ?: 'N/A' ?></td></tr>
<tr><th>Status</th><td><?= $ptw['status'] ?></td></tr>
</table>

<br>

<table>
<tr><th>Permit Issued By</th><td height="40"></td>
<th>Permit Accepted By</th><td></td></tr>
<tr><th>Signature</th><td height="40"></td>
<th>Signature</th><td></td></tr>
</table>

</div>
</body>
</html>

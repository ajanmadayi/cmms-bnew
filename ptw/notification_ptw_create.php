<?php
session_start();

/* ================= SECURITY ================= */
if (
    !isset($_SESSION['logged_in']) ||
    !in_array($_SESSION['role'], ['CCR','CCRCHP'])
){
    header("Location: ../login.php");
    exit;
}

/* ================= DB ================= */
require_once __DIR__ . '/../config/db.php';
if (!$conn) die("DB Connection Failed");

/* ================= GET NOTIFICATION ================= */
$notif_no = $_GET['notif_no'] ?? '';
if ($notif_no === '') die("Invalid Notification");

$notif_no = mysqli_real_escape_string($conn,$notif_no);

$res = mysqli_query($conn,"
    SELECT *
    FROM notification_master
    WHERE notif_no='$notif_no'
      AND status=1
");

$notification = mysqli_fetch_assoc($res);
if (!$notification) die("Invalid or Already Processed Notification");

/* ================= PTW NUMBER ================= */
$r = mysqli_query($conn,"
    SELECT ptw_no
    FROM ptw_master
    ORDER BY id DESC LIMIT 1
");

$last = 0;
if ($x = mysqli_fetch_assoc($r))
    $last = (int)substr($x['ptw_no'], -5);

$ptw_no = "PTW".str_pad($last+1,5,"0",STR_PAD_LEFT);

/* ================= ISOLATION LIST ================= */
$isolations = [];
$q = mysqli_query($conn,"
    SELECT system,isolation_equipment
    FROM chp_isolation
");

while($r=mysqli_fetch_assoc($q))
    $isolations[] = $r;

/* ================= FORM SUBMIT ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ptw_date   = mysqli_real_escape_string($conn,$_POST['ptw_date']);
    $job_desc   = mysqli_real_escape_string($conn,$_POST['job_description']);
    $valid_from = mysqli_real_escape_string($conn,$_POST['valid_from']);
    $valid_to   = mysqli_real_escape_string($conn,$_POST['valid_to']);

    $iso_sys  = mysqli_real_escape_string($conn,$_POST['iso_system']);
    $iso_eq   = mysqli_real_escape_string($conn,$_POST['iso_equipment']);
    $iso_desc = mysqli_real_escape_string($conn,$_POST['iso_description']);

    $extra_permits = isset($_POST['extra_permits'])
        ? mysqli_real_escape_string($conn,implode(', ', $_POST['extra_permits']))
        : '';

    mysqli_query($conn,"
        INSERT INTO ptw_master
        (ptw_no,ptw_date,notif_no,
         unit_no,system,sub_system,description,
         class,`group`,eqpt_code,
         valid_from,valid_to,
         job_description,
         iso_system,iso_equipment,iso_description,
         extra_permits,
         created_by,created_role,status,created_at)
        VALUES
        ('$ptw_no','$ptw_date','$notif_no',
         '{$notification['unit_no']}',
         '{$notification['system']}',
         '{$notification['sub_system']}',
         '{$notification['description']}',
         '{$notification['class']}',
         '{$notification['group']}',
         '{$notification['eqpt_code']}',
         '$valid_from','$valid_to',
         '$job_desc',
         '$iso_sys','$iso_eq','$iso_desc',
         '$extra_permits',
         '{$_SESSION['fullname']}',
         '{$_SESSION['role']}',
         'CREATED',NOW())
    ");

    mysqli_query($conn,"
        UPDATE notification_master
        SET status=2,
            ptw_no='$ptw_no',
            approved_by='{$_SESSION['fullname']}',
            approved_role='{$_SESSION['role']}',
            approved_on=NOW()
        WHERE notif_no='$notif_no'
    ");

    header("Location: ptw_print.php?ptw_no=$ptw_no");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Create PTW | CMMS</title>

<style>
body{margin:0;background:#eeeeee;font-family:"Segoe UI",Arial}

/* HEADER */
.app-header{
    position:fixed;top:0;left:0;right:0;
    height:60px;background:#808080;color:#fff;
    display:flex;align-items:center;justify-content:space-between;
    padding:0 20px;z-index:1000;
}
.app-header img{height:34px}
.header-title{font-size:22px;font-weight:700}

/* SIDEBAR */
.app-sidebar{
    position:fixed;top:60px;left:0;width:220px;
    height:calc(100vh - 60px);background:#707070;
}
.app-sidebar a{
    display:block;padding:12px 18px;
    color:#f1f1f1;text-decoration:none;
}
.app-sidebar a.active,
.app-sidebar a:hover{
    background:#606060;color:#fff;
}

/* CONTENT */
.app-content{
    margin-left:220px;margin-top:60px;padding:25px;
}

/* CARD */
.card-box{
    background:#fff;border-radius:8px;
    padding:20px;
    box-shadow:0 4px 14px rgba(0,0,0,.12);
}

/* TABLE */
table{width:100%;border-collapse:collapse;margin-bottom:15px}
td{padding:10px;border-bottom:1px solid #ddd}
.label{background:#f3f4f6;font-weight:600;width:22%}

/* INPUT */
input,select,textarea{
    width:100%;padding:8px;
    border:1px solid #ccc;border-radius:6px;
}
textarea{height:80px}

/* BUTTON */
button{
    padding:10px 22px;background:#606060;
    color:#fff;border:none;border-radius:6px;
    cursor:pointer;
}
button:hover{background:#505050}

.section-title{
    margin:15px 0 8px;
    font-weight:600;color:#333;
}
</style>
</head>

<body>

<!-- HEADER -->
<div class="app-header">
    <div style="display:flex;align-items:center;gap:12px;">
        <img src="../assets/images/thdc_logo.png">
        <div class="header-title">CMMS â€“ Create PTW</div>
    </div>

    <div style="display:flex;align-items:center;gap:12px;">
        <img src="../assets/images/steag_logo.png">
        <?= htmlspecialchars($_SESSION['fullname']) ?>
    </div>
</div>

<!-- SIDEBAR -->
<div class="app-sidebar">
    <a href="../dashboard/ccr_dashboard.php">Dashboard</a>
    <a class="active">Create PTW</a>
</div>

<!-- CONTENT -->
<div class="app-content">
<div class="card-box">

<form method="post">

<table>
<tr>
<td class="label">PTW No</td>
<td><?= $ptw_no ?></td>
<td class="label">PTW Date</td>
<td><input type="date" name="ptw_date" value="<?= date('Y-m-d') ?>"></td>
</tr>

<tr>
<td class="label">Notification No</td>
<td><?= $notification['notif_no'] ?></td>
<td class="label">KKS</td>
<td><?= $notification['kks'] ?></td>
</tr>

<tr>
<td class="label">Department</td>
<td><?= $notification['department'] ?></td>
<td class="label">Equipment</td>
<td><?= $notification['eqpt_code'] ?></td>
</tr>

<tr>
<td class="label">System</td>
<td><?= $notification['system'] ?></td>
<td class="label">Sub System</td>
<td><?= $notification['sub_system'] ?></td>
</tr>

<tr>
<td class="label">Valid From</td>
<td><input type="datetime-local" name="valid_from"></td>
<td class="label">Valid To</td>
<td><input type="datetime-local" name="valid_to"></td>
</tr>
</table>

<div class="section-title">Job Description</div>
<textarea name="job_description"></textarea>

<div class="section-title">Isolation</div>

<table>
<tr>
<td class="label">Isolation System</td>
<td>
<input name="iso_system" id="iso_system"
       value="<?= $notification['sub_system'] ?>" readonly>
</td>

<td class="label">Isolation Equipment</td>
<td>
<select name="iso_equipment" id="iso_equipment"></select>
</td>
</tr>

<tr>
<td class="label">Isolation Description</td>
<td colspan="3">
<textarea name="iso_description"></textarea>
</td>
</tr>
</table>

<div class="section-title">Extra Permits</div>

<label><input type="checkbox" name="extra_permits[]" value="Hot Work"> Hot Work</label><br>
<label><input type="checkbox" name="extra_permits[]" value="Confined Space"> Confined Space</label><br>
<label><input type="checkbox" name="extra_permits[]" value="Height Work"> Height Work</label>

<br><br>
<button type="submit">Generate PTW</button>

</form>
</div>
</div>

<script>
const rows = <?= json_encode($isolations) ?>;
const eq = document.getElementById('iso_equipment');

function normalize(v){
    return (v || '').toLowerCase().trim();
}

const sys = normalize(
    document.getElementById('iso_system').value
);

eq.innerHTML = '<option value="">--Select--</option>';

rows.forEach(r=>{
    if(normalize(r.system) === sys){
        const o = document.createElement('option');
        o.value = r.isolation_equipment;
        o.textContent = r.isolation_equipment;
        eq.appendChild(o);
    }
});
</script>

</body>
</html>

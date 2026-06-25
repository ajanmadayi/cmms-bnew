<?php
session_start();

/* ================= DB ================= */
require_once __DIR__ . '/config/db.php';

/* ================= INPUT ================= */
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    header("Location: login.php?error=1");
    exit;
}

/* ================= AUTH ================= */
$q = mysqli_prepare($conn,"
    SELECT id, fullname, role, department
    FROM users
    WHERE username = ?
      AND password = MD5(?)
      AND active = 1
    LIMIT 1
");

mysqli_stmt_bind_param($q, "ss", $username, $password);
mysqli_stmt_execute($q);
$res = mysqli_stmt_get_result($q);

if (mysqli_num_rows($res) !== 1) {
    header("Location: login.php?error=1");
    exit;
}

$user = mysqli_fetch_assoc($res);

/* =================================================
   ROLE NORMALIZATION
================================================= */
$rawRole = strtoupper(trim($user['role']));

/* CCR FAMILY */
if (strpos($rawRole, 'CCR') !== false) {
    $role = 'CCR';
}
/* ISSUER FAMILY + NOTIFICATION USER */
elseif (in_array($rawRole, ['ISSUER','USER','CHP','NOTIF_USER'])) {
    $role = 'ISSUER';
}
/* UNKNOWN ROLE */
else {
    session_destroy();
    header("Location: login.php?unauthorized=1");
    exit;
}

/* ================= SESSION ================= */
session_regenerate_id(true);

$_SESSION['logged_in']  = true;
$_SESSION['id']         = $user['id'];
$_SESSION['fullname']   = $user['fullname'];
$_SESSION['role']       = $role;
$_SESSION['department'] = $user['department'];

/* ================= ROUTING ================= */
if ($role === 'CCR') {
    header("Location: dashboard/ccr_dashboard.php");
    exit;
}

if ($role === 'ISSUER') {
    header("Location: dashboard/issuer_dashboard.php");
    exit;
}

/* ================= FAILSAFE ================= */
session_destroy();
header("Location: login.php?unauthorized=1");
exit;

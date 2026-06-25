<?php
session_start();

/* ================= ERROR REPORTING ================= */
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ================= DB CONNECTION ================= */
require_once __DIR__ . '/config/db.php';

/* ================= IF ALREADY LOGGED IN ================= */
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === 1) {
    $role = $_SESSION['role'] ?? '';
    if ($role === 'CCR') {
        header("Location: dashboard/ccr_dashboard.php");
        exit;
    }
    if ($role === 'ISSUER') {
        header("Location: dashboard/issuer_dashboard.php");
        exit;
    }
}

/* ================= LOGIN LOGIC ================= */
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = "Username and Password required";
    } else {

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, username, fullname, department, role, password
             FROM users WHERE username=? LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($res)) {

            if ($row['password'] === $password) {

                session_regenerate_id(true);

                $rawRole = strtoupper(trim($row['role']));

                if (in_array($rawRole, ['CCR','BMD','BMD_CCR','TMD_CCR']))
                    $role = 'CCR';
                elseif (in_array($rawRole, ['ISSUER','USER','CHP','NOTIF_USER']))
                    $role = 'ISSUER';
                else
                    die('Unauthorized role');

                $_SESSION['logged_in']=1;
                $_SESSION['id']=$row['id'];
                $_SESSION['username']=$row['username'];
                $_SESSION['fullname']=$row['fullname'];
                $_SESSION['department']=$row['department'];
                $_SESSION['role']=$role;

                header("Location: dashboard/".
                    ($role==='CCR'?'ccr_dashboard.php':'issuer_dashboard.php'));
                exit;
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "User not found";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>CMMS Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    background:#355d6b;
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    font-family:Segoe UI,Arial;
}

.login-card{
    width:460px;
    border-radius:10px;
    box-shadow:0 15px 40px rgba(0,0,0,.35);
}

.login-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:10px;
}

.login-header img{
    height:40px;
}

.login-title{
    text-align:center;
    font-size:22px;
    font-weight:700;
    margin:10px 0 20px;
}

.login-btn{
    background:#355d6b;
    border:none;
}

.login-btn:hover{
    background:#2d4f5b;
}

.demo-box{
    background:#f1f1f1;
    padding:10px;
    border-radius:6px;
    font-size:13px;
    margin-top:10px;
}
</style>
</head>

<body>

<div class="card login-card">
<div class="card-body p-4">

    <!-- LOGOS -->
    <div class="login-header">
        <img src="assets/images/thdc_logo.png">
        <img src="assets/images/steag_logo.png">
    </div>

    <div class="login-title">
        CMMS Login
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger text-center">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="post">

        <input type="text" name="username"
               class="form-control mb-3"
               placeholder="Username" required autofocus>

        <input type="password" name="password"
               class="form-control mb-3"
               placeholder="Password" required>

        <button class="btn login-btn text-white w-100">
            Login
        </button>

    </form>

    <div class="demo-box mt-3">
        <strong>Demo Users</strong><br>
        User :user1/ 123456<br>
        CCR : chp_ccr / 1234<br>
        CCR : bmd_ccr / 1234
    </div>

</div>
</div>

</body>
</html>

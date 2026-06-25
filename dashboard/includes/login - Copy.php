<?php
session_start();

/* =================================================
   IF USER IS ALREADY LOGGED IN → ROLE REDIRECT
   ================================================= */
if (isset($_SESSION['logged_in']) && isset($_SESSION['role'])) {

    switch ($_SESSION['role']) {

        case 'ISSUER':
        case 'USER':
            header("Location: dashboard/issuer_dashboard.php");
            break;

        /* HOD DASHBOARD REMOVED PERMANENTLY */

        case 'CCRCHP':
            header("Location: dashboard/ccr_dashboard.php");
            break;

        case 'SAFETY':
            header("Location: dashboard/safety_dashboard.php");
            break;

        default:
            session_destroy();
            header("Location: login.php");
            break;
    }
    exit;
}

$error = isset($_GET['error']);
?>
<!DOCTYPE html>
<html>
<head>
<title>CMMS Login</title>
<meta charset="utf-8">
<style>
body{
    margin:0;
    height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#2c5364;
    font-family:Arial
}
.card{
    background:#fff;
    width:420px;
    padding:25px;
    border-radius:6px
}
img{height:40px}
input,button{
    width:100%;
    padding:10px;
    margin:8px 0
}
button{
    background:#2c5364;
    color:#fff;
    border:none;
    cursor:pointer
}
.err{
    background:#f8d7da;
    padding:8px;
    margin-bottom:10px;
    color:#721c24
}
.demo{
    background:#f4f6f8;
    padding:10px;
    font-size:13px
}
</style>
</head>

<body>

<div class="card">

    <div style="display:flex;justify-content:space-between;align-items:center">
        <img src="thdc_logo.png">
        <img src="steag_logo.png">
    </div>

    <h2 align="center">CMMS Login</h2>

    <?php if ($error): ?>
        <div class="err">Invalid Login</div>
    <?php endif; ?>

    <form method="post" action="authenticate.php">
        <input name="username" placeholder="User ID" required>
        <input name="password" type="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>

    <div class="demo">
        <b>Demo Users</b><br>
        CHP User : chp_user1 / 1234<br>
        CHP CCR  : chp_ccr / 1234<br>
        Safety   : safety_user / 1234
    </div>

</div>

</body>
</html>

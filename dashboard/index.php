<?php
session_start();

if (!isset($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit;
}

switch ($_SESSION['role']) {
    case 'CCR':
        header("Location: ccr_dashboard.php");
        break;

    case 'CHP':
        header("Location: chp_dashboard.php");
        break;

    case 'ADMIN':
        header("Location: admin_dashboard.php");
        break;

    default:
        header("Location: ../login.php");
}
exit;

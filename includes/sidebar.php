<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ================= SECURITY ================= */
if (!isset($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit;
}

$role = $_SESSION['role'] ?? '';
$role = strtoupper($role);

/* ================= DASHBOARD ROUTE ================= */
$dashboardLink = '../login.php';

if ($role === 'CCR') {
    $dashboardLink = '../dashboard/ccr_dashboard.php';
} elseif ($role === 'ISSUER') {
    $dashboardLink = '../dashboard/issuer_dashboard.php';
}
?>

<div class="sidebar">
    <a href="<?= $dashboardLink ?>">Dashboard</a>

    <?php if ($role === 'CCR'): ?>
        <a href="../notifications/notification_create.php">Create Notification</a>
        <a href="../notifications/notification_list.php">Notification List</a>
    <?php endif; ?>

    <?php if ($role === 'ISSUER'): ?>
        <a href="../notifications/notification_create.php">Create Notification</a>
        <a href="../notifications/notification_list.php">My Notifications</a>
    <?php endif; ?>

    <a href="../logout.php">Logout</a>
</div>

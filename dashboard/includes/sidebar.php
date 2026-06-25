<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit;
}

$role = $_SESSION['role'] ?? '';
?>

<div class="sidebar">

    <!-- DASHBOARD (ROLE ROUTER) -->
    <a href="../dashboard/index.php">
        Dashboard
    </a>

    <!-- CREATE NOTIFICATION (COMMON) -->
    <a href="../notifications/create_notification.php">
        Create Notification
    </a>

    <!-- NOTIFICATION LIST (COMMON) -->
    <a href="../notifications/notification_list.php">
        Notification List
    </a>

    <?php if ($role === 'CCR'): ?>
        <!-- CCR ONLY -->
        <a href="../ccr/ccr_reports.php">
            CCR Reports
        </a>
    <?php endif; ?>

    <?php if ($role === 'CHP'): ?>
        <!-- CHP ONLY -->
        <a href="../chp/chp_dashboard.php">
            CHP Dashboard
        </a>
    <?php endif; ?>

    <?php if ($role === 'ADMIN'): ?>
        <!-- ADMIN ONLY -->
        <a href="../admin/user_management.php">
            User Management
        </a>
        <a href="../admin/master_data.php">
            Master Data
        </a>
    <?php endif; ?>

</div>

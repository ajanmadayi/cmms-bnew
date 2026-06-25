<?php
session_start();
require_once('../config/db.php');

/* ========== SECURITY ========== */
if (!isset($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit;
}

/* ========== DATA ========== */
$asset_id        = $_POST['asset_id'] ?? 0;
$kks             = trim($_POST['kks'] ?? '');
$eqpt_code       = trim($_POST['eqpt_code'] ?? '');
$ptw_dep         = $_POST['ptw_dep'] ?? '';
$department      = $_POST['department'] ?? '';
$unit_no         = $_POST['unit_no'] ?? '';
$system          = $_POST['system'] ?? '';
$sub_system      = $_POST['sub_system'] ?? '';
$description     = $_POST['description'] ?? '';
$class           = $_POST['class'] ?? '';
$group           = $_POST['group'] ?? '';
$job_description = $_POST['job_description'] ?? '';
$notif_date      = $_POST['notif_date'] ?? date('Y-m-d');
$valid_from      = $_POST['valid_from'] ?? date('Y-m-d H:i:s');
$valid_to        = $_POST['valid_to'] ?? date('Y-m-d H:i:s');

$created_by      = $_SESSION['username'];
$created_role    = $_SESSION['role'];
$status          = '1';

/* ========== TRANSACTION ========== */
mysqli_begin_transaction($conn);

try {

    /* ========== INSERT (NO notif_no) ========== */
    $sql = "
        INSERT INTO notification_master (
            asset_id, kks, eqpt_code,
            ptw_dep, department, unit_no,
            system, sub_system, description,
            class, `group`, job_description,
            notif_date, valid_from, valid_to,
            created_by, created_role, status, created_on
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
        )";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception(mysqli_error($conn));
    }

    mysqli_stmt_bind_param(
        $stmt,
        "isssssssssssssssss",
        $asset_id,
        $kks,
        $eqpt_code,
        $ptw_dep,
        $department,
        $unit_no,
        $system,
        $sub_system,
        $description,
        $class,
        $group,
        $job_description,
        $notif_date,
        $valid_from,
        $valid_to,
        $created_by,
        $created_role,
        $status
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_stmt_error($stmt));
    }

    /* ========== GET AUTO ID ========== */
    $notification_id = mysqli_insert_id($conn);
    if ($notification_id <= 0) {
        throw new Exception("AUTO_INCREMENT not working");
    }

    /* ========== GENERATE notif_no ========== */
    $notif_no = 'THDCNOT' . str_pad($notification_id, 6, '0', STR_PAD_LEFT);

    /* ========== UPDATE SAME ROW ========== */
    $upd = mysqli_prepare(
        $conn,
        "UPDATE notification_master
         SET notif_no = ?
         WHERE notification_id = ?"
    );

    mysqli_stmt_bind_param($upd, "si", $notif_no, $notification_id);

    if (!mysqli_stmt_execute($upd)) {
        throw new Exception(mysqli_stmt_error($upd));
    }

    mysqli_commit($conn);

    $_SESSION['success'] = "Notification Created : $notif_no";
    header("Location: notification_list.php");
    exit;

} catch (Exception $e) {

    mysqli_rollback($conn);
    error_log("NOTIFICATION SAVE ERROR: " . $e->getMessage());

    $_SESSION['error'] = $e->getMessage();
    header("Location: notification_create.php");
    exit;
}

<?php
/* ================= DB CONNECTION ================= */
require_once __DIR__ . '/../../config/db.php';

if (!$conn) {
    http_response_code(500);
    echo json_encode([]);
    exit;
}

/* ================= INPUT ================= */
$keyword = trim($_POST['keyword'] ?? '');

if ($keyword === '' || strlen($keyword) < 2) {
    echo json_encode([]);
    exit;
}

$k = mysqli_real_escape_string($conn, $keyword);

/* ================= SEARCH QUERY ================= */
$sql = "
SELECT
    ptw_dep,
    department,
    unit_no,
    system,
    sub_system,
    sub_system_details,
    description,
    final_description,
    eqpt_code,
    class,
    `group`,
    location,
    equipment_make,
    kks_tag,
    drawing_no
FROM asset_master
WHERE
       ptw_dep            LIKE '%$k%'
    OR department         LIKE '%$k%'
    OR unit_no            LIKE '%$k%'
    OR system             LIKE '%$k%'
    OR sub_system         LIKE '%$k%'
    OR sub_system_details LIKE '%$k%'
    OR description        LIKE '%$k%'
    OR final_description  LIKE '%$k%'
    OR eqpt_code          LIKE '%$k%'
    OR class              LIKE '%$k%'
    OR `group`            LIKE '%$k%'
    OR location           LIKE '%$k%'
    OR equipment_make     LIKE '%$k%'
    OR kks_tag            LIKE '%$k%'
    OR drawing_no         LIKE '%$k%'
LIMIT 20
";

$res = mysqli_query($conn, $sql);

$data = [];
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $data[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($data);
exit;

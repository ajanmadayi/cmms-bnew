<?php
require_once __DIR__ . '/../../config/db.php';

$k = mysqli_real_escape_string($conn, $_POST['keyword']);

$sql = "
SELECT
    id AS asset_id,
    kks,
    eqpt_code,
    ptw_dep,
    unit_no,
    system,
    sub_system,
    description,
    class,
    `group`
FROM asset_master
WHERE
    kks LIKE '%$k%' OR
    eqpt_code LIKE '%$k%' OR
    system LIKE '%$k%' OR
    sub_system LIKE '%$k%' OR
    description LIKE '%$k%'
LIMIT 20
";

$r = mysqli_query($conn, $sql);

$data = [];
while ($row = mysqli_fetch_assoc($r)) {
    $data[] = $row;
}

echo json_encode($data);

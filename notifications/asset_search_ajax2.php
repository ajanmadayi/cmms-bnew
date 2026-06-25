<?php
require_once('../config/db.php');

$q = trim($_POST['keyword'] ?? '');

if ($q === '' || strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$q = mysqli_real_escape_string($conn, $q);

/*
  SEARCH ALL FIELDS
  ❌ EXCLUDE asset_id
  ❌ EXCLUDE kks_temp
*/

$sql = "
SELECT
    system,
    sub_system,
    sub_system_details,
    description
FROM asset_master
WHERE
    status = 1
    AND (
        system LIKE '%$q%'
        OR sub_system LIKE '%$q%'
        OR sub_system_details LIKE '%$q%'
        OR description LIKE '%$q%'
        OR final_description LIKE '%$q%'
        OR location LIKE '%$q%'
        OR equipment_make LIKE '%$q%'
        OR equipment_technical_data LIKE '%$q%'
        OR drawing_no LIKE '%$q%'
    )
LIMIT 20
";

$res = mysqli_query($conn, $sql);

$data = [];
while ($row = mysqli_fetch_assoc($res)) {
    $data[] = $row;
}

echo json_encode($data);

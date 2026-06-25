<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Asset Search Demo | CMMS</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
body{
    background:#eeeeee;
    font-family:Segoe UI,Arial;
}
.card{
    margin-top:40px;
}
#asset_result{
    position:absolute;
    z-index:9999;
    background:#ffffff;
    border:1px solid #ccc;
    width:100%;
    max-height:240px;
    overflow-y:auto;
    display:none;
}
.asset-item{
    padding:10px;
    cursor:pointer;
    border-bottom:1px solid #eee;
}
.asset-item:hover{
    background:#f3f3f3;
}
</style>
</head>

<body>

<div class="container">
    <div class="card p-4 shadow-sm">
        <h5 class="mb-3">Asset Master Search</h5>

        <div class="position-relative">
            <input type="text"
                   id="asset_search"
                   class="form-control"
                   placeholder="Search system, sub system, description, equipment, location…">

            <div id="asset_result"></div>
        </div>
    </div>
</div>

<script>
$('#asset_search').on('keyup', function () {

    const q = $(this).val().trim();

    if (q.length < 2) {
        $('#asset_result').hide().html('');
        return;
    }

    $.ajax({
        url: 'asset_search_ajax.php',
        type: 'POST',
        dataType: 'json',
        data: { keyword: q },
        success: function (res) {

            let html = '';

            if (res.length === 0) {
                html = '<div class="p-2 text-muted">No results found</div>';
            } else {
                res.forEach(r => {
                    html += `
                    <div class="asset-item">
                        <strong>${r.eqpt_code ?? '-'}</strong><br>
                        ${r.system} / ${r.sub_system}<br>
                        <small class="text-muted">
                            ${r.description ?? ''} ${r.sub_system_details ?? ''}
                        </small>
                    </div>`;
                });
            }

            $('#asset_result').html(html).show();
        },
        error: function (e) {
            console.error(e.responseText);
        }
    });
});
</script>

</body>
</html>

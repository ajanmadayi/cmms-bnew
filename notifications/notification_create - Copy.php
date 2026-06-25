<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit;
}

$fullname   = $_SESSION['fullname'];
$department = $_SESSION['department'];

 




?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Create Notification | CMMS</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
body{
    background:#eeeeee;                 /* light neutral background */
    font-family:Segoe UI,Arial;
    color:#1f2933;
}

/* ================= HEADER ================= */
.header{
    position:fixed;
    top:0;left:0;right:0;
    height:60px;
    background:#808080;                 /* ✅ MAIN GRAY */
    color:#ffffff;
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:0 20px;
    z-index:9999;
}
.header img{height:34px}

/* ================= LAYOUT ================= */
.content{
    margin-top:60px;
    margin-left:220px;
    padding:25px;
}

/* ================= SIDEBAR ================= */
.sidebar{
    position:fixed;
    top:60px;
    left:0;
    width:220px;
    height:calc(100vh - 60px);
    background:#707070;                 /* slightly darker than header */
}

/* Sidebar links */
.sidebar a{
    display:block;
    padding:12px 18px;
    color:#f1f1f1;                      /* readable light text */
    text-decoration:none;
    font-size:14px;
    font-weight:500;
}

/* Hover + Active */
.sidebar a.active,
.sidebar a:hover{
    background:#606060;                 /* darker hover */
    color:#ffffff;
}

/* ================= CARD ================= */
.card-box{
    background:#ffffff;
    border-radius:8px;
    box-shadow:0 4px 14px rgba(0,0,0,.12);
}

/* ================= FILLED FIELD HIGHLIGHT ================= */
.form-control.filled,
.form-select.filled{
    background-color:#f3f3f3 !important; /* soft grey fill */
    border-color:#808080 !important;     /* theme gray */
}
</style>

</head>
<script>
$(document).ready(function(){

/* ===================== HELPERS ===================== */
function clear(sel){
    $(sel).html('<option value="">Select</option>').removeClass('filled');
}

function resetAsset(){
    $('#asset_id').val('');
    $('#eqpt_code').val('').removeClass('filled');
}

function markFilled(el){
    if ($(el).val() && $(el).val().trim() !== '') {
        $(el).addClass('filled');
    } else {
        $(el).removeClass('filled');
    }
}

function markAllFilled(){
    $('#notificationForm').find('input, select, textarea').each(function(){
        markFilled(this);
    });
}

$(document).on('input change','input,select,textarea',function(){
    markFilled(this);
});

/* ===================== LOAD PTW ===================== */
function loadPTW(dept){

    clear('#ptw_dep');

    if(!dept) return;

    $.post('../ajax/get_ptw_deps_by_department.php',
        { department: dept },
        function(res){
            JSON.parse(res||'[]').forEach(r=>{
                $('#ptw_dep').append(
                    `<option value="${r.ptw_dep}">${r.ptw_dep}</option>`
                );
            });
        }
    );
}

/* ===================== DEPARTMENT CHANGE ===================== */
$('#department').on('change', function(){

    loadPTW(this.value);

    resetAsset();

    if(!deptChangeByKKS){
        $('#kks').val('').removeClass('filled');
        $('#asset_search').val('');
        $('#asset_result').hide().html('');
    }

    clear('#unit_no');
    clear('#system');
    clear('#sub_system');
    clear('#description');
    clear('#class');
    clear('#group');

    markAllFilled();

    deptChangeByKKS = false;
});



/* ===================== PTW → UNIT ===================== */
$('#ptw_dep').change(function(){

    clear('#unit_no,#system,#sub_system,#description,#class,#group');
    resetAsset();

    if(!this.value) return;

    $.post('../ajax/get_units.php',{
        department: $('#department').val(),
        ptw_dep: this.value
    },function(res){
        JSON.parse(res||'[]').forEach(r=>{
            $('#unit_no').append(
                `<option value="${r.unit_no}">${r.unit_no}</option>`
            );
        });
    });
});

/* ===================== UNIT → SYSTEM ===================== */
$('#unit_no').change(function(){

    clear('#system,#sub_system,#description,#class,#group');
    resetAsset();

    if(!this.value) return;

    $.post('../ajax/get_systems.php',{
        department: $('#department').val(),
        ptw_dep: $('#ptw_dep').val(),
        unit_no: this.value
    },function(res){
        JSON.parse(res||'[]').forEach(r=>{
            $('#system').append(
                `<option value="${r.system}">${r.system}</option>`
            );
        });
    });
});

/* ===================== SYSTEM → SUB SYSTEM ===================== */
$('#system').change(function(){

    clear('#sub_system,#description,#class,#group');
    resetAsset();

    if(!this.value) return;

    $.post('../ajax/get_sub_systems.php',{
        department: $('#department').val(),
        ptw_dep: $('#ptw_dep').val(),
        unit_no: $('#unit_no').val(),
        system: this.value
    },function(res){
        JSON.parse(res||'[]').forEach(r=>{
            $('#sub_system').append(
                `<option value="${r.sub_system}">${r.sub_system}</option>`
            );
        });
    });
});

/* ===================== SUB SYSTEM → DESCRIPTION ===================== */
$('#sub_system').change(function(){

    clear('#description,#class,#group');
    resetAsset();

    if(!this.value) return;

    $.post('../ajax/get_descriptions.php',{
        department: $('#department').val(),
        ptw_dep: $('#ptw_dep').val(),
        unit_no: $('#unit_no').val(),
        system: $('#system').val(),
        sub_system: this.value
    },function(res){
        JSON.parse(res||'[]').forEach(r=>{
            $('#description').append(
                `<option value="${r.description}">${r.description}</option>`
            );
        });
    });
});

/* ===================== DESCRIPTION → CLASS ===================== */
$('#description').change(function(){

    clear('#class,#group');
    resetAsset();

    if(!this.value) return;

    $.post('../ajax/get_classes.php',{
        description: this.value
    },function(res){
        JSON.parse(res||'[]').forEach(r=>{
            $('#class').append(
                `<option value="${r.class}">${r.class}</option>`
            );
        });
    });
});

/* ===================== CLASS → GROUP ===================== */
$('#class').change(function(){

    clear('#group');
    resetAsset();

    if(!this.value) return;

    $.post('../ajax/get_groups.php',{
        description: $('#description').val(),
        class: this.value
    },function(res){
        JSON.parse(res||'[]').forEach(r=>{
            $('#group').append(
                `<option value="${r.group}">${r.group}</option>`
            );
        });
    });
});

/* ===================== GROUP → EQUIPMENT ===================== */
$('#group').change(function(){

    if(
        !$('#ptw_dep').val() ||
        !$('#unit_no').val() ||
        !$('#system').val() ||
        !$('#sub_system').val() ||
        !$('#description').val()
    ) return;

    $.post('../ajax/get_asset_by_selection.php',{
        department : $('#department').val(),
        ptw_dep    : $('#ptw_dep').val(),
        unit_no    : $('#unit_no').val(),
        system     : $('#system').val(),
        sub_system : $('#sub_system').val(),
        description: $('#description').val(),
        class      : $('#class').val() || '',
        group      : $('#group').val() || ''
    },function(d){

        if(d.status === 'success'){
            $('#asset_id').val(d.asset_id);
            $('#eqpt_code').val(d.eqpt_code).addClass('filled');
            markAllFilled();
        } else {
            resetAsset();
        }

    },'json');
});

/* ===================== KKS SEARCH ===================== */
$('#kks').on('blur', function(){

    const v = $(this).val().trim();
    if(!v) return;

    $.post('../ajax/get_asset_by_kks_or_eqpt.php',
        { value:v },
        function(d){

          if(d.status === 'success'){

    deptChangeByKKS = true;

    $('#department').val(d.department).trigger('change');

    // wait until PTW reload finishes
    setTimeout(function(){

        $('#asset_id').val(d.asset_id);
        $('#eqpt_code').val(d.eqpt_code);

        $('#ptw_dep').html(`<option selected>${d.ptw_dep}</option>`);
        $('#unit_no').html(`<option selected>${d.unit_no}</option>`);
        $('#system').html(`<option selected>${d.system}</option>`);
        $('#sub_system').html(`<option selected>${d.sub_system}</option>`);
        $('#description').html(`<option selected>${d.description}</option>`);
        $('#class').html(`<option selected>${d.class}</option>`);
        $('#group').html(`<option selected>${d.group}</option>`);

        markAllFilled();

    }, 300); // wait for dropdown reset
}

            else{
                alert('Invalid KKS / Equipment Code');
            }

        }, 'json');
});

markAllFilled();

});
/* ================= GLOBAL ASSET SEARCH ================= */
$('#asset_search').on('keyup', function () {

    const q = $(this).val().trim();

    if (q.length < 2) {
        $('#asset_result').hide().html('');
        return;
    }

    $.ajax({
        url: '../ajax/asset_search_ajax.php',
        type: 'POST',
        dataType: 'json',
        data: { keyword: q },

        success: function (res) {

            let html = '';

            if (!res || res.length === 0) {
                html = '<div class="p-2 text-muted">No results found</div>';
            } else {
                res.forEach(r => {
                    html += `
                    <div class="asset-item p-2"
                         style="cursor:pointer;border-bottom:1px solid #eee"
                         data-kks="${r.kks_tag ?? ''}"
                         data-eqpt="${r.eqpt_code ?? ''}">
                        <strong>${r.eqpt_code ?? '-'}</strong><br>
                        ${r.system} / ${r.sub_system}<br>
                        <small class="text-muted">
                            ${r.description ?? ''}
                        </small>
                    </div>`;
                });
            }

            $('#asset_result').html(html).show();
        }
    });
});

/* ================= CLICK SEARCH RESULT ================= */
$(document).on('click', '.asset-item', function () {

    const kks  = $(this).data('kks');
    const eqpt = $(this).data('eqpt');

    const value = kks && kks !== '' ? kks : eqpt;

    $('#kks').val(value).trigger('blur');

    $('#asset_search').val('');
    $('#asset_result').hide();
});

/* hide results when clicking outside */
$(document).on('click', function (e) {
    if (!$(e.target).closest('#asset_search, #asset_result').length) {
        $('#asset_result').hide();
    }
});

</script>






<body>

<div class="header">
    <img src="../assets/images/thdc_logo.png">
    <strong>CMMS – Create Notification</strong>
    <div>
        <img src="../assets/images/steag_logo.png">
        <?= htmlspecialchars($fullname) ?> |
        <a href="../logout.php" class="text-white ms-2">Logout</a>
    </div>
</div>

<div class="sidebar">
    <a href="../dashboard/index.php">Dashboard</a>

    <a class="active">Create Notification</a>
    <a href="notification_list.php">Notification List</a>
</div>

<div class="content">
<div class="card card-box p-4">

<h5 class="mb-3">Notification Details</h5>

<form id="notificationForm" method="post" action="notification_save.php">
<input type="hidden" id="asset_id" name="asset_id">

<div class="row g-3">

<div class="col-md-3">
<label>Date</label>
<input type="date" class="form-control" name="notif_date" value="<?= date('Y-m-d') ?>">
</div>

<div class="col-md-3">
<label>KKS / Equipment Code</label>
<input class="form-control" id="kks" name="kks">
</div>

<div class="col-md-6 position-relative">
    <label>Search Asset (System / Sub System / Description)</label>
    <input type="text"
           id="asset_search"
           class="form-control"
           placeholder="Type system, sub system, description…">

    <div id="asset_result"
         style="position:absolute;
                z-index:9999;
                background:#ffffff;
                border:1px solid #ccc;
                width:100%;
                max-height:240px;
                overflow-y:auto;
                display:none">
    </div>
</div>




<div class="w-100"></div>

<div class="col-md-4">
<label>Department</label>
<select class="form-select" id="department" name="department"
    <?= ($department != 'ALL') ? 'disabled' : '' ?>>

<?php
if ($department == 'ALL') {

    $q = mysqli_query($conn,
        "SELECT DISTINCT department
         FROM asset_master
         ORDER BY department");

    while ($d = mysqli_fetch_assoc($q)) {
        echo "<option value='{$d['department']}'>{$d['department']}</option>";
    }

} else {

    echo "<option value='$department'>$department</option>";
}
?>
</select>

<?php
if ($department != 'ALL') {
    echo "<input type='hidden' name='department' value='$department'>";
}
?>

<option><?= $department ?></option>
</select>
</div>

<div class="col-md-4">
<label>PTW Department</label>
<select class="form-select" id="ptw_dep" name="ptw_dep"></select>
</div>

<div class="col-md-4">
<label>Unit</label>
<select class="form-select" id="unit_no" name="unit_no"></select>
</div>

<div class="col-md-4">
<label>System</label>
<select class="form-select" id="system" name="system"></select>
</div>

<div class="col-md-4">
<label>Sub System</label>
<select class="form-select" id="sub_system" name="sub_system"></select>
</div>

<div class="col-md-4">
<label>Description</label>
<select class="form-select" id="description" name="description"></select>
</div>

<div class="col-md-2">
<label>Class</label>
<select class="form-select" id="class" name="class"></select>
</div>

<div class="col-md-2">
<label>Group</label>
<select class="form-select" id="group" name="group"></select>
</div>

<div class="col-md-2">
<label>Equipment Code</label>
<input class="form-control" id="eqpt_code" name="eqpt_code" readonly>
</div>






<div class="col-md-3">
<label>Valid From</label>
<input id="valid_from" type="datetime-local" class="form-control" name="valid_from">
</div>

<div class="col-md-3">
<label>Valid To</label>
<input id="valid_to" type="datetime-local" class="form-control" name="valid_to">
</div>

<div class="col-md-12">
<label>Job Description</label>
<textarea class="form-control" name="job_description"></textarea>
</div>

<div class="col-md-12 text-center">
<button class="btn btn-primary px-5">Create Notification</button>
</div>

</div>
</form>
</div>
</div>

</body>
</html>











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
                    <div class="asset-item p-2"
     style="cursor:pointer;border-bottom:1px solid #eee"
     data-kks="${r.kks_tag ?? ''}"
     data-eqpt="${r.eqpt_code ?? ''}">

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

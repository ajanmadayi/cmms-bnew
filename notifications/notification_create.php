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
.header-title{
    font-size:22px;
    font-weight:700;
    letter-spacing:.3px;
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
$(document).ready(function(){

/* helpers */
function getDept(){
    return $('#department').val();
}

/* load PTW function */
function loadPTW(){
    const dept = $('#department').val();

    $('#ptw_dep').html('<option value="">Select</option>');

    if(!dept) return;

    $.post('../ajax/get_ptw_deps_by_department.php',
        { department: dept },
        function(res){
            const data = JSON.parse(res || '[]');
            data.forEach(r=>{
                $('#ptw_dep').append(
                    `<option value="${r.ptw_dep}">${r.ptw_dep}</option>`
                );
            });
        }
    );
}

/* >>> PASTE HERE <<< */
$('#department').on('change', function(){

    loadPTW();

    $('#unit_no,#system,#sub_system,#description,#class,#group')
        .html('<option value="">Select</option>');
});

/* load PTW at page start */
loadPTW();

});

/* ================= FILLED FIELD HIGHLIGHT ================= */
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

markAllFilled();
$(document).on('input change', 'input, select, textarea', function(){
    markFilled(this);
});

/* ================= COMMON HELPERS ================= */
function getDept(){
    return $('#department').val();
}

function clear(sel){
    $(sel).html('<option value="">Select</option>');
}

function resetAsset(){
    $('#asset_id,#eqpt_code').val('');
}

/* ================= KKS DIRECT SEARCH ================= */
$('#kks').on('blur', function(){

    const v = $(this).val().trim();
    if(!v) return;

    $.post('../ajax/get_asset_by_kks_or_eqpt.php',{ value:v },function(d){

       if(d.status === 'success'){

    if ($('#department option[value="'+d.department+'"]').length === 0) {
        $('#department').append(
            `<option value="${d.department}">${d.department}</option>`
        );
    }

    $('#department').val(d.department).trigger('change');

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
}


        else{
            alert('Invalid KKS / Equipment Code');
            $('#kks').val('');
            resetAsset();
            markAllFilled();
        }

    },'json');
});

/* ================= KKS CLEAR ================= */
$('#kks').on('input', function(){

    if ($(this).val().trim() !== '') return;

    // clear asset
    resetAsset();

    // clear all dropdowns
    clear('#ptw_dep,#unit_no,#system,#sub_system,#description,#class,#group');

    // reset department dropdown
    $('#department').prop('selectedIndex', 0);

    // remove filled colors
    $('#notificationForm')
        .find('input, select, textarea')
        .removeClass('filled');

});

/* ================= GLOBAL ASSET SEARCH ================= */
$('#asset_search').on('keyup', function () {

    const q = $(this).val().trim();

    // minimum 2 characters
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
        },

        error: function (xhr) {
            console.log(xhr.responseText);
        }
    });
});

/* ================= CLICK SEARCH RESULT ================= */
$(document).on('click', '.asset-item', function () {

    const kks  = $(this).data('kks');
    const eqpt = $(this).data('eqpt');

    const value = kks && kks !== '' ? kks : eqpt;

    if (!value) return;

    // fill KKS box and trigger existing search logic
    $('#kks').val(value).trigger('blur');

    // cleanup UI
    $('#asset_search').val('');
    $('#asset_result').hide();
});

/* ================= HIDE SEARCH WHEN CLICK OUTSIDE ================= */
$(document).on('click', function (e) {

    if (!$(e.target).closest('#asset_search, #asset_result').length) {
        $('#asset_result').hide();
    }
});



/* ================= LOAD PTW ================= */
$.post('../ajax/get_ptw_deps_by_department.php',{ department: getDept() },function(res){
    clear('#ptw_dep');
    JSON.parse(res||'[]').forEach(r=>{
        $('#ptw_dep').append(`<option value="${r.ptw_dep}">${r.ptw_dep}</option>`);
    });
});


/* ================= PTW → UNIT ================= */
$('#ptw_dep').change(function(){

    clear('#unit_no,#system,#sub_system,#description,#class,#group');
    resetAsset();

    if(!this.value) return;

    $.post('../ajax/get_units.php',{
        department: getDept(),
        ptw_dep: this.value
    },function(res){
        JSON.parse(res||'[]').forEach(r=>{
            $('#unit_no').append(`<option value="${r.unit_no}">${r.unit_no}</option>`);
        });
        markAllFilled();
    });
});

/* ================= UNIT → SYSTEM ================= */
$('#unit_no').change(function(){

    clear('#system,#sub_system,#description,#class,#group');
    resetAsset();

    if(!this.value) return;

    $.post('../ajax/get_systems.php',{
        department: getDept(),
        ptw_dep: $('#ptw_dep').val(),
        unit_no: this.value
    },function(res){
        JSON.parse(res||'[]').forEach(r=>{
            $('#system').append(`<option value="${r.system}">${r.system}</option>`);
        });
        markAllFilled();
    });
});

/* ================= SYSTEM → SUB SYSTEM ================= */
$('#system').change(function(){

    clear('#sub_system,#description,#class,#group');
    resetAsset();

    if(!this.value) return;

    $.post('../ajax/get_sub_systems.php',{
        department: getDept(),
        ptw_dep: $('#ptw_dep').val(),
        unit_no: $('#unit_no').val(),
        system: this.value
    },function(res){
        JSON.parse(res||'[]').forEach(r=>{
            $('#sub_system').append(`<option value="${r.sub_system}">${r.sub_system}</option>`);
        });
        markAllFilled();
    });
});

/* ================= SUB SYSTEM → DESCRIPTION ================= */
$('#sub_system').change(function(){

    clear('#description,#class,#group');
    resetAsset();

    if(!this.value) return;

    $.post('../ajax/get_descriptions.php',{
        department: getDept(),
        ptw_dep: $('#ptw_dep').val(),
        unit_no: $('#unit_no').val(),
        system: $('#system').val(),
        sub_system: this.value
    },function(res){
        JSON.parse(res||'[]').forEach(r=>{
            $('#description').append(`<option value="${r.description}">${r.description}</option>`);
        });
        markAllFilled();
    });
});

/* ================= DESCRIPTION → CLASS ================= */
$('#description').change(function(){

    clear('#class,#group');
    resetAsset();

    if(!this.value) return;

    $.post('../ajax/get_classes.php',{
        description: this.value
    },function(res){
        JSON.parse(res||'[]').forEach(r=>{
            $('#class').append(`<option value="${r.class}">${r.class}</option>`);
        });
        markAllFilled();
    });
});

/* ================= CLASS → GROUP ================= */
$('#class').change(function(){

    clear('#group');
    resetAsset();

    if(!this.value) return;

    $.post('../ajax/get_groups.php',{
        description: $('#description').val(),
        class: this.value
    },function(res){
        JSON.parse(res||'[]').forEach(r=>{
            $('#group').append(`<option value="${r.group}">${r.group}</option>`);
        });
        markAllFilled();
    });
});

/* ================= GROUP → EQUIPMENT ================= */
$('#group').change(function(){

    if(
        !$('#ptw_dep').val() ||
        !$('#unit_no').val() ||
        !$('#system').val() ||
        !$('#sub_system').val() ||
        !$('#description').val()
    ) return;

    $.post('../ajax/get_asset_by_selection.php',{
        department : getDept(),
        ptw_dep    : $('#ptw_dep').val(),
        unit_no    : $('#unit_no').val(),
        system     : $('#system').val(),
        sub_system : $('#sub_system').val(),
        description: $('#description').val(),
        class      : $('#class').val() || 'NA',
        group      : $('#group').val() || 'NA'
    },function(d){
        if(d.status === 'success'){
            $('#asset_id').val(d.asset_id);
            $('#eqpt_code').val(d.eqpt_code);
            markAllFilled();
        }
    },'json');
});

});
</script>







<body>

<div class="header">

    <!-- LEFT -->
    <div style="display:flex;align-items:center;gap:12px;">
        <img src="../assets/images/thdc_logo.png">
        <div class="header-title">
            CMMS – Create Notification
        </div>
    </div>

    <!-- RIGHT -->
    <div style="display:flex;align-items:center;gap:12px;">
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
    <label>Search Asset (Description)</label>
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
<select class="form-select" id="department" name="department">

<?php
/* user1 sees all departments */
if ($_SESSION['username'] === 'user1') {

    $res = mysqli_query($conn,
        "SELECT DISTINCT department
         FROM asset_master
         ORDER BY department");

    while ($r = mysqli_fetch_assoc($res)) {
        echo '<option value="'.$r['department'].'">'.
             $r['department'].'</option>';
    }

} else {

    /* normal users fixed department */
    echo '<option value="'.$department.'">'.
         $department.'</option>';
}
?>

</select>

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
<label>Planned Start</label>
<input id="valid_from" type="datetime-local" class="form-control" name="valid_from">
</div>

<div class="col-md-3">
<label>Planned Finish</label>
<input id="valid_to" type="datetime-local" class="form-control" name="valid_to">
</div>
<div class="col-md-3">
<label>Priority</label>
<select class="form-select" name="priority" id="priority">
    <option value="1">1 / A – Very High (Emergency)</option>
    <option value="2">2 / B – High</option>
    <option value="3" selected>3 / C – Medium</option>
    <option value="4">4 / D – Low</option>
</select>
<small class="text-muted">
Emergency → Normal maintenance priority
</small>
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

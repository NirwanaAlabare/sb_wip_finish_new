document.addEventListener("DOMContentLoaded", () => {
    $.fn.modal.Constructor.prototype._enforceFocus = function() {};

    showDate();
    showTime();

    $('#input-type').hide();

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('.select2').select2({
        theme: "bootstrap-5",
        width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
        placeholder: $( this ).data( 'placeholder' ),
    });
});

// General

// show date
function showDate() {
    if (document.getElementById("tanggal") && (document.getElementById("tanggal").value == "" || document.getElementById("tanggal").value == null)) {
        let date = new Date();

            let dateFormat = setDateFormat(date);

            document.getElementById("tanggal").value = dateFormat;
    }
}

// show time
function showTime() {
    let date = new Date();
    let h = date.getHours(); // 0 - 23
    let m = date.getMinutes(); // 0 - 59
    let s = date.getSeconds(); // 0 - 59
    let session = " AM";

    if(h == 0){
        h = 12;
    }

    if(h == 12){
        session = " PM";
    }

    if(h > 12){
        h = h - 12;
        session = " PM";
    }

    h = (h < 10) ? "0" + h : h;
    m = (m < 10) ? "0" + m : m;
    s = (s < 10) ? "0" + s : s;

    let time = h + ":" + m + session;

    if (document.getElementById("jam")) {
        document.getElementById("jam").value = time;
    }

    setTimeout(showTime, 1000);
}

// yy-mm-dd format
function setDateFormat(date) {
    var d = new Date(date),
        month = "" + (d.getMonth() + 1),
        day = "" + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2)
        month = "0" + month;
    if (day.length < 2)
        day = "0" + day;

    return [year, month, day].join("-");
}

// Authentication
function login(e, evt) {
    evt.preventDefault();

    $.ajax({
        url: e.getAttribute('action'),
        type: e.getAttribute('method'),
        data: new FormData(e),
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.status == 200) {
                console.log(res.message);
                location.href = res.redirect;
            } else {
                console.error(res.message);
                for(let i = 0;i < res.additional.length;i++) {
                    document.getElementById(res.additional[i]).classList.add('is-invalid');
                }
                iziToast.error({
                    title: 'Error',
                    message: res.message,
                    position: 'topCenter'
                });
            }
        }, error: function (jqXHR) {
            let res = jqXHR.responseJSON;
            let message = '';
            console.log(res.message);
            for (let key in res.errors) {
                message += res.errors[key]+' ';
                document.getElementById(key).classList.add('is-invalid');
            };
            iziToast.error({
                title: 'Error',
                message: message,
                position: 'topCenter'
            });
        }
    });
}

function logout(url) {
    Swal.fire({
        title: 'Logout?',
        showConfirmButton: true,
        showDenyButton: true,
        confirmButtonText: 'Logout',
        confirmButtonColor: '#6531a0',
        denyButtonText: 'Cancel',
      }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: url,
                type: 'post',
                data: {confirmed : result.isConfirmed},
                success: function(res) {
                    if (res.status == 200) {
                        console.log(res.message);
                        location.href = res.redirect;
                    }
                }
            });
        }
    });
}

// filter modal
function showFilterModal() {
    $("#filter-modal").modal("show");
}

function hideFilterModal() {
    $("#filter-modal").modal("hide");
}

// defect modal
function showDefectModal() {
    $("#defect-modal").modal("show");
}

function hideDefectModal() {
    $("#defect-modal").modal("hide");
}

// reject modal
function showRejectModal() {
    $("#reject-modal").modal("show");
}

function hideRejectModal() {
    $("#reject-modal").modal("hide");
}

// undo modal
function showUndoModal() {
    $("#undo-modal").modal("show");
}

function hideUndoModal() {
    $("#undo-modal").modal("hide");
}

// add product type modal
function showAddProductTypeModal() {
    $("#product-type-modal").modal("show");
}

function hideAddProductTypeModal() {
    $("#product-type-modal").modal("hide");
}

// add defect type modal
function showAddDefectTypeModal() {
    $("#defect-type-modal").modal("show");
}

function hideAddDefectTypeModal() {
    $("#defect-type-modal").modal("hide");
}

// add defect area modal
function showAddDefectAreaModal() {
    $("#defect-area-modal").modal("show");
}

function hideAddDefectAreaModal() {
    $("#defect-area-modal").modal("hide");
}

// mass rework modal
function showMassReworkModal() {
    $("#mass-rework-modal").modal("show");
}

function hideMassReworkModal() {
    $("#mass-rework-modal").modal("hide");
}

// all rework modal
function showAllReworkModal() {
    $("#all-rework-modal").modal("show");
}

function hideAllReworkModal() {
    $("#all-rework-modal").modal("hide");
}

// rework
function reworkConfirmation() {
    Swal.fire({
        icon: 'info',
        title: 'REWORK this defect?',
        html: `<table class="table text-start w-auto mx-auto">
                    <tr>
                        <td>ID<td>
                        <td>:<td>
                        <td>?<td>
                    <tr>
                    <tr>
                        <td>Size<td>
                        <td>:<td>
                        <td>?<td>
                    <tr>
                    <tr>
                        <td>Defect Type<td>
                        <td>:<td>
                        <td>?<td>
                    <tr>
                    <tr>
                        <td>Defect Area<td>
                        <td>:<td>
                        <td>?<td>
                    <tr>
                </table>`,
        showConfirmButton: true,
        showDenyButton: true,
        confirmButtonText: 'Rework',
        confirmButtonColor: '#447efa',
        denyButtonText: 'Batal',
    }).then((result) => {
        if (result.isConfirmed) {
            location.reload;
        } else if (result.isDenied) {
            Swal.fire({
                icon: 'info',
                title: 'REWORK Canceled',
                confirmButtonText: 'Ok',
                confirmButtonColor: '#447efa',
            })
        }
    });
}

// mass reject modal
function showMassRejectModal() {
    $("#mass-reject-modal").modal("show");
}

function hideMassRejectModal() {
    $("#mass-reject-modal").modal("hide");
}

// all reject modal
function showAllRejectModal() {
    $("#all-reject-modal").modal("show");
}

function hideAllRejectModal() {
    $("#all-reject-modal").modal("hide");
}

// reject
function rejectConfirmation() {
    Swal.fire({
        icon: 'info',
        title: 'REJECT this defect?',
        html: `<table class="table text-start w-auto mx-auto">
                    <tr>
                        <td>ID<td>
                        <td>:<td>
                        <td>?<td>
                    <tr>
                    <tr>
                        <td>Size<td>
                        <td>:<td>
                        <td>?<td>
                    <tr>
                    <tr>
                        <td>Defect Type<td>
                        <td>:<td>
                        <td>?<td>
                    <tr>
                    <tr>
                        <td>Defect Area<td>
                        <td>:<td>
                        <td>?<td>
                    <tr>
                </table>`,
        showConfirmButton: true,
        showDenyButton: true,
        confirmButtonText: 'Reject',
        confirmButtonColor: '#fa4456',
        denyButtonText: 'Batal',
    }).then((result) => {
        if (result.isConfirmed) {
            location.reload;
        } else if (result.isDenied) {
            Swal.fire({
                icon: 'info',
                title: 'REWORK Canceled',
                confirmButtonText: 'Ok',
                confirmButtonColor: '#fa4456',
            })
        }
    });
}

// qty input
function increment(id) {
    let element = document.getElementById(id);
    element.value = parseInt(element.value) + 1;
}

function decrement(id) {
    let element = document.getElementById(id);
    element.value = parseInt(element.value) - 1;
}

// popup notification
function showNotification(type, message) {
    switch (type) {
        case 'info' :
            iziToast.info({
                title: 'Information',
                message: message,
                position: 'topCenter'
            });
            break;
        case 'success' :
            iziToast.success({
                title: 'Success',
                message: message,
                position: 'topCenter'
            });
            break;
        case 'warning' :
            iziToast.warning({
                title: 'Warning',
                message: message,
                position: 'topCenter'
            });
            break;
        case 'error' :
            iziToast.error({
                title: 'Error',
                message: message,
                position: 'topCenter'
            });
            break;
    }
}

// enable form
function enableForm(element, elementOppositionId, formId) {
    // hide this element
    element.classList.remove("d-block");
    element.classList.add("d-none");

    // show opposition element
    document.getElementById(elementOppositionId).classList.remove("d-none");
    document.getElementById(elementOppositionId).classList.add("d-block");

    // form
    let form = document.getElementById(formId);
    let formElements = form.elements;

    for (let i = 0; i < formElements.length; i++) {
        if (formElements[i].type != 'submit' && formElements[i].type != 'button') {
            formElements[i].disabled = false;
        } else {
            formElements[i].classList.remove('d-none');
            formElements[i].classList.add('d-block');
        }
    }
}

// disable form
function disableForm(element, elementOppositionId, formId) {
    // hide this element
    element.classList.remove("d-block");
    element.classList.add("d-none");

    // show opposition element
    document.getElementById(elementOppositionId).classList.remove("d-none");
    document.getElementById(elementOppositionId).classList.add("d-block");

    // form
    let form = document.getElementById(formId);
    let formElements = form.elements;

    for (let i = 0; i < formElements.length; i++) {
        if (formElements[i].type != 'submit' && formElements[i].type != 'button') {
            formElements[i].disabled = true;
        } else {
            formElements[i].classList.remove('d-block');
            formElements[i].classList.add('d-none');
        }
    }
}

// Update Profile
function submitForm(e, evt) {
    evt.preventDefault();

    $.ajax({
        url: e.getAttribute('action'),
        type: e.getAttribute('method'),
        data: new FormData(e),
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.status == 200) {
                console.log(res.message);
                location.href = res.redirect;
                iziToast.success({
                    title: 'Success',
                    message: res.message,
                    position: 'topCenter'
                });
            } else {
                console.error(res.message);
                for(let i = 0;i < res.additional.length;i++) {
                    document.getElementById(res.additional[i]).classList.add('is-invalid');
                }
                iziToast.error({
                    title: 'Error',
                    message: res.message,
                    position: 'topCenter'
                });
            }
        }, error: function (jqXHR) {
            let res = jqXHR.responseJSON;
            let message = '';
            console.log(res.message);
            for (let key in res.errors) {
                message += res.errors[key]+' ';
                document.getElementById(key).classList.add('is-invalid');
            };
            iziToast.error({
                title: 'Error',
                message: message,
                position: 'topCenter'
            });
        }
    });
}

// Latest Output Chart
// var latestOutput='';

// var options = {
//     series: [
//         {
//             name: "High - 2013",
//             data: [28, 29, 33, 36, 32, 32, 33]
//         },
//         {
//             name: "Low - 2013",
//             data: [12, 11, 14, 18, 17, 13, 13]
//         }
//     ],
//     chart: {
//         height: 350,
//         type: 'line',
//         dropShadow: {
//             enabled: true,
//             color: '#000',
//             top: 18,
//             left: 7,
//             blur: 10,
//             opacity: 0.2
//         },
//         toolbar: {
//             show: false
//         }
//     },
//     colors: ['#77B6EA', '#545454'],
//     dataLabels: {
//         enabled: true,
//     },
//     stroke: {
//         curve: 'smooth'
//     },
//     grid: {
//         borderColor: '#e7e7e7',
//         row: {
//             colors: ['#f3f3f3', 'transparent'],
//             opacity: 0.5
//         },
//     },
//     markers: {
//         size: 1
//     },
//     xaxis: {
//         categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
//         title: {
//             text: 'Month'
//         }
//     },
//     yaxis: {
//         min: 5,
//         max: 40
//     },
//     legend: {
//         position: 'top',
//         horizontalAlign: 'right',
//         floating: true,
//         offsetY: -25,
//         offsetX: -5
//     }
// };

// var chart = new ApexCharts(document.querySelector("#daily-chart"), options);
// chart.render();

// Select Defect Area
function showSelectDefectArea(defectAreaImage) {
    document.body.style.maxHeight = '100%';
    document.body.style.overflow = 'hidden';

    let defectAreaImageElement = document.getElementById('defect-area-img');
    defectAreaImageElement.src = 'http://10.10.5.62:8080/erp/pages/prod_new/upload_files/'+defectAreaImage;

    let selectDefectArea = document.getElementById('select-defect-area');
    selectDefectArea.style.display = 'flex';
    selectDefectArea.style.flexDirection = 'column';
    selectDefectArea.style.alignItems = 'center';
}

function hideSelectDefectArea() {
    document.body.style.maxHeight = null;
    document.body.style.overflow = null;

    let defectAreaImageElement = document.getElementById('defect-area-img');
    defectAreaImageElement.src = '';

    let selectDefectArea = document.getElementById('select-defect-area');
    selectDefectArea.style.display = 'none';
    selectDefectArea.style.flexDirection = null;
    selectDefectArea.style.justifyContent = null;
    selectDefectArea.style.alignItems = null;
}

// Select Reject Area
function showSelectRejectArea(rejectAreaImage) {
    document.body.style.maxHeight = '100%';
    document.body.style.overflow = 'hidden';

    let rejectAreaImageElement = document.getElementById('reject-area-img');
    rejectAreaImageElement.src = 'http://10.10.5.62:8080/erp/pages/prod_new/upload_files/'+rejectAreaImage;

    let selectRejectArea = document.getElementById('select-reject-area');
    selectRejectArea.style.display = 'flex';
    selectRejectArea.style.flexDirection = 'column';
    selectRejectArea.style.alignItems = 'center';
}

function hideSelectRejectArea() {
    document.body.style.maxHeight = null;
    document.body.style.overflow = null;

    let rejectAreaImageElement = document.getElementById('reject-area-img');
    rejectAreaImageElement.src = '';

    let selectRejectArea = document.getElementById('select-reject-area');
    selectRejectArea.style.display = 'none';
    selectRejectArea.style.flexDirection = null;
    selectRejectArea.style.justifyContent = null;
    selectRejectArea.style.alignItems = null;
}

// Show Defect Area Image
function showDefectAreaImage(defectAreaImage) {
    document.body.style.maxHeight = '100%';
    document.body.style.overflow = 'hidden';

    let defectAreaImageElement = document.getElementById('defect-area-img-show');
    defectAreaImageElement.src = 'http://10.10.5.62:8080/erp/pages/prod_new/upload_files/'+defectAreaImage;

    let showDefectArea = document.getElementById('show-defect-area');
    showDefectArea.style.display = 'flex';
    showDefectArea.style.flexDirection = 'column';
    showDefectArea.style.alignItems = 'center';
}

function hideDefectAreaImage() {
    document.body.style.maxHeight = null;
    document.body.style.overflow = null;

    let defectAreaImageElement = document.getElementById('defect-area-img-show');
    defectAreaImageElement.src = '';

    let showDefectArea = document.getElementById('show-defect-area');
    showDefectArea.style.display = 'none';
    showDefectArea.style.flexDirection = null;
    showDefectArea.style.justifyContent = null;
    showDefectArea.style.alignItems = null;
}

function clearOutputInputJs() {
    if (document.getElementById('rft-input')) {
        let rftElement = document.getElementById('rft-input');
        rftElement.value = '';
    }
}

// Reminder
function showReminder(hoursminutes) {
    Swal.fire({
        icon: 'info',
        title: 'Reminder',
        html: 'Waktu saat ini : <b>'+hoursminutes+'</b><br class="mb-3">Harap sempatkan untuk menginput data di setiap jam jika memungkinkan<br class="mb-3"><small>Jika ada kendala dalam penggunaan aplikasi tolong di infokan</small>',
        showConfirmButton: true,
        showDenyButton: false,
        confirmButtonText: 'Oke',
        confirmButtonColor: '#6531a0',
    });
}

if (document.getElementById("alert-sound")) {
    var sound = document.getElementById("alert-sound");
    var played = false;


    window.addEventListener('click', function(event) {
        sound.pause();
        sound.currentTime = 0;
    });

    setInterval(function() {
        let now = new Date();
        let hours = String(now.getHours()).padStart(2, '0');
        let minutes = String(now.getMinutes()).padStart(2, '0');
        let seconds = now.getSeconds();
        let hoursminutes = hours+':'+minutes;

        if (!played) {
            switch (hoursminutes) {
                case "07:53" :
                    played = true;
                    // sound.play();
                    // showReminder(hoursminutes);
                    break;
                case "08:53" :
                    played = true;
                    // sound.play();
                    // showReminder(hoursminutes);
                    break;
                case "09:53" :
                    played = true;
                    // sound.play();
                    // showReminder(hoursminutes);
                    break;
                case "10:53" :
                    played = true;
                    // sound.play();
                    // showReminder(hoursminutes);
                    break;
                case "11:53" :
                    played = true;
                    // sound.play();
                    // showReminder(hoursminutes);
                    break;
                case "13:53" :
                    played = true;
                    // sound.play();
                    // showReminder(hoursminutes);
                    break;
                case "14:53" :
                    played = true;
                    // sound.play();
                    // showReminder(hoursminutes);
                    break;
                case "15:51" :
                    played = true;
                    // sound.play();
                    // showReminder(hoursminutes);
                    break;
                case "16:53" :
                    played = true;
                    // sound.play();
                    // showReminder(hoursminutes);
                    break;
            }
        }

        if (seconds == "0") {
            played = false;
        }
    }, 1000);
}

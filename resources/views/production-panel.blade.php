@extends('layouts.index', ['orderDate' => $orderInfo->tgl_plan])

@section('content')
    {{-- Production Panel Livewire --}}
    @livewire('production-panel', ['orderInfo' => $orderInfo, 'orderWsDetails' => $orderWsDetails])

    {{-- Select Defect Area --}}
    <div class="select-defect-area" id="select-defect-area">
        <div class="defect-area-position-container">
            <div class="d-flex">
                <div class="d-flex justify-content-center align-items-center">
                    <label class="text-light bg-dark" style="padding: .375rem .75rem;height: 100%">X </label>
                    <input type="text" class="form-control rounded-0" id="defect-area-position-x" readonly>
                </div>
                <div class="d-flex justify-content-center align-items-center">
                    <label class="text-light bg-dark h-100" style="padding: .375rem .75rem;height: 100%">Y </label>
                    <input type="text" class="form-control rounded-0" id="defect-area-position-y" readonly>
                </div>
            </div>
            <div class="d-flex">
                <button class="btn btn-danger rounded-0" id="defect-area-confirm">
                    <i class="fa-regular fa-check"></i>
                </button>
                <button class="btn btn-danger rounded-0" id="defect-area-cancel">
                    <i class="fa-regular fa-xmark"></i>
                </button>
            </div>
        </div>
        <div class="defect-area-img-container" id="defect-area-img-container">
            <div class="defect-area-img-point" id="defect-area-img-point"></div>
            <img src="" alt="" class="img-fluid defect-area-img" id="defect-area-img">
        </div>
    </div>

    {{-- Show Defect Area --}}
    <div class="show-defect-area" id="show-defect-area">
        <div class="position-relative d-flex flex-column justify-content-center align-items-center">
            <button type="button" class="btn btn-lg btn-light rounded-0 hide-defect-area-img" onclick="onHideDefectAreaImage()">
                <i class="fa-regular fa-xmark fa-lg"></i>
            </button>
            <div class="defect-area-img-container mx-auto">
                <div class="defect-area-img-point" id="defect-area-img-point-show"></div>
                <img src="" alt="" class="img-fluid defect-area-img" id="defect-area-img-show">
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            $('.select2').select2({
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
            });
        });

        Livewire.on('alert', (type, message) => {
            showNotification(type, message);
        })

        Livewire.on('showModal', (type) => {
            if (type == 'defect') {
                showDefectModal();
            } else if (type == 'undo') {
                showUndoModal();
            } else if (type == 'addProductType') {
                showAddProductTypeModal();
            } else if (type == 'addDefectType') {
                showAddDefectTypeModal();
            } else if (type == 'addDefectArea') {
                showAddDefectAreaModal();
            } else if (type == 'massRework') {
                showMassReworkModal();
            } else if (type == 'allRework') {
                showAllReworkModal();
            }
        });

        Livewire.on('hideModal', (type) => {
            if (type == 'defect') {
                hideDefectModal();
            } else if (type == 'undo') {
                hideUndoModal();
            } else if (type == 'addDefectType') {
                hideAddDefectTypeModal();
            } else if (type == 'addDefectArea') {
                hideAddDefectAreaModal();
            } else if (type == 'massRework') {
                hideMassReworkModal();
            }
        });

        Livewire.on('fromInputPanel', (type) => {
            $('#input-type').hide();
        });

        Livewire.on('toInputPanel', (type) => {
            $('#input-type').removeClass();
            $('#input-type').addClass('bg-'+type+' w-100 fs-6 py-1 mb-0 rounded text-center text-light fw-bold');
            $('#input-type').html(type.toUpperCase());
            $('#input-type').show();
        });

        Livewire.on('preSubmitAllRework', () => {
            Swal.fire({
                icon: 'info',
                title: 'REWORK semua DEFECT',
                html: `Yakin akan me-REWORK semua DEFECT?`,
                showConfirmButton: true,
                showDenyButton: true,
                confirmButtonText: 'Rework',
                confirmButtonColor: '#447efa',
                denyButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.emit('submitAllRework');
                } else if (result.isDenied) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Submit REWORK dibatalkan',
                        confirmButtonText: 'Ok',
                        confirmButtonColor: '#447efa',
                    });
                }
            });
        });

        Livewire.on('preSubmitRework', (defectId, defectSize, defectType, defectArea, defectImage, defectX, defectY) => {
            Swal.fire({
                icon: 'info',
                title: 'REWORK defect ini?',
                html: `<table class="table text-start w-auto mx-auto">
                            <tr>
                                <td>ID<td>
                                <td>:<td>
                                <td>`+defectId+`<td>
                            <tr>
                            <tr>
                                <td>Size<td>
                                <td>:<td>
                                <td>`+defectSize+`<td>
                            <tr>
                            <tr>
                                <td>Defect Type<td>
                                <td>:<td>
                                <td>`+defectType+`<td>
                            <tr>
                            <tr>
                                <td>Defect Area<td>
                                <td>:<td>
                                <td>`+defectArea+`<td>
                            <tr>
                            <tr>
                                <td>Defect Image<td>
                                <td>:<td>
                                <td>
                                    <button type="button" class="btn btn-dark" onclick="onShowDefectAreaImage('`+defectImage+`', '`+defectX+`', '`+defectY+`')">
                                        <i class="fa-regular fa-image"></i>
                                    </button>
                                <td>
                            <tr>
                        </table>`,
                showConfirmButton: true,
                showDenyButton: true,
                confirmButtonText: 'Rework',
                confirmButtonColor: '#447efa',
                denyButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.emit('submitRework', defectId);
                } else if (result.isDenied) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Submit REWORK dibatalkan',
                        confirmButtonText: 'Ok',
                        confirmButtonColor: '#447efa',
                    });
                }
            });
        });

        Livewire.on('preCancelRework', (reworkId, defectId, defectSize, defectType, defectArea, defectImage, defectX, defectY) => {
            Swal.fire({
                icon: 'warning',
                title: 'Kembalikan REWORK ini ke DEFECT?',
                html: `<table class="table text-start w-auto mx-auto">
                            <tr>
                                <td>Rework ID<td>
                                <td>:<td>
                                <td>`+reworkId+`<td>
                            <tr>
                                <tr>
                                <td>Defect ID<td>
                                <td>:<td>
                                <td>`+defectId+`<td>
                            <tr>
                            <tr>
                                <td>Size<td>
                                <td>:<td>
                                <td>`+defectSize+`<td>
                            <tr>
                            <tr>
                                <td>Defect Type<td>
                                <td>:<td>
                                <td>`+defectType+`<td>
                            <tr>
                            <tr>
                                <td>Defect Area<td>
                                <td>:<td>
                                <td>`+defectArea+`<td>
                            <tr>
                            <tr>
                                <td>Defect Image<td>
                                <td>:<td>
                                <td>
                                    <button type="button" class="btn btn-dark" onclick="onShowDefectAreaImage('`+defectImage+`', '`+defectX+`', '`+defectY+`')">
                                        <i class="fa-regular fa-image"></i>
                                    </button>
                                <td>
                            <tr>
                        </table>`,
                showConfirmButton: true,
                showDenyButton: true,
                confirmButtonText: 'Defect',
                confirmButtonColor: '#ff971f',
                denyButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.emit('cancelRework', reworkId, defectId);
                } else if (result.isDenied) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Pengembalian REWORK KE DEFECT dibatalkan',
                        confirmButtonText: 'Ok',
                        confirmButtonColor: '#447efa',
                    });
                }
            });
        });

        // Select Defect Area Position
        Livewire.on('showSelectDefectArea', async function (defectAreaImage) {
            showSelectDefectArea(defectAreaImage);
        });

        if (document.getElementById('select-defect-area')) {
            let defectAreaImageContainer = document.getElementById('defect-area-img-container');
            let defectAreaImage = document.getElementById('defect-area-img');
            let defectAreaImagePoint = document.getElementById('defect-area-img-point');
            let defectAreaPositionX = document.getElementById('defect-area-position-x');
            let defectAreaPositionY = document.getElementById('defect-area-position-y');
            let defectAreaConfirm = document.getElementById('defect-area-confirm');
            let defectAreaCancel = document.getElementById('defect-area-cancel');

            let localMousePos = { x: undefined, y: undefined };
            let globalMousePos = { x: undefined, y: undefined };

            defectAreaImageContainer.addEventListener('mousemove', (event) => {
                let rect = defectAreaImage.getBoundingClientRect();

                const localX = parseFloat((event.clientX - rect.left))/parseFloat(rect.width) * 100;
                const localY = parseFloat((event.clientY - rect.top))/parseFloat(rect.height) * 100;

                localMousePos = { x: localX, y: localY };

                defectAreaImageContainer.addEventListener('click', (event) => {
                    defectAreaImagePoint.style.width = 0.03 * rect.width+'px';
                    defectAreaImagePoint.style.height = defectAreaImagePoint.style.width;
                    defectAreaImagePoint.style.left =  'calc('+localMousePos.x+'% - '+0.015 * rect.width+'px)';
                    defectAreaImagePoint.style.top =  'calc('+localMousePos.y+'% - '+0.015 * rect.width+'px)';
                    defectAreaImagePoint.style.display = 'block';

                    defectAreaPositionX.value = localMousePos.x;
                    defectAreaPositionY.value = localMousePos.y;
                });
            });

            defectAreaConfirm.addEventListener('click', () => {
                Livewire.emit('setDefectAreaPosition', defectAreaPositionX.value, defectAreaPositionY.value);

                hideSelectDefectArea();
            });

            defectAreaCancel.addEventListener('click', () => {
                defectAreaImagePoint.style.left = '0px';
                defectAreaImagePoint.style.top = '0px';
                defectAreaImagePoint.style.display = 'none';

                defectAreaPositionX.value = null;
                defectAreaPositionY.value = null;

                Livewire.emit('setDefectAreaPosition', defectAreaPositionX.value, defectAreaPositionY.value);

                hideSelectDefectArea();
            });
        }

        Livewire.on('clearSelectDefectAreaPoint', () => {
            let defectAreaImagePoint = document.getElementById('defect-area-img-point');
            let defectAreaPositionX = document.getElementById('defect-area-position-x');
            let defectAreaPositionY = document.getElementById('defect-area-position-y');

            defectAreaImagePoint.style.left = '0px';
            defectAreaImagePoint.style.top = '0px';
            defectAreaImagePoint.style.display = 'none';

            defectAreaPositionX.value = null;
            defectAreaPositionY.value = null;

            Livewire.emit('setDefectAreaPosition', defectAreaPositionX.value, defectAreaPositionY.value);
        });

        function onShowDefectAreaImage(defectAreaImage, x, y) {
            Livewire.emit('showDefectAreaImage', defectAreaImage, x, y);
        }

        Livewire.on('showDefectAreaImage', async function (defectAreaImage, x, y) {
            await showDefectAreaImage(defectAreaImage);

            let defectAreaImageElement = document.getElementById('defect-area-img-show');
            let defectAreaImagePointElement = document.getElementById('defect-area-img-point-show');

            defectAreaImageElement.style.display = 'block'

            let rect = await defectAreaImageElement.getBoundingClientRect();

            let pointWidth = null;
            if (rect.width == 0) {
                pointWidth = 35;
            } else {
                pointWidth = 0.03 * rect.width;
            }

            defectAreaImagePointElement.style.width = pointWidth+'px';
            defectAreaImagePointElement.style.height = defectAreaImagePointElement.style.width;
            defectAreaImagePointElement.style.left =  'calc('+x+'% - '+0.5 * pointWidth+'px)';
            defectAreaImagePointElement.style.top =  'calc('+y+'% - '+0.5 * pointWidth+'px)';
            defectAreaImagePointElement.style.display = 'block';
        });

        function onHideDefectAreaImage() {
            hideDefectAreaImage();

            Livewire.emit('hideDefectAreaImageClear');
        }

        Livewire.on('loadReworkPageJs', () => {
            if (document.getElementById('all-defect-area-img')) {
                let defectAreaImage = document.getElementById('all-defect-area-img');
                let defectAreaImagePoint = document.getElementsByClassName('all-defect-area-img-point');

                let rect = defectAreaImage.getBoundingClientRect();

                for(i = 0; i < defectAreaImagePoint.length; i++) {
                    defectAreaImagePoint[i].style.width = 0.03 * rect.width+'px';
                    defectAreaImagePoint[i].style.height = defectAreaImagePoint[i].style.width;
                    defectAreaImagePoint[i].style.left =  'calc('+defectAreaImagePoint[i].getAttribute('data-x')+'% - '+0.015 * rect.width+'px)';
                    defectAreaImagePoint[i].style.top =  'calc('+defectAreaImagePoint[i].getAttribute('data-y')+'% - '+0.015 * rect.width+'px)';
                }
            }
        });

        Livewire.on('loadingStart', () => {
            if (document.getElementById('loading-order-list')) {
                $('#loading-order-list').removeClass('hidden');
                $('#content-order-list').addClass('hidden');
            }
            if (document.getElementById('loading-rft')) {
                $('#loading-rft').removeClass('hidden');
                $('#content-rft').addClass('hidden');
            }
            if (document.getElementById('loading-defect')) {
                $('#loading-defect').removeClass('hidden');
                $('#content-defect').addClass('hidden');
            }
            if (document.getElementById('loading-defect-history')) {
                $('#loading-defect-history').removeClass('hidden');
                $('#content-defect-history').addClass('hidden');
            }
            if (document.getElementById('loading-reject')) {
                $('#loading-reject').removeClass('hidden');
                $('#content-reject').addClass('hidden');
            }
            if (document.getElementById('loading-rework')) {
                $('#loading-rework').removeClass('hidden');
                $('#content-rework').addClass('hidden');
            }
            if (document.getElementById('loading-profile')) {
                $('#loading-profile').removeClass('hidden');
                $('#content-profile').addClass('hidden');
            }
            if (document.getElementById('loading-history')) {
                $('#loading-history').removeClass('hidden');
                $('#content-history').addClass('hidden');
            }
            if (document.getElementById('loading-undo')) {
                $('#loading-undo').removeClass('hidden');
                $('#content-undo').addClass('hidden');
            }
        });

        // function clearOutputInputJs() {
        //     if (document.getElementById('rft-input')) {
        //         let rftElement = document.getElementById('rft-input');
        //         rftElement.value = '';
        //     }

        //     if (document.getElementById('defect-input')) {
        //         let defectElement = document.getElementById('defect-input');
        //         defectElement.value = '';
        //     }

        //     if (document.getElementById('reject-input')) {
        //         let rejectElement = document.getElementById('reject-input');
        //         rejectElement.value = '';
        //     }
        // }

        // Livewire.on('loadingComplete', () => {
        //     if (document.getElementById('loading')) {
        //         $('#loading').addClass('hidden');
        //     }
        // });

        // Livewire.hook('message.processed', (message, component) => {
        //     if (document.getElementById('loading')) {
        //         $('#loading').addClass('hidden');
        //     };
        // })
    </script>
@endsection

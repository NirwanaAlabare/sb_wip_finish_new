<div wire:poll.visible.30000ms>
    <div class="loading-container-fullscreen" wire:loading wire:target="toRft, toDefect, toDefectHistory, toReject, toRework, toProductionPanel, preSubmitUndo, submitUndo, updateOrder, toProductionPanel, setAndSubmitInput, submitInput">
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>

    {{-- No Connection --}}
    <div class="alert alert-danger alert-dismissible fade show" role="alert" wire:offline>
        <strong>Koneksi Terputus.</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    {{-- Production Info --}}
    <div class="production-info row row-gap-1 justify-content-center align-items-end mb-3">
        <div class="col-md">
            <div class="mb-1">
                <label class="form-label mb-0">Buyer</label>
                <input type="text" class="form-control form-control-sm" id="buyer-name" value="{{ $orderInfo->buyer_name }}" readonly>
            </div>
        </div>
        <div class="col-md">
            <div class="mb-1">
                <label class="form-label mb-0">WS Number</label>
                <input type="text" class="form-control form-control-sm" id="ws-number" value="{{ $orderInfo->ws_number }}" readonly>
            </div>
        </div>
        <div class="col-md">
            <div class="mb-1">
                <label class="form-label mb-0">Product Type</label>
                <input type="text" class="form-control form-control-sm" id="product-type" value="{{ $orderInfo->product_type }}" readonly>
            </div>
        </div>
        <div class="col-md">
            <div class="mb-1">
                <label class="form-label mb-0">Style</label>
                <input type="text" class="form-control form-control-sm" id="style-name" value="{{ $orderInfo->style_name }}" readonly>
            </div>
        </div>
        <div class="col-md">
            <div class="mb-1" wire:ignore>
                <label class="form-label mb-0">Color</label>
                <select class="select2 form-select-sm" name="state" id="product-color" wire:model='selectedColor'>
                    @foreach ($orderWsDetails as $order)
                        <option value="{{ $order->id }}" data-color-name="{{ $order->color }}">{{ $order->color }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-auto">
            <div class="mb-1" wire:ignore>
                <a href="{{ $this->baseUrl."/production-panel/".$order->id."/manual" }}" class="btn btn-danger btn-sm w-auto fw-bold"><i class="fa-solid fa-rotate"></i> SCAN</a>
            </div>
        </div>
    </div>

    {{-- Production Panels --}}
    <div class="production-panel row row-gap-3" id="production-panel">
        @if ($panels)
            <div class="row row-gap-3 justify-content-center">
                <div class="col-md-6" id="defect-panel">
                    <div class="d-flex h-100">
                        <div class="card-custom bg-defect d-flex justify-content-between align-items-center w-75 h-100" {{-- onclick="toDefect()" --}} wire:click='toDefect'>
                            <div class="d-flex flex-column gap-3">
                                <p class="text-light"><i class="fa-regular fa-circle-exclamation fa-2xl"></i></p>
                                <p class="text-light">DEFECT</p>
                            </div>
                            <p class="text-light fs-1">{{ $outputDefect }}</p>
                        </div>
                        <div class="card-custom-footer bg-light w-25 h-100">
                            <div class="d-flex flex-column justify-content-center align-items-stretch h-100 gap-1">
                                <button class="history single-item btn btn-pale h-100" {{-- onclick="toDefectHistory()" --}} wire:click='toDefectHistory'>
                                    <div class="d-flex flex-column justify-content-center align-items-center w-100 h-100">
                                        <p class="mb-1">HISTORY</p>
                                        <p class="mb-0"><i class="fa-regular fa-clock-rotate-left fa-xl"></i></p>
                                    </div>
                                </button>
                                {{-- <button type="button" class="reset multi-item lower btn btn-pale h-50" wire:click="preSubmitUndo('defect')">
                                    <div class="d-flex flex-column justify-content-center align-items-center w-100 h-100">
                                        <p class="mb-1">UNDO</p>
                                        <p class="mb-0"><i class="fa-regular fa-rotate-left fa-xl"></i></p>
                                    </div>
                                </button> --}}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6" id="rework-panel">
                    <div class="d-flex h-100">
                        <div class="card-custom bg-rework d-flex justify-content-between align-items-center w-75 h-100" {{-- onclick="toRework()" --}} wire:click='toRework'>
                            <div class="d-flex flex-column gap-3">
                                <p class="text-light"><i class="fa-regular fa-arrows-rotate fa-2xl"></i></p>
                                <p class="text-light">REWORK</p>
                            </div>
                            <p class="text-light fs-1">{{ $outputRework }}</p>
                        </div>
                        <div class="card-custom-footer bg-light w-25 h-100">
                            <button class="reset single-item btn btn-pale w-100 h-100" wire:click="preSubmitUndo('rework')" disabled>
                                <i class="fa-regular fa-rotate-left fa-2xl"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6" id="reject-panel">
                    <div class="d-flex h-100">
                        <div class="card-custom bg-reject d-flex justify-content-between align-items-center w-75 h-100" {{-- onclick="toReject()" --}} wire:click='toReject'>
                            <div class="d-flex flex-column gap-3">
                                <p class="text-light"><i class="fa-regular fa-circle-xmark fa-2xl"></i></p>
                                <p class="text-light">REJECT</p>
                            </div>
                            <p class="text-light fs-1">{{ $outputReject }}</p>
                        </div>
                        <div class="card-custom-footer bg-light w-25 h-100">
                            <button class="reset single-item btn btn-pale w-100 h-100" wire:click="preSubmitUndo('reject')" disabled>
                                <i class="fa-regular fa-rotate-left fa-2xl"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Defect --}}
        {{-- @if ($defect) --}}
        <div class="{{ $defect ? '' : 'd-none' }}">
            @livewire('scan.defect', ["orderWsDetailSizes" => $orderWsDetailSizes])
        </div>
        {{-- @endif --}}

        {{-- Defect History --}}
        @if ($defectHistory)
            @livewire('scan.defect-history', ["orderWsDetailSizes" => $orderWsDetailSizes])
        @endif

        {{-- Rework --}}
        <div class="{{ $rework ? '' : 'd-none' }}">
            @livewire('scan.rework', ["orderWsDetailSizes" => $orderWsDetailSizes])
        </div>

        {{-- Reject --}}
        {{-- @if ($reject) --}}
        <div class="{{ $reject ? '' : 'd-none' }}">
            @livewire('scan.reject', ["orderWsDetailSizes" => $orderWsDetailSizes])
        </div>
        {{-- @endif --}}

        {{-- Undo --}}
        <div class="modal" tabindex="-1" id="undo-modal" wire:ignore.self>
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">UNDO <span class="bg-{{ $undoType }} fs-5 px-3 py-1 mb-0 rounded text-center text-light fw-bold">{{ strtoupper($undoType) }}</span></h5>
                  <button type="button" class="btn btn-light border-none pt-1 close" data-dismiss="modal" aria-label="Close" wire:click="$emit('hideModal', 'undo')">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" class="form-control" name="undo" id="undo" value="{{ $undoType }}">
                    <div class="row">
                        <div class="col">
                            <div class="mb-3">
                                @error('undoQty')
                                    <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                        <small>
                                            <strong>Error</strong> {{$message}}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </small>
                                    </div>
                                @enderror
                                <label class="form-label">QTY</label>
                                <input type="number" class="form-control @error('undoQty') is-invalid @enderror" name="undo-qty" id="undo-qty" value="1" wire:model='undoQty'>
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-3">
                                @error('undoSize')
                                    <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                        <small>
                                            <strong>Error</strong> {{$message}}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </small>
                                    </div>
                                @enderror
                                <label class="form-label">Size</label>
                                <select class="form-select @error('undoSize') is-invalid @enderror" name="undo-size" id="undo-size" wire:model='undoSize'>
                                    <option value="" selected disabled>Select Size</option>
                                    @if ($undoSizes)
                                        @foreach ($undoSizes as $size)
                                            <option value="{{ $size->so_det_id }}">{{ $size->size }} ({{ "qty : ".$size->total }})</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    @if ($undoType == 'defect' || $undoType == 'rework')
                        <div class="mb-3">
                            <label class="form-label">Defect Type <small>(not required)</small></label>
                            <select class="form-select" name="undo-defect-type" id="undo-defect-type" wire:model='undoDefectType'>
                                <option value="" selected>Select Defect Type</option>
                                @foreach ($undoDefectTypes as $defect)
                                    <option value="{{ $defect->id }}">{{ $defect->defect_type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Defect Area <small>(not required)</small></label>
                            <select class="form-select" name="undo-defect-area" id="undo-defect-area" wire:model='undoDefectArea'>
                                <option value="" selected>Select Defect Area</option>
                                @foreach ($undoDefectAreas as $defect)
                                    <option value="{{ $defect->id }}">{{ $defect->defect_area }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                  {{-- <button type="button" class="btn btn-secondary" data-dismiss="modal" wire:click="$emit('hideModal', 'undo')">Close</button> --}}
                  <button type="button" class="btn btn-dark" wire:click='submitUndo()'>UNDO</button>
                </div>
              </div>
            </div>
        </div>
    </div>

    {{-- Select Output Type --}}
    <div class="modal" tabindex="-1" id="select-output-type-modal" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h5 class="modal-title">PILIH TIPE OUTPUT</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="hideOutputTypeModal()"></button>
                </div>
                <div class="modal-body">
                    @error('numberingInput')
                        <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                            <strong>Error</strong> {{$message}}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @enderror
                    <div class="row g-3">
                        <div class="col-md-6">
                            <button class="btn btn-rft w-100 py-5" wire:click="setAndSubmitInput('rft')" onclick="hideOutputTypeModal();">
                                <h3><b>RFT</b></h3>
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-defect w-100 py-5" wire:click="setAndSubmitInput('defect')" onclick="hideOutputTypeModal();">
                                <h3><b>DEFECT</b></h3>
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-reject w-100 py-5" wire:click="setAndSubmitInput('reject')" onclick="hideOutputTypeModal();">
                                <h3><b>REJECT</b></h3>
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-rework w-100 py-5" wire:click="setAndSubmitInput('rework')" onclick="hideOutputTypeModal();">
                                <h3><b>REWORK</b></h3>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal" onclick="hideOutputTypeModal()">Batal</button>
                    {{-- <button type="button" class="btn btn-success" wire:click='submitDefectArea'>Tambahkan</button> --}}
                </div>
            </div>
        </div>
    </div>

    @if ($panels)
        <div class="w-100">
            <p class="mt-4 text-center opacity-50"><small><i>{{ date('Y') }} &copy; Nirwana Digital Solution</i></small></p>
        </div>
    @endif

    @if (!$panels)
        {{-- Back --}}
        <a wire:click="toProductionPanel" class="back bg-danger text-light text-center w-auto" id="back-button">
            <i class="fa-regular fa-reply"></i>
        </a>
    @endif
</div>

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            restrictYesterdayMasterPlan();

            @this.updateOrder();
        });

        // window.addEventListener("focus", () => {
        //     restrictYesterdayMasterPlan();
        // });

        // Pad 2 Digits
        function pad(n) {
            return n < 10 ? '0' + n : n
        }

        // Restrict Yesterday Master Plan
        function restrictYesterdayMasterPlan() {
            let date = new Date();
            let day = pad(date.getDate());
            let month = pad(date.getMonth() + 1);
            let year = date.getFullYear();

            // This arrangement can be altered based on how we want the date's format to appear.
            let currentDate = `${year}-${month}-${day}`;

            console.log(@this.orderDate, currentDate);

            if (@this.orderDate != currentDate) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Anda sedang mengakses Master Plan yang sudah berlalu',
                    html: `Master Plan yang anda akses berasal dari tanggal <br> <b>'`+ document.getElementById('tanggal').value +`'</b> <br> `,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                    confirmButtonColor: '#6531a0'
                });
            }
        }

        var scannedQrCode = "";
        document.addEventListener("keydown", function(e) {
            let textInput = e.key || String.fromCharCode(e.keyCode);
            let targetName = e.target.localName;

            if (targetName != 'input') {
                if (textInput && textInput.length === 1) {
                    scannedQrCode = scannedQrCode+textInput;

                    if (scannedQrCode.length >= 9) {
                        let i = 0;
                        let j = 1;
                        let k = 2;

                        if (scannedQrCode.includes('WIP')) {
                            @this.scannedNumberingCode = scannedQrCode;
                        } else {
                            // break decoded text
                            let breakDecodedText = scannedQrCode.split('-');

                            console.log(breakDecodedText);

                            // set kode_numbering
                            @this.scannedNumberingInput = breakDecodedText[i];

                            // set so_det_id
                            @this.scannedSizeInput = breakDecodedText[j];

                            // set size
                            @this.scannedSizeInputText = breakDecodedText[k];
                        }
                    }
                }

                if (@this.panels && textInput == "Enter") {
                    // open dialog
                    $("#select-output-type-modal").show();
                }
            }
        });

        function hideOutputTypeModal() {
            $("#select-output-type-modal").hide();
            scannedQrCode = '';
        }

        $('#product-color').on('change', function (e) {
            var selectedColor = $('#product-color').select2("val");
            var selectedColorName = $('#product-color').find(':selected').data('color-name');

            console.log(selectedColor);
            console.log(selectedColorName);

            @this.set('selectedColor', selectedColor);
            @this.set('selectedColorName', selectedColorName);

            @this.updateOrder();
        });
    </script>
@endpush

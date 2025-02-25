<div>
    <div class="loading-container-fullscreen" wire:loading wire:target="setAndSubmitInput, submitInput, updateOrder, submitRapidInput">
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    {{-- Production Input --}}
    <div class="production-input row row-gap-3">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center bg-reject text-light">
                    <p class="mb-0 fs-5">Scan QR</p>
                    {{-- <button class="btn btn-dark" wire:click="$emit('showModal', 'rapidReject')"><i class="fa-solid fa-layer-group"></i></button> --}}
                </div>
                <div class="card-body" wire:ignore.self>
                    @error('numberingInput')
                        <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                            <strong>Error</strong> {{$message}}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @enderror
                    {{-- <div id="reject-reader" width="600px"></div> --}}
                    <input type="text" class="qty-input" id="scannedRejectItem" name="scannedRejectItem">
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center bg-reject text-light">
                    <p class="mb-0 fs-5">Size</p>
                    <div class="d-flex justify-content-end align-items-center gap-1">
                        <div class="d-flex align-items-center gap-3 me-3">
                            <p class="mb-1 fs-5">REJECT</p>
                            <p class="mb-1 fs-5">:</p>
                            <p id="reject-qty" class="mb-1 fs-5">{{ $reject->count() }}</p>
                        </div>
                        <button class="btn btn-dark"  wire:click="$emit('preSubmitUndo', 'reject')" disabled>
                            <i class="fa-regular fa-rotate-left"></i>
                        </button>
                        {{-- <button class="btn btn-dark">
                            <i class="fa-regular fa-gear"></i>
                        </button> --}}
                    </div>
                </div>
                @error('sizeInput')
                    <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                        <strong>Error</strong> {{$message}}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @enderror
                <div class="card-body">
                    <div class="loading-container hidden" id="loading-reject">
                        <div class="loading mx-auto"></div>
                    </div>
                    <div class="row h-100 row-gap-3" id="content-reject">
                        @foreach ($orderWsDetailSizes as $order)
                            <div class="col-md-4">
                                <div class="bg-reject text-white w-100 h-100 py-auto rounded-3 d-flex flex-column justify-content-center align-items-center">
                                    @if ($order->ws)
                                        <p class="fs-6 mb-0">{{ $order->ws }}</p>
                                    @endif
                                    @if ($order->color)
                                        <p class="fs-6 mb-0">{{ $order->color }}</p>
                                    @endif
                                    <p class="fs-3 mb-0">{{ $order->size }}</p>
                                    @if ($order->dest != "-" && $order->dest != null)
                                        <p class="fs-6 mb-0">{{ $order->dest }}</p>
                                    @endif
                                    <p class="fs-5 mb-0">{{ $reject->where('so_det_id', $order->so_det_id)->count() }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <footer class="footer fixed-bottom py-3">
        <div class="container-fluid">
            <div class="d-flex justify-content-end">
                <button class="btn btn-dark btn-lg ms-auto fs-3" wire:click='submitInput'>SELESAI</button>
            </div>
        </div>
    </footer>

    {{-- Rapid Reject --}}
    <div class="modal" tabindex="-1" id="rapid-reject-modal" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-reject text-light">
                    <h5 class="modal-title"><i class="fa-solid fa-clone"></i> Reject Rapid Scan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <p class="text-center">Scanned Item : <b>{{ $rapidRejectCount }}</b></p>
                        <input type="text" class="qty-input" id="rapid-reject-input">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal" wire:click='submitRapidInput'>Selesai</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // Scan QR
        // if (document.getElementById("reject-reader")) {
        //     function onScanSuccess(decodedText, decodedResult) {
        //         // handle the scanned code as you like, for example:
        //         console.log(`Code matched = ${decodedText}`, decodedResult);

        //         // break decoded text
        //         let breakDecodedText = decodedText.split('-');

        //         console.log(breakDecodedText);

        //         // set kode_numbering
        //         @this.numberingInput = breakDecodedText[3];

        //         // set so_det_id
        //         @this.sizeInput = breakDecodedText[4];

        //         // set sisze
        //         @this.sizeInputText = breakDecodedText[5];

        //         // submit
        //         @this.submitInput();

        //         clearRejectScan()
        //     }

        //     Livewire.on('renderQrScanner', async (type) => {
        //         if (type == 'reject') {
        //             document.getElementById('back-button').disabled = true;
        //             await refreshRejectScan(onScanSuccess);
        //             document.getElementById('back-button').disabled = false;
        //         }
        //     });

        //     Livewire.on('toInputPanel', async (type) => {
        //         if (type == 'reject') {
        //             document.getElementById('back-button').disabled = true;
        //             await @this.updateOutput();
        //             await initRejectScan(onScanSuccess);
        //             document.getElementById('back-button').disabled = false;
        //         }
        //     });

        //     Livewire.on('fromInputPanel', () => {
        //         clearRejectScan();
        //     });
        // }

        var scannedRejectItemInput = document.getElementById("scannedRejectItem");

        scannedRejectItemInput.addEventListener("change", async function () {
            @this.numberingInput = this.value;

            // submit
            this.setAttribute("disabled", true);

            // submit
            await @this.submitInput();

            this.removeAttribute("disabled");
            this.value = '';
        });

        var scannedRapidRejectInput = document.getElementById("rapid-reject-input");

        scannedRapidRejectInput.addEventListener("change", function () {
            @this.pushRapidReject(this.value, null, null);

            this.value = '';
        });

        Livewire.on('qrInputFocus', async (type) => {
            if (type == 'reject') {
                scannedRejectItemInput.focus();
            }
        });

        Livewire.on('toInputPanel', async (type) => {
            if (type == 'reject') {
                scannedRejectItemInput.focus();
            }
        });

        // Livewire.on('fromInputPanel', () => {
        //     clearRejectScan();
        // });
    </script>
@endpush

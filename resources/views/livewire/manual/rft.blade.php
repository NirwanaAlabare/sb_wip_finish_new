<div>
    <div class="loading-container-fullscreen" wire:loading wire:target="submitInput, updateOrder">
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    {{-- Production Input --}}
    <div class="production-input row row-gap-3">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center bg-rft text-light">
                    <p class="mb-0 fs-5">QTY</p>
                    {{-- <button class="btn btn-dark">
                        <i class="fa-regular fa-plus"></i>
                    </button> --}}
                </div>
                @error('outputInput')
                    <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                        <strong>Error</strong> {{$message}}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @enderror
                <div class="card-body">
                    <div class="mb-3">
                        <h3 class="text-center"><i class="fa-regular fa-shirt"></i> Piece</h3>
                    </div>
                    <input type="number" class="qty-input" id="rft-input" value="{{ $outputInput }}" wire:model.defer='outputInput'>
                    <div class="d-flex justify-content-between gap-1 mt-3">
                        <button class="btn btn-danger w-50 fs-3" id="decrement" wire:click="outputDecrement">-1</button>
                        <button class="btn btn-success w-50 fs-3" id="increment" wire:click="outputIncrement">+1</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center bg-rft text-light">
                    <p class="mb-0 fs-5">Size</p>
                    <div class="d-flex justify-content-end align-items-center gap-1">
                        <div class="d-flex align-items-center gap-3 me-3">
                            <p class="mb-1 fs-5">RFT</p>
                            <p class="mb-1 fs-5">:</p>
                            <p id="rft-qty" class="mb-1 fs-5">{{ $output->count() }}</p>
                        </div>
                        <button class="btn btn-dark" wire:click='clearInput'>
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
                    <div class="loading-container hidden" id="loading-rft">
                        <div class="loading mx-auto"></div>
                    </div>
                    <div class="row h-100 row-gap-3" id="content-rft">
                        @foreach ($orderWsDetailSizes as $order)
                            <label class="size-input col-md-4">
                                <input type="radio" name="size-input" id="size-input" value="{{ $order->so_det_id }}"  wire:model.defer='sizeInput'>
                                <div class="btn btn-rft btn-size w-100 h-100 fs-3 py-auto d-flex flex-column justify-content-center align-items-center">
                                    <p class="fs-3 mb-0">{{ $order->size }}</p>
                                    <p class="fs-5 mb-0">{{ $output->where('size', $order->size)->count() }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <p class="text-center opacity-50 my-0"><small><i>{{ date('Y') }} &copy; Nirwana Digital Solution</i></small></p>
    </div>

    {{-- Footer --}}
    <footer class="footer fixed-bottom py-3">
        <div class="container-fluid">
            <div class="d-flex justify-content-end">
                <button class="btn btn-dark btn-lg ms-auto fs-3" wire:click='submitInput' {{ $submitting ? 'disabled' : ''}}>SELESAI</button>
            </div>
        </div>
    </footer>
</div>

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            document.getElementById('rft-input').addEventListener("keyup", async (event) => {
                if (event.key === 'Enter' || event.keyCode === 13) {
                    await @this.submitInput();
                    let el = document.querySelector( ':focus' );
                    if( el ) el.blur();
                }
            });
        })
    </script>
@endpush

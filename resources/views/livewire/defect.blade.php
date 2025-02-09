<div>
    <div class="loading-container-fullscreen" wire:loading wire:target="selectDefectAreaPosition, preSubmitInput, submitInput, updateOrder">
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    <div class="production-input row row-gap-3">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center bg-defect text-light">
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
                    <input type="number" class="qty-input" id="defect-input" value="{{ $outputInput }}" wire:model.defer='outputInput'>
                    <div class="d-flex justify-content-between gap-1 mt-3">
                        <button class="btn btn-danger w-50 fs-3" id="decrement" wire:click='outputDecrement'>-1</button>
                        <button class="btn btn-success w-50 fs-3" id="increment" wire:click='outputIncrement'>+1</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center bg-defect text-light">
                    <p class="mb-1 fs-5">Size</p>
                    <div class="d-flex flex-wrap justify-content-md-end align-items-center gap-1">
                        {{-- <div class="d-flex align-items-center gap-1 me-3">
                            <label class="mb-1">Type</label>
                            <select type="text" class="form-select">
                                <option value="" selected>Defect Type</option>
                                <option value="">asd</option>
                                <option value="">asd</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center gap-1 me-3">
                            <label class="mb-1">Area</label>
                            <select type="text" class="form-select">
                                <option value="" selected>Defect Area</option>
                                <option value="">asd</option>
                                <option value="">asd</option>
                            </select>
                        </div> --}}
                        <div class="d-flex align-items-center gap-3 me-3">
                            <p class="mb-1 fs-5">DEFECT</p>
                            <p class="mb-1 fs-5">:</p>
                            <p id="defect-qty" class="mb-1 fs-5">{{ $output }}</p>
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
                    <div class="loading-container hidden" id="loading-defect">
                        <div class="loading mx-auto"></div>
                    </div>
                    <div class="loading-container" wire:loading wire:target='setSizeInput'>
                        <div class="loading mx-auto"></div>
                    </div>
                    <div class="row h-100 row-gap-3" id="content-defect" wire:loading.remove wire:target='setSizeInput'>
                        @foreach ($orderWsDetailSizes as $order)
                            <label class="size-input col-md-4">
                                <input type="radio" name="size-input" id="size-input" value="{{ $order->so_det_id }}"  wire:model.defer='sizeInput'>
                                <div class="btn btn-defect btn-size w-100 h-100 fs-3 py-auto d-flex justify-content-center align-items-center">
                                    {{ $order->size }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Defect Modal --}}
    <div class="modal" tabindex="-1" id="defect-modal" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-defect text-light">
                    <h5 class="modal-title">DEFECT</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        {{-- <div class="mb-3">
                            @error('productType')
                                <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                    <small>
                                        <strong>Error</strong> {{$message}}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </small>
                                </div>
                            @enderror
                            <div class="d-flex align-items-center mb-1">
                                <button type="button" class="btn btn-sm btn-light rounded-0 me-1" wire:click="$emit('showModal', 'addProductType')">
                                    <i class="fa-regular fa-plus fa-xs"></i>
                                </button>
                                <label class="form-label me-1 mb-0">Product Type</label>
                            </div>
                            <div wire:ignore id="select-product-type-container">
                                <select class="form-select @error('productType') is-invalid @enderror" id="product-type-select2" wire:model='productType'>
                                    <option value="" selected>Select product type</option>
                                    @foreach ($productTypes as $product)
                                        <option value="{{ $product->id }}">
                                            {{ $product->product_type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div> --}}
                        <div class="mb-3">
                            @error('defectType')
                                <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                    <small>
                                        <strong>Error</strong> {{$message}}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </small>
                                </div>
                            @enderror
                            <div class="d-flex align-items-center mb-1">
                                <button type="button" class="btn btn-sm btn-light rounded-0 me-1" wire:click="$emit('showModal', 'addDefectType')">
                                    <i class="fa-regular fa-plus fa-xs"></i>
                                </button>
                                <label class="form-label me-1 mb-0">Defect Type</label>
                            </div>
                            <div wire:ignore id="select-defect-type-container">
                                <select class="form-select @error('defectType') is-invalid @enderror" id="defect-type-select2" wire:model='defectType'>
                                    <option value="" selected>Select defect type</option>
                                    @foreach ($defectTypes as $defect)
                                        <option value="{{ $defect->id }}">
                                            {{ $defect->defect_type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            @error('defectArea')
                                <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                    <small>
                                        <strong>Error</strong> {{$message}}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </small>
                                </div>
                            @enderror
                            <div class="d-flex align-items-center mb-1">
                                <button type="button" class="btn btn-sm btn-light rounded-0 me-1" wire:click="$emit('showModal', 'addDefectArea')">
                                    <i class="fa-regular fa-plus fa-xs"></i>
                                </button>
                                <label class="form-label me-1 mb-0">Defect Area</label>
                            </div>
                            <div class="d-flex gap-1">
                                <div class="w-75" wire:ignore id="select-defect-area-container">
                                    <select class="form-select @error('defectArea') is-invalid @enderror" id="defect-area-select2" wire:model='defectArea'>
                                        <option value="" selected>Select defect area</option>
                                        @foreach ($defectAreas as $defect)
                                            <option value="{{ $defect->id }}">
                                                {{ $defect->defect_area }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="w-25">
                                    <button type="button" wire:click="selectDefectAreaPosition" class="btn btn-dark w-100">
                                        <i class="fa-regular fa-image"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            @if ($errors->has('defectAreaPositionX') || $errors->has('defectAreaPositionY'))
                                <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                    <small>
                                        <strong>Error</strong> Harap tentukan posisi defect area dengan mengklik tombol <button type="button"class="btn btn-dark btn-sm"><i class="fa-regular fa-image fa-2xs"></i></button> di samping 'select defect area'.
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </small>
                                </div>
                            @endif
                            <div class="d-none">
                                <label class="form-label me-1 mb-2">Defect Area Position</label>
                                <div class="row">
                                    <div class="col d-flex justify-content-center align-items-center">
                                        <label class="form-label me-1 mb-0">X </label>
                                        <div class="d-flex">
                                            <input class="form-control @error('defectAreaPositionX') is-invalid @enderror" id="defect-area-position-x-livewire" wire:model='defectAreaPositionX' readonly>
                                        </div>
                                    </div>
                                    <div class="col d-flex justify-content-center align-items-center">
                                        <label class="form-label me-1 mb-1">Y </label>
                                        <div class="d-flex">
                                            <input class="form-control @error('defectAreaPositionY') is-invalid @enderror" id="defect-area-position-x-livewire" wire:model='defectAreaPositionY' readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" wire:click='submitInput'>Selesai</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Product Type --}}
    <div class="modal" tabindex="-1" id="product-type-modal" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-defect text-light">
                    <h5 class="modal-title">TAMBAH PRODUCT TYPE</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            @error('defectAreaAdd')
                                <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                    <small>
                                        <strong>Error</strong> {{$message}}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </small>
                                </div>
                            @enderror
                            <label class="form-label me-1 mb-0">Product Type</label>
                            <input type="text" class="form-control" name="product-type-add" id="product-type-add" wire:model='productTypeAdd'>
                        </div>
                        <div class="mb-3">
                            @error('productTypeImageAdd')
                                <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                    <small>
                                        <strong>Error</strong> {{$message}}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </small>
                                </div>
                            @enderror
                            <label class="form-label me-1 mb-0">Product Type Image</label>
                            <input type="file" class="form-control" name="product-type-image-add" id="product-type-image-add" style="border-radius: 5px 5px 0 0;" wire:model='productTypeImageAdd'>
                            <div class="d-flex justify-content-center border" style="border-radius: 0 0 5px 5px;">
                                @if ($productTypeImageAdd)
                                    <img src="{{ $productTypeImageAdd->temporaryUrl() }}" class="img-fluid">
                                @else
                                    <p class="text-center mb-1">*Preview Gambar*</p>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" wire:click='submitProductType'>Tambahkan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Defect Type --}}
    <div class="modal" id="defect-type-modal" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-defect text-light">
                    <h5 class="modal-title">TAMBAH DEFECT TYPE</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            @error('defectTypeAdd')
                                <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                    <small>
                                        <strong>Error</strong> {{$message}}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </small>
                                </div>
                            @enderror
                            <label class="form-label me-1 mb-0">Defect Type</label>
                            <input type="text" class="form-control" name="defect-type-add" id="defect-type-add" wire:model='defectTypeAdd'>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" wire:click='submitDefectType'>Tambahkan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Defect Area --}}
    <div class="modal" tabindex="-1" id="defect-area-modal" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-defect text-light">
                    <h5 class="modal-title">TAMBAH DEFECT AREA</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            @error('defectAreaAdd')
                                <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                    <small>
                                        <strong>Error</strong> {{$message}}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </small>
                                </div>
                            @enderror
                            <label class="form-label me-1 mb-0">Defect Area</label>
                            <input type="text" class="form-control" name="defect-area-add" id="defect-area-add" wire:model='defectAreaAdd'>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" wire:click='submitDefectArea'>Tambahkan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <footer class="footer fixed-bottom py-3">
        <div class="container-fluid">
            <div class="d-flex justify-content-end">
                <button class="btn btn-dark btn-lg ms-auto fs-3" wire:click='preSubmitInput'>LANJUT</button>
            </div>
        </div>
    </footer>
</div>

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Product Type
            $('#product-type-select2').select2({
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
                dropdownParent: $('#defect-modal .modal-content #select-product-type-container')
            });

            $('#product-type-select2').on('change', function (e) {
                var productType = $('#product-type-select2').select2("val");
                @this.set('productType', productType);
            });

            // Defect Type
            $('#defect-type-select2').select2({
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
                dropdownParent: $('#defect-modal .modal-content #select-defect-type-container')
            });

            $('#defect-type-select2').on('change', function (e) {
                var defectType = $('#defect-type-select2').select2("val");
                @this.set('defectType', defectType);
            });

            // Defect Area
            $('#defect-area-select2').select2({
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
                dropdownParent: $('#defect-modal .modal-content #select-defect-area-container')
            });

            $('#defect-area-select2').on('change', function (e) {
                var defectArea = $('#defect-area-select2').select2("val");
                @this.set('defectArea', defectArea);
            });

            Livewire.on('clearSelectDefectAreaPoint', () => {
                $('#product-type-select2').val("").trigger('change');
                $('#defect-type-select2').val("").trigger('change');
                $('#defect-area-select2').val("").trigger('change');
            });

            document.getElementById('defect-input').addEventListener("keyup", async (event) => {
                if (event.key === 'Enter' || event.keyCode === 13) {
                    await @this.preSubmitInput();
                    let el = document.querySelector( ':focus' );
                    if( el ) el.blur();
                }
            });
        })
    </script>
@endpush

<div>
    <div class="loading-container-fullscreen" wire:loading wire:target="selectDefectAreaPosition, setAndSubmitInput, preSubmitInput, submitInput, updateOrder, submitRapidInput">
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
                            <p id="reject-qty" class="mb-1 fs-5">{{ $output }}</p>
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
                        @foreach ($orderWsDetailSizes->groupBy('size') as $key => $order)
                            <div class="col-md-4">
                                <div class="bg-reject text-white w-100 h-100 py-auto rounded-3 d-flex flex-column justify-content-center align-items-center">
                                    <p class="fs-3 mb-0">{{ $key }}</p>
                                    {{-- @if ($order->dest != "-" && $order->dest != null)
                                        <p class="fs-6 mb-0">{{ $order->dest }}</p>
                                    @endif --}}
                                    <p class="fs-5 mb-0">{{ $reject->where('size', $key)->count() }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-header align-items-center bg-reject text-light">
                    <p class="mb-0 fs-5">Defect List</p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-5 align-self-center">
                            <div class="w-100 h-100" wire:loading wire:target='loadRejectPage'>
                                <div class="loading-container">
                                    <div class="loading"></div>
                                </div>
                            </div>
                            <div class="scroll-defect-area-img" wire:loading.remove wire:target='loadRejectPage'>
                                <div class="all-defect-area-img-container">
                                    @foreach ($allDefectPosition as $defectPosition)
                                        <div class="all-defect-area-img-point" data-x="{{ floatval($defectPosition->defect_area_x) }}" data-y="{{ floatval($defectPosition->defect_area_y) }}"></div>
                                    @endforeach
                                    @if ($allDefectImage)
                                        <img src="http://10.10.5.62:8080/erp/pages/prod_new/upload_files/{{ $allDefectImage->gambar }}" class="all-defect-area-img" id="all-defect-area-img" alt="defect image">
                                    @else
                                        <img src="/assets/images/notfound.png" class="all-defect-area-img" alt="defect image">
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7 table-responsive">
                            <div class="d-flex align-items-center gap-3 my-3">
                                <button class="btn btn-reject fw-bold rounded-0 w-25 h-100" wire:click="$emit('preSubmitAllReject')">Reject all</button>
                                <input type="text" class="form-control rounded-0 w-75 h-100" wire:model='allDefectListFilter' placeholder="Search defect">
                            </div>
                            <table class="table table-bordered vertical-align-center">
                                <thead>
                                    <tr>
                                        <th>Tipe</th>
                                        <th>Area</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($allDefectList->count() < 1)
                                        <tr>
                                            <td colspan="4" class="text-center">
                                                <div wire:loading>
                                                    <div class="loading-small"></div>
                                                </div>
                                                <div wire:loading.remove>
                                                    Defect tidak ditemukan
                                                </div>
                                            </td>
                                        </tr>
                                    @else
                                        @foreach ($allDefectList as $defectList)
                                            <tr>
                                                <td>{{ $defectList->defect_type }}</td>
                                                <td>{{ $defectList->defect_area }}</td>
                                                <td><b>{{$defectList->total}}</b></td>
                                                <td>
                                                    <div wire:loading>
                                                        <div class="loading-small"></div>
                                                    </div>
                                                    <div wire:loading.remove>
                                                        <button class="btn btn-sm btn-reject fw-bold w-100"
                                                            wire:click="preSubmitMassReject('{{ $defectList->defect_type_id }}', '{{ $defectList->defect_area_id }}', '{{ $defectList->defect_type }}', '{{ $defectList->defect_area }}')"
                                                        >
                                                            Reject
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                            {{ $allDefectList->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center bg-reject text-light">
                    <p class="mb-0 fs-5">Data Defect</p>
                    <div class="d-flex justify-content-end align-items-center gap-1">
                        <button type="button" class="btn btn-dark" wire:click="$emit('preSubmitUndo', 'defect')" disabled>
                            <i class="fa-regular fa-rotate-left"></i>
                        </button>
                        {{-- <button type="button" class="btn btn-dark">
                            <i class="fa-regular fa-gear"></i>
                        </button> --}}
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <div class="d-flex justify-content-center align-items-center">
                        <input type="text" class="form-control mb-3 rounded-0" id="search-defect" name="search-defect" wire:model='searchDefect' placeholder="Search here...">
                    </div>
                    <table class="table table-bordered text-center align-middle">
                        <tr>
                            <th>No.</th>
                            <th>Waktu</th>
                            <th>QR</th>
                            <th>Size</th>
                            <th>Defect Type</th>
                            <th>Defect Area</th>
                            <th>Defect Area Image</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        @if ($defects->count() < 1)
                            <tr>
                                <td colspan='9'>Defect tidak ditemukan</td>
                            </tr>
                        @else
                            @foreach ($defects as $defect)
                                <tr>
                                    <td>{{ $defects->firstItem() + $loop->index }}</td>
                                    <td>{{ $defect->updated_at ? $defect->created_at : $defect->updated_at }}</td>
                                    <td>{{ $defect->kode_numbering }}</td>
                                    <td>{{ $defect->so_det_size }}</td>
                                    <td>{{ $defect->defectType->defect_type}}</td>
                                    <td>{{ $defect->defectArea->defect_area }}</td>
                                    <td>
                                        <button type="button" class="btn btn-dark" wire:click="showDefectAreaImage('{{$defect->masterPlan->gambar}}', {{$defect->defect_area_x}}, {{$defect->defect_area_y}})'">
                                            <i class="fa-regular fa-image"></i>
                                        </button>
                                    </td>
                                    <td class="text-defect fw-bold">{{ strtoupper($defect->status) }}</td>
                                    <td>
                                        <div wire:loading>
                                            <div class="loading-small"></div>
                                        </div>
                                        <div wire:loading.remove>
                                            <button class="btn btn-sm btn-reject fw-bold w-100"
                                                wire:click="$emit('preSubmitReject', '{{ $defect->id }}', '{{ $defect->so_det_size }}', '{{ $defect->defectType->defect_type }}', '{{ $defect->defectArea->defect_area }}', '{{ $defect->masterPlan->gambar }}', '{{ $defect->defect_area_x }}', '{{ $defect->defect_area_y }}')">
                                                REJECT
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </table>
                    {{ $defects->links() }}
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center bg-reject text-light">
                    <p class="mb-0 fs-5">Data Reject</p>
                    <div class="d-flex justify-content-end align-items-center gap-1">
                        <button type="button" class="btn btn-dark" wire:click="$emit('preSubmitUndo', 'reject')" disabled>
                            <i class="fa-regular fa-rotate-left"></i>
                        </button>
                        {{-- <button type="button" class="btn btn-dark">
                            <i class="fa-regular fa-gear"></i>
                        </button> --}}
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <div class="d-flex justify-content-center align-items-center">
                        <input type="text" class="form-control mb-3 rounded-0" id="search-reject" name="search-reject" wire:model='searchReject' placeholder="Search here...">
                    </div>
                    <table class="table table-bordered text-center align-middle">
                        <tr>
                            <th>No.</th>
                            <th>Waktu</th>
                            <th>QR</th>
                            <th>Size</th>
                            <th>Defect Type</th>
                            <th>Defect Area</th>
                            <th>Defect Area Image</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        @if ($rejects->count() < 1)
                            <tr>
                                <td colspan='9'>Reject tidak ditemukan</td>
                            </tr>
                        @else
                            @foreach ($rejects as $reject)
                                <tr>
                                    <td>{{ $rejects->firstItem() + $loop->index }}</td>
                                    <td>{{ $reject->updated_at ? $reject->created_at : $reject->updated_at }}</td>
                                    <td>{{ $reject->defect ? $reject->kode_numbering : $reject->kode_numbering }}</td>
                                    <td>{{ $reject->so_det_size }}</td>
                                    <td>{{ $reject->defect ? ($reject->defectType ? $reject->defectType->defect_type : '-') : ($reject->defectType ? $reject->defectType->defect_type : '-') }}</td>
                                    <td>{{ $reject->defect ? ($reject->defectArea ? $reject->defectArea->defect_area : '-') : ($reject->defectArea ? $reject->defectArea->defect_area : '-') }}</td>
                                    <td>
                                        @if ($reject->defect)
                                            <button type="button" class="btn btn-dark" wire:click="showDefectAreaImage('{{$reject->masterPlan->gambar}}', {{$reject->defect_area_x}}, {{$reject->defect_area_y}})'">
                                                <i class="fa-regular fa-image"></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-dark" wire:click="showDefectAreaImage('{{$reject->masterPlan->gambar}}', {{$reject->reject_area_x}}, {{$reject->reject_area_y}})'">
                                                <i class="fa-regular fa-image"></i>
                                            </button>
                                        @endif
                                    </td>
                                    <td class="text-reject fw-bold">{{ strtoupper($reject->status) }}</td>
                                    <td>
                                        <div wire:loading>
                                            <div class="loading-small"></div>
                                        </div>
                                        <div wire:loading.remove>
                                            @if ($reject->defect)
                                                <button class="btn btn-sm btn-defect fw-bold w-100" wire:click="$emit('preCancelReject', '{{ $reject->id }}', '{{ $reject->id }}', '{{ $reject->so_det_size }}', '{{ $reject->defectType ? $reject->defectType->defect_type : '-' }}', '{{ ($reject->defectArea ? $reject->defectArea->defect_area : '-') }}', '{{$reject->masterPlan->gambar}}', {{$reject->defect_area_x}}, {{$reject->defect_area_y}})">CANCEL</button>
                                            @else
                                                <button class="btn btn-sm btn-muted fw-bold w-100" disabled>MATI</button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </table>
                    {{ $rejects->links() }}
                </div>
            </div>
        </div>
    </div>

     {{-- Reject Modal --}}
     <div class="modal" tabindex="-1" id="reject-modal" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-reject text-light">
                    <h5 class="modal-title">REJECT</h5>
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
                            @error('rejectType')
                                <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                    <small>
                                        <strong>Error</strong> {{$message}}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </small>
                                </div>
                            @enderror
                            <div class="d-flex align-items-center mb-1">
                                <label class="form-label me-1 mb-0">Reject Type</label>
                            </div>
                            <div wire:ignore id="select-reject-type-container">
                                <select class="form-select @error('rejectType') is-invalid @enderror" id="reject-type-select2" wire:model='rejectType'>
                                    <option value="" selected>Select reject type</option>
                                    @foreach ($defectTypes as $defect)
                                        <option value="{{ $defect->id }}">
                                            {{ $defect->defect_type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            @error('rejectArea')
                                <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                    <small>
                                        <strong>Error</strong> {{$message}}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </small>
                                </div>
                            @enderror
                            <div class="d-flex align-items-center mb-1">
                                <label class="form-label me-1 mb-0">Reject Area</label>
                            </div>
                            <div class="d-flex gap-1">
                                <div class="w-75" wire:ignore id="select-reject-area-container">
                                    <select class="form-select @error('rejectArea') is-invalid @enderror" id="reject-area-select2" wire:model='rejectArea'>
                                        <option value="" selected>Select defect area</option>
                                        @foreach ($defectAreas as $defect)
                                            <option value="{{ $defect->id }}">
                                                {{ $defect->defect_area }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="w-25">
                                    <button type="button" wire:click="selectRejectAreaPosition" class="btn btn-dark w-100">
                                        <i class="fa-regular fa-image"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            @if ($errors->has('rejectAreaPositionX') || $errors->has('rejectAreaPositionY'))
                                <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                    <small>
                                        <strong>Error</strong> Harap tentukan posisi reject area dengan mengklik tombol <button type="button"class="btn btn-dark btn-sm"><i class="fa-regular fa-image fa-2xs"></i></button> di samping 'select defect area'.
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </small>
                                </div>
                            @endif
                            <div class="d-none">
                                <label class="form-label me-1 mb-2">Reject Area Position</label>
                                <div class="row">
                                    <div class="col d-flex justify-content-center align-items-center">
                                        <label class="form-label me-1 mb-0">X </label>
                                        <div class="d-flex">
                                            <input class="form-control @error('rejectAreaPositionX') is-invalid @enderror" id="reject-area-position-x-livewire" wire:model='rejectAreaPositionX' readonly>
                                        </div>
                                    </div>
                                    <div class="col d-flex justify-content-center align-items-center">
                                        <label class="form-label me-1 mb-1">Y </label>
                                        <div class="d-flex">
                                            <input class="form-control @error('rejectAreaPositionY') is-invalid @enderror" id="reject-area-position-x-livewire" wire:model='rejectAreaPositionY' readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <div id="regular-submit-reject" wire:ignore.self>
                        <button type="button" class="btn btn-success" wire:click='submitInput'>Selesai</button>
                    </div>
                    {{-- <div id="rapid-submit-reject" wire:ignore.self>
                        <button type="button" class="btn btn-success" wire:click='submitRapidInput'>Selesai</button>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>

    {{-- Mass Reject Modal --}}
    <div class="modal" tabindex="-1" id="mass-reject-modal" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header bg-reject">
              <h5 class="modal-title text-light fw-bold">REJECT</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="hidden" name="mass-defect-type" id="mass-defect-type" wire:model=massDefectType>
                    @error('massDefectType')
                        <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                            <small>
                                <strong>Error</strong> {{$message}}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </small>
                        </div>
                    @enderror
                    <label class="form-label">Defect Type</label>
                    <input type="text" class="form-control @error('massDefectType') is-invalid @enderror" wire:model=massDefectTypeName disabled>
                </div>
                <div class="mb-3">
                    <input type="hidden" name="mass-defect-area" id="mass-defect-area" wire:model=massDefectArea>
                    @error('massDefectArea')
                        <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                            <small>
                                <strong>Error</strong> {{$message}}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </small>
                        </div>
                    @enderror
                    <label class="form-label">Defect Area</label>
                    <input type="text" class="form-control @error('massDefectArea') is-invalid @enderror" wire:model=massDefectAreaName disabled>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="mb-3">
                            @error('massQty')
                                <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                    <small>
                                        <strong>Error</strong> {{$message}}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </small>
                                </div>
                            @enderror
                            <label class="form-label">QTY</label>
                            <input type="number" class="form-control @error('massQty') is-invalid @enderror" name="mass-qty" id="mass-qty" value="1" wire:model=massQty>
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-3" x-data="{ sizeMass: $wire.entangle('massSize') }">
                            @error('massSize')
                                <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                    <small>
                                        <strong>Error</strong> {{$message}}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </small>
                                </div>
                            @enderror
                            <label class="form-label">Size</label>
                            <select class="form-select @error('massSize') is-invalid @enderror" name="mass-size" id="mass-size" x-model='sizeMass'>
                                <option value="" selected disabled>Select Size</option>
                                @foreach ($massSelectedDefect as $defect)
                                    <option value="{{ $defect->so_det_id }}">{{ $defect->size }} ({{"qty : ".$defect->total}})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-reject" wire:click='submitMassReject()'>Reject</button>
                <button type="button" class="btn btn-no" data-dismiss="modal" wire:click="$emit('hideModal', 'massReject')">Batal</button>
            </div>
          </div>
        </div>
    </div>

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
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal" wire:click='preSubmitRapidInput'>Lanjut</button>
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
            // Reject Type
            $('#reject-type-select2').select2({
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
                dropdownParent: $('#reject-modal .modal-content #select-reject-type-container')
            });

            $('#reject-type-select2').on('change', function (e) {
                var rejectType = $('#reject-type-select2').select2("val");
                @this.set('rejectType', rejectType);
            });

            // Reject Area
            $('#reject-area-select2').select2({
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
                dropdownParent: $('#reject-modal .modal-content #select-reject-area-container')
            });

            $('#reject-area-select2').on('change', function (e) {
                var rejectArea = $('#reject-area-select2').select2("val");
                @this.set('rejectArea', rejectArea);
            });

            Livewire.on('clearSelectRejectAreaPoint', () => {
                $('#reject-type-select2').val("").trigger('change');
                $('#reject-area-select2').val("").trigger('change');
            });
        })

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

            this.setAttribute("disabled", true);

            // submit
            await @this.preSubmitInput();

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
                @this.updateOutput();
                scannedRejectItemInput.focus();
            }
        });

        // Livewire.on('fromInputPanel', () => {
        //     clearRejectScan();
        // });

        $('#reject-area-modal').on('hidden.bs.modal', function () {
            scannedRejectItemInput.focus();
        })
    </script>
@endpush

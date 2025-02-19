<div wire:init="loadRejectPage">
    <div class="loading-container-fullscreen" wire:loading wire:target="selectRejectAreaPosition, preSubmitInput, submitInput, updateOrder">
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    {{-- Production Input --}}
    <div class="production-input row row-gap-3">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center bg-reject text-light">
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
                    <input type="number" class="qty-input" id="reject-input" value="{{ $outputInput }}" wire:model.defer='outputInput'>
                    <div class="d-flex justify-content-between gap-1 mt-3">
                        <button class="btn btn-danger w-50 fs-3" id="decrement" wire:click="outputDecrement">-1</button>
                        <button class="btn btn-success w-50 fs-3" id="increment" wire:click="outputIncrement">+1</button>
                    </div>
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
                            <p id="reject-qty" class="mb-1 fs-5">{{ $output->count() }}</p>
                        </div>
                        <button class="btn btn-dark" wire:click="$emit('preSubmitUndo', 'reject')">
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
                            <label class="size-input col-md-4">
                                <input type="radio" name="size-input" id="size-input" value="{{ $order->so_det_id }}" wire:model.defer='sizeInput'>
                                <div class="btn btn-reject btn-size w-100 h-100 fs-3 py-auto d-flex flex-column justify-content-center align-items-center">
                                    <p class="fs-3 mb-0">{{ $order->size }}</p>
                                    <p class="fs-5 mb-0">{{ $output->where('size', $order->size)->count() }}</p>
                                </div>
                            </label>
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
                        <button type="button" class="btn btn-dark" wire:click="$emit('preSubmitUndo', 'defect')">
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
                                        <button type="button" class="btn btn-dark" wire:click="showDefectAreaImage('{{$defect->masterPlan->gambar}}', {{$defect->defect_area_x}}, {{$defect->defect_area_y}})">
                                            <i class="fa-regular fa-image"></i>
                                        </button>
                                    </td>
                                    <td class="text-defect fw-bold">{{ strtoupper($defect->defect_status) }}</td>
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
                        <button type="button" class="btn btn-dark" wire:click="$emit('preSubmitUndo', 'reject')">
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
                                    <td>{{ $reject->kode_numbering }}</td>
                                    <td>{{ $reject->so_det_size }}</td>
                                    <td>{{ $reject->defect ? ($reject->defectType ? $reject->defectType->defect_type : '-') : ($reject->defectType ? $reject->defectType->defect_type : '-') }}</td>
                                    <td>{{ $reject->defect ? ($reject->defectArea ? $reject->defectArea->defect_area : '-') : ($reject->defectArea ? $reject->defectArea->defect_area : '-') }}</td>
                                    <td>
                                        @if ($reject->defect)
                                            <button type="button" class="btn btn-dark" wire:click="showDefectAreaImage('{{$reject->masterPlan->gambar}}', {{$reject->defect_area_x}}, {{$reject->defect_area_y}})">
                                                <i class="fa-regular fa-image"></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-dark" wire:click="showDefectAreaImage('{{$reject->masterPlan->gambar}}', {{$reject->defect_area_x}}, {{$reject->defect_area_y}})">
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

    <div class="modal" tabindex="-1" id="all-reject-modal" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-body">
                    <h5>Reject semua defect?</h5>
                    <div class="d-flex justify-content-center align-items-center my-3">
                        <button type="button" class="btn btn-reject" wire:click='submitAllReject()'>Reject</button>
                        <button type="button" class="btn btn-no" data-dismiss="modal" wire:click="$emit('hideModal', 'allReject')">Batal</button>
                    </div>
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
                                <label class="form-label me-1 mb-0">Reject Type</label>
                            </div>
                            <div wire:ignore id="select-reject-type-container">
                                <select class="form-select @error('rejectType') is-invalid @enderror" id="sasdasdadasd-select2" wire:model='rejectType'>
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
                                    <select class="form-select @error('rejectArea') is-invalid @enderror" id="sasdasdadasd-select2s" wire:model='rejectArea'>
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
                                            <input class="form-control @error('rejectAreaPositionY') is-invalid @enderror" id="reject-area-position-y-livewire" wire:model='rejectAreaPositionY' readonly>
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
            // Defect Type
            $('#sasdasdadasd-select2').select2({
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
                dropdownParent: $('#reject-modal .modal-content #select-reject-type-container')
            });

            $('#sasdasdadasd-select2').on('change', function (e) {
                var rejectType = $('#sasdasdadasd-select2').select2("val");
                @this.set('rejectType', rejectType);
            });

            // Defect Area
            $('#sasdasdadasd-select2s').select2({
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
                dropdownParent: $('#reject-modal .modal-content #select-reject-area-container')
            });

            $('#sasdasdadasd-select2s').on('change', function (e) {
                var rejectArea = $('#sasdasdadasd-select2s').select2("val");
                @this.set('rejectArea', rejectArea);
            });

            $(document).on('select2:open', () => {
                document.querySelector('.select2-search__field').focus();
            });

            Livewire.on('clearSelectRejectAreaPoint', () => {
                $('#product-type-select2').val("").trigger('change');
                $('#reject-type-select2').val("").trigger('change');
                $('#reject-area-select2').val("").trigger('change');
            });

            document.getElementById('reject-input').addEventListener("keyup", async (event) => {
                if (event.key === 'Enter' || event.keyCode === 13) {
                    await @this.preSubmitInput();
                    let el = document.querySelector( ':focus' );
                    if( el ) el.blur();
                }
            });
        })
    </script>
@endpush


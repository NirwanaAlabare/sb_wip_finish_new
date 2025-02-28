<div wire:init="loadReworkPage">
    <div class="loading-container-fullscreen" wire:loading wire:target='submitMassRework'>
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    {{-- Production Input --}}
    <div class="mt-5 hidden" id="loading-rework">
        <div class="loading mx-auto"></div>
    </div>
    <div class="production-input row row-gap-3" id="content-rework">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header align-items-center bg-rework text-light">
                    <p class="mb-0 fs-5">Defect List</p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-5 align-self-center">
                            <div class="w-100 h-100" wire:loading wire:target='loadReworkPage'>
                                <div class="loading-container">
                                    <div class="loading"></div>
                                </div>
                            </div>
                            <div class="scroll-defect-area-img" wire:loading.remove wire:target='loadReworkPage'>
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
                                <button class="btn btn-rework fw-bold rounded-0 w-25 h-100" wire:click="$emit('preSubmitAllRework')">Rework all</button>
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
                                                        <button class="btn btn-sm btn-rework fw-bold w-100"
                                                            wire:click="preSubmitMassRework('{{ $defectList->defect_type_id }}', '{{ $defectList->defect_area_id }}', '{{ $defectList->defect_type }}', '{{ $defectList->defect_area }}')"
                                                        >
                                                            REWORK
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
                <div class="card-header d-flex justify-content-between align-items-center bg-rework text-light">
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
                            <th>ID</th>
                            <th>Size</th>
                            <th>Defect Type</th>
                            <th>Defect Area</th>
                            <th>Defect Area Image</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        @if ($defects->count() < 1)
                            <tr>
                                <td colspan='8'>Defect tidak ditemukan</td>
                            </tr>
                        @else
                            @foreach ($defects as $defect)
                                <tr>
                                    <td>{{ $defects->firstItem() + $loop->index }}</td>
                                    <td>{{ $defect->id }}</td>
                                    <td>{{ $defect->so_det_size }}</td>
                                    <td>{{ $defect->defect_type}}</td>
                                    <td>{{ $defect->defect_area }}</td>
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
                                            <button class="btn btn-sm btn-rework fw-bold w-100"
                                                wire:click="$emit('preSubmitRework', '{{ $defect->id }}', '{{ $defect->so_det_size }}', '{{ $defect->defectType->defect_type }}', '{{ $defect->defectArea->defect_area }}', '{{ $defect->masterPlan->gambar }}', '{{ $defect->defect_area_x }}', '{{ $defect->defect_area_y }}')">
                                                REWORK
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
                <div class="card-header d-flex justify-content-between align-items-center bg-rework text-light">
                    <p class="mb-0 fs-5">Data Rework</p>
                    <div class="d-flex justify-content-end align-items-center gap-1">
                        <button type="button" class="btn btn-dark" wire:click="$emit('preSubmitUndo', 'rework')">
                            <i class="fa-regular fa-rotate-left"></i>
                        </button>
                        {{-- <button type="button" class="btn btn-dark">
                            <i class="fa-regular fa-gear"></i>
                        </button> --}}
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <div class="d-flex justify-content-center align-items-center">
                        <input type="text" class="form-control mb-3 rounded-0" id="search-rework" name="search-rework" wire:model='searchRework' placeholder="Search here...">
                    </div>
                    <table class="table table-bordered text-center align-middle">
                        <tr>
                            <th>No.</th>
                            <th>ID</th>
                            <th>Size</th>
                            <th>Defect Type</th>
                            <th>Defect Area</th>
                            <th>Defect Area Image</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        @if ($reworks->count() < 1)
                            <tr>
                                <td colspan='8'>Rework tidak ditemukan</td>
                            </tr>
                        @else
                            @foreach ($reworks as $rework)
                                <tr>
                                    <td>{{ $reworks->firstItem() + $loop->index }}</td>
                                    <td>{{ $rework->id }}</td>
                                    <td>{{ $rework->so_det_size }}</td>
                                    <td>{{ $rework->defect_type}}</td>
                                    <td>{{ $rework->defect_area }}</td>
                                    <td class="text-rework fw-bold">{{ strtoupper($rework->status) }}</td>
                                    <td>
                                        <button type="button" class="btn btn-dark" wire:click="showDefectAreaImage('{{$rework->gambar}}', {{$rework->defect_area_x}}, {{$rework->defect_area_y}})'">
                                            <i class="fa-regular fa-image"></i>
                                        </button>
                                    </td>
                                    <td>
                                        <div wire:loading>
                                            <div class="loading-small"></div>
                                        </div>
                                        <div wire:loading.remove>
                                            <button class="btn btn-sm btn-defect fw-bold w-100" wire:click="$emit('preCancelRework', '{{ $rework->id }}', '{{ $rework->id }}', '{{ $rework->so_det_size }}', '{{ $rework->defect_type }}', '{{ $rework->defect_area }}', '{{$rework->gambar}}', {{$rework->defect_area_x}}, {{$rework->defect_area_y}})">CANCEL</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </table>
                    {{ $reworks->links() }}
                </div>
            </div>
        </div>
    </div>

    <div class="modal" tabindex="-1" id="mass-rework-modal" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header bg-rework">
              <h5 class="modal-title text-light fw-bold">REWORK</h5>
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
                        <div class="mb-3">
                            @error('massSize')
                                <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                    <small>
                                        <strong>Error</strong> {{$message}}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </small>
                                </div>
                            @enderror
                            <label class="form-label">Size</label>
                            <select class="form-select @error('massSize') is-invalid @enderror" name="mass-size" id="mass-size" wire:model='massSize'>
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
                <button type="button" class="btn btn-rework" wire:click='submitMassRework()'>Rework</button>
                <button type="button" class="btn btn-no" data-dismiss="modal" wire:click="$emit('hideModal', 'massRework')">Batal</button>
            </div>
          </div>
        </div>
    </div>

    <div class="modal" tabindex="-1" id="all-rework-modal" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-body">
                    <h5>Rework semua defect?</h5>
                    <div class="d-flex justify-content-center align-items-center my-3">
                        <button type="button" class="btn btn-rework" wire:click='submitAllRework()'>Rework</button>
                        <button type="button" class="btn btn-no" data-dismiss="modal" wire:click="$emit('hideModal', 'allRework')">Batal</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

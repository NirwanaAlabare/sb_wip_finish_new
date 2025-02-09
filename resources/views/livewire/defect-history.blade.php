<div wire:poll.visible.30000ms>
    <div class="production-input row row-gap-3">
        <div class="col-md-12">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center bg-defect text-light">
                    <p class="mb-0 fs-5">Defect History</p>
                    <div class="d-flex justify-content-end align-items-center gap-1">
                        <button type="button" class="btn btn-dark" wire:click="$emit('preSubmitUndo', 'defect')">
                            <i class="fa-regular fa-rotate-left"></i>
                        </button>
                    </div>
                </div>
                <div class="loading-container hidden my-3" id="loading-defect-history">
                    <div class="loading mx-auto"></div>
                </div>
                <div class="card-body table-responsive" id="content-defect-history">
                    <div class="d-flex justify-content-center align-items-center">
                        <input type="text" class="form-control mb-3 rounded-0" id="search" name="search" wire:model='search' placeholder="Search here...">
                        <button class="btn btn-dark mb-3 rounded-0" data-bs-toggle="modal" data-bs-target="#filter-modal"><i class="fa-regular fa-filter"></i></button>
                    </div>
                    <table class="table table-bordered text-center align-middle">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Tanggal & Waktu</th>
                                <th>Size</th>
                                <th>Defect Type</th>
                                <th>Defect Area</th>
                                <th>Defect Area Image</th>
                                <th>Status</th>
                                <th>Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($defects->count() < 1)
                                <tr>
                                    <td colspan="8"><i class="fa-solid fa-circle-exclamation"></i> Defect tidak ditemukan</td>
                                </tr>
                            @else
                                @foreach ($defects as $defect)
                                    @php
                                        $defectStatusColor = ($defect->defect_status == 'defect' ? 'text-defect' : ($defect->defect_status == 'reworked' ? 'text-rework' : 'text-danger'))
                                    @endphp
                                    <tr>
                                        <td>{{ $defects->firstItem() + $loop->index }}</td>
                                        <td>{{ $defect->updated_at }}</td>
                                        <td>{{ $defect->so_det_size }}</td>
                                        <td>{{ $defect->defect_type }}</td>
                                        <td>{{ $defect->defect_area }}</td>
                                        <td>
                                            <button type="button" class="btn btn-dark" wire:click="showDefectAreaImage('{{$defect->gambar}}', {{$defect->defect_area_x}}, {{$defect->defect_area_y}})'">
                                                <i class="fa-regular fa-image"></i>
                                            </button>
                                        </td>
                                        <td class="{{ $defectStatusColor }} fw-bold">{{ strtoupper($defect->defect_status) }}</td>
                                        <td>{{ $defect->total }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                    {{ $defects->links() }}
                </div>
            </div>
        </div>

        {{-- Filter Modal --}}
        <div class="modal" tabindex="-1" id="filter-modal" aria-hidden="true" wire:ignore.self>
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Filter</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Size</label>
                            <select class="form-select" aria-label="Default select example" wire:model='filterDefectSize'>
                                <option value="all" selected>Semua Size</option>
                                @foreach ($orderWsDetailSizes as $order)
                                    <option value="{{ $order->so_det_id }}">{{ $order->size }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Defect Type</label>
                            <select class="form-select" aria-label="Default select example" wire:model='filterDefectType'>
                                <option value="all" selected>Semua Defect Type</option>
                                @foreach ($defectTypes as $defectType)
                                    <option value="{{ $defectType->id }}">{{ $defectType->defect_type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Defect Area</label>
                            <select class="form-select" aria-label="Default select example" wire:model='filterDefectArea'>
                                <option value="all" selected>Semua Defect Area</option>
                                @foreach ($defectAreas as $defectArea)
                                    <option value="{{ $defectArea->id }}">{{ $defectArea->defect_area }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Status</label>
                            <select class="form-select" aria-label="Default select example" wire:model='filterDefectStatus'>
                                <option value="all" selected>Semua Status</option>
                                <option class="text-defect fw-bold" value="defect">Defect</option>
                                <option class="text-rework fw-bold" value="reworked">Reworked</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        {{-- <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center bg-rework text-light">
                    <p class="mb-0 fs-5">Size</p>
                    <div class="d-flex justify-content-end align-items-center gap-1">
                        <div class="d-flex align-items-center gap-3 me-3">
                            <p class="mb-1 fs-5">REWORK</p>
                            <p class="mb-1 fs-5">:</p>
                            <p id="rft-qty" class="mb-1 fs-5">0</p>
                        </div>
                        <button class="btn btn-dark">
                            <i class="fa-regular fa-rotate-left"></i>
                        </button>
                        <button class="btn btn-dark">
                            <i class="fa-regular fa-gear"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <input type="hidden" class="form-control mb-3">
                    <div class="row h-100 row-gap-3">
                        <div class="col-3">
                            <button class="btn btn-rework w-100 h-100 fs-3">
                                XS
                            </button>
                        </div>
                        <div class="col-3">
                            <button class="btn btn-rework w-100 h-100 fs-3">
                                S
                            </button>
                        </div>
                        <div class="col-3">
                            <button class="btn btn-rework w-100 h-100 fs-3">
                                M
                            </button>
                        </div>
                        <div class="col-3">
                            <button class="btn btn-rework w-100 h-100 fs-3">
                                L
                            </button>
                        </div>
                        <div class="col-3">
                            <button class="btn btn-rework w-100 h-100 fs-3">
                                XL
                            </button>
                        </div>
                        <div class="col-3">
                            <button class="btn btn-rework w-100 h-100 fs-3">
                                XXL
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
</div>

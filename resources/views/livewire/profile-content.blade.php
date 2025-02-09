<div>
    {{-- Summary Line --}}
    <div class="mb-5" wire:poll.visible>
        <h5 class="text-center mb-3">SUMMARY LINE</h5>
        <div class="d-flex justify-content-center align-items-center">
            <div class="mb-3">
                <input type="date" class="form-control" name="date-from" id="date-from" value="{{ date('Y-m-d') }}" wire:model='dateFrom'>
            </div>
            <span class="mx-3 mb-3"> - </span>
            <div class="mb-3">
                <input type="date" class="form-control" name="date-to" id="date-to" value="{{ date('Y-m-d') }}" wire:model='dateTo'>
            </div>
        </div>
        <div class="loading-container" wire:loading wire:target="dateFrom, dateTo">
            <div class="loading-container">
                <div class="loading"></div>
            </div>
        </div>
        <div class="loading-container hidden" id="loading-profile">
            <div class="loading mx-auto"></div>
        </div>
        <div class="row row-gap-3" id="content-profile" wire:loading.remove wire:target="dateFrom, dateTo">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-rft text-light fw-bold">
                        RFT
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Total RFT</h5>
                        <p class="card-text fs-3 fw-bold text-rft" wire:poll.visible>{{ $totalRft }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-defect text-light fw-bold">
                        DEFECT
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Total DEFECT</h5>
                        <p class="card-text fs-3 fw-bold text-defect" wire:poll.visible>{{ $totalDefect }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-reject text-light fw-bold">
                        REJECT
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Total REJECT</h5>
                        <p class="card-text fs-3 fw-bold text-reject">{{ $totalReject }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-rework text-light fw-bold">
                        REWORK
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Total REWORK</h5>
                        <p class="card-text fs-3 fw-bold text-rework">{{ $totalRework }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

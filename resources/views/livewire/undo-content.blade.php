<div>
    {{-- Latest Undo --}}
    <div class="mt-1" wire:poll.visible.5000ms>
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
        {{-- <div class="loading-container hidden" id="loading-undo">
            <div class="loading mx-auto"></div>
        </div> --}}
        <div class="row" id="content-undo" wire:loading.remove wire:target="dateFrom, dateTo">
            {{-- <div class="col-md-12 table-responsive">
                <table class="table table-bordered w-100 mx-auto">
                    <thead>
                        <tr>
                            <th class="text-end">Tanggal & Waktu</th>
                            <th class="text-start">Tipe</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < count($latestOutput); $i++)
                            <tr>
                                <td class="text-end">{{ $latestOutput[$i]->updated_at }}</td>
                                <td class="text-start"> - </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div> --}}
            <div class="col-md-12 table-responsive">
                <table class="table table-bordered w-100 mx-auto">
                    <thead>
                        <tr>
                            <th>Tanggal & Waktu</th>
                            <th>Ukuran</th>
                            <th>Tipe</th>
                            <th>Qty</th>
                            {{-- <th>Detail</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($latestUndo) < 1)
                            <tr>
                                <td colspan="4" class="text-center">Data tidak ditemukan</td>
                            </tr>
                        @else
                            @foreach ($latestUndo as $undo)
                                <tr>
                                    <td>{{ $undo->updated_at }}</td>
                                    <td>{{ $undo->size }}</td>
                                    <td class="text-{{$undo->keterangan}} fw-bold">{{ strtoupper($undo->keterangan) }}</td>
                                    <td>{{ $undo->total }}</td>
                                    {{-- <td>
                                        @if ($undo->keterangan == 'defect' || $undo->keterangan == 'rework')
                                            <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#output-detail">
                                                <i class="fa-regular fa-magnifying-glass"></i>
                                            </button>
                                        @else
                                            <p>-</p>
                                        @endif
                                    </td> --}}
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
                {{ $latestUndo->links( )}}
                <button class="btn btn-danger w-auto" wire:click='restoreUndo'>
                    <i class="fa fa-reply"></i> Restore
                </button>
            </div>
        </div>
    </div>
</div>

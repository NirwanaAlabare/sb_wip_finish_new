<div>
    {{-- Latest Output --}}
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
        {{-- <div class="loading-container hidden" id="loading-history">
            <div class="loading mx-auto"></div>
        </div> --}}
        <div class="row" id="content-history" wire:loading.remove wire:target="dateFrom, dateTo">
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
            <div class="col-md-6 table-responsive">
                <p class="text-rft fw-bold mb-1"> RFT </p>
                <table class="table table-bordered w-100 mx-auto">
                    <thead>
                        <tr>
                            <th>Tanggal & Waktu</th>
                            <th>Ukuran</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($latestRfts) < 1)
                            <tr>
                                <td colspan="3" class="text-center">Data tidak ditemukan</td>
                            </tr>
                        @else
                            @foreach ($latestRfts as $latestRft)
                                <tr>
                                    <td>{{ $latestRft->updated_at }}</td>
                                    <td>{{ $latestRft->size }}</td>
                                    <td>{{ $latestRft->total }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="col-md-6 table-responsive">
                <p class="text-reject fw-bold mb-1"> REJECT </p>
                <table class="table table-bordered w-100 mx-auto">
                    <thead>
                        <tr>
                            <th>Tanggal & Waktu</th>
                            <th>Ukuran</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($latestRejects) < 1)
                            <tr>
                                <td colspan="3" class="text-center">Data tidak ditemukan</td>
                            </tr>
                        @else
                            @foreach ($latestRejects as $latestReject)
                                <tr>
                                    <td>{{ $latestReject->updated_at }}</td>
                                    <td>{{ $latestReject->size }}</td>
                                    <td>{{ $latestReject->total }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="col-md-6 table-responsive">
                <p class="text-defect fw-bold mb-1"> DEFECT </p>
                <table class="table table-bordered w-100 mx-auto">
                    <thead>
                        <tr>
                            <th>Tanggal & Waktu</th>
                            <th>Ukuran</th>
                            <th>Defect Type</th>
                            <th>Defect Area</th>
                            @if ($masterPlan)
                                <th>Defect Image</th>
                            @endif
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($latestDefects) < 1)
                            <tr>
                                <td colspan="6" class="text-center">Data tidak ditemukan</td>
                            </tr>
                        @else
                            @foreach ($latestDefects as $latestDefect)
                                <tr>
                                    <td>{{ $latestDefect->updated_at }}</td>
                                    <td>{{ $latestDefect->size }}</td>
                                    <td>{{ $latestDefect->defect_type }}</td>
                                    <td>{{ $latestDefect->defect_area }}</td>
                                    @if ($masterPlan)
                                        <td>
                                            <button type="button" class="btn btn-dark" wire:click="$emit('showDefectAreaImage', '{{$latestDefect->gambar}}', {{$latestDefect->defect_area_x}}, {{$latestDefect->defect_area_y}})'">
                                                <i class="fa-regular fa-image"></i>
                                            </button>
                                        </td>
                                    @endif
                                    <td>{{ $latestDefect->total }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="col-md-6 table-responsive">
                <p class="text-rework fw-bold mb-1"> REWORK </p>
                <table class="table table-bordered w-100 mx-auto">
                    <thead>
                        <tr>
                            <th>Tanggal & Waktu</th>
                            <th>Ukuran</th>
                            <th>Defect Type</th>
                            <th>Defect Area</th>
                            @if ($masterPlan)
                                <th>Defect Image</th>
                            @endif
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($latestReworks) < 1)
                            <tr>
                                <td colspan="6" class="text-center">Data tidak ditemukan</td>
                            </tr>
                        @else
                            @foreach ($latestReworks as $latestRework)
                                <tr>
                                    <td>{{ $latestRework->updated_at }}</td>
                                    <td>{{ $latestRework->size }}</td>
                                    <td>{{ $latestRework->defect_type }}</td>
                                    <td>{{ $latestRework->defect_area }}</td>
                                    @if ($masterPlan)
                                        <td>
                                            <button type="button" class="btn btn-dark" wire:click="$emit('showDefectAreaImage', '{{$latestRework->gambar}}', {{$latestRework->defect_area_x}}, {{$latestRework->defect_area_y}})'">
                                                <i class="fa-regular fa-image"></i>
                                            </button>
                                        </td>
                                    @endif
                                    <td>{{ $latestRework->total }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            {{-- <div class="col-md-8">
                <div id="daily-chart"></div>
            </div> --}}
        </div>
    </div>
</div>

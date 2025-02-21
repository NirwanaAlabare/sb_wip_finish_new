<div>
    <div class="d-flex gap-1">
        <div class="input-group mb-3">
            <input type="hidden" wire:model='date'>
            <input type="text" class="form-control" wire:model='search' placeholder="Search Order...">
            <button class="btn btn-danger" type="button" id="button-search-order"><i class="fa-regular fa-magnifying-glass"></i></button>
        </div>
        <button class="btn btn-outline-danger mb-3" type="button" wire:click="preSubmitFilter" id="filter-button"><i class="fa-regular fa-filter"></i></button>
        <button class="btn btn-danger mb-3" type="button" wire:click="toggleMethod">{{ strtoUpper($method) }}</button>
    </div>

    <div class="loading-container-fullscreen hidden" id="loading-order-list">
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>

    <div class="w-100" wire:loading wire:target='search, date, filterLine, filterBuyer, filterWs, filterProductType, filterStyle'>
        <div class="loading-container">
            <div class="loading"></div>
        </div>
        <p class="text-center text-danger fw-bold mt-3 mb-0">
            Mohon Tunggu...
        </p>
    </div>

    <div class="order-list row row-gap-3 mb-3 h-100" wire:loading.remove wire:target='search, date'>
        @if ($orders->isEmpty())
            <h5 class="text-center text-muted mt-3"><i class="fa-solid fa-circle-exclamation"></i> Order tidak ditemukan</h5>
        @else
            @foreach ($orders as $order)
                <a href="{{ $this->baseUrl."/production-panel/".$order->id."/".$method }}" class="order col-md-6 h-100">
                    <div class="card overflow-hidden h-100">
                        @if ($order->plan_date != date('Y-m-d'))
                            <span class="{{ $this->date < date('Y-m-d') && $order->plan_date < $this->date ? 'bg-defect' : 'bg-danger' }} text-light text-center fw-bold p-1 rounded-1" style="position: absolute; width:35%; top:10%; right: -10%; transform:rotate(45deg);">BERLALU</span>
                        @endif
                        <div class="card-body justify-content-start">
                            <table class="table table-responsive mb-1">
                                <tr>
                                    <td class="text-nowrap">Line</td>
                                    <td class="text-nowrap">:</td>
                                    <td class="fw-bold">{{ strtoupper($order->sewing_line) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-nowrap">Buyer</td>
                                    <td class="text-nowrap">:</td>
                                    <td class="fw-bold">{{ ucwords($order->buyer_name) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-nowrap">WS Number</td>
                                    <td class="text-nowrap">:</td>
                                    <td class="fw-bold">{{ $order->ws_number }}</td>
                                </tr>
                                <tr>
                                    <td class="text-nowrap">Product Type</td>
                                    <td class="text-nowrap">:</td>
                                    <td class="fw-bold">{{ $order->product_type }}</td>
                                </tr>
                                <tr>
                                    <td class="text-nowrap">Style</td>
                                    <td class="text-nowrap">:</td>
                                    <td class="fw-bold">{{ ucwords($order->style_name) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-nowrap">Plan Date</td>
                                    <td class="text-nowrap">:</td>
                                    <td class="fw-bold">{{ $order->plan_date }}</td>
                                </tr>
                            </table>
                            <div class="mx-2">
                                <div class="d-flex justify-content-between w-100">
                                    <p class="mb-1">Output : <b>{{ $order->progress }}</b></p>
                                    <p class="mb-1">Endline : <b>{{ $order->target }}</b></p>
                                </div>
                                <div class="progress" role="progressbar" aria-valuenow="{{ $order->progress }}" aria-valuemin="0" aria-valuemax="{{ $order->target }}" style="height: 15px">
                                    @php
                                        $outputProgress = $order->target > 0 ? floatval($order->progress)/floatval($order->target) * 100 : 0;
                                    @endphp
                                    <div class="progress-bar fw-bold {{ $outputProgress > 100 ? 'bg-rft' : 'bg-success' }}" style="width:{{  $outputProgress }}%">{{ $outputProgress > 100 ? 'TARGET TERLAMPAUI' : '' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a href="{{ $this->baseUrl."/production-panel/".$order->id }}">
            @endforeach
        @endif
    </div>

    <div class="w-100 mt-3">
        <p class="text-center opacity-50"><small><i>{{ date('Y') }} &copy; Nirwana Digital Solution</i></small></p>
    </div>

    {{-- Filter Modal --}}
    <div class="modal" tabindex="-1" id="filter-modal" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-light">
                    <h5 class="modal-title"><i class="fa-regular fa-filter"></i> FILTER</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Line</label>
                            <div wire:ignore id="select-line-container">
                                <select class="form-select @error('filterLine') is-invalid @enderror" id="line-select2" wire:model='filterLine'>
                                    <option value="" selected>Select Line</option>
                                    @foreach ($orderFilters->groupBy('sewing_line') as $order)
                                        <option value="{{ $order->first()->sewing_line }}">
                                            {{ strtoupper($order->first()->sewing_line) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Buyer</label>
                            <div wire:ignore id="select-buyer-container">
                                <select class="form-select @error('filterBuyer') is-invalid @enderror" id="buyer-select2" wire:model='filterBuyer'>
                                    <option value="" selected>Select Buyer</option>
                                    @foreach ($orderFilters->groupBy('buyer_name') as $order)
                                        <option value="{{ $order->first()->buyer_name }}">
                                            {{ strtoupper($order->first()->buyer_name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. WS</label>
                            <div wire:ignore id="select-ws-container">
                                <select class="form-select @error('filterWs') is-invalid @enderror" id="ws-select2" wire:model='filterWs'>
                                    <option value="" selected>Select No. WS</option>
                                    @foreach ($orderFilters->groupBy('ws_number') as $order)
                                        <option value="{{ $order->first()->ws_number }}">
                                            {{ strtoupper($order->first()->ws_number) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Product Type</label>
                            <div wire:ignore id="select-product-type-container">
                                <select class="form-select @error('filterProductType') is-invalid @enderror" id="product-type-select2" wire:model='filterProductType'>
                                    <option value="" selected>Select Product Type</option>
                                    @foreach ($orderFilters->groupBy('product_type') as $order)
                                        <option value="{{ $order->first()->product_type }}">
                                            {{ strtoupper($order->first()->product_type) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Style</label>
                            <div wire:ignore id="select-style-container">
                                <select class="form-select @error('filterStyle') is-invalid @enderror" id="style-select2" wire:model='filterStyle'>
                                    <option value="" selected>Select Style</option>
                                    @foreach ($orderFilters->groupBy('style_name') as $order)
                                        <option value="{{ $order->first()->style_name }}">
                                            {{ strtoupper($order->first()->style_name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa-regular fa-times"></i> Tutup</button>
                    <button type="button" class="btn btn-success" wire:click="clearFilter"><i class="fa-regular fa-broom"></i> Bersihkan</button>
                    {{-- <button type="button" class="btn btn-success" wire:click='submitFilter'><i class="fa-regular fa-check"></i> Terapkan</button> --}}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        Livewire.emit("loadingStart");

        document.addEventListener("DOMContentLoaded", () => {
            $("#loading-order-list").addClass("hidden");

            $('#filter-button').on('click', function (e) {
                Livewire.emit("loadingStart");
            });

            // Line
            $('#line-select2').select2({
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
                dropdownParent: $('#filter-modal .modal-content #select-line-container')
            });

            $('#line-select2').on('change', function (e) {
                Livewire.emit("loadingStart");
                var filterLine = $('#line-select2').select2("val");
                @this.set('filterLine', filterLine);
            });

            // Buyer
            $('#buyer-select2').select2({
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
                dropdownParent: $('#filter-modal .modal-content #select-buyer-container')
            });

            $('#buyer-select2').on('change', function (e) {
                Livewire.emit("loadingStart");
                var filterBuyer = $('#buyer-select2').select2("val");
                @this.set('filterBuyer', filterBuyer);
            });

            // WS
            $('#ws-select2').select2({
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
                dropdownParent: $('#filter-modal .modal-content #select-ws-container')
            });

            $('#ws-select2').on('change', function (e) {
                Livewire.emit("loadingStart");
                var filterWs = $('#ws-select2').select2("val");
                @this.set('filterWs', filterWs);
            });

            // Product Type
            $('#product-type-select2').select2({
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
                dropdownParent: $('#filter-modal .modal-content #select-product-type-container')
            });

            $('#product-type-select2').on('change', function (e) {
                Livewire.emit("loadingStart");
                var filterProductType = $('#product-type-select2').select2("val");
                @this.set('filterProductType', filterProductType);
            });

            // Style
            $('#style-select2').select2({
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
                dropdownParent: $('#filter-modal .modal-content #select-style-container')
            });

            $('#style-select2').on('change', function (e) {
                Livewire.emit("loadingStart");
                var filterStyle = $('#style-select2').select2("val");
                @this.set('filterStyle', filterStyle);
            });

            Livewire.on('clearFilterInput', () => {
                $('#line-select2').val("").trigger('change');
                $('#buyer-select2').val("").trigger('change');
                $('#ws-select2').val("").trigger('change');
                $('#product-type-select2').val("").trigger('change');
                $('#style-select2').val("").trigger('change');
            });

            Livewire.emit("loadingStop");
        })
    </script>
@endpush

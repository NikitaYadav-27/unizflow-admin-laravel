@if (!request()->has('turbo'))
    @include('header', ['page' => 'profit'])
@endif

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 my-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-style2 mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);">Accounting</a></li>
            <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-chart-arrows-vertical me-1 fs-15"></i>Admin Profit</li>
        </ol>
    </nav>
</div>

<div class="card custom-card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="row g-3 align-items-end">
            <div class="col-xxl-2 col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <label class="form-label fs-12 fw-semibold text-muted mb-1">Page Length</label>
                <select onchange="changeLength(this.value)" class="form-select form-select-sm" id="pageSize">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50" selected>50</option>
                    <option value="100">100</option>
                    <option value="500">500</option>
                </select>
            </div>

            <div class="col-xxl-3 col-xl-3 col-lg-5 col-md-8 col-sm-12">
                <label class="form-label fs-12 fw-semibold text-muted mb-1">Date Range</label>
                <div id="reportrange" class="form-control form-control-sm d-flex justify-content-between align-items-center bg-white" style="cursor: pointer; min-height: 31px;">
                    <div><i class="ti ti-calendar me-1 text-primary"></i><span class="fs-12"></span></div>
                    <i class="fa fa-caret-down text-muted fs-11"></i>
                </div>
            </div>

            <div class="col-xxl-2 col-xl-2 col-lg-4 col-md-4 col-sm-6">
                <label class="form-label fs-12 fw-semibold text-muted mb-1">Service</label>
                <select onchange="changeService(this.value);" class="form-select form-select-sm" id="serviceFilter">
                    <option value="">All Services</option>
                    <option value="payin">PayIn</option>
                    <option value="payout">PayOut</option>
                </select>
            </div>

            <div class="col-xxl-2 col-xl-2 col-lg-4 col-md-4 col-sm-6">
                <label class="form-label fs-12 fw-semibold text-muted mb-1">Provider</label>
                <select onchange="changeProvider(this.value);" class="form-select form-select-sm" id="providerFilter">
                    <option value="">All Providers</option>
                    @foreach ($providers as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} ({{ strtoupper($p->type) }})</option>
                    @endforeach
                </select>
            </div>

            <div class="col-xxl-3 col-xl-3 col-lg-4 col-md-4 col-sm-6">
                <label class="form-label fs-12 fw-semibold text-muted mb-1">Partner / Merchant</label>
                <select onchange="changeUser(this.value);" class="form-select form-select-sm" id="userFilter">
                    <option value="">All Partners</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 mt-2">
                <label class="form-label fs-12 fw-semibold text-muted mb-1">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="ti ti-search text-muted"></i></span>
                    <input type="text" onkeyup="changeSearchValue(this.value)" class="form-control border-start-0" id="searchValue" placeholder="Search Txn ID, Merchant, Name...">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4 g-3">
    <div class="col-xxl-3 col-xl-3 col-md-6 col-12">
        <div class="card custom-card border-0 shadow-sm h-100 mb-0 bg-white">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0" style="width: 46px; height: 46px; background-color: #0f233a;">
                        <i class="ti ti-wallet fs-20"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h4 class="fw-bold mb-0 text-dark fs-17 text-truncate" id="summaryVolume">₹ 0.00</h4>
                        <span class="text-muted fs-12 fw-medium">Total Volume</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xxl-3 col-xl-3 col-md-6 col-12">
        <div class="card custom-card border-0 shadow-sm h-100 mb-0 bg-white">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0" style="width: 46px; height: 46px; background-color: #0ea5e9;">
                        <i class="ti ti-arrow-down-left fs-20"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h4 class="fw-bold mb-0 text-dark fs-17 text-truncate" id="summaryPartner">₹ 0.00</h4>
                        <span class="text-muted fs-12 fw-medium">Partner Charges</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xxl-3 col-xl-3 col-md-6 col-12">
        <div class="card custom-card border-0 shadow-sm h-100 mb-0 bg-white">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0" style="width: 46px; height: 46px; background-color: #f59e0b;">
                        <i class="ti ti-arrow-up-right fs-20"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h4 class="fw-bold mb-0 text-dark fs-17 text-truncate" id="summaryProvider">₹ 0.00</h4>
                        <span class="text-muted fs-12 fw-medium">Provider Charges</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xxl-3 col-xl-3 col-md-6 col-12">
        <div class="card custom-card border-0 shadow-sm h-100 mb-0 bg-white">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0" style="width: 46px; height: 46px; background-color: #10b981;">
                        <i class="ti ti-chart-arrows-vertical fs-20"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="d-flex align-items-center gap-2">
                            <h4 class="fw-bold mb-0 text-dark fs-17 text-truncate" id="summaryProfit">₹ 0.00</h4>
                            <span class="badge bg-success-transparent fs-10" id="summaryMargin">0.00%</span>
                        </div>
                        <span class="text-muted fs-12 fw-medium">Admin Net Profit</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card custom-card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex gap-2 mb-3">
            <button onclick="exportProfitCsv()" class="btn btn-success btn-sm px-3 fw-semibold d-inline-flex align-items-center gap-1 shadow-sm" style="background-color: #22c55e; border-color: #22c55e;">
                <i class="ti ti-file-spreadsheet fs-15"></i> Export Report (CSV)
            </button>
        </div>

        <div class="table-responsive w-100" style="overflow-x: auto !important;">
            <table class="table text-nowrap table-hover align-middle w-100 mb-0 border" id="dataTable">
                <thead style="background-color: #e2e8f0; color: #334155;">
                    <tr>
                        <th scope="col" style="width: 45px;" class="text-center">#</th>
                        <th scope="col" style="min-width: 170px;">Txn ID / Service</th>
                        <th scope="col" style="min-width: 125px;">Date Time</th>
                        <th scope="col" style="min-width: 160px;">Partner</th>
                        <th scope="col" style="min-width: 110px;">Provider</th>
                        <th scope="col" style="min-width: 110px;">Amount</th>
                        <th scope="col" style="min-width: 150px;">Partner Charge</th>
                        <th scope="col" style="min-width: 150px;">Provider Charge</th>
                        <th scope="col" style="min-width: 135px;">Admin Profit</th>
                        <th scope="col" style="min-width: 95px;" class="text-center">Margin %</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    window.dataTable = null;
    window.startDate = "";
    window.endDate = "";
    window.service = "";
    window.provider = "";
    window.userId = "";
    window.searchValue = "";

    function getProfitReport() {
        var start = moment().subtract(29, 'days');
        var end = moment();

        function cb(start, end) {
            $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            window.startDate = start.format("YYYY-MM-DD");
            window.endDate = end.format("YYYY-MM-DD");
            if (window.dataTable) {
                window.dataTable.DataTable().ajax.reload();
            }
        }

        $('#reportrange').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'All Time': [moment('2020-01-01'), moment('2030-12-31')]
            }
        }, cb);

        $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        window.startDate = start.format("YYYY-MM-DD");
        window.endDate = end.format("YYYY-MM-DD");

        window.dataTable = $("#dataTable").dataTable({
            bDestroy: true,
            destroy: true,
            bFilter: false,
            bLengthChange: false,
            serverSide: true,
            processing: true,
            paging: true,
            ordering: false,
            autoWidth: false,
            pageLength: 50,
            columnDefs: [
                { defaultContent: "-", targets: "_all" },
                { className: "text-center", targets: [0, 9] }
            ],
            ajax: {
                url: "{{ route('api.tables.profit') }}",
                type: "GET",
                data: function (d) {
                    d.startDate = window.startDate;
                    d.endDate = window.endDate;
                    d.service = window.service;
                    d.provider = window.provider;
                    d.user_id = window.userId;
                    d.searchValue = window.searchValue;
                },
                dataSrc: function(json) {
                    if (json.summary) {
                        $('#summaryVolume').text(json.summary.total_volume || '₹ 0.00');
                        $('#summaryPartner').text(json.summary.partner_charge || '₹ 0.00');
                        $('#summaryProvider').text(json.summary.provider_charge || '₹ 0.00');
                        $('#summaryProfit').text(json.summary.profit || '₹ 0.00');
                        $('#summaryMargin').text(json.summary.margin || '0.00%');

                        if (json.summary.profit_raw < 0) {
                            $('#summaryProfit').removeClass('text-success').addClass('text-danger');
                            $('#summaryMargin').removeClass('bg-success-transparent text-success').addClass('bg-danger-transparent text-danger');
                        } else {
                            $('#summaryProfit').removeClass('text-danger').addClass('text-success');
                            $('#summaryMargin').removeClass('bg-danger-transparent text-danger').addClass('bg-success-transparent text-success');
                        }
                    }
                    return json.data;
                }
            }
        });
    }

    function changeLength(value) {
        if (window.dataTable) {
            window.dataTable.DataTable().page.len(value).draw();
        }
    }

    function changeService(value) {
        window.service = value;
        if (window.dataTable) {
            window.dataTable.DataTable().ajax.reload();
        }
    }

    function changeProvider(value) {
        window.provider = value;
        if (window.dataTable) {
            window.dataTable.DataTable().ajax.reload();
        }
    }

    function changeUser(value) {
        window.userId = value;
        if (window.dataTable) {
            window.dataTable.DataTable().ajax.reload();
        }
    }

    function changeSearchValue(value) {
        window.searchValue = value.trim();
        if (window.dataTable) {
            window.dataTable.DataTable().ajax.reload();
        }
    }

    function exportProfitCsv() {
        let params = new URLSearchParams({
            startDate: window.startDate,
            endDate: window.endDate,
            service: window.service,
            provider: window.provider,
            user_id: window.userId,
            searchValue: window.searchValue
        });
        window.open('/export-profit?' + params.toString());
    }

    window.onloads = window.onloads || [];
    window.onloads.push(getProfitReport);
</script>

<script class="afterLoadScript" type="text/plain">
    getProfitReport();
</script>

@if (!request()->has('turbo'))
    @include('footer')
@endif

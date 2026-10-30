<?php if (!isset($_GET['turbo'])) {
    $page = "profit";
    echo view('header', get_defined_vars());
} ?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 my-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-style2 mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
            <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-chart-arrows-vertical me-1 fs-15"></i>Admin Profit</li>
        </ol>
    </nav>
    <div>
        <button onclick="exportProfitCsv()" class="btn btn-success-light d-flex align-items-center gap-1">
            <i class="ti ti-download fs-15"></i> Export Report (CSV)
        </button>
    </div>
</div>

<!-- Summary Metrics Cards -->
<div class="row mb-3 g-3">
    <div class="col-xxl-3 col-xl-3 col-md-6 col-12">
        <div class="card custom-card border-0 shadow-sm h-100 mb-0" style="background: linear-gradient(135deg, rgba(32, 143, 132, 0.08) 0%, rgba(32, 143, 132, 0.02) 100%);">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-12 fw-semibold text-uppercase">Total Volume</span>
                        <h4 class="fw-bold mt-1 mb-0 text-dark" id="summaryVolume">₹ 0.00</h4>
                        <small class="text-muted fs-11">Successful Transactions</small>
                    </div>
                    <span class="avatar avatar-md rounded-circle bg-primary-transparent text-primary">
                        <i class="ti ti-wallet fs-20"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xxl-3 col-xl-3 col-md-6 col-12">
        <div class="card custom-card border-0 shadow-sm h-100 mb-0" style="background: linear-gradient(135deg, rgba(13, 110, 253, 0.08) 0%, rgba(13, 110, 253, 0.02) 100%);">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-12 fw-semibold text-uppercase">Partner Charges</span>
                        <h4 class="fw-bold mt-1 mb-0 text-primary" id="summaryPartner">₹ 0.00</h4>
                        <small class="text-muted fs-11">Gross Revenue Collected</small>
                    </div>
                    <span class="avatar avatar-md rounded-circle bg-info-transparent text-info">
                        <i class="ti ti-arrow-down-left fs-20"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xxl-3 col-xl-3 col-md-6 col-12">
        <div class="card custom-card border-0 shadow-sm h-100 mb-0" style="background: linear-gradient(135deg, rgba(255, 149, 0, 0.08) 0%, rgba(255, 149, 0, 0.02) 100%);">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-12 fw-semibold text-uppercase">Provider Charges</span>
                        <h4 class="fw-bold mt-1 mb-0 text-warning" id="summaryProvider">₹ 0.00</h4>
                        <small class="text-muted fs-11">Cost Paid to Providers</small>
                    </div>
                    <span class="avatar avatar-md rounded-circle bg-warning-transparent text-warning">
                        <i class="ti ti-arrow-up-right fs-20"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xxl-3 col-xl-3 col-md-6 col-12">
        <div class="card custom-card border-0 shadow-sm h-100 mb-0" style="background: linear-gradient(135deg, rgba(25, 135, 84, 0.08) 0%, rgba(25, 135, 84, 0.02) 100%);">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted fs-12 fw-semibold text-uppercase">Admin Net Profit</span>
                            <span class="badge bg-success-transparent fs-10" id="summaryMargin">0.00%</span>
                        </div>
                        <h4 class="fw-bold mt-1 mb-0 text-success" id="summaryProfit">₹ 0.00</h4>
                        <small class="text-muted fs-11">Partner Fee − Provider Fee</small>
                    </div>
                    <span class="avatar avatar-md rounded-circle bg-success-transparent text-success">
                        <i class="ti ti-chart-arrows-vertical fs-20"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Bar & Table Card -->
<div class="card custom-card">
    <div class="card-body">
        <!-- Responsive Grid Filters -->
        <div class="row g-3 mb-4">
            <div class="col-xxl-2 col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <label class="form-label fs-12 fw-semibold text-muted mb-1">Rows per page</label>
                <select onchange="changeLength(this.value)" class="form-select form-select-sm" id="pageSize">
                    <option value="10">10 entries</option>
                    <option value="25">25 entries</option>
                    <option value="50" selected>50 entries</option>
                    <option value="100">100 entries</option>
                </select>
            </div>

            <div class="col-xxl-4 col-xl-4 col-lg-5 col-md-8 col-sm-12">
                <label class="form-label fs-12 fw-semibold text-muted mb-1">Date Range</label>
                <div id="reportrange" class="form-control form-control-sm d-flex justify-content-between align-items-center" style="cursor: pointer; min-height: 31px;">
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
                    <?php foreach($providers as $p) : ?>
                        <option value="<?= $p->id; ?>"><?= htmlspecialchars($p->name); ?> (<?= strtoupper($p->type); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-xxl-2 col-xl-2 col-lg-4 col-md-4 col-sm-6">
                <label class="form-label fs-12 fw-semibold text-muted mb-1">Partner / Merchant</label>
                <select onchange="changeUser(this.value);" class="form-select form-select-sm" id="userFilter">
                    <option value="">All Partners</option>
                    <?php foreach($users as $u) : ?>
                        <option value="<?= $u->id; ?>"><?= htmlspecialchars($u->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 mt-2">
                <div class="row align-items-center g-2">
                    <div class="col-md-6 col-12">
                        <span class="text-muted fs-12">
                            <i class="ti ti-info-circle me-1"></i> Showing transactions with partner charge vs provider cost.
                        </span>
                    </div>
                    <div class="col-md-6 col-12 d-flex justify-content-md-end">
                        <div class="input-group input-group-sm" style="max-width: 380px; width: 100%;">
                            <span class="input-group-text bg-light border-end-0"><i class="ti ti-search text-muted"></i></span>
                            <input type="text" onkeyup="changeSearchValue(this.value)" class="form-control border-start-0" id="searchValue" placeholder="Search Txn ID, Merchant, Name...">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive w-100" style="overflow-x: auto !important;">
            <table class="table text-nowrap table-bordered table-hover align-middle w-100 mb-0" id="dataTable">
                <thead class="table-primary">
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
            $('#reportrange span').html(start.format('MMM D, YYYY') + ' - ' + end.format('MMM D, YYYY'));
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
                'All Time': [moment('2020-01-01'), moment()]
            }
        }, cb);

        $('#reportrange span').html(start.format('MMM D, YYYY') + ' - ' + end.format('MMM D, YYYY'));
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
                url: "/api/tables/profit",
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
        window.dataTable.DataTable().page.len(value).draw();
    }

    function changeService(value) {
        window.service = value;
        window.dataTable.DataTable().ajax.reload();
    }

    function changeProvider(value) {
        window.provider = value;
        window.dataTable.DataTable().ajax.reload();
    }

    function changeUser(value) {
        window.userId = value;
        window.dataTable.DataTable().ajax.reload();
    }

    function changeSearchValue(value) {
        window.searchValue = value.trim();
        window.dataTable.DataTable().ajax.reload();
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

<?php if (!isset($_GET['turbo'])) {
    echo view('footer', get_defined_vars());
} ?>

@if (!request()->has('turbo'))
    @include('header', ['page' => 'accounting_ledger'])
@endif

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 my-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-style2 mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);">Accounting</a></li>
            <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-calculator me-1 fs-15"></i>Provider Commission Ledger</li>
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

            <div class="col-xxl-4 col-xl-4 col-lg-5 col-md-8 col-sm-12">
                <label class="form-label fs-12 fw-semibold text-muted mb-1">Date Range</label>
                <div id="reportrange" class="form-control form-control-sm d-flex justify-content-between align-items-center bg-white" style="cursor: pointer; min-height: 31px;">
                    <div><i class="ti ti-calendar me-1 text-primary"></i><span class="fs-12"></span></div>
                    <i class="fa fa-caret-down text-muted fs-11"></i>
                </div>
            </div>

            <div class="col-xxl-2 col-xl-2 col-lg-4 col-md-4 col-sm-6">
                <label class="form-label fs-12 fw-semibold text-muted mb-1">Panel</label>
                <select onchange="changePanel(this.value)" class="form-select form-select-sm" id="panelFilter">
                    <option value="">All Panels</option>
                    @if (isset($panels))
                        @foreach ($panels as $p)
                            <option value="{{ $p }}">{{ $p }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div class="col-xxl-4 col-xl-4 col-lg-12 col-md-8 col-sm-12">
                <label class="form-label fs-12 fw-semibold text-muted mb-1">Search Notes</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="ti ti-search text-muted"></i></span>
                    <input type="text" onkeyup="changeSearchValue(this.value)" class="form-control border-start-0" id="searchValue" placeholder="Search in Notes">
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
                        <i class="ti ti-menu-2 fs-20"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h4 class="fw-bold mb-0 text-dark fs-17 text-truncate" id="openingAmount">₹ 0.00</h4>
                        <span class="text-muted fs-12 fw-medium">Opening Amount</span>
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
                        <i class="ti ti-currency-rupee fs-20"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h4 class="fw-bold mb-0 text-dark fs-17 text-truncate" id="closingAmount">₹ 0.00</h4>
                        <span class="text-muted fs-12 fw-medium">Closing Amount</span>
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
                        <i class="ti ti-currency-rupee fs-20"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h4 class="fw-bold mb-0 text-dark fs-17 text-truncate" id="totalCredit">₹ 0.00</h4>
                        <span class="text-muted fs-12 fw-medium">Total Credit</span>
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
                        <i class="ti ti-currency-rupee fs-20"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h4 class="fw-bold mb-0 text-dark fs-17 text-truncate" id="totalDebit">₹ 0.00</h4>
                        <span class="text-muted fs-12 fw-medium">Total Debit</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card custom-card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex gap-2 mb-3">
            <button onclick="exportLedgerExcel()" class="btn btn-success btn-sm px-3 fw-semibold d-inline-flex align-items-center gap-1 shadow-sm" style="background-color: #22c55e; border-color: #22c55e;">
                <i class="ti ti-file-spreadsheet fs-15"></i> Export to Excel
            </button>
            <button onclick="exportLedgerPdf()" class="btn btn-danger btn-sm px-3 fw-semibold d-inline-flex align-items-center gap-1 shadow-sm" style="background-color: #ef4444; border-color: #ef4444;">
                <i class="ti ti-file-text fs-15"></i> Export to PDF
            </button>
        </div>

        <div class="table-responsive w-100" style="overflow-x: auto !important;">
            <table class="table text-nowrap table-hover align-middle w-100 mb-0 border" id="dataTable">
                <thead style="background-color: #e2e8f0; color: #334155;">
                    <tr>
                        <th scope="col" style="width: 50px;">ID</th>
                        <th scope="col" style="min-width: 100px;">Panel</th>
                        <th scope="col" style="min-width: 110px;">Txn Type</th>
                        <th scope="col" style="min-width: 90px;">Type</th>
                        <th scope="col" style="min-width: 120px;">Amount</th>
                        <th scope="col" style="min-width: 80px;">Mode</th>
                        <th scope="col" style="min-width: 320px;">Notes</th>
                        <th scope="col" style="min-width: 110px;">Date</th>
                        <th scope="col" style="min-width: 130px;">Opening</th>
                        <th scope="col" style="min-width: 130px;">Closing</th>
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
    window.panel = "";
    window.searchValue = "";

    function getAccountingLedgerReport() {
        var start = moment('2026-10-13');
        if (!start.isValid() || Math.abs(moment().diff(start, 'days')) > 365) {
            start = moment().subtract(29, 'days');
        }
        var end = moment('2026-10-26');
        if (!end.isValid() || Math.abs(moment().diff(end, 'days')) > 365) {
            end = moment();
        }

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
                'Ledger Demo (Oct 13 - Oct 26)': [moment('2026-10-13'), moment('2026-10-26')],
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
                { defaultContent: "-", targets: "_all" }
            ],
            ajax: {
                url: "{{ route('api.tables.accounting_ledger') }}",
                type: "GET",
                data: function (d) {
                    d.startDate = window.startDate;
                    d.endDate = window.endDate;
                    d.panel = window.panel;
                    d.searchValue = window.searchValue;
                },
                dataSrc: function (json) {
                    if (json.stats) {
                        $('#openingAmount').text(json.stats.opening_amount || '₹ 0.00');
                        $('#closingAmount').text(json.stats.closing_amount || '₹ 0.00');
                        $('#totalCredit').text(json.stats.total_credit || '₹ 0.00');
                        $('#totalDebit').text(json.stats.total_debit || '₹ 0.00');
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

    function changePanel(value) {
        window.panel = value;
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

    function exportLedgerExcel() {
        let params = new URLSearchParams({
            startDate: window.startDate,
            endDate: window.endDate,
            panel: window.panel,
            searchValue: window.searchValue
        });
        window.open("{{ route('api.admin.accounting.export.excel') }}?" + params.toString());
    }

    function exportLedgerPdf() {
        let params = new URLSearchParams({
            startDate: window.startDate,
            endDate: window.endDate,
            panel: window.panel,
            searchValue: window.searchValue
        });
        window.open("{{ route('api.admin.accounting.export.pdf') }}?" + params.toString());
    }

    window.onloads = window.onloads || [];
    window.onloads.push(getAccountingLedgerReport);
</script>

<script class="afterLoadScript" type="text/plain">
    getAccountingLedgerReport();
</script>

@if (!request()->has('turbo'))
    @include('footer')
@endif

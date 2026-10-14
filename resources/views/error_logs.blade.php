<?php if (!isset($_GET['turbo'])) {
    $page = "error_logs";
    echo view('header', get_defined_vars());
} ?>

<div class="d-flex justify-content-between align-items-center">
    <nav aria-label="breadcrumb" class="my-4">
        <ol class="breadcrumb breadcrumb-style2 mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
            <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-bug me-1 fs-15"></i>Error Logs</li>
        </ol>
    </nav>
</div>

<div class="card custom-card">
    <div class="card-body">
        <div class="row mb-3 g-2">
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="form-group mb-0">
                    <label class="form-label mb-1">Page Length</label>
                    <select onchange="changeLength(this.value)" class="form-select" id="pageSize">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="form-group mb-0">
                    <label class="form-label mb-1">Date Range</label>
                    <div id="reportrange" class="form-control w-100" style="white-space: nowrap; cursor: pointer;">
                        <i class="ti ti-calendar"></i>&nbsp;
                        <span></span> <i class="fa fa-caret-down float-end mt-1"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="form-group mb-0">
                    <label class="form-label mb-1">Type</label>
                    <select onchange="changeType(this.value);" class="form-select" id="type">
                        <option value="">All Types</option>
                        <option value="PAYIN">PAYIN</option>
                        <option value="PAYOUT">PAYOUT</option>
                    </select>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="form-group mb-0">
                    <label class="form-label mb-1">Provider</label>
                    <select onchange="changeProvider(this.value);" class="form-select" id="provider">
                        <option value="">All Providers</option>
                        <?php foreach($providers as $prov) : ?>
                            <option value="<?= $prov->id ?>"><?= htmlspecialchars($prov->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="col-lg-3 col-md-8 col-sm-12">
                <div class="form-group mb-0">
                    <label class="form-label mb-1">Search TxnID</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input type="text" onkeyup="changeSearchValue(this.value)" class="form-control" id="searchValue" placeholder="Search TxnID...">
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table text-nowrap table-bordered table-hover" id="dataTable">
                <thead class="table-primary">
                    <tr>
                        <th scope="col" style="width: 60px;">#</th>
                        <th scope="col">Type</th>
                        <th scope="col">Txn ID</th>
                        <th scope="col">Provider</th>
                        <th scope="col">Payload & Error</th>
                        <th scope="col">Logged Time</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal to view Log details -->
<div class="modal fade" id="logDetailModal" tabindex="-1" aria-labelledby="logDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logDetailModalLabel"><i class="ti ti-bug text-danger me-1"></i> Error Log Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <strong>Txn ID:</strong> <span id="modalTxnid" class="badge bg-light text-dark fs-13"></span>
                    </div>
                    <div class="col-md-3">
                        <strong>Type:</strong> <span id="modalType"></span>
                    </div>
                    <div class="col-md-3">
                        <strong>Provider:</strong> <span id="modalProvider" class="fw-semibold"></span>
                    </div>
                    <div class="col-12">
                        <strong>Logged At:</strong> <span id="modalTime" class="text-muted"></span>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="fw-bold mb-0 text-danger"><i class="ti ti-alert-triangle"></i> Error Response (Log):</label>
                        <button class="btn btn-sm btn-light" onclick="copyModalContent('modalLogText')"><i class="ti ti-copy"></i> Copy</button>
                    </div>
                    <pre id="modalLogText" class="p-3 bg-light rounded text-dark border" style="max-height: 250px; overflow-y: auto; font-size: 12px; white-space: pre-wrap; word-break: break-all;"></pre>
                </div>

                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="fw-bold mb-0 text-primary"><i class="ti ti-send"></i> Outgoing Request Payload:</label>
                        <button class="btn btn-sm btn-light" onclick="copyModalContent('modalRequestText')"><i class="ti ti-copy"></i> Copy</button>
                    </div>
                    <pre id="modalRequestText" class="p-3 bg-light rounded text-dark border" style="max-height: 250px; overflow-y: auto; font-size: 12px; white-space: pre-wrap; word-break: break-all;"></pre>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    window.dataTable = null;
    window.searchBy = "txnid";
    window.searchValue = "";
    window.startDate = "";
    window.endDate = "";
    window.type = "";
    window.provider = "";

    function getLogs() {
        var start = moment().subtract(6, 'days');
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
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
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
            pageLength: 10,
            columnDefs: [
                { defaultContent: "-", targets: "_all" }
            ],
            ajax: {
                url: "/api/tables/error_logs",
                type: "GET",
                data: function (d) {
                    d.searchBy = window.searchBy;
                    d.searchValue = window.searchValue;
                    d.startDate = window.startDate;
                    d.endDate = window.endDate;
                    d.type = window.type;
                    d.provider = window.provider;
                }
            }
        });
    }

    function changeLength(value) {
        window.dataTable.DataTable().page.len(value).draw();
    }

    function changeType(value) {
        window.type = value;
        window.dataTable.DataTable().ajax.reload();
    }

    function changeProvider(value) {
        window.provider = value;
        window.dataTable.DataTable().ajax.reload();
    }

    function changeSearchValue(value) {
        window.searchValue = value.trim();
        window.dataTable.DataTable().ajax.reload();
    }

    function viewLogModal(id) {
        var el = document.getElementById('log_data_' + id);
        if (!el) return;

        $('#modalTxnid').text(el.getAttribute('data-txnid') || 'N/A');
        $('#modalType').html('<span class="badge bg-danger-transparent">' + (el.getAttribute('data-type') || '') + '</span>');
        $('#modalProvider').text(el.getAttribute('data-provider') || 'N/A');
        $('#modalTime').text(el.getAttribute('data-time') || '');

        var rawLog = el.getAttribute('data-log') || '';
        var rawReq = el.getAttribute('data-request') || '';

        try {
            var parsedLog = JSON.parse(rawLog);
            $('#modalLogText').text(JSON.stringify(parsedLog, null, 2));
        } catch(e) {
            $('#modalLogText').text(rawLog || 'No log recorded');
        }

        try {
            var parsedReq = JSON.parse(rawReq);
            $('#modalRequestText').text(JSON.stringify(parsedReq, null, 2));
        } catch(e) {
            $('#modalRequestText').text(rawReq || 'No request payload recorded');
        }

        var modal = new bootstrap.Modal(document.getElementById('logDetailModal'));
        modal.show();
    }

    function copyModalContent(elementId) {
        var text = document.getElementById(elementId).innerText;
        navigator.clipboard.writeText(text).then(function() {
            if (typeof toastr !== 'undefined') {
                toastr.success('Copied to clipboard');
            } else {
                alert('Copied to clipboard');
            }
        });
    }

    window.onloads.push(getLogs);
</script>

<script class="afterLoadScript" type="text/plain">
    getLogs();
</script>

<?php if (!isset($_GET['turbo'])) {
    echo view('footer', get_defined_vars());
} ?>

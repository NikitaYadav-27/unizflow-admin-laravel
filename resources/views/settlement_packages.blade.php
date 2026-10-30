@if (!request()->has('turbo'))
    @include('header', ['page' => 'settlement_packages'])
@endif

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigate('{{ route('admin.dashboard') }}')"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-receipt me-1 fs-15"></i>Settlements</a></li>
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-box me-1 fs-15"></i>Settlement Packages</li>
    </ol>
</nav>

<div class="card custom-card">
    <div class="card-header justify-content-between align-items-center">
        <div class="card-title">
            <i class="ti ti-box me-2 text-primary fs-18"></i>Settlement Packages
        </div>
        <div>
            <button onclick="navigate('{{ route('admin.settlement-packages.add') }}')" class="btn btn-primary btn-sm">
                <i class="ti ti-circle-plus me-1"></i>Add Settlement Package
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
            <div class="me-2">
                <select onchange="changeLength(this.value)" class="form-select form-select-sm" id="pageSize">
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                    <option value="100">100 per page</option>
                </select>
            </div>

            <div class="d-flex gap-2">
                <div>
                    <input type="text" onkeyup="changeSearchValue(this.value)" class="form-control form-control-sm" id="searchValue"
                        placeholder="Search package name...">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table text-nowrap table-bordered table-hover align-middle" id="settlementPackagesTable">
                <thead class="table-primary">
                    <tr>
                        <th scope="col" style="width: 60px;">#</th>
                        <th scope="col">Package Name</th>
                        <th scope="col">Frequency</th>
                        <th scope="col">Configured Slots (Run Time & Txn Window)</th>
                        <th scope="col">Created At</th>
                        <th scope="col" style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    window.dataTable = null;
    window.searchTimeout = null;

    function initSettlementPackagesTable() {
        if (window.dataTable) {
            window.dataTable.destroy();
        }

        window.dataTable = $("#settlementPackagesTable").DataTable({
            "bFilter": false,
            "bLengthChange": false,
            "serverSide": true,
            "processing": true,
            "paging": true,
            "ordering": false,
            "ajax": {
                "url": "{{ route('api.tables.settlement-packages') }}",
                "type": "GET",
                "data": function (d) {
                    d.searchValue = $("#searchValue").val() || "";
                }
            },
            "language": {
                "emptyTable": "No settlement packages found",
                "processing": '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading packages...'
            }
        });
    }

    function changeLength(val) {
        if (window.dataTable) {
            window.dataTable.page.len(parseInt(val)).draw();
        }
    }

    function changeSearchValue(val) {
        clearTimeout(window.searchTimeout);
        window.searchTimeout = setTimeout(() => {
            if (window.dataTable) {
                window.dataTable.draw();
            }
        }, 300);
    }

    function deleteSettlementPackage(encId, name) {
        Swal.fire({
            title: "Delete Package?",
            text: `Are you sure you want to delete "${name}"? This action cannot be undone.`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route('admin.settlement-packages.delete') }}',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ package_id: encId }),
                    success: function (res) {
                        notify("success", res.message || "Package deleted successfully");
                        if (window.dataTable) {
                            window.dataTable.draw();
                        }
                    },
                    error: function (xhr) {
                        let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to delete package";
                        notify("error", msg);
                    }
                });
            }
        });
    }

    if (typeof jQuery !== 'undefined') {
        initSettlementPackagesTable();
    } else {
        window.onloads = window.onloads || [];
        window.onloads.push(initSettlementPackagesTable);
    }
</script>

<script class="afterLoadScript" type="text/plain">
    initSettlementPackagesTable();
</script>

@if (!request()->has('turbo'))
    @include('footer')
@endif

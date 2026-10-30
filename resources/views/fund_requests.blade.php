@if (!request()->has('turbo'))
    @include('header', ['page' => $estatus . '_fund_requests'])
@endif

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item">
            <a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a>
        </li>
        <li class="breadcrumb-item">
            <a href="javascript:void(0);"><i class="ti ti-cash me-1 fs-15"></i>Fund Requests</a>
        </li>
        @if ($status == "Pending")
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-history me-1 fs-15"></i>Pending</li>
        @elseif ($status == "Success")
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-circle-check me-1 fs-15"></i>Completed</li>
        @elseif ($status == "Failed")
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-alert-circle me-1 fs-15"></i>Rejected</li>
        @endif
    </ol>
</nav>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-2 flex-wrap gap-2">
            <div class="me-2 sm-w-100">
                <select onchange="changeLength(this.value)" class="form-select" id="pageSize" onchange="window.pageSize = this.value; getTransactions(window.pageNo,window.pageSize,window.searchBy,window.searchValue)">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <div class="me-2 sm-w-100">
                    <select class="form-select" id="searchBy" onchange="changeSearchBy(this.value)">
                        <option value="">Select</option>
                        <option value="txnid">Transaction ID</option>
                        <option value="rrn">RRN</option>
                        <option value="user">User Name</option>
                    </select>
                </div>
                <div class="me-2 sm-w-100">
                    <input type="text" onkeyup="changeSearchValue(this.value)" class="form-control" id="searchValue" placeholder="Search" />
                </div>

                <div id="reportrange" class="form-control sm-w-100" style="width: min-content; white-space: nowrap;"><i class="ti ti-calendar"></i>&nbsp; <span></span> <i class="fa fa-caret-down"></i></div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table text-nowrap table-bordered" id="dataTable">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">User Name</th>
                        <th scope="col">Bank</th>
                        <th scope="col">Mode</th>
                        <th scope="col">Txn Id</th>
                        <th scope="col">RRN</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Status</th>
                        <th scope="col">Receipt</th>
                        <th scope="col">Remarks</th>
                        <th scope="col">Create Time</th>
                        <th scope="col">Complete Time</th>
                        @if ($status == "Pending")
                        <th scope="col">Actions</th>  
                        @elseif ($status == "Failed")
                        <th scope="col">Reason</th>
                        @endif
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function acceptRequest(id) {
        Swal.fire({
            title: "Are you sure to Accept this Fund Request ?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, Accept it",
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (result.isConfirmed) {
                modifyRequest(id, "Success");
            }
        });
    }

function rejectRequest(id) {
    Swal.fire({
        title: "Are you sure to reject this Fund Request?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, Reject it",
        cancelButtonText: "Cancel",
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: "Reason for Rejection",
                input: "text",
                inputPlaceholder: "Enter reason...",
                inputAttributes: {
                    required: "true"
                },
                showCancelButton: true,
                confirmButtonText: "Submit",
                cancelButtonText: "Cancel",
                preConfirm: (reason) => {
                    if (!reason) {
                        Swal.showValidationMessage("Reason is required");
                    }
                    return reason;
                }
            }).then((inputResult) => {
                if (inputResult.isConfirmed && inputResult.value) {
                    modifyRequest(id, "Failed", inputResult.value);
                }
            });
        }
    });
}

    function modifyRequest(id, status, reason = "") {
        Swal.fire({
            title: "Enter Transaction PIN",
            html: `
    <form onsubmit="return false">
      <input id="pin-input" type="password" 
             maxlength="6" 
             inputmode="numeric" 
             pattern="[0-9]*" 
             placeholder="6-digit PIN"
             autocomplete="off"
             class="form-control my-3">
    </form>
  `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: "Submit",
            preConfirm: () => {
                const pin = document.getElementById("pin-input").value;
                if (!pin) return "You need to enter the PIN!";
                if (!/^\d{6}$/.test(pin)) return "PIN must be exactly 6 digits!";
                return pin;
            },
        }).then((result) => {
            if (result.isConfirmed) {
                let pin = result.value;
                if(pin.length != 6) {
                    return notify("error","Please enter Valid 6 digit pin",() => {
                        modifyRequest(id, status);
                    });
                }
                
             $.post('/api/admin/fund-requests/update',{
                 id : id ,
                 status : status,
                 pin : pin,
                 reason : reason
             },(data) =>{
                notify("success",data.message,() => {
                    navigate('{{ route('admin.fund-requests.index', ['status' => 'pending']) }}');
                });
             }).fail((xhr) => {
                notify("error",xhr.responseJSON.message);
             });
                
            }
        });
    }

    window.dataTable = null;
    window.searchBy = "";
    window.searchValue = "";
    window.startDate = "";
    window.endDate = "";

    function getTransactions() {
        var start = moment().subtract(6, "days");
        var end = moment();

        function cb(start, end) {
            $("#reportrange span").html(start.format("MMMM D, YYYY") + " - " + end.format("MMMM D, YYYY"));

            window.startDate = start.format("YYYY-MM-DD");
            window.endDate = end.format("YYYY-MM-DD");
            window.dataTable.DataTable().ajax.reload();
        }

        $("#reportrange").daterangepicker(
            {
                startDate: start,
                endDate: end,
                ranges: {
                    Today: [moment(), moment()],
                    Yesterday: [moment().subtract(1, "days"), moment().subtract(1, "days")],
                    "Last 7 Days": [moment().subtract(6, "days"), moment()],
                    "Last 30 Days": [moment().subtract(29, "days"), moment()],
                    "This Month": [moment().startOf("month"), moment().endOf("month")],
                    "Last Month": [moment().subtract(1, "month").startOf("month"), moment().subtract(1, "month").endOf("month")],
                },
            },
            cb
        );

        $("#reportrange span").html(start.format("MMMM D, YYYY") + " - " + end.format("MMMM D, YYYY"));
        window.startDate = start.format("YYYY-MM-DD");
        window.endDate = end.format("YYYY-MM-DD");

        window.dataTable = $("#dataTable").dataTable({
            bFilter: false,
            bLengthChange: false,
            serverSide: true,
            processing: true,
            paging: true,
            ordering: false,
            ajax: {
                url: "{{ route('api.tables.fund-requests') }}",
                type: "GET",
                data: function (d) {
                    d.searchBy = window.searchBy;
                    d.searchValue = window.searchValue;
                    d.startDate = window.startDate;
                    d.endDate = window.endDate;
                    d.status = "{{ $status }}";
                },
            },
        });

        document.querySelector(".card-body").append(document.querySelectorAll("#dataTable_wrapper .row")[2]);
    }

    function changeLength(value) {
        window.dataTable.DataTable().page.len(value).draw();
    }

    function changeSearchBy(value) {
        window.searchBy = value;

        if (window.searchValue != "") {
            window.dataTable.DataTable().ajax.reload();
        }
    }

    function changeSearchValue(value) {
        window.searchValue = value;

        if (window.searchBy != "") {
            window.dataTable.DataTable().ajax.reload();
        }
    }
</script>
<script class="afterLoadScript" type="text/plain">
    getTransactions();
</script>

<style>
    #dataTable_paginate {
        display: flex;
        justify-content: end;
    }

    .table-responsive + .row {
        margin-top: 20px;
        position: sticky;
    }

    .table-responsive {
        max-height: 60vh;
        overflow-y: scroll;
    }
</style>

@if (!request()->has('turbo'))
    @include('footer')
@endif

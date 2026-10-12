<?php if (!isset($_GET['turbo'])) {
    $page = "pending_credits";
    echo view('header', get_defined_vars());
} ?>

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-credit-card me-1 fs-15"></i>User Credits</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-history me-1 fs-15"></i>Pending Credits</li>
    </ol>
</nav>



<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-2 flex-wrap gap-2">

            <div class="me-2">
                <select onchange="changeLength(this.value)" class="form-select" id="pageSize"
                    onchange="window.pageSize = this.value; getTransactions(window.pageNo,window.pageSize,window.searchBy,window.searchValue)">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

            <div class="d-flex">

                <div class="me-2">
                    <select class="form-select" id="searchBy" onchange="changeSearchBy(this.value)">
                        <option value="">Select</option>
                        <option value="user">User</option>
                        <option value="txnid">TxnId</option>
                        <option value="amount">Amount</option>
                        <option value="credit_remarks">Credit Remarks</option>
                        <option value="created_at">Date</option>
                       
                    </select>
                </div>
                <div class="me-2">
                    <input type="text" onkeyup="changeSearchValue(this.value)" class="form-control" id="searchValue"
                        placeholder="Search">
                </div>


            </div>
        </div>

        <div class="table-responsive">
            <table class="table text-nowrap table-bordered" id="dataTable">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">User</th>
                        <th scope="col">TxnId</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Credit Remarks</th>
                        <th scope="col">Credited At</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>


    </div>
</div>




<script>

    window.dataTable = null;
    window.searchBy = "";
    window.searchValue = "";


    function getTransactions() {
        window.dataTable = $("#dataTable").dataTable(
            {
                "bFilter": false,
                "bLengthChange": false,
                "serverSide": true,
                "processing": true,
                "paging": true,
                "ordering" : false,
                "ajax": {
                    "url": "/api/tables/credits",
                    "type": "GET",
                    "data": function (d) {
                        d.searchBy = window.searchBy;
                        d.searchValue = window.searchValue;
                         d.type = "pending";
                    }
                }
            }
        );

       
    }

    function changeLength(value) {
        window.dataTable.DataTable().page.len(value).draw();
    }

    function changeSearchBy(value) {
        window.searchBy = value;
        //refresh data
        if (window.searchValue != "") {
            window.dataTable.DataTable().ajax.reload();
        }

    }

    function changeSearchValue(value) {
        window.searchValue = value;
        //refresh data
        if (window.searchBy != "") {
            window.dataTable.DataTable().ajax.reload();
        }

    }
    
    function debitCredits(id) {
       Swal.fire({
  title: 'Are you sure?',
  text: "Money will be debited from user's wallet",
  icon: 'warning',
  input: 'text',
  inputLabel: 'Enter remarks',
  inputPlaceholder: 'Enter reason or remarks',
  inputValidator: (value) => {
    if (!value) {
      return 'Remarks are required!';
    }
  },
  showCancelButton: true,
  confirmButtonColor: '#3085d6',
  cancelButtonColor: '#d33',
  confirmButtonText: 'Yes, Debit Amount!'
}).then((result) => {
  if (result.isConfirmed) {
    const remarks = result.value;

    $.post("/api/admin/advance-credit/debit", {
      id: id,
      remarks: remarks
    }, (data) => {
      notify("success", data.message, () => {
        navigate(location.pathname);
      });
    }).fail((xhr) => {
      notify("error", xhr.responseJSON.message);
    });
  }
});

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

    .table-responsive+.row {
        margin-top: 20px;
        position: sticky;
    }

    .table-responsive {
        max-height: 60vh;
        overflow-y: scroll;
    }
</style>


<?php if (!isset($_GET['turbo'])) {
    echo view('footer', get_defined_vars());
} ?>
<?php if (!isset($_GET['turbo'])) {
    $page = $status."_transactions";
    echo view('header', get_defined_vars());
} ?>

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-history me-1 fs-15"></i>Hold Transactions</a>
        </li>
        <?php if($status == "hold") : ?>
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-circle-minus me-1 fs-15"></i>On Hold Transactions</li>
        <?php else : ?>
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-circle-check me-1 fs-15"></i>Released Transactions</li>
        <?php endif; ?>
    </ol>
</nav>


<div class="card">
    <div class="card-body">
        
        <div class="row mb-2">
            <div class="col-lg-3">
                <div class="form-group mb-2">
                <label class="mb-1">Page Length</label>
                 <select onchange="changeLength(this.value)" class="form-select" id="pageSize"
                    onchange="window.pageSize = this.value; getTransactions(window.pageNo,window.pageSize,window.searchBy,window.searchValue)">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="500">500</option>
                    <!--<option value="-1">All</option>-->
                </select>
                </div>
            </div>

            
            <div class="col-lg-3">
                <div class="form-group mb-2">
                   <label class="mb-1">User</label>
                    <select onchange="changeUser(this.value);" class="form-select" id="user">
                    <option value="">All</option>
                    <?php foreach((new \App\Models\User())->get() as $user) : ?>
                       <option value="<?= $user['id']; ?>"><?= $user['name']; ?></option>
                    <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            
           
            
           
            
            <div class="col-lg-3">
                 <div class="form-group mb-2">
                     <label class="mb-1">Search By</label>
                      <select class="form-select" id="searchBy" data-placeholder="Select" onchange="changeSearchBy(this.value)">
                        <option></option>
                       
                       
                    </select>
                 </div>
            </div>
            <div class="col-lg-3">
                  <div class="form-group mb-2">
                     <label class="mb-1">Search Value</label>
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
                        <th scope="col">Type</th>
                        <th scope="col">TxnId</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Status</th>
                        <th scope="col">Remarks</th>
                        <th scope="col">Hold Date</th>
                        <?php if($status == "hold") : ?>
                        <th scope="col">Actions</th>
                        <?php else : ?>
                        <th scope="col">Released Date</th>
                        <?php endif; ?>
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
    window.userId = "";


    function getTransactions() {
        window.dataTable = $("#dataTable").dataTable(
            {
                "bDestroy": true,
                "destroy": true,
                "bFilter": false,
                "bLengthChange": false,
                "serverSide": true,
                "processing": true,
                "paging": true,
                "ordering" : false,
                "columnDefs": [
                    { "defaultContent": "-", "targets": "_all" }
                ],
                "ajax": {
                    "url": "/api/tables/hold_transactions",
                    "type": "GET",
                    "data": function (d) {
                        d.searchBy = window.searchBy;
                        d.searchValue = window.searchValue;
                        d.userId = window.userId;
                        d.status = '<?= $status ?>';
                    }
                }
            }
        );
        
          document.querySelector(".card-body").append(document.querySelectorAll("#dataTable_wrapper .row")[2])

       
    }

    function changeLength(value) {
        window.dataTable.DataTable().page.len(value).draw();
    }
    
    function changeUser(value){
       window.userId = value;
       window.dataTable.DataTable().ajax.reload();
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

    window.releaseHold = function(id) {
        Swal.fire({
            title: "Release Hold Amount?",
            text: "Are you sure you want to release this held amount back to user's wallet?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#28a745",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes, Release it!",
            cancelButtonText: "Cancel"
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Release Remarks",
                    input: "text",
                    inputPlaceholder: "Enter remarks (optional)",
                    inputValue: "Hold released by admin",
                    showCancelButton: true,
                    confirmButtonText: "Confirm Release",
                    cancelButtonText: "Cancel"
                }).then((inputResult) => {
                    if (inputResult.isConfirmed) {
                        let remarks = inputResult.value || "Hold released by admin";
                        
                        $.post('/api/admin/hold-transactions/release', {
                            id: id,
                            remarks: remarks
                        }, function(response) {
                            if (response.status === 'success') {
                                notify("success", response.message, function() {
                                    if (window.dataTable) {
                                        window.dataTable.DataTable().ajax.reload();
                                    } else {
                                        location.reload();
                                    }
                                });
                            } else {
                                notify("error", response.message || "Failed to release hold");
                            }
                        }).fail(function(xhr) {
                            let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Error releasing hold transaction";
                            notify("error", msg);
                        });
                    }
                });
            }
        });
    };

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
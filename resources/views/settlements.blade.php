<?php if (!isset($_GET['turbo'])) {
    $page = "settlement_". $status;
    echo view('header', get_defined_vars());
} ?>

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-receipt me-1 fs-15"></i>Settlements</a>
        </li>
        <?php if($status == "pending") :  ?>
           <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-history me-1 fs-15"></i>Pending</li>
        <?php elseif($status == "hold") :  ?>
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-forbid me-1 fs-15"></i>Hold</li>
        <?php else : ?>
           <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-circle-check me-1 fs-15"></i>Completed</li>
        <?php endif; ?>
    </ol>
</nav>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-2 flex-wrap gap-2">

            <div class="d-flex gap-2 me-2 sm-w-100 align-items-center">
                <select onchange="changeLength(this.value)" class="form-select" id="pageSize"
                    onchange="window.pageSize = this.value; getTransactions(window.pageNo,window.pageSize,window.searchBy,window.searchValue)">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option selected value="50">50</option>
                    <option value="100">100</option>
                </select>
                <?php if($status == "pending") : ?>
                    <button type="button" class="btn btn-sm btn-primary-transparent text-nowrap" onclick="triggerAutoSettlement(this)" title="Process settlements for users configured with Automatic Settlement">
                        <i class="ti ti-bolt me-1"></i>Run Auto Settlement
                    </button>
                <?php endif; ?>
            </div>

            <div class="d-flex flex-wrap gap-2">

                <div class="me-2 sm-w-100">
                    <select class="form-select" id="searchBy" onchange="changeSearchBy(this.value)">
                        <option value="">Select</option>
                        <option value="txnid">Transaction ID</option>
                        <option value="batch_id">Batch ID</option>
                        <option value="user">User Name</option>
                    </select>
                </div>
                <div class="me-2 sm-w-100">
                    <input type="text" onkeyup="changeSearchValue(this.value)" class="form-control" id="searchValue"
                        placeholder="Search">
                </div>


               <div id="reportrange" class="form-control sm-w-100" style="width: min-content; white-space: nowrap;">
                 <i class="ti ti-calendar"></i>&nbsp;
                 <span></span> <i class="fa fa-caret-down"></i>
               </div>

            </div>
        </div>

        <div class="table-responsive">
            <table class="table text-nowrap table-bordered" id="dataTable">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">User Name</th>
                        <th scope="col">Batch ID</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Charges</th>
                        <th scope="col">GST</th>
                        <th scope="col">Net Amount</th>
                        <th scope="col">Settle To</th>
                        <th scope="col">TXNID</th>
                        <th scope="col">Status</th>
                        <th scope="col">Create Time</th>
                        <th scope="col">Settled Time</th>
                        <th scope="col">Transactions</th>
                        <?php  if($status == "pending" || $status == "hold") : ?>
                        <th scope="col">Actions</th>
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

    function settleToWallet(id) {
        Swal.fire({
          title:  "Are you sure to settle this amount in Wallet ?",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Yes, Settle it",
          cancelButtonText: "Don't Settle",
        }).then((result) => {
          if (result.isConfirmed) {
             $.post('/api/admin/settlement/settle-to-wallet',{
                 id : id 
             },(data) =>{
                notify("success",data.message,() => {
                    navigate('/admin/settlements/<?= $status ?>');
                });
             }).fail((xhr) => {
                notify("error",xhr.responseJSON.message);
             });
          }
        });
    }
    
    function holdSettlement(id){
        Swal.fire({
          title:  "Are you sure to hold the settlement ?",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Yes, Hold it",
          cancelButtonText: "Don't Hold",
        }).then((result) => {
          if (result.isConfirmed) {
             $.post('/api/admin/settlement/hold-settlement',{
                 id : id 
             },(data) =>{
                notify("success",data.message,() => {
                    navigate('/admin/settlements/<?= $status ?>');
                });
             }).fail((xhr) => {
                notify("error",xhr.responseJSON.message);
             });
          }
        });
    }
    
    function settleToBank(id) {
      navigate('/admin/settlement/bank/'+id);  
    }

    window.dataTable = null;
    window.searchBy = "";
    window.searchValue = "";
    window.startDate = "";
    window.endDate = "";


    function getTransactions() {
        
        
    var start = moment().subtract(6, 'days'); // 6 days ago
    var end = moment(); // today

    function cb(start, end) {
        $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
       // renderTransactionsVolume(start, end);
       window.startDate = start.format("YYYY-MM-DD");
       window.endDate = end.format("YYYY-MM-DD");
       window.dataTable.DataTable().ajax.reload();
       
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

     $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
     window.startDate = start.format("YYYY-MM-DD");
     window.endDate = end.format("YYYY-MM-DD");
    // cb(start, end); 
    
    
    
        window.dataTable = $("#dataTable").dataTable(
            {
                "bFilter": false,
                "bLengthChange": false,
                "serverSide": true,
                "processing": true,
                "paging": true,
                "ordering" : false,
                "pageLength": 50,
                "ajax": {
                    "url": "/api/tables/settlements",
                    "type": "GET",
                    "data": function (d) {
                        d.searchBy = window.searchBy;
                        d.searchValue = window.searchValue;
                        d.startDate = window.startDate;
                        d.endDate = window.endDate;
                        d.status = '<?= $status == "completed" ?"Success":$status ?>'
                    }
                }
            }
        );

        document.querySelector(".card-body").append(document.querySelectorAll("#dataTable_wrapper .row")[2])
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

    function triggerAutoSettlement(btn) {
        let orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';
        $.ajax({
            url: '/api/admin/settlements/trigger-auto',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({}),
            success: function(res) {
                btn.disabled = false;
                btn.innerHTML = orig;
                notify("success", res.message || "Auto settlement executed");
                if (window.dataTable) {
                    window.dataTable.DataTable().ajax.reload();
                }
            },
            error: function(xhr) {
                btn.disabled = false;
                btn.innerHTML = orig;
                let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to run auto settlement";
                notify("error", msg);
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
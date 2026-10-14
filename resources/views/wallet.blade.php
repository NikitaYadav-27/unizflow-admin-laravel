<?php if (!isset($_GET['turbo'])) {
    $page = "wallet";
    echo view('header', get_defined_vars());
} ?>
<div class="d-flex justify-content-between align-items-center">
    

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-wallet me-1 fs-15"></i>Wallet ledger</a>
        </li>
       
    </ol>
</nav>

<button onclick="exportTransactions();" class="btn btn-success"><i class="ti ti-file-spreadsheet"></i>&nbsp;&nbsp;Export</button>

</div>


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
                <label class="mb-1">Date Range</label>
                 <div id="reportrange" class="form-control w-100" style="width: min-content; white-space: nowrap;">
                 <i class="ti ti-calendar"></i>&nbsp;
                 <span></span> <i class="fa fa-caret-down"></i>
               </div>
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
                   <label class="mb-1">Type</label>
                    <select onchange="changeType(this.value);" class="form-select" id="type">
                    <option value="">All</option>
                       <option value="SETTLEMENT">SETTLEMENT</option>
                       <option value="PAYOUT">PAYOUT</option>
                       <option value="REFUND">REFUND</option>
                       <option value="TOPUP">TOPUP</option>
                       <option value="HOLD">HOLD</option>
                       <option value="HOLD-RELEASE">HOLD-RELEASE</option>
                       <option value="ADVANCE-CREDIT">ADVANCE-CREDIT</option>
                       <option value="ADVANCE-DEBIT">ADVANCE-DEBIT</option>
                       <option value="REFUND-DEBITED">REFUND-DEBITED</option>
                    </select>
                </div>
            </div>
            
           
           
            
            <div class="col-lg-3">
                 <div class="form-group mb-2">
                     <label class="mb-1">Search By</label>
                      <select class="form-select" id="searchBy" data-placeholder="Select" onchange="changeSearchBy(this.value)">
                        <option></option>
                        <option value="txnid">TxnId</option>
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
            
            
   
        
                          <div class="row g-2">
                                    <div class="col-xl-3">
                                        <div class="d-flex flex-wrap align-items-top p-4 border rounded">
                                            <div class="me-3 lh-1">
                                                <span class="avatar avatar-md avatar-rounded bg-success shadow-sm">
                                                    <i class="ti ti-receipt fs-18"></i>
                                                </span>
                                            </div>
                                            <div class="flex-fill">
                                                <h5 class="fw-semibold mb-1" id="totalSettlement">0</h5>
                                                <p class="text-muted mb-0 fs-12">Total Settlement</p>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="col-xl-3">
                                        <div class="d-flex flex-wrap align-items-top p-4 border rounded">
                                            <div class="me-3 lh-1">
                                                <span class="avatar avatar-md avatar-rounded bg-orange shadow-sm">
                                                    <i class="ti ti-cash fs-18"></i>
                                                </span>
                                            </div>
                                            <div class="flex-fill">
                                                <h5 class="fw-semibold mb-1" id="totalPayout">0</h5>
                                                <p class="text-muted mb-0 fs-12">Total Payout</p>
                                            </div>
                                           
                                        </div>
                                    </div>
                                    <div class="col-xl-3">
                                        <div class="d-flex flex-wrap align-items-top p-4 border rounded">
                                            <div class="me-3 lh-1">
                                                <span class="avatar avatar-md avatar-rounded bg-secondary shadow-sm">
                                                    <i class="ti ti-receipt-refund fs-18"></i>
                                                </span>
                                            </div>
                                            <div class="flex-fill">
                                                <h5 class="fw-semibold mb-1" id="totalRefund">0</h5>
                                                <p class="text-muted mb-0 fs-12">Total Refund</p>
                                            </div>
                                           
                                        </div>
                                    </div>
                                    <div class="col-xl-3">
                                        <div class="d-flex flex-wrap align-items-top p-4 border rounded">
                                            <div class="me-3 lh-1">
                                                <span class="avatar avatar-md avatar-rounded bg-primary shadow-sm">
                                                    <i class="ti ti-wallet fs-18"></i>
                                                </span>
                                            </div>
                                            <div class="flex-fill">
                                                <h5 class="fw-semibold mb-1" id="totalTopup">0</h5>
                                                <p class="text-muted mb-0 fs-12">Total Topup</p>
                                            </div>
                                          
                                        </div>
                                    </div>
                                    
                                    <div class="col-xl-3">
                                        <div class="d-flex flex-wrap align-items-top p-4 border rounded">
                                            <div class="me-3 lh-1">
                                                <span class="avatar avatar-md avatar-rounded bg-danger shadow-sm">
                                                    <i class="ti ti-forbid-2 fs-18"></i>
                                                </span>
                                            </div>
                                            <div class="flex-fill">
                                                <h5 class="fw-semibold mb-1" id="totalHold">0</h5>
                                                <p class="text-muted mb-0 fs-12">Total Hold</p>
                                            </div>
                                          
                                        </div>
                                    </div>
                                    
                                    <div class="col-xl-3">
                                        <div class="d-flex flex-wrap align-items-top p-4 border rounded">
                                            <div class="me-3 lh-1">
                                                <span class="avatar avatar-md avatar-rounded bg-teal shadow-sm">
                                                    <i class="ti ti-circle-check fs-18"></i>
                                                </span>
                                            </div>
                                            <div class="flex-fill">
                                                <h5 class="fw-semibold mb-1" id="totalHoldReleased">0</h5>
                                                <p class="text-muted mb-0 fs-12">Total Hold Released</p>
                                            </div>
                                          
                                        </div>
                                    </div>
                                    
                                    <div class="col-xl-3">
                                        <div class="d-flex flex-wrap align-items-top p-4 border rounded">
                                            <div class="me-3 lh-1">
                                                <span class="avatar avatar-md avatar-rounded bg-purple shadow-sm">
                                                    <i class="ti ti-building-bank fs-18"></i>
                                                </span>
                                            </div>
                                            <div class="flex-fill">
                                                <h5 class="fw-semibold mb-1" id="totalAdvanceCredited">0</h5>
                                                <p class="text-muted mb-0 fs-12">Total Advance Credit</p>
                                            </div>
                                          
                                        </div>
                                    </div>
                                    
                                    
                                    <div class="col-xl-3">
                                        <div class="d-flex flex-wrap align-items-top p-4 border rounded">
                                            <div class="me-3 lh-1">
                                                <span class="avatar avatar-md avatar-rounded bg-indigo shadow-sm">
                                                    <i class="ti ti-credit-card fs-18"></i>
                                                </span>
                                            </div>
                                            <div class="flex-fill">
                                                <h5 class="fw-semibold mb-1" id="totalAdvanceDebited">0</h5>
                                                <p class="text-muted mb-0 fs-12">Total Advance Debit</p>
                                            </div>
                                          
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
                        <th scope="col">TxnType</th>
                        <th scope="col">Txnid</th>
                        <th scope="col">Opening</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Remarks</th>
                        <th scope="col">Closing</th>
                        <th scope="col">DateTime</th>
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
    window.startDate = "";
    window.endDate = "";
    window.type = "all";
    window.user_id = "";
    
    
    function exportTransactions() {
        window.open('/export-wallet-transactions?searchBy='+window.searchBy+"&searchValue="+window.searchValue+"&startDate="+window.startDate+"&endDate="+window.endDate+"&type="+window.type+"&user_id="+window.user_id);
    }
    

    function getTransactions() {
        
        
    var start = moment().subtract(6, 'days'); // 6 days ago
    var end = moment(); // today

    function cb(start, end) {
        $('#reportrange span').html(start.format('MMM D, YYYY') + ' - ' + end.format('MMM D, YYYY'));
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

     $('#reportrange span').html(start.format('MMM D, YYYY') + ' - ' + end.format('MMM D, YYYY'));
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
                "ajax": {
                    "url": "/api/tables/wallet",
                    "type": "GET",
                    "data": function (d) {
                        d.searchBy = window.searchBy;
                        d.searchValue = window.searchValue;
                        d.startDate = window.startDate;
                        d.endDate = window.endDate;
                        d.type = window.type;
                        d.user_id = window.user_id;
           
                    },
                    dataSrc: function (json) {
            
                       $("#totalSettlement").html("₹ " + json.extra.settlement);
                       $("#totalPayout").html("₹ " + json.extra.payout);
                       $("#totalRefund").html("₹ " + json.extra.refund);
                       $("#totalTopup").html("₹ " + json.extra.topup);
                       $("#totalHold").html("₹ " + json.extra.hold);
                       $("#totalHoldReleased").html("₹ " + json.extra.holdReleased);
                       $("#totalAdvanceCredited").html("₹ " + json.extra.advanceCredited);
                       $("#totalAdvanceDebited").html("₹ " + json.extra.advanceDebited);
           

           
                       return json.data; 
                    }
                }
            }
        );

        document.querySelector(".card-body").append(document.querySelectorAll("#dataTable_wrapper .row")[2])
    }

    function changeLength(value) {
        window.dataTable.DataTable().page.len(value).draw();
    }
    
    function changeStatus(value){
        window.status = value;
        window.dataTable.DataTable().ajax.reload();
    }
    
    function changeUser(value){
        window.user_id = value;
        window.dataTable.DataTable().ajax.reload();
    }
    
    function changeProvider(value){
        window.provider = value;
        window.dataTable.DataTable().ajax.reload();
    }
    
    function changeType(value){
        window.type = value;
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

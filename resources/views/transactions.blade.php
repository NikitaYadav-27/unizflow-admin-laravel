<?php if (!isset($_GET['turbo'])) {
    $page = $type . "_transactions";
    echo view('header', get_defined_vars());
} ?>
<div class="d-flex justify-content-between align-items-center">
    

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-arrows-right-left me-1 fs-15"></i>Transactions</a>
        </li>
        <?php if ($type == "all"): ?>
           <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-list me-1 fs-15"></i>All Transactions</li>
        <?php elseif ($type == "payin"): ?>
           <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-arrow-down-left me-1 fs-15"></i>Payin Transactions</li>
        <?php elseif ($type == "payout"): ?>
           <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-arrow-up-right me-1 fs-15"></i>Payout Transactions</li>
        <?php elseif ($type == "qr"): ?>
           <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-qrcode me-1 fs-15"></i>QR Transactions</li>
        <?php endif; ?>
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
                   <label class="mb-1">Status</label>
                    <select onchange="changeStatus(this.value);" class="form-select" id="status">
                    <option value="">All</option>
                    <option value="Success">Success</option>
                    <option value="Pending">Pending</option>
                    <option value="Failed">Failed</option>
                    <option value="Expired">Expired</option>
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
                   <label class="mb-1">Provider</label>
                    <select onchange="changeProvider(this.value);" class="form-control" id="provider">
                    <option value="">All</option>
                    <?php foreach((new \App\Models\Providers())->get() as $provider) : ?>
                       <?php if($provider['type'] == $type || $type == "all") : ?>
                       <option value="<?= $provider['id']; ?>"><?= $provider['name']; ?></option>
                       <?php endif; ?>
                    <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
           
            
            <div class="col-lg-3">
                 <div class="form-group mb-2">
                     <label class="mb-1">Search By</label>
                      <select class="form-select" id="searchBy" data-placeholder="Select" onchange="changeSearchBy(this.value)">
                        <option></option>
                        <option value="txnid">TxnId</option>
                        <option value="merchant_txnid">Merchant TxnId</option>
                        <option value="reference_id">Reference Id</option>
                        <option value="rrn">RRN</option>
                        <option value="name">Name</option>
                        <?php if($type == "payin") : ?>
                        <option value="email">Email</option>
                        <?php endif; ?>
                        
                        <option value="phone">Phone</option>
                        <option value="amount">Amount</option>
                         <?php if($type == "all" || $type == "payin" || $type == "qr") : ?>
                        <option value="payee_name">Payee Name</option>
                        <option value="payee_vpa">Payee VPA</option>
                        <option value="payee_phone">Payee Phone</option>
                        <?php endif; ?>
                        <?php if($type == "all" || $type == "payout") : ?>
                        <option value="account_number">Account Number</option>
                        <option value="ifsc_code">IFSC Code</option>
                        <?php endif; ?>
                        <option value="order_id">Order ID</option>
                        <option value="source">Source</option>
                        <option value="remarks">Remarks</option>
                        <?php if($type == "all" || $type == "qr") : ?>
                        <option value="qr_name">QR Name</option>
                        <?php endif; ?>
                        <?php if($type == "all" || $type == "payin" || $type == "qr") : ?>
                        <option value="settlement_txnid">Settlement TxnId</option>
                        <?php endif; ?>
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
            
            
   
        
                          <div class="row g-0">
                                    <div class="col-xl-3 border-end border-inline-end-dashed">
                                        <div class="d-flex flex-wrap align-items-top p-4">
                                            <div class="me-3 lh-1">
                                                <span class="avatar avatar-md avatar-rounded bg-primary shadow-sm">
                                                    <i class="ti ti-list fs-18"></i>
                                                </span>
                                            </div>
                                            <div class="flex-fill">
                                                <h5 class="fw-semibold mb-1" id="totalTransactions">0</h5>
                                                <p class="text-muted mb-0 fs-12">Total Transactions</p>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="col-xl-3 border-end border-inline-end-dashed">
                                        <div class="d-flex flex-wrap align-items-top p-4">
                                            <div class="me-3 lh-1">
                                                <span class="avatar avatar-md avatar-rounded bg-secondary shadow-sm">
                                                    <i class="ti ti-currency-rupee fs-18"></i>
                                                </span>
                                            </div>
                                            <div class="flex-fill">
                                                <h5 class="fw-semibold mb-1" id="totalAmount">0</h5>
                                                <p class="text-muted mb-0 fs-12">Total Amount</p>
                                            </div>
                                           
                                        </div>
                                    </div>
                                    <div class="col-xl-3 border-end border-inline-end-dashed">
                                        <div class="d-flex flex-wrap align-items-top p-4">
                                            <div class="me-3 lh-1">
                                                <span class="avatar avatar-md avatar-rounded bg-success shadow-sm">
                                                    <i class="ti ti-currency-rupee fs-18"></i>
                                                </span>
                                            </div>
                                            <div class="flex-fill">
                                                <h5 class="fw-semibold mb-1" id="totalCharges">0</h5>
                                                <p class="text-muted mb-0 fs-12">Total Charges</p>
                                            </div>
                                           
                                        </div>
                                    </div>
                                    <div class="col-xl-3">
                                        <div class="d-flex flex-wrap align-items-top p-4">
                                            <div class="me-3 lh-1">
                                                <span class="avatar avatar-md avatar-rounded bg-warning shadow-sm">
                                                    <i class="ti ti-currency-rupee fs-18"></i>
                                                </span>
                                            </div>
                                            <div class="flex-fill">
                                                <h5 class="fw-semibold mb-1" id="totalGST">0</h5>
                                                <p class="text-muted mb-0 fs-12">Total GST</p>
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
                        <th scope="col">Service</th>
                        <th scope="col">TxnId</th>
                        <?php if ($type == "payin" || $type == "payout" || $type == "all"): ?>
                        <th scope="col">Merchant TxnId</th>
                        <?php endif; ?>
                        <th scope="col">Reference Id</th>
                        <th scope="col">RRN</th>
                        <th scope="col">Status</th>
                        <th scope="col">Name</th>
                        <?php if ($type == "payin" || $type == "all"): ?>
                        <th scope="col">Email</th>
                        <?php endif; ?>
                        <th scope="col">Phone</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Charges</th>
                        <th scope="col">GST</th>
                        <th scope="col">Net Amount</th>
                        <?php if ($type == "payin" || $type == "qr" || $type == "all"): ?>
                        <th scope="col">Payee Name</th>
                        <th scope="col">Payee VPA</th>
                        <th scope="col">Payee Phone</th>
                        <?php endif; ?>
                        <?php if ($type == "payout" || $type == "all"): ?>
                        <th scope="col">Account Number</th>
                        <th scope="col">IFSC Code</th>
                        <th scope="col">Bank Name</th>
                        <?php endif; ?>
                        <th scope="col">Order Id</th>
                        <th scope="col">Source</th>
                        <?php if ($type == "payout" || $type == "all"): ?>
                        <th scope="col">Is Debited</th>
                        <th scope="col">Is Refunded</th>
                        <th scope="col">Is Proceesed</th>
                        <?php endif; ?>
                        <th scope="col">Reason</th>
                        <th scope="col">Remarks</th>
                        <th scope="col">Provider</th>
                       
                        <?php if ($type == "payin" || $type == "qr" || $type == "all"): ?>
                        <th scope="col">Is Settled</th>
                        <th scope="col">Settlement TxnId</th>
                        <?php endif; ?>
                        
                        <th>Txn Complete Time</th>
                        <th>Updated At</th>
                        <th>Created At</th>
                          <?php if ($type == "payout" || $type == "all"): ?>
                        <th>Receipt</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>


    </div>
</div>


<?= view("includes/receipt") ?>


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
                    navigate('/admin/settlements/pending');
                });
             }).fail((xhr) => {
                notify("error",xhr.responseJSON.message);
             });
          }
        });
    }
    
    function settleToBank(id) {
        
    }

    window.dataTable = null;
    window.searchBy = "";
    window.searchValue = "";
    window.startDate = "";
    window.endDate = "";
    window.type = "<?= $type ?>";
    window.status = "";
    window.user_id = "";
    window.provider = "";
    
    
    function exportTransactions() {
        window.open('/export-transactions?searchBy='+window.searchBy+"&searchValue="+window.searchValue+"&startDate="+window.startDate+"&endDate="+window.endDate+"&type="+window.type+"&status="+window.status+"&user_id="+window.user_id+"&provider="+window.provider);
    }
    
     


    function getTransactions() {
        
   
    var start = moment();
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
                    "url": "/api/tables/transactions",
                    "type": "GET",
                    "data": function (d) {
                        d.searchBy = window.searchBy;
                        d.searchValue = window.searchValue;
                        d.startDate = window.startDate;
                        d.endDate = window.endDate;
                        d.type = window.type;
                        d.user_id = window.user_id;
                        d.status = window.status;
                        d.provider = window.provider;
                    },
                    dataSrc: function (json) {
            
                       $("#totalTransactions").html(json.extra.total_transactions);
                       $("#totalAmount").html(json.extra.total_amount);
                       $("#totalCharges").html(json.extra.total_charges);
                       $("#totalGST").html(json.extra.total_gst);
           

           
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

@if (!request()->has('turbo'))
    @include('header', ['page' => 'settlement_' . $status])
@endif

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-receipt me-1 fs-15"></i>Settlements</a>
        </li>
        @if ($status == 'pending')
           <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-history me-1 fs-15"></i>Pending</li>
        @elseif ($status == 'hold')
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-forbid me-1 fs-15"></i>Hold</li>
        @else
           <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-circle-check me-1 fs-15"></i>Completed</li>
        @endif
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
                @if ($status == 'pending')
                    <button type="button" class="btn btn-sm btn-primary-transparent text-nowrap" onclick="triggerAutoSettlement(this)" title="Process settlements for users configured with Automatic Settlement">
                        <i class="ti ti-bolt me-1"></i>Run Auto Settlement
                    </button>
                    <button type="button" class="btn btn-sm btn-success-transparent text-nowrap" data-bs-toggle="modal" data-bs-target="#createManualSettlementModal" onclick="openManualSettlementModal()" title="Create an on-demand settlement batch for any merchant">
                        <i class="ti ti-plus me-1"></i>Create Manual Settlement
                    </button>
                @endif
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
                        @if ($status == 'pending' || $status == 'hold')
                        <th scope="col">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>

    </div>
</div>

<div class="modal fade" id="createManualSettlementModal" aria-labelledby="createManualSettlementModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title fw-bold" id="createManualSettlementModalLabel">
            <i class="ti ti-plus text-success me-1"></i>Create On-Demand Settlement Batch
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center" style="font-size: 13px;">
            <i class="ti ti-info-circle fs-5 me-2"></i>
            <div>
                Select a merchant and date/time range to bundle eligible unsettled payin transactions into a new Pending settlement batch.
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Select Merchant <span class="text-danger">*</span></label>
                <select class="form-select no-select2" id="manual_settle_user_id" onchange="previewManualSettlement()" style="width: 100%;">
                    <option value="">-- Choose Merchant --</option>
                    @if (!empty($users))
                        @php
                            $manualUsers = $users->filter(fn($u) => ($u->settlement_type ?? 'manual') === 'manual');
                            $autoUsers = $users->filter(fn($u) => ($u->settlement_type ?? 'manual') === 'automatic');
                        @endphp
                        @if ($manualUsers->isNotEmpty())
                            <optgroup label="Manual Settlement Merchants">
                                @foreach ($manualUsers as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->merchant_id ?? 'ID: ' . $u->id }}) [Manual]</option>
                                @endforeach
                            </optgroup>
                        @endif
                        @if ($autoUsers->isNotEmpty())
                            <optgroup label="Automatic Settlement Merchants">
                                @foreach ($autoUsers as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->merchant_id ?? 'ID: ' . $u->id }}) [Automatic]</option>
                                @endforeach
                            </optgroup>
                        @endif
                    @endif
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">From Date & Time <span class="text-danger">*</span></label>
                <input type="datetime-local" class="form-control" id="manual_settle_from_time" onchange="previewManualSettlement()">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">To Date & Time <span class="text-danger">*</span></label>
                <input type="datetime-local" class="form-control" id="manual_settle_to_time" onchange="previewManualSettlement()">
            </div>
        </div>

        
        <div id="manual_settle_preview_box" class="card border rounded-3 p-3 bg-light mb-0" style="display:none;">
            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                <span class="fw-semibold text-primary"><i class="ti ti-calculator me-1"></i>Settlement Batch Preview</span>
                <div class="d-flex align-items-center gap-2">
                    <span id="prev_type" class="badge bg-secondary-transparent fs-11">Manual</span>
                    <span id="prev_spinner" class="spinner-border spinner-border-sm text-primary" style="display:none;"></span>
                </div>
            </div>
            
            <div id="prev_content">
                <div class="row g-2 text-sm">
                    <div class="col-sm-6 d-flex justify-content-between">
                        <span class="text-muted">Unsettled Payins:</span>
                        <span class="badge bg-primary" id="prev_count">0</span>
                    </div>
                    <div class="col-sm-6 d-flex justify-content-between">
                        <span class="text-muted">Gross Collection:</span>
                        <strong class="text-dark">₹<span id="prev_gross">0.00</span></strong>
                    </div>
                    <div class="col-sm-6 d-flex justify-content-between">
                        <span class="text-muted">Platform Charges:</span>
                        <span class="text-danger">-₹<span id="prev_charges">0.00</span></span>
                    </div>
                    <div class="col-sm-6 d-flex justify-content-between">
                        <span class="text-muted">GST (18%):</span>
                        <span class="text-danger">-₹<span id="prev_gst">0.00</span></span>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                    <span class="fw-bold fs-6 text-dark">Estimated Net Payable:</span>
                    <span class="fs-5 fw-bold text-success">₹<span id="prev_net">0.00</span></span>
                </div>
            </div>

            <div id="manual_settle_empty_warning" class="alert alert-warning py-2 px-3 mt-2 mb-0" style="display:none; font-size: 13px;">
                <i class="ti ti-alert-triangle me-1"></i>No unsettled payin transactions found for this merchant in the selected range.
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" id="btnSubmitManualSettlement" onclick="submitManualSettlement()" disabled>
            <i class="ti ti-check me-1"></i>Generate Settlement Batch
        </button>
      </div>
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
             $.post('{{ route('admin.settlement.settle-to-wallet') }}',{
                 id : id 
             },(data) =>{
                notify("success",data.message,() => {
                    navigate('{{ route('admin.settlements.index', ['status' => $status]) }}');
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
             $.post('{{ route('admin.settlement.hold-settlement') }}',{
                 id : id 
             },(data) =>{
                notify("success",data.message,() => {
                    navigate('{{ route('admin.settlements.index', ['status' => $status]) }}');
                });
             }).fail((xhr) => {
                notify("error",xhr.responseJSON.message);
             });
          }
        });
    }
    
    function settleToBank(id) {
      navigate('{{ route('admin.settlement.bank', ['id' => '__ID__']) }}'.replace('__ID__', id));  
    }

    window.dataTable = null;
    window.searchBy = "";
    window.searchValue = "";
    window.startDate = "";
    window.endDate = "";

    function getTransactions() {
        
        
    var start = moment().subtract(6, 'days');
    var end = moment();

    function cb(start, end) {
        $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

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
                "pageLength": 50,
                "columnDefs": [
                    { "defaultContent": "-", "targets": "_all" }
                ],
                "ajax": {
                    "url": "{{ route('api.tables.settlements') }}",
                    "type": "GET",
                    "data": function (d) {
                        d.searchBy = window.searchBy;
                        d.searchValue = window.searchValue;
                        d.startDate = window.startDate;
                        d.endDate = window.endDate;
                        d.status = '{{ $status == 'completed' ? 'Success' : $status }}'
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

    function triggerAutoSettlement(btn) {
        let orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';
        $.ajax({
            url: '{{ route('admin.settlements.trigger-auto') }}',
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

    function initManualSettlementMerchantSelect() {
        if (typeof $.fn.select2 !== 'undefined') {
            try {
                if ($('#manual_settle_user_id').hasClass('select2-hidden-accessible')) {
                    $('#manual_settle_user_id').select2('destroy');
                }
            } catch (e) {}

            $('#manual_settle_user_id').select2({
                dropdownParent: $('#createManualSettlementModal'),
                width: '100%',
                placeholder: '-- Choose Merchant --',
                allowClear: true
            });

            $('#manual_settle_user_id').off('change.manual_preview').on('change.manual_preview', function() {
                previewManualSettlement();
            });
        }
    }

    $(document).on('shown.bs.modal', '#createManualSettlementModal', function() {
        initManualSettlementMerchantSelect();
    });

    function openManualSettlementModal() {
        let now = new Date();
        let pad = (n) => String(n).padStart(2, '0');
        let toStr = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate()) + 'T' + pad(now.getHours()) + ':' + pad(now.getMinutes());
        
        let past = new Date(now.getTime() - (7 * 24 * 60 * 60 * 1000));
        let fromStr = past.getFullYear() + '-' + pad(past.getMonth() + 1) + '-' + pad(past.getDate()) + 'T00:00';

        if (!$('#manual_settle_from_time').val()) {
            $('#manual_settle_from_time').val(fromStr);
        }
        if (!$('#manual_settle_to_time').val()) {
            $('#manual_settle_to_time').val(toStr);
        }

        setTimeout(function() {
            initManualSettlementMerchantSelect();
            if ($('#manual_settle_user_id').val()) {
                previewManualSettlement();
            }
        }, 150);
    }

    var previewManualDebounce = null;
    function previewManualSettlement() {
        clearTimeout(previewManualDebounce);
        previewManualDebounce = setTimeout(function() {
            let userId = $('#manual_settle_user_id').val();
            let fromTime = $('#manual_settle_from_time').val();
            let toTime = $('#manual_settle_to_time').val();

            if (!userId || !fromTime || !toTime) {
                $('#manual_settle_preview_box').hide();
                $('#btnSubmitManualSettlement').prop('disabled', true);
                return;
            }

            $('#manual_settle_preview_box').show();
            $('#prev_spinner').show();
            $('#manual_settle_empty_warning').hide();
            $('#prev_content').css('opacity', '0.5');

            $.ajax({
                url: '{{ route('admin.settlement.preview-manual') }}',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    user_id: userId,
                    from_time: fromTime,
                    to_time: toTime
                }),
                success: function(res) {
                    $('#prev_spinner').hide();
                    $('#prev_content').css('opacity', '1');
                    if (res.status === 'success' && res.data) {
                        let d = res.data;
                        let isAuto = (d.settlement_type || '').toLowerCase() === 'automatic';
                        $('#prev_type').text(isAuto ? '⚡ Automatic Merchant' : '📋 Manual Merchant')
                                       .attr('class', isAuto ? 'badge bg-success-transparent fs-11' : 'badge bg-secondary-transparent fs-11');
                        $('#prev_count').text(d.count);
                        $('#prev_gross').text(Number(d.total_amount).toFixed(2));
                        $('#prev_charges').text(Number(d.total_charges).toFixed(2));
                        $('#prev_gst').text(Number(d.total_gst).toFixed(2));
                        $('#prev_net').text(Number(d.net_amount).toFixed(2));

                        if (d.count > 0 && d.net_amount > 0) {
                            $('#btnSubmitManualSettlement').prop('disabled', false);
                            $('#manual_settle_empty_warning').hide();
                        } else {
                            $('#btnSubmitManualSettlement').prop('disabled', true);
                            $('#manual_settle_empty_warning').show();
                        }
                    }
                },
                error: function(xhr) {
                    $('#prev_spinner').hide();
                    $('#prev_content').css('opacity', '1');
                    $('#btnSubmitManualSettlement').prop('disabled', true);
                    let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to calculate preview";
                    notify("error", msg);
                }
            });
        }, 250);
    }

    function submitManualSettlement() {
        let userId = $('#manual_settle_user_id').val();
        let fromTime = $('#manual_settle_from_time').val();
        let toTime = $('#manual_settle_to_time').val();

        if (!userId || !fromTime || !toTime) {
            notify("error", "Please select merchant and valid date/time range");
            return;
        }

        let btn = $('#btnSubmitManualSettlement');
        let orig = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Creating Batch...');

        $.ajax({
            url: '{{ route('admin.settlement.create-manual') }}',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                user_id: userId,
                from_time: fromTime,
                to_time: toTime
            }),
            success: function(res) {
                btn.prop('disabled', false).html(orig);
                let modalEl = document.getElementById('createManualSettlementModal');
                let modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) {
                    modalInstance.hide();
                }
                notify("success", res.message || "Manual settlement batch created successfully!");
                if (window.dataTable) {
                    window.dataTable.DataTable().ajax.reload();
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(orig);
                let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to create manual settlement batch";
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

    
    #createManualSettlementModal .select2-container {
        width: 100% !important;
        display: block !important;
    }
    #createManualSettlementModal .select2-container--default .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.375rem !important;
        display: flex !important;
        align-items: center !important;
        padding: 0 6px !important;
        background-color: #fff !important;
    }
    #createManualSettlementModal .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        color: #495057 !important;
        padding-left: 6px !important;
        padding-right: 24px !important;
        width: 100% !important;
    }
    #createManualSettlementModal .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        position: absolute !important;
        top: 1px !important;
        right: 8px !important;
        width: 20px !important;
        display: block !important;
    }
    #createManualSettlementModal .select2-dropdown {
        z-index: 1065 !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.375rem !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
    }
    #createManualSettlementModal .select2-search--dropdown {
        padding: 6px !important;
    }
    #createManualSettlementModal .select2-search--dropdown .select2-search__field {
        border: 1px solid #ced4da !important;
        border-radius: 0.25rem !important;
        padding: 6px 10px !important;
        width: 100% !important;
        outline: none !important;
    }

    
    #createManualSettlementModal .modal-dialog-scrollable .modal-body,
    #createManualSettlementModal .modal-body,
    #createManualSettlementModal .modal-content {
        scrollbar-width: none !important; 
        -ms-overflow-style: none !important; 
        overflow-y: auto !important;
    }
    #createManualSettlementModal .modal-dialog-scrollable .modal-body::-webkit-scrollbar,
    #createManualSettlementModal .modal-body::-webkit-scrollbar,
    #createManualSettlementModal .modal-content::-webkit-scrollbar {
        display: none !important; 
        width: 0 !important;
        height: 0 !important;
    }
</style>

@if (!request()->has('turbo'))
    @include('footer')
@endif
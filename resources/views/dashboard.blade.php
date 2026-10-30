@if (!request()->has('turbo'))
    @include('header', ['page' => 'dashboard'])
@endif

@php
$wallets = \App\Models\User::selectRaw("sum(wallet) as wallet, sum(hold_wallet) as hold_wallet")->first();
$unsettledTotal = \App\Models\Transaction::where('service', 'payin')->where('status', 'Success')->where('settled', 0)->sum('net_amount');
@endphp

<div class="d-md-flex d-block align-items-center justify-content-between py-3 page-header-breadcrumb">
    <div>
        
        <nav aria-label="breadcrumb" class="my-0">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
       
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-browser me-1 fs-15"></i>Admin Dashboard</li>
    </ol>
</nav>

    </div>
    <div class="btn-list mt-md-0 mt-2 d-flex align-items-center gap-2 flex-wrap">
        <button class="btn btn-primary text-nowrap" style="padding: .375rem .75rem !important;" title="Total Usable Wallets"><i class="ti ti-wallet"></i>&nbsp;₹ {{ round($wallets['wallet'] ?? 0, 2) }}</button>
        <button class="btn btn-purple text-nowrap" style="padding: .375rem .75rem !important;" title="Total Funds on Hold"><i class="ti ti-alert-circle"></i>&nbsp;₹ {{ round($wallets['hold_wallet'] ?? 0, 2) }}</button>
        <button class="btn btn-warning text-nowrap" style="padding: .375rem .75rem !important;" title="Total Unsettled Payin Collections"><i class="ti ti-clock"></i>&nbsp;Unsettled: ₹ {{ number_format($unsettledTotal, 2) }}</button>
         <div id="reportrange" class="form-control sm-w-100" style="width: min-content; white-space: nowrap;">
           <i class="ti ti-calendar"></i>&nbsp;
           <span></span> <i class="fa fa-caret-down"></i>
         </div>
    </div>
</div>

<div class="row position-relative">
    
    
    
    
    
  <div class="col-xxl-6 col-xl-12">
    <div class="row row-cols-12">
      <div class="col-lg-6">
        <div class="card custom-card position-relative">
          <span class="badge bg-primary position-absolute top-0 end-0 fs-12" style="min-width: 20%; border-radius: 0px 0.25rem; width: min-content;" id="totalTransactionCount">0</span>
          <div class="card-body">
            <div class="d-flex align-items-top">
              <div class="me-3">
                <span class="avatar avatar-md p-2 bg-primary">
                  <i class="ti ti-arrows-right-left fs-5"></i>
                </span>
              </div>
              <div class="flex-fill">
                <div class="d-flex mb-1 align-items-top justify-content-between">
                  <h5 class="fw-semibold mb-0 fs-6 lh-1" id="totalTransactionAmount">0</h5>
                </div>
                <p class="mb-0 fs-10 op-7 text-muted fw-semibold">TOTAL TRANSACTIONS</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card custom-card position-relative">
          <span class="badge bg-success position-absolute top-0 end-0 fs-12" style="min-width: 20%; border-radius: 0px 0.25rem; width: min-content;" id="totalPayinCount">0</span>
          <div class="card-body">
            <div class="d-flex align-items-top">
              <div class="me-3">
                <span class="avatar avatar-md p-2 bg-success">
                  <i class="ti ti-arrow-down-left fs-5"></i>
                </span>
              </div>
              <div class="flex-fill">
                <div class="d-flex mb-1 align-items-top justify-content-between">
                  <h5 class="fw-semibold mb-0 fs-6 lh-1" id="totalPayinAmount">0</h5>
                </div>
                <p class="mb-0 fs-10 op-7 text-muted fw-semibold">TOTAL PAYIN</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card custom-card position-relative">
          <span class="badge bg-danger position-absolute top-0 end-0 fs-12" style="min-width: 20%; border-radius: 0px 0.25rem; width: min-content;" id="totalPayoutCount">0</span>
          <div class="card-body">
            <div class="d-flex align-items-top">
              <div class="me-3">
                <span class="avatar avatar-md p-2 bg-danger">
                  <i class="ti ti-arrow-up-right fs-5"></i>
                </span>
              </div>
              <div class="flex-fill">
                <div class="d-flex mb-1 align-items-top justify-content-between">
                  <h5 class="fw-semibold mb-0 fs-6 lh-1" id="totalPayoutAmount">0</h5>
                </div>
                <p class="mb-0 fs-10 op-7 text-muted fw-semibold">TOTAL PAYOUT</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card custom-card position-relative">
          <span class="badge bg-info position-absolute top-0 end-0 fs-12" style="min-width: 20%; border-radius: 0px 0.25rem; width: min-content;" id="totalSettlementCount">0</span>
          <div class="card-body">
            <div class="d-flex align-items-top">
              <div class="me-3">
                <span class="avatar avatar-md p-2 bg-info">
                  <i class="ti ti-receipt fs-5"></i>
                </span>
              </div>
              <div class="flex-fill">
                <div class="d-flex mb-1 align-items-top justify-content-between">
                  <h5 class="fw-semibold mb-0 fs-6 lh-1" id="totalSettlementAmount">0</h5>
                </div>
                <p class="mb-0 fs-10 op-7 text-muted fw-semibold">TOTAL SETTLEMENT</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card custom-card position-relative">
          <span class="badge bg-teal position-absolute top-0 end-0 fs-12" style="min-width: 20%; border-radius: 0px 0.25rem; width: min-content;" id="totalTopupCount">0</span>
          <div class="card-body">
            <div class="d-flex align-items-top">
              <div class="me-3">
                <span class="avatar avatar-md p-2 bg-teal">
                  <i class="ti ti-wallet fs-5"></i>
                </span>
              </div>
              <div class="flex-fill">
                <div class="d-flex mb-1 align-items-top justify-content-between">
                  <h5 class="fw-semibold mb-0 fs-6 lh-1" id="totalTopupAmount">0</h5>
                </div>
                <p class="mb-0 fs-10 op-7 text-muted fw-semibold">TOTAL TOPUP</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card custom-card position-relative">
          <span class="badge bg-orange position-absolute top-0 end-0 fs-12" style="min-width: 20%; border-radius: 0px 0.25rem; width: min-content;" id="totalChargesCount">0</span>
          <div class="card-body">
            <div class="d-flex align-items-top">
              <div class="me-3">
                <span class="avatar avatar-md p-2 bg-orange">
                  <i class="ti ti-currency-rupee fs-5"></i>
                </span>
              </div>
              <div class="flex-fill">
                <div class="d-flex mb-1 align-items-top justify-content-between">
                  <h5 class="fw-semibold mb-0 fs-6 lh-1" id="totalChargesAmount">0</h5>
                </div>
                <p class="mb-0 fs-10 op-7 text-muted fw-semibold">TOTAL CHARGES</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-xl-12">
        <div class="card custom-card overflow-hidden">
          <div class="card-header justify-content-between">
            <div class="card-title">Payment Insights</div>
          </div>
          <div class="card-body p-0">
            <div class="row px-3 py-2">
                
                <div class="col-4">
                <div class="form-group">
                  <label class="mb-1">User</label>
                  <select class="form-control" onchange="updateUser(this.value)">
                    <option value="all">All</option>
                    @foreach (\App\Models\User::where("active", 1)->get() as $user)
                      <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              
              <div class="col-4">
                <div class="form-group">
                  <label class="mb-1">Type</label>
                  <select class="form-control" onchange="updateType(this.value);">
                    <option value="all">All</option>
                    <option value="payin">Payin</option>
                    <option value="payout">Payout</option>
                  </select>
                </div>
              </div>
              <div class="col-4">
                <div class="form-group">
                  <label class="mb-1">Trends By</label>
                  <select class="form-control" onchange="updateBy(this.value);">
                    <option value="monthly">Monthly</option>
                    <option value="weekly">Weekly</option>
                  </select>
                </div>
              </div>
            </div>
            <div id="paymentInsight" class=" mt-sm-0 mt-3"></div>
            <div class="px-3 py-3">
              <div class="row">
                <div class="col-4 d-flex flex-column align-items-center justify-content-center">
                  <label>Daily avg. payment</label>
                  <h6 class="fw-bold" id="dailyAveragePayment">₹ 0</h6>
                </div>
                <div class="col-4 d-flex flex-column align-items-center justify-content-center">
                  <label>Daily avg. count</label>
                  <h6 class="fw-bold" id="dailyAverageCount">0</h6>
                </div>
                <div class="col-4 d-flex flex-column align-items-center justify-content-center">
                  <label>Avg. transaction value</label>
                  <h6 class="fw-bold" id="averageTransactionValue">₹ 0</h6>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xxl-6 col-xl-12">
    <div class="row">
      <div class="col-xxl-12 col-xl-12">
        <div class="card custom-card overflow-hidden">
          <div class="card-header justify-content-between">
            <div class="card-title"> Top Users </div>
            <div class="form-group d-flex gap-2 align-items-center">
                <label class="mb-1">Order By</label>
                <select class="form-control no-select2" style="width: min-content !important;" onchange="updateOrder(this.value);">
                   <option value="total">Total Amount</option>
                   <option value="payin">Total Payin</option>
                   <option value="payout">Total Payout</option>
                </select>
            </div>
            
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover text-nowrap">
                <thead>
                  <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Total Payin</th>
                    <th scope="col">Total Payout</th>
                    <th scope="col">Total Amount</th>
                  </tr>
                </thead>
                <tbody id="usersCont">
                  
                  
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xxl-12 col-xl-12">
        <div class="row">
          <div class="col-xxl-12 col-xl-12">
            <div class="card custom-card overflow-hidden">
              <div class="card-header justify-content-between">
                <div class="card-title"> Top Providers </div>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-hover text-nowrap">
                    <thead>
                      <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Type</th>
                        <th scope="col">Total Count</th>
                        <th scope="col">Total Amount</th>
                      </tr>
                    </thead>
                    <tbody id="providerCont"> 
                      
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-12">
        <div class="card custom-card">
          <div class="card-header">
            <h6 class="card-title">Transactions Status</h6>
          </div>
          <div class="card-body">
            <div class="d-flex align-items-center mb-3">
              <h4 class="fw-bold mb-0" id="totalTransactionCountS"></h4>
              <div class="ms-2">
                <span class="text-muted ms-1">Total Transactions</span>
              </div>
            </div>
            <div class="progress-stacked progress-animate progress-xs mb-4">
              <div class="progress-bar bg-success" role="progressbar" id="successTransactionProgress" style="width: 0%;" aria-valuenow="97.40" aria-valuemin="0" aria-valuemax="100"></div>
              <div class="progress-bar bg-warning" role="progressbar" id="pendingTransactionProgress" style="width: 0%;" aria-valuenow="0.00" aria-valuemin="0" aria-valuemax="100"></div>
              <div class="progress-bar bg-danger" role="progressbar" id="failedTransactionProgress" style="width: 0%;" aria-valuenow="2.60" aria-valuemin="0" aria-valuemax="100"></div>
              <div class="progress-bar bg-pink" role="progressbar" id="expiredTransactionProgress" style="width: 0%;" aria-valuenow="0.00" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <ul class="list-unstyled mb-0 pt-2 crm-deals-status">
              <li class="success">
                <div class="d-flex align-items-center justify-content-between">
                  <div>Successful Transactions</div>
                  <div class="fs-12 text-muted" id="successTransactionCount">0</div>
                </div>
              </li>
              <li class="warning">
                <div class="d-flex align-items-center justify-content-between">
                  <div>Pending Transactions</div>
                  <div class="fs-12 text-muted" id="pendingTransactionCount">0</div>
                </div>
              </li>
              <li class="danger">
                <div class="d-flex align-items-center justify-content-between">
                  <div>Failed Transactions</div>
                  <div class="fs-12 text-muted" id="failedTransactionCount">0</div>
                </div>
              </li>
              <li class="pink">
                <div class="d-flex align-items-center justify-content-between">
                  <div>Expired Transactions</div>
                  <div class="fs-12 text-muted" id="expiredTransactionCount">0</div>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  .apexcharts-menu-icon {
    display: none;
  }

  .crm-deals-status li.danger:before {
    background-color: rgb(var(--danger-rgb));
  }
  
  .crm-deals-status li.pink:before {
    background-color: rgb(var(--pink-rgb));
  }
  
  
 

</style>

<script>
  window.startDate = null;
  window.endDate = null;
  window.report_user_id = "all";
  window.report_type = "all";
  window.report_by = "monthly";
  window.users_order_by = "total";
  
  function updateUser(value){
      window.report_user_id = value;
      loadDashboardData();
  }
  
  function updateOrder(value){
      window.users_order_by = value;
      loadDashboardData();
  }
  
  function updateBy(value){
      window.report_by = value;
      loadDashboardData();
  }
  
  function updateType(value){
      window.report_type = value;
      loadDashboardData();
  }
  
  function load() {
      
    var start = moment();
    var end = moment();

    function cb(start, end) {
        $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        window.startDate = start.format("YYYY-MM-DD");
        window.endDate = end.format("YYYY-MM-DD");
        loadDashboardData();
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
     
     
     loadDashboardData();
    
     
     
      
  }
  
  function loadDashboardData() {
      $("#dashLoader").removeClass("d-none").addClass("d-flex");
      
      $.get("/api/admin/dashboard/fetch-data",{
          startDate : window.startDate,
          endDate : window.endDate,
          usersBy : window.users_order_by,
          reportType : window.report_type,
          reportUser : window.report_user_id,
          reportBy : window.report_by
      },(data) => {
          
          $("#totalTransactionCount").html(data.summary.transactions.count);
          $("#totalTransactionAmount").html("₹ " + data.summary.transactions.amount);
          
          $("#totalPayinCount").html(data.summary.payin.count);
          $("#totalPayinAmount").html("₹ " + data.summary.payin.amount);
          
          $("#totalPayoutCount").html(data.summary.payout.count);
          $("#totalPayoutAmount").html("₹ " + data.summary.payout.amount);
          
          $("#totalChargesCount").html(data.summary.charges.count);
          $("#totalChargesAmount").html("₹ " + data.summary.charges.amount);
          
          $("#totalSettlementCount").html(data.summary.settlement.count);
          $("#totalSettlementAmount").html("₹ " + data.summary.settlement.amount);
          
          $("#totalTopupCount").html(data.summary.topup.count);
          $("#totalTopupAmount").html("₹ " + data.summary.topup.amount);
          
          
          $("#usersCont").html("");
          
          data.users.forEach((user) => {
              $("#usersCont").append(`
                   <tr>
                    <th scope="row">
                      <div class="d-flex align-items-center">
                        <div class="me-2">
                          <span class="avatar bg-primary avatar-sm p-1 "> ${getInitials(user.user_name)} </span>
                        </div>
                        <div>
                          <p class="fw-semibold mb-0">${user.user_name}</p>
                          <p class="fs-12 text-muted mb-0">${user.user_merchant_id}</p>
                        </div>
                      </div>
                    </th>
                    <td>₹ ${user.transaction_total_payin_amount} </td>
                    <td>₹ ${user.transaction_total_payout_amount}</td>
                    <td>₹ ${user.transaction_total_amount}</td>
                  </tr>`);
          });
          
          $("#providerCont").html("");
          
          data.providers.forEach((provider) => {
              $("#providerCont").append(`
                      <tr>
                        <th scope="row">
                          <div class="d-flex align-items-center">
                            <div class="me-2">
                              <img src="${provider.logo}" style="width: 70px; height: 40px;">
                            </div>
                            <div>
                              <p class="fw-semibold mb-0">${provider.name}</p>
                            </div>
                          </div>
                        </th>
                        <td> ${getTypeSpan(provider.type)} </td>
                        <td>${provider.total_transactions}</td>
                        <td>₹ ${provider.total_amount}</td>
                      </tr>`);
          });
          
          

          let totalTransactions = 0;
          let totalSuccessTransactions = 0;
          let totalPendingTransactions = 0;
          let totalFailedTransactions = 0;
          let totalExpiredTransactions = 0;
          
          data.status.forEach((txn) => {
              if(txn.status == "Success"){
                  totalSuccessTransactions = parseInt(txn.transaction_count);
              }
              if(txn.status == "Pending"){
                  totalPendingTransactions = parseInt(txn.transaction_count);
              }
              if(txn.status == "Failed"){
                  totalFailedTransactions = parseInt(txn.transaction_count);
              }
              if(txn.status == "Expired"){
                  totalExpiredTransactions = parseInt(txn.transaction_count);
              }
          });
          
          totalTransactions = totalSuccessTransactions + totalPendingTransactions + totalFailedTransactions + totalExpiredTransactions;
          
          const percentage = (count) => totalTransactions > 0 ? ((count / totalTransactions) * 100).toFixed(2) : "0.00";

          const successPercentage = percentage(totalSuccessTransactions);
          const pendingPercentage = percentage(totalPendingTransactions);
          const failedPercentage = percentage(totalFailedTransactions);
          const expiredPercentage = percentage(totalExpiredTransactions);

          $("#totalTransactionCountS").html(totalTransactions);
          $("#successTransactionCount").html(totalSuccessTransactions);
          $("#pendingTransactionCount").html(totalPendingTransactions);
          $("#failedTransactionCount").html(totalFailedTransactions);
          $("#expiredTransactionCount").html(totalExpiredTransactions);
          try{
          document.querySelector("#successTransactionProgress").style.width = successPercentage+"%";
          document.querySelector("#pendingTransactionProgress").style.width = pendingPercentage+"%";
          document.querySelector("#failedTransactionProgress").style.width = failedPercentage+"%";
          document.querySelector("#expiredTransactionProgress").style.width = expiredPercentage+"%";
          
          
          loadGraph(data.report.labels, data.report.data);
          
          $("#dailyAveragePayment").html("₹ " + data.report.dailyAvgPayment);
          $("#dailyAverageCount").html(data.report.dailyAvgCount);
          $("#averageTransactionValue").html("₹ " + data.report.avgTxnValue);
          
          }catch(e){}
          
          
          $("#dashLoader").addClass("d-none").removeClass("d-flex");
      }).fail((xhr) => {
         notify("error",xhr.responseJSON.message); 
         $("#dashLoader").addClass("d-none").removeClass("d-flex");
      });;
  }
  
  function getTypeSpan(type) {
      if(type == "payin") {
          return '<span class="badge bg-success-transparent"><i class="ti ti-arrow-down-left"></i> &nbsp;Payin</span>';
      }
      if(type == "qr") {
          return '<span class="badge bg-primary-transparent"><i class="ti ti-qrcode"></i> &nbsp;Static QR</span>';
      }
      if(type == "payout") {
          return '<span class="badge bg-danger-transparent"><i class="ti ti-cash"></i> &nbsp;Payout</span>';
      }
  }
  
  function getInitials(name) {
     if (!name) return '';
     const words = name.trim().split(/\s+/);
     const initials = words.slice(0, 2).map(word => word[0].toUpperCase()).join('');
     return initials;
  }
  
  function loadGraph(labels,data) {
      
      $("#paymentInsight").html("");
         var options = {
        series: [{
            name: 'Transaction Amount',
            data: data
        }],
        chart: {
            height: 320,
            type: 'bar',
        },
        grid: {
            borderColor: '#f2f5f7',
        },
        plotOptions: {
            bar: {
                borderRadius: 10,
                dataLabels: {
                    position: 'top',
                },
            }
        },
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return val + "L";
            },
            offsetY: -20,
            style: {
                fontSize: '12px',
                colors: ["#185765"]
            }
        },
        colors: ["#185765"],
        xaxis: {
            categories: labels,
            position: 'bottom',
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            },
            crosshairs: {
                fill: {
                    type: 'gradient',
                    gradient: {
                        colorFrom: '#185765',
                        colorTo: '#185765',
                        stops: [0, 100],
                        opacityFrom: 0.4,
                        opacityTo: 0.5,
                    }
                }
            },
            tooltip: {
                enabled: true,
            },
            labels: {
                show: true,
                style: {
                    colors: "#185765",
                    fontSize: '11px',
                    fontWeight: 600,
                    cssClass: 'apexcharts-xaxis-label',
                },
            }
        },
        yaxis: {
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false,
            },
            labels: {
                show: false,
                formatter: function (val) {
                    return val + "L";
                }
            }

        },
        title: {
            text: 'Monthly Inflation in Argentina, 2002',
            floating: true,
            offsetY: 330,
            align: 'center',
            style: {
                color: '##185765'
            }
        }
    };
    var chart = new ApexCharts(document.querySelector("#paymentInsight"), options);
    chart.render();
  }
</script>

<script class="afterLoadScript" type="plain/text">
load();

</script>

@if (!request()->has('turbo'))
    @include('footer')
@endif
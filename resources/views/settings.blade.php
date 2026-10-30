@if (!request()->has('turbo'))
    @include('header', ['page' => 'settings'])
@endif

@php
$settingsModel = new \App\Models\Settings();
@endphp

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-settings me-1 fs-15"></i>Settings</a>
        </li>
    </ol>
</nav>

<div class="row">
    <div class="col-lg-4">
       <div class="card custom-card">
        <div class="card-header">
            <h6 class="card-title"><i class="ti ti-settings"></i> &nbsp;Manage Services</h6>
        </div>
        <div class="card-body p-0 pb-1">
           <table class="table table-bordered">
               <tr>
                   <th><i class="ti ti-arrow-up-right"></i>&nbsp;Payin</th>
                   <td class="">
                       <div class="custom-toggle-switch toggle-sm ms-2">
                         <input onchange="switchSetting('payin_enable',this.checked)" {{ $settingsModel->isPayinEnabled() ? 'checked' : '' }} id="payin" name="toggleswitchsize" type="checkbox">
                         <label for="payin" class="label-primary"></label>
                       </div>
                   </td>
               </tr>
               <tr>
                   <th><i class="ti ti-arrow-down-left"></i>&nbsp;Payout</th>
                   <td class="">
                       <div class="custom-toggle-switch toggle-sm ms-2">
                         <input onchange="switchSetting('payout_enable',this.checked)" {{ $settingsModel->isPayoutEnabled() ? 'checked' : '' }} id="payout" name="toggleswitchsize" type="checkbox">
                         <label for="payout" class="label-primary"></label>
                       </div>
                   </td>
               </tr>
           </table>

        </div>
      </div> 
    </div>

    <div class="col-lg-4">
       <div class="card custom-card">
        <div class="card-header">
            <h6 class="card-title"><i class="ti ti-receipt-tax"></i> &nbsp;Tax & GST Settings</h6>
        </div>
        <div class="card-body">
            <form onsubmit="saveGstSetting(event)">
                <div class="form-group mb-2">
                    <label class="form-label fw-semibold">Default GST Rate (%)</label>
                    <div class="input-group">
                        <input type="number" step="0.01" min="0" max="100" class="form-control" id="gst_rate_input" value="{{ \App\Models\Settings::getGSTRate() }}" placeholder="18" required>
                        <span class="input-group-text">%</span>
                        <button type="submit" class="btn btn-primary" id="saveGstBtn">
                            <i class="ti ti-device-floppy me-1"></i> Save
                        </button>
                    </div>
                   
                </div>
            </form>
        </div>
      </div> 
    </div>
</div>

<script>
    function switchSetting(name, value) {
        $.post("/api/admin/settings/update",{
          name : name,
          value : value ? 1:0
        },(data) => {
            notify("success",data.message);
        }).fail((xhr) => {
            notify("error",xhr.responseJSON.message);
        });
    }

    function saveGstSetting(e) {
        e.preventDefault();
        let rate = document.getElementById("gst_rate_input").value;
        if (rate === "" || isNaN(rate) || rate < 0) {
            notify("error", "Please enter a valid GST rate");
            return;
        }
        let btn = document.getElementById("saveGstBtn");
        let orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

        $.post("/api/admin/settings/update", {
            name: 'gst_rate',
            value: rate
        }, function(data) {
            btn.disabled = false;
            btn.innerHTML = orig;
            notify("success", data.message || "GST Rate updated successfully");
        }).fail(function(xhr) {
            btn.disabled = false;
            btn.innerHTML = orig;
            notify("error", xhr.responseJSON?.message || "Failed to update GST rate");
        });
    }
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

@if (!request()->has('turbo'))
    @include('footer')
@endif
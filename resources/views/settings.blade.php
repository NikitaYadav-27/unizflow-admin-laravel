<?php if (!isset($_GET['turbo'])) {
    $page = "settings";
    echo view('header', get_defined_vars());
}
$settingsModel = new \App\Models\Settings();
?>

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
                         <input onchange="switchSetting('payin_enable',this.checked)" <?= $settingsModel->isPayinEnabled()?'checked':''?> id="payin" name="toggleswitchsize" type="checkbox">
                         <label for="payin" class="label-primary"></label>
                       </div>
                   </td>
               </tr>
               <tr>
                   <th><i class="ti ti-arrow-down-left"></i>&nbsp;Payout</th>
                   <td class="">
                       <div class="custom-toggle-switch toggle-sm ms-2">
                         <input onchange="switchSetting('payout_enable',this.checked)" <?= $settingsModel->isPayoutEnabled()?'checked':''?> id="payout" name="toggleswitchsize" type="checkbox">
                         <label for="payout" class="label-primary"></label>
                       </div>
                   </td>
               </tr>
           </table>

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
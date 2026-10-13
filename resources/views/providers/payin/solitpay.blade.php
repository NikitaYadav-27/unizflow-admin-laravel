<?php 
$userModel = new \App\Models\User();
$users = $userModel->get();
$transactionModel = new \App\Models\Transaction();
$providerSlabModel = new \App\Models\Providerslab();
$providerSlabs = $providerSlabModel->where('provider_id', $provider['id'])->get();

?><div class="card custom-card">
  <div class="card-header d-flex justify-content-between">
    <h6 class="card-title mb-0"> <?= $provider['name'];?> </h6>
    <img style="width: 60px; height: 30px; object-fit: contain;" onerror="this.onerror=null;this.src='/assets/images/company-logos/1.png';" src="<?= $provider['logo'];?>">
  </div>
  <div class="card-body">
    <ul class="nav nav-tabs tab-style-1 d-sm-flex d-block" role="tablist">
      

      <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" data-bs-target="#settings" href="#settings">Settings</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" data-bs-target="#slabs" href="#slabs">Slabs</a>
      </li>
    </ul>
    <div class="tab-content">

      
      <div class="tab-pane px-0 border-0 show active" id="settings" role="tabpanel">
          <form action="/api/admin/providers/update/<?= $provider['id'] ?>" novalidate method="POST">
              <div class="row">
                 <div class="col-lg-4 mb-2">
                   <label class="mb-1">Provider Name <span class="text-primary">*</span></label>
                   <input class="form-control" name="name" placeholder="Enter Provider Name" value="<?= $provider['name']?>" required>
                   <div class="invalid-feedback"></div>
                 </div>  
                 
                 <div class="col-lg-4 mb-2">
                   <label class="mb-1">Gateway Name <span class="text-primary">*</span></label>
                   <input class="form-control" name="gateway" placeholder="Enter Gateway Name" value="<?= $provider['gateway']?>" required>
                   <div class="invalid-feedback"></div>
                 </div>
                 
                 <div class="col-lg-4 mb-2">
                   <label class="mb-1">Logo <span class="text-primary">(optional)</span></label>
                   <input onchange="handleFile(this)" class="form-control" type="file" accept=".png">
                 </div>
                 
                  <div class="col-lg-4 mb-2">
                   <label class="mb-1">Daily Limit <span class="text-primary">*</span></label>
                   <input class="form-control" type="number" validation="amount" name="daily_limit" placeholder="Enter Daily Limit" value="<?= $provider['daily_limit']?>" required>
                   <div class="invalid-feedback"></div>
                 </div>  
                 
                 <input type="hidden" name="logo">
                 
              </div>
              <button type="submit" class="btn btn-primary float-end mt-3">Update</button>
          </form>
          <script>
function handleFile(input) {
    const file = input.files[0];
    if (!file) return;

    if (file.type !== "image/png") {
        alert("Only .png files are allowed!");
        input.value = "";  
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        document.querySelector("input[name=logo]").value = e.target.result;
    };
    reader.readAsDataURL(file);
}
</script>
      </div>

      <div class="tab-pane px-0 border-0" id="slabs" role="tabpanel">
          <form action="/api/admin/update-provider-slab" method="POST">
             <input type="hidden" name="provider_id" value="<?= $provider['id']; ?>">
             <div class="table-responsive">
                <table class="table w-100">
                    <thead class="table-primary">
                    <tr>
                        <th style="padding: 12px 10px !important;" class="text-center">Min Amount</th>
                        <th style="padding: 12px 10px !important;" class="text-center">Max Amount</th>
                        <th style="padding: 12px 10px !important;" class="text-center">Charge Amount</th>
                        <th style="padding: 12px 10px !important;" class="text-center">Percent</th>
                        <th style="padding: 12px 10px !important;" class="text-center">GST</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php for($i = 1; $i < 7; $i ++) : ?>
                    <tr>
                        <td>
                             <input type="number" class="form-control text-center" step="0.01" value="<?php if(isset($providerSlabs[$i-1])){ echo $providerSlabs[$i-1]['min_amount']; } ?>" placeholder="Min Amount" name="min_amount_<?= $i; ?>">
                        </td>
                         <td>
                            <input type="number" class="form-control text-center" step="0.01" value="<?php if(isset($providerSlabs[$i-1])){ echo $providerSlabs[$i-1]['max_amount']; } ?>" placeholder="Max Amount" name="max_amount_<?= $i; ?>">
                        </td>
                         <td>
                            <input type="number" class="form-control text-center" step="0.01" value="<?php if(isset($providerSlabs[$i-1])){ echo $providerSlabs[$i-1]['amount']; } ?>" placeholder="Charge Amount" name="charge_amount_<?= $i; ?>">
                        </td>
                         <td class="text-center">
                            <div class="custom-toggle-switch toggle-sm d-inline-block">
                              <input id="payin-slab-percent-<?= $i; ?>" type="checkbox" value="percent" name="percent_<?= $i; ?>" <?php if(isset($providerSlabs[$i-1])){ echo $providerSlabs[$i-1]['percent'] == 1 ? "checked" : ""; } ?>>
                              <label for="payin-slab-percent-<?= $i; ?>" class="label-primary"></label>
                            </div>
                        </td>
                         <td class="text-center">
                            <div class="custom-toggle-switch toggle-sm d-inline-block">
                              <input id="payin-slab-gst-<?= $i; ?>" type="checkbox" value="gst" name="gst_<?= $i; ?>" <?php if(isset($providerSlabs[$i-1])){ echo $providerSlabs[$i-1]['gst'] == 1 ? "checked" : ""; } ?>>
                              <label for="payin-slab-gst-<?= $i; ?>" class="label-primary"></label>
                            </div>
                        </td>
                    </tr>
                    <?php endfor; ?>
                    </tbody>
                </table>
             </div>
             <button type="submit" class="btn btn-primary mt-3 float-end">Save Changes</button>
          </form>
      </div>
    </div>
  </div>
</div>

<script>

   function onRequestResponse(response){
      notify("success",response.message);
   }
   
   function onRequestError(error){
       notify("error",error.responseJSON.message);
   }
    
   
  
</script>

<style>
    .createQrDialog .swal2-title{
        text-align: left !important;
    }
    
    .swal2-input-label{
        display: none !important;
    }
    
    .nb .select2-container--default .select2-selection--single .select2-selection__rendered{
        border: none !important;
        padding: 0px !important;
    }
    .nb .select2-selection__arrow{
        display: none !important;
    }
</style>
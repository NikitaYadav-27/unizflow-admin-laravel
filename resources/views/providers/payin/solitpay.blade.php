<?php 
$userModel = new \App\Models\User();
$users = $userModel->get();
$transactionModel = new \App\Models\Transaction();


?><div class="card custom-card">
  <div class="card-header d-flex justify-content-between">
    <h6 class="card-title"> <?= $provider['name'];?> </h6>
    <img style="width: 60px; height: 30px;" src="
			<?= $provider['logo'];?>">
  </div>
  <div class="card-body">
    <ul class="nav nav-tabs tab-style-1 d-sm-flex d-block" role="tablist">
      

      <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" data-bs-target="#settings" href="#settings">Settings</a>
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

    // Check if file is PNG
    if (file.type !== "image/png") {
        alert("Only .png files are allowed!");
        input.value = ""; // reset file input
        return;
    }

    // Convert to Base64
    const reader = new FileReader();
    reader.onload = function(e) {
        document.querySelector("input[name=logo]").value = e.target.result;
    };
    reader.readAsDataURL(file);
}
</script>
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
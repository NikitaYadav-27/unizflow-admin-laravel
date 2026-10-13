<?php
 $providerModel = new \App\Models\Providers();
 $payouts = $providerModel->where("type", "payout")->where("active",1)->get();
 $provider_id = count($payouts) > 0 ? $payouts[0]['id'] : 0;
 if(isset($_GET['tab']) && $_GET['tab'] != ""){
     $provider_id = $_GET['tab'];
 }
 
 if (!function_exists('getProvider')) {
     function getProvider($payouts, $id){
          foreach($payouts as $provider){
             if($provider['id'] == $id){
                 return $provider;
             } 
          }
          return count($payouts) > 0 ? $payouts[0] : null;
     }
 }

 $currentProvider = getProvider($payouts, $provider_id);
?>
<div class="row">
  <div class="col-lg-3 mb-2" style="height: 86vh !important; overflow: scroll;">
      <button type="button" class="btn btn-primary-light w-100 mb-2 d-flex align-items-center justify-content-center gap-1" data-bs-toggle="modal" data-bs-target="#addProviderModal">
        <i class="ti ti-plus fs-15"></i> Add Provider
      </button>

      <?php foreach($payouts as $provider) : ?>  
      <div class="card mb-2 provider <?= ($currentProvider && $currentProvider['id'] == $provider['id']) ? "active" : ""?>" onclick="navigate('/admin/providers/payout?tab=<?= $provider['id']; ?>')">
        <div class="card-body d-flex gap-3 align-items-center">
          <div>
            <img src="<?= $provider['logo']; ?>" onerror="this.onerror=null;this.src='/assets/images/company-logos/1.png';" style="width: 60px; height: 30px; object-fit: contain;">
          </div>
          <div class="title fs-14 ms-2 fw-semibold"><?= $provider['name']; ?></div>
        </div>
      </div>
      <?php endforeach; ?>
      
 
  </div>
  <div class="col-lg-9 mb-2">
     <?php 
        if($currentProvider) {
          echo view("providers/payout/solitpay", ["provider" => $currentProvider]);
        } else {
     ?>
        <div class="card custom-card">
          <div class="card-body text-center py-5">
            <i class="ti ti-building-bank text-muted" style="font-size: 3rem;"></i>
            <h6 class="mt-3">No Payout Providers Found</h6>
            <p class="text-muted">Click the button below to add your first payout provider.</p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProviderModal">
              <i class="ti ti-plus me-1"></i> Add Provider
            </button>
          </div>
        </div>
     <?php } ?>
  
  </div>
</div>

<style>
    .card.provider{
       filter: grayscale(1) !important;
       cursor: pointer;
    }
    .card.provider .title{
        color: gray;
    }
    
    .card.provider.active {
        filter: grayscale(0) !important;
        border: 2px solid rgb(var(--primary-rgb));
    }
    
    .card.provider.active .title{
        color: rgb(var(--primary-rgb)) !important;
    }
</style>

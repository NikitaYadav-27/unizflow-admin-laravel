<?php
 $providerModel = new \App\Models\Providers();
 $payins = $providerModel->where("type", "payin")->where("active",1)->get();
 $provider_id = $payins[0]['id'];
 if(isset($_GET['tab']) && $_GET['tab'] != ""){
     $provider_id = $_GET['tab'];
 }
 
 
 function getProvider($payins,$id){
      foreach($payins as $provider){
         if($provider['id'] == $id){
             return $provider;
         } 
      }
 }

?>


<div class="row">
  <div class="col-lg-3 mb-2" style="height: 86vh !important; overflow: scroll;">
   
      <?php foreach($payins as $provider) : ?>  
      <div class="card mb-2 provider <?= $provider_id == $provider['id']?"active":""?>" onclick="navigate('/admin/providers/payin?tab=<?= $provider['id']; ?>')">
        <div class="card-body d-flex gap-3 align-items-center">
          <div>
            <img src="<?= $provider['logo']; ?>" style="width: 60px; height: 30px;">
          </div>
          <div class="title fs-14 ms-2 fw-semibold"><?= $provider['name']; ?></div>
        </div>
      </div>
      <?php endforeach; ?>
      
 
  </div>
  <div class="col-lg-9 mb-2">
     <?php 
        if($provider_id == 1) {
          echo view("providers/payin/solitpay",["provider" => getProvider($payins,$provider_id)]);
        } else if($provider_id == 3) {
          echo view("providers/payin/solitpay",["provider" => getProvider($payins,$provider_id)]);
        }
     ?>
  
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

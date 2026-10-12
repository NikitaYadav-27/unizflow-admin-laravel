<?php if (!isset($_GET['turbo'])) {
    $page = $type."_providers";
    echo view('header', get_defined_vars());
} ?>

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-building-bank me-1 fs-15"></i>Providers</a></li>
        <li class="breadcrumb-item active" aria-current="page">
            <?php if($type == "payin") : ?>
              <i class="ti ti-arrow-down-left me-1 fs-15"></i> Payin Providers
            <?php elseif($type == "payout") : ?>
             <i class="ti ti-arrow-up-right me-1 fs-15"></i> Payout Providers
            <?php elseif($type == "qr") : ?>
             <i class="ti ti-qrcode me-1 fs-15"></i> QR Providers
            <?php endif; ?>
            
        </li>
    </ol>
</nav>


    
    <?php echo view("providers/".$type); ?>



<?php if (!isset($_GET['turbo'])) {
    echo view('footer', get_defined_vars());
} ?>
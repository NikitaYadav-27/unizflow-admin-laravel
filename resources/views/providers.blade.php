<?php if (!isset($_GET['turbo'])) {
    $page = $type."_providers";
    echo view('header', get_defined_vars());
} ?>

<div class="d-flex justify-content-between align-items-center my-4 flex-wrap gap-2">
    <nav aria-label="breadcrumb">
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
    <div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProviderModal">
            <i class="ti ti-plus me-1"></i> Add <?= ucfirst($type); ?> Provider
        </button>
    </div>
</div>

<?php echo view("providers/".$type); ?>

<div class="modal fade" id="addProviderModal" tabindex="-1" aria-labelledby="addProviderModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="addProviderModalLabel">Add New <?= ucfirst($type); ?> Provider</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="/api/admin/providers/create" method="POST" novalidate>
        <div class="modal-body">
            <input type="hidden" name="type" value="<?= $type; ?>">
            <input type="hidden" name="logo" id="new_provider_logo">

            <div class="mb-3">
                <label class="form-label">Provider Name <span class="text-danger">*</span></label>
                <input class="form-control" name="name" placeholder="Enter Provider Name" required>
                <div class="invalid-feedback"></div>
            </div>

            <div class="mb-3">
                <label class="form-label">Gateway Name <span class="text-danger">*</span></label>
                <input class="form-control" name="gateway" placeholder="Enter Gateway Name" required>
                <div class="invalid-feedback"></div>
            </div>

            <div class="mb-3">
                <label class="form-label">Daily Limit <span class="text-danger">*</span></label>
                <input class="form-control" type="number" validation="amount" name="daily_limit" placeholder="Enter Daily Limit" value="0" required>
                <div class="invalid-feedback"></div>
            </div>

            <div class="mb-3">
                <label class="form-label">Logo <span class="text-muted">(PNG, optional)</span></label>
                <input onchange="handleNewProviderFile(this)" class="form-control" type="file" accept=".png">
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Create Provider</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function handleNewProviderFile(input) {
    const file = input.files[0];
    if (!file) return;
    if (file.type !== "image/png") {
        alert("Only .png files are allowed!");
        input.value = "";
        return;
    }
    const reader = new FileReader();
    reader.onload = function(e) {
        document.querySelector("#new_provider_logo").value = e.target.result;
    };
    reader.readAsDataURL(file);
}

function onRequestResponse(response){
    notify("success", response.message);
    if (response.provider_id) {
        try {
            const modalEl = document.getElementById('addProviderModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            $('.modal-backdrop').remove();
        } catch(e){}
        setTimeout(function(){
            navigate('/admin/providers/' + response.type + '?tab=' + response.provider_id);
        }, 300);
    }
}

function onRequestError(error){
    notify("error", error.responseJSON ? error.responseJSON.message : "An error occurred");
}
</script>

<?php if (!isset($_GET['turbo'])) {
    echo view('footer', get_defined_vars());
} ?>
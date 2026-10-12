<?php if (!isset($_GET['turbo'])) {
    $page = "add_package";
    echo view('header', get_defined_vars());
} ?>

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-box me-1 fs-15"></i>Packages</a></li>
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-edit me-1 fs-15"></i>Edit Package</li>
    </ol>
</nav>


<div class="row">
    <div class="col-lg-4">
        <div class="card custom-card">
            <div class="card-header">
                <h6 class="card-title"><i class="ti ti-circle-plus me-1 fs-15"></i>&nbsp;&nbsp;Edit Package</h6>
            </div>
           <div class="card-body">
               <form action="/api/admin/package/update" method="POST">
                   <input type="hidden" name="package_id" value="<?= $package_id; ?>">
                   <div class="form-group">
                       <label class="mb-1">Package Name <span class="text-primary">*</span></label>
                       <input value="<?= $package['name']; ?>" type="text" name="name" required class="form-control" placeholder="Enter Name">
                   </div>
                   
                   <button type="submit" class="btn btn-primary mt-3 float-end btn-sm"><i class="ti ti-edit"></i>&nbsp;&nbsp;Update Package</button>
               </form>
           </div>
        </div>
    </div>
</div>





<script>

   function onRequestResponse(response){
      notify("success",response.message,() => {
          navigate("/admin/packages");
      });
   }
   
   function onRequestError(error){
       notify("error",error.responseJSON.message);
   }

</script>



<?php if (!isset($_GET['turbo'])) {
    echo view('footer', get_defined_vars());
} ?>
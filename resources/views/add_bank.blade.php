<?php if (!isset($_GET['turbo'])) {
    $page = "banks";
    echo view('header', get_defined_vars());
}

?>

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-cash me-1 fs-15"></i>Fund Requests</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-building-bank me-1 fs-15"></i>Banks</a></li>
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-circle-plus me-1 fs-15"></i>Add Bank</li>
    </ol>
</nav>


<div class="row">
    <div class="col-lg-8 col-md-12 col-sm-12 mb-2">
        <div class="card custom-card h-100 mb-2">
            <div class="card-header">
                <h5 class="card-title"><i class="ti ti-circle-plus me-1 fs-15"></i> Add Bank</h5>
            </div>
            <div class="card-body">
                <form action="/api/admin/banks/add" method="POST" novalidate>
                    <div class="row">
                        
                        <div class="col-lg-4">
                            <div class="form-group mb-2">
                                <label class="mb-1">Name <span class="text-primary">*</span></label>

                                <input  type="text" required
                                    class="form-control rounded-end" id="name" name="name"
                                    placeholder="Name">
                                <div class="invalid-feedback"></div>

                            </div>
                        </div>

                       
                        <div class="col-lg-4">
                            <div class="form-group mb-2">
                                <label class="mb-1">Account Name <span class="text-primary">*</span></label>

                                <input  type="text" required
                                    class="form-control rounded-end" id="acname" name="acname"
                                    placeholder="Account Name">
                                <div class="invalid-feedback"></div>

                            </div>
                        </div>


                        <div class="col-lg-4">
                            <div class="form-group mb-2">
                                <label class="mb-1">Account Number <span class="text-primary">*</span></label>

                                <input validation="account" type="text" class="form-control rounded-end"
                                    id="account_number" name="account_number" placeholder="Account Number" required>
                                <div class="invalid-feedback"></div>

                            </div>
                        </div>

                      

                        <div class="col-lg-4">
                            <div class="form-group mb-2">
                                <label class="mb-1">IFSC Code <span class="text-primary">*</span></label>

                                <input oninput="ifsc_check(this)" validation="ifsc" type="text"
                                    class="form-control rounded-end" id="ifsc_code" name="ifsc_code"
                                    placeholder="IFSC Code" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>


                        <div class="col-lg-4">
                            <div class="form-group mb-2">
                                <label class="mb-1">Bank <span class="text-primary">*</span></label>
                                <input type="text" readonly name="bank_name" id="bank_name"
                                    class="form-control rounded-end" placeholder="Bank Name" required>
                                <div class="invalid-feedback text-muted"></div>
                            </div>
                        </div>


                       <div class="col-lg-4">
                            <div class="form-group mb-2">
                                <label class="mb-1">Branch <span class="text-primary">*</span></label>
                                <input type="text" readonly name="branch_name" id="branch_name"
                                    class="form-control rounded-end" placeholder="Branch Name" required>
                                <div class="invalid-feedback text-muted"></div>
                            </div>
                        </div>



                    </div>

                    <button class="btn btn-primary float-end mt-3" type="submit"><i
                            class="ti ti-circle-plus"></i>&nbsp;Add Bank</button>
                </form>
            </div>
        </div>
    </div>

   


    
</div>




<script>
    function ifsc_check(input) {
        try {
            let ifsc = input.value;
            if (ifsc.length == 11) {
                $.ajax({
                    url: 'https://ifsc.razorpay.com/' + ifsc,
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                       $('#bank_name').val(response.BANK);
                       $('#branch_name').val(response.BRANCH);

                    }
                });
            } else {
                $("#bank_name").val("");
                $("#branch").html("");
                $("#branch").removeClass("d-flex");
            }
        } catch (e) {
            console.log(e);
        }
    }

   
    
    function onRequestResponse(response) {
       notify("success",response.message,() => {
           navigate('/admin/fund-request-banks');
       });
       try{document.querySelector("form").reset();}catch(e){}
    }

    function onRequestError(error) {
       notify("error",error.responseJSON.message);
    }
</script>





<?php if (!isset($_GET['turbo'])) {
    echo view('footer', get_defined_vars());
} ?>
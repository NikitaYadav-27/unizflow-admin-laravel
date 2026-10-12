<?php if (!isset($_GET['turbo'])) {
    $page = "user";
    echo view('header', get_defined_vars());
} ?>

    <nav aria-label="breadcrumb" class="my-4">
        <ol class="breadcrumb breadcrumb-style2 mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-users me-1 fs-15"></i>Users</a></li>
            <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-user me-1 fs-15"></i>
                <?= $user['name']; ?>
            </li>
        </ol>
    </nav>


    <div class="row">
        <div class="col-lg-4">
            <div class="card custom-card overflow-hidden">
                <div class="card-body p-0">
                    <div class="d-sm-flex align-items-top p-4 border-bottom-0 main-profile-cover">
                        <div>
                            <span class="avatar avatar-xxl avatar-rounded online me-3 bg-white text-primary fw-bold">
                                            <?php 
                                  //initials of name
                                    $name = $user['name'];
                                    $name = explode(" ", $name);
                                    $initials = "";
                                    foreach ($name as $n) {
                                        $initials .= strtoupper($n[0]);
                                    }
                                    echo $initials;
                                ?>
                                        </span>
                        </div>
                        <div class="flex-fill main-profile-info">
                            <div class="d-flex align-items-center justify-content-between">
                                <h6 class="fw-semibold mb-1 text-fixed-white"><?= $user['name']; ?></h6>

                            </div>
                            <p class="mb-1 text-muted text-fixed-white op-7">
                                <?= $user['email']; ?>
                            </p>
                            <p class="fs-12 text-fixed-white mb-4 op-5">
                                <span class="me-3"><i class="ri-profile-line me-1 align-middle"></i><?= $user['merchant_id']; ?></span>
                                <span><i class="ri-smartphone-line me-1 align-middle"></i>+91 <?= $user['phone']; ?></span>
                            </p>
                            <div class="d-flex mb-0">
                                <div class="me-4">
                                    <p class="fw-bold fs-20 text-fixed-white text-shadow mb-0">₹
                                        <?= $user['wallet']?>
                                    </p>
                                    <p class="mb-0 fs-11 op-5 text-fixed-white"><i class="ri-wallet-2-line"></i> Wallet Balance</p>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="p-4 border-bottom border-block-end-dashed">
                        <p class="fs-15 mb-2 me-4 fw-semibold">Contact Information :</p>
                        <div class="text-muted">

                            <p class="mb-2">
                                <span class="avatar avatar-sm avatar-rounded me-2 bg-primary-transparent ">
                                                <i class="ri-profile-line align-middle fs-14"></i>
                                            </span>
                                <?= $user['merchant_id'] ?>
                            </p>

                            <p class="mb-2">
                                <span class="avatar avatar-sm avatar-rounded me-2 bg-primary-transparent ">
                                                <i class="ri-mail-line align-middle fs-14"></i>
                                            </span>
                                <?= $user['email']; ?>
                            </p>
                            <p class="mb-0">
                                <span class="avatar avatar-sm avatar-rounded me-2 bg-primary-transparent ">
                                                <i class="ri-phone-line align-middle fs-14"></i>
                                            </span> +91
                                <?= $user['phone']; ?>
                            </p>

                        </div>
                    </div>







                  

                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card custom-card">

                <div class="card-body">
                    <ul class="nav nav-pills justify-content-start nav-style-2 mb-3" role="tablist">
                        
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" role="tab" aria-current="page" href="#overview" aria-selected="true"><i class="ti ti-report"></i>&nbsp;Controls</a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link " data-bs-toggle="tab" role="tab" aria-current="page" href="#info" aria-selected="true"><i class="ti ti-user"></i>&nbsp;User Info</a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link " data-bs-toggle="tab" role="tab" aria-current="page" href="#manageFunds" aria-selected="true"><i class="ti ti-credit-card"></i>&nbsp;Manage Funds</a>
                        </li>
                        
                          <li class="nav-item">
                            <a class="nav-link " data-bs-toggle="tab" role="tab" aria-current="page" href="#banks" aria-selected="true"><i class="ti ti-building-bank"></i>&nbsp;Banks</a>
                        </li>
                        
                       
                        
                    </ul>
                    <div class="tab-content p-0">
                         <div class="tab-pane show active text-muted" id="overview" role="tabpanel">
                            <form action="/api/admin/update-user" method="POST">
                              <div class="row">
                                <div class="col-lg-4">
                                  <div class="form-group mb-2">
                                    <label class="mb-1">Package <span class="text-primary">*</span></label>
                                    <select class="form-control" data-placeholder="Select Package" name="package">
                                        <option></option>
                                        <?php foreach((new \App\Models\Package())->get() as $package) : ?>
                                          <option <?= $package['id'] == $user['package']?"selected":""?> value="<?= $package['id'];?>"><?= $package['name']?></option>
                                        <?php endforeach; ?>
                                    </select>
                                  </div>
                                </div>
                                <input type="hidden" name="user_id" value="<?= $user['id'];?>">
                                 <div class="col-lg-4">
                                  <div class="form-group mb-2">
                                    <label class="mb-1">Payin Provider <span class="text-primary">*</span></label>
                                    <select class="form-control" data-placeholder="Select Payin Provider" name="payin_provider">
                                        <option></option>
                                        <?php foreach((new \App\Models\Providers())->where("type","payin")->get() as $provider) : ?>
                                          <option <?= $provider['id'] == $user['payin_provider']?"selected":""?> value="<?= $provider['id'];?>"><?= $provider['name']?></option>
                                        <?php endforeach; ?>
                                    </select>
                                  </div>
                                </div>
                                
                                <div class="col-lg-4">
                                  <div class="form-group mb-2">
                                    <label class="mb-1">Payout Provider <span class="text-primary">*</span></label>
                                    <select class="form-control" data-placeholder="Select Payout Provider" name="payout_provider">
                                        <option></option>
                                        <?php foreach((new \App\Models\Providers())->where("type","payout")->get() as $provider) : ?>
                                          <option <?= $provider['id'] == $user['payout_provider']?"selected":""?> value="<?= $provider['id'];?>"><?= $provider['name']?></option>
                                        <?php endforeach; ?>
                                    </select>
                                  </div>
                                </div>
                                
                                
                                <div class="col-lg-4">
                                  <div class="form-group mb-2">
                                    <label class="mb-1">Daily Payin Limit <span class="text-primary">*</span></label>
                                    <input class="form-control" value="<?= $user['payin_limit']?>" name="payin_limit" type="number" placeholder="Enter Payin Limit">
                                  </div>
                                </div>
                                
                                <div class="col-lg-4">
                                  <div class="form-group mb-2">
                                    <label class="mb-1">Daily Payout Limit <span class="text-primary">*</span></label>
                                    <input class="form-control"  value="<?= $user['payout_limit']?>" name="payout_limit" type="number" placeholder="Enter Payout Limit">
                                  </div>
                                </div>
                                
                                <div class="col-lg-4">
                                  <div class="form-group mb-2">
                                    <label class="mb-1">Payout Per Transaction Limit <span class="text-primary">*</span></label>
                                    <input class="form-control"  value="<?= $user['payout_per_limit']?>" name="payout_per_limit" type="number" placeholder="Enter Per Transaction Limit">
                                  </div>
                                </div>
                                
                                
                                
                                <div class="col-lg-4">
                                  <div class="form-group mb-2">
                                    <label class="mb-1">Settlement Package <span class="text-primary">*</span></label>
                                    <select class="form-control" data-placeholder="Settlement Package" name="settlement_package">
                                        <option></option>
                                        <?php foreach((new \App\Models\SettlementPackage())->get() as $package) : ?>
                                          <option <?= $package['id'] == $user['settlement_package']?"selected":""?> value="<?= $package['id'];?>"><?= $package['name']?></option>
                                        <?php endforeach; ?>
                                    </select>
                                  </div>
                                </div>
                                
                                
                                
                              </div>  
                              <div class="d-flex w-100 justify-content-end">
                                  <button type="submit" class="btn btn-primary mt-3">Update Changes</button>
                              </div>
                              
                            </form>
                        </div>
                        
                        <div class="tab-pane text-muted" id="info" role="tabpanel">
                            <form action="/api/admin/update-user-info" method="POST" novalidate>
                              <div class="row">
                                <input type="hidden" name="user_id" value="<?= $user['id'];?>">
                                <div class="col-lg-4">
                                  <div class="form-group mb-2">
                                    <label class="mb-1">Name <span class="text-primary">*</span></label>
                                    <input class="form-control" validation="first-last-name" value="<?= $user['name']?>" required name="name" placeholder="Enter Name">
                                    <div class="invalid-feedback"></div>
                                  </div>
                                </div>
                                
                                <div class="col-lg-4">
                                  <div class="form-group mb-2">
                                    <label class="mb-1">Email <span class="text-primary">*</span></label>
                                    <input class="form-control" validation="email" value="<?= $user['email']?>" required name="email" placeholder="Enter Email">
                                    <div class="invalid-feedback"></div>
                                  </div>
                                </div>
                                
                                <div class="col-lg-4">
                                  <div class="form-group mb-2">
                                    <label class="mb-1">Phone <span class="text-primary">*</span></label>
                                    <input class="form-control" validation="phone" value="<?= $user['phone']?>" required name="phone" placeholder="Enter Phone">
                                    <div class="invalid-feedback"></div>
                                  </div>
                                </div>
                                
                                <div class="col-lg-4">
                                  <div class="form-group mb-2">
                                    <label class="mb-1">Password <span class="text-primary">*</span></label>
                                    <input class="form-control" type="password" validation="password" placeholder="Leave blank to keep current" autocomplete="new-password"  name="password" placeholder="Enter Phone">
                                    <div class="invalid-feedback"></div>
                                  </div>
                                </div>
                                
                                
                                
                                
                                
                               
                                
                                
                              </div>  
                              <div class="d-flex w-100 justify-content-end">
                                  <button type="submit" class="btn btn-primary mt-3">Update Changes</button>
                              </div>
                              
                            </form>
                        </div>
                        
                        <div class="tab-pane text-muted" id="manageFunds" role="tabpanel">
                            <form action="/api/admin/user/fund-manage" id="manageFundsForm" method="POST" novalidate>
                              <div class="row">
                                <input type="hidden" name="user_id" value="<?= $user['id'];?>">
                                 <div class="row">
                                     
                                     
                                     
                                <div class="col-lg-4">
                                  <div class="form-group mb-2">
                                    <label class="mb-1">Action <span class="text-primary">*</span></label>
                                    <select class="form-control" data-placeholder="Select Action" name="action" required>
                                        <option></option>
                                        <option value="DEBIT">Debit Funds</option>
                                        <option value="HOLD">Hold Funds</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                  </div>
                                </div>
                                
                                <div class="col-lg-4">
                                  <div class="form-group mb-2">
                                    <label class="mb-1">Amount <span class="text-primary">*</span></label>
                                    <input class="form-control" validation="amount" required name="amount" type="number" placeholder="Enter Amount">
                                     <div class="invalid-feedback"></div>
                                  </div>
                                </div>
                                
                                <div class="col-lg-4">
                                  <div class="form-group mb-2">
                                    <label class="mb-1">Remarks <span class="text-primary">*</span></label>
                                    <input class="form-control" validation="remarks" required name="remarks"  placeholder="Enter Remarks">
                                     <div class="invalid-feedback"></div>
                                  </div>
                                </div>
                                
                                
                                
                                
                                </div>
                                
                                
                                
      
                                
                              </div>  
                              <div class="d-flex w-100 justify-content-end">
                                  <button type="submit" class="btn btn-primary mt-3">Update Changes</button>
                              </div>
                              
                            </form>
                        </div>
                        
                        
                        
                        <div class="tab-pane text-muted" id="banks" role="tabpanel">
                            <!--<div class="table-responsive">-->
            <table class="table text-nowrap table-bordered" id="banksTable">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Account</th>
                        <th scope="col">IFSC</th>
                        <th scope="col">Bank</th>
                        <th scope="col">Branch</th>
                        <th scope="col">Status</th>
                        <th scope="col">Created At</th>
                        <th scope="col">Actions</th>
                      
                    </tr>
                </thead>
                <tbody>
                    <?php $index =1; foreach(\Illuminate\Support\Facades\DB::connection()->table('users_bank')->where('user_id', $user['id'])->orderBy('id', 'DESC')->get()->map(fn($item) => (array)$item) as $bank) : ?>
                    <tr>
                        <td><?= $index; ?></td>
                        <td><?= $bank['account_name']?></td>
                        <td><?= $bank['account_number']?></td>
                        <td><?= $bank['account_ifsc']?></td>
                        <td><?= $bank['bank_name']?></td>
                        <td><?= $bank['branch']?></td>
                        <td>
                            <?php if($bank['status'] == "Pending") :  ?>
                            <span class="badge bg-warning-transparent">Pending</span>
                            <?php elseif($bank['status'] == "Approved") : ?>
                            <span class="badge bg-success-transparent">Approved</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $bank['created_at']?></td>
                        <td>
                            <?php if($bank['status'] == "Pending") :  ?>
                              <div class="d-flex gap-2 align-items-center">
                                  <button onclick="updateBank(<?= $bank['id']?>,'approve')" class="btn btn-sm btn-success-transparent"><i class="ti ti-check"></i></button>
                                  <button onclick="updateBank(<?= $bank['id']?>,'reject')" class="btn btn-sm btn-danger-transparent"><i class="ti ti-x"></i></button>
                              </div>
                            <?php elseif($bank['status'] == "Approved") : ?>
                            NA
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php $index++; endforeach; ?>
                </tbody>
            </table>
        <!--</div>-->
                        </div>
                        
                       
                      
                    </div>
                </div>
            </div>
        </div>
    </div>




    <script>
    
    function updateBank(id,status){
        let actionText = (status === "approve") ? "Approve" : "Reject";
        let confirmBtnColor = (status === "approve") ? "#28a745" : "#dc3545";

    Swal.fire({
        title: `Are you sure?`,
        text: `Do you really want to ${actionText} this bank account?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: confirmBtnColor,
        cancelButtonColor: "#6c757d",
        confirmButtonText: `Yes, ${actionText} it!`
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("/api/admin/user/profile/update-bank-status", {
                id: id,
                status: status
            })
            .done((data) => {
                Swal.fire("Success!", `Bank account ${actionText}d successfully!`, "success")
                .then(() => {
                    location.reload();
                });
            })
            .fail((xhr) => {
                Swal.fire("Error!", xhr.responseJSON?.message || "Something went wrong!", "error");
            });
        }
    });
    }
    
   function onRequestResponse(response){
      notify("success",response.message,() => {
          try{
            document.querySelector('#manageFundsForm').reset();
          }catch(e){
              
          }
      });
   }
   
   function onRequestError(error){
       notify("error",error.responseJSON.message);
   }

function load(){
    $("#banksTable").dataTable({
       "ordering": false
    });
}

    </script>
    <script class="afterLoadScript" type="text/plain">
         load();
    </script>

    <style>
    
    .dt-layout-full{
       width: 100%;
       overflow: scroll;
    }
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
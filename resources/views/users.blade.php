<?php if (!isset($_GET['turbo'])) {
    $page = "$status-users";
    echo view('header', get_defined_vars());
} ?>

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-users me-1 fs-15"></i>Users</a></li>
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-<?php 
         
         if($status=="active"){
             echo "circle-check";
         }elseif($status == "inactive"){
             echo "circle-minus";
         }else{
             echo "lock";
         }
        
        ?> me-1 fs-15"></i><?= ucwords($status); ?> Users</li>
    </ol>
</nav>



<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-2 flex-wrap gap-2">

            <div class="me-2">
                <select onchange="changeLength(this.value)" class="form-select" id="pageSize"
                    onchange="window.pageSize = this.value; getUsers(window.pageNo,window.pageSize,window.searchBy,window.searchValue)">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option selected value="100">100</option>
                </select>
            </div>

            <div class="d-flex">

                <div class="me-2">
                    <select class="form-select" id="searchBy" onchange="changeSearchBy(this.value)">
                        <option value="">Select</option>
                        <option value="merchant_id">Mechant ID</option>
                        <option value="name">Name</option>
                        <option value="email">Email</option>
                        <option value="phone">Phone</option>
                        <option value="wallet">Wallet</option>
                    </select>
                </div>
                <div class="me-2">
                    <input type="text" onkeyup="changeSearchValue(this.value)" class="form-control" id="searchValue"
                        placeholder="Search">
                </div>
                
                
                <div class="me-2" style="display: none;" id="bulkActionCorner">
                    <div class="d-flex align-items-center gap-2">
                        
                   
                    <select class="form-select" id="bulkAction" onchange="bulkActionChange(this.value);" data-placeholder="Select Action">
                        <option></option>
                        <option value="payin_provider">Update Payin Provider</option>
                        <option value="payout_provider">Update Payout Provider</option>
                        <option value="status">Update Status</option>
                        <option value="payout">Update Payout Status</option>
                        <option value="payin">Update Payin Status</option>
                    </select>
                    <div style="display: none;" id="payinProviders">
                    <select class="form-select"  data-placeholder="Select Payin Provider" onchange="changeValue(this.value,'payinProviders')">
                        <option></option>
                        <?php foreach((new \App\Models\Providers())->where('type', 'payin')->get() as $provider) : ?>
                          <option value="<?= $provider['id']; ?>"><?= $provider['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    </div>
                    <div style="display: none;" id="payoutProviders">
                    <select class="form-select"  data-placeholder="Select Payout Provider" onchange="changeValue(this.value,'payoutProviders')">
                        <option></option>
                         <?php foreach((new \App\Models\Providers())->where('type', 'payout')->get() as $provider) : ?>
                          <option value="<?= $provider['id']; ?>"><?= $provider['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    </div>
                    <div style="display: none;" id="status">
                    <select class="form-select"  data-placeholder="Select Status" onchange="changeValue(this.value,'status')">
                        <option></option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    </div>
                    
                     </div>
                </div>
                


            </div>
        </div>

        <div class="table-responsive">
            <table class="table text-nowrap table-bordered" id="dataTable">
                <thead class="table-primary">
                    <tr>
                        <th scope="col"><input class="form-check-input" onclick="selectAll(this);" id="selectAll" type="checkbox"></th>
                        <th scope="col">ID</th>
                        <th scope="col">Lock</th>
                        <th scope="col">Merchant ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Email</th>
                        <th scope="col">Wallet</th>
                        <th scope="col">Package</th>
                        <th scope="col">Active</th>
                        <th scope="col">Payin</th>
                        <th scope="col">Payout</th>
                        <th scope="col">Today's Collection</th>
                        <th scope="col">Settlement</th>
                        <th scope="col">Actions</th>
                        <!--<th scope="col">Actions</th>-->
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>


    </div>
</div>

<style>
    .select2-selection__arrow{
        display: none !important;
    }
</style>


<script>

    function changeValue(value,id) {
        let selectedUsers = [];
        document.querySelectorAll("input.user-select").forEach((input) => {
            if(input.checked) {
                selectedUsers.push(parseInt(input.getAttribute("data-uid")));
            }
        });
        let actionName = document.querySelector("#bulkAction").selectedOptions[0].innerHTML;
        let actionValue = document.querySelector("#"+id+" select").selectedOptions[0].innerHTML;
        Swal.fire({
           title: 'Continue with Following Bulk Action ?',
           html: `
               <b>Action Name:</b> ${actionName}<br>
               <b>Action Value:</b> ${actionValue}
           `,
           icon: 'warning',
           showCancelButton: true,
           confirmButtonText: 'Continue',
           cancelButtonText: 'Cancel',
           confirmButtonColor: '#d33',
           cancelButtonColor: '#3085d6',
       }).then((result) => {
           if (result.isConfirmed) {
              $.ajax({
                url: "/api/admin/user/bulk-action",
                method: "POST",
                contentType: "application/json",
                data: JSON.stringify({
                    action: $("#bulkAction").val(),
                    value: value,
                    users: selectedUsers
                }),
                success: function(data) {
                    navigate(location.pathname, false, () => {
                        notify("success", data.message);
                    });
                },
                error: function(xhr) {
                    notify("error", xhr.responseJSON?.message || "Something went wrong");
                }
              });
           }
       });
    }

    function bulkActionChange(value) {
        if(value == "payin_provider") {
            $("#payinProviders").show();
            $("#payoutProviders").hide();
            $("#status").hide();
        }else if(value == "payout_provider") {
            $("#payinProviders").hide();
            $("#payoutProviders").show();
            $("#status").hide();
        }else if(value == "payout" || value == "status" || value == "payin"){
            $("#payinProviders").hide();
            $("#payoutProviders").hide();
            $("#status").show();
        }
        
    }

    function bulkActionChecker(){
         let totalChecked = document.querySelectorAll("input.user-select:checked").length;
         if(totalChecked >= 1) {
             $("#bulkActionCorner").show();
         }else{
             $("#bulkActionCorner").hide();
         }
    }

    function selectAll(ele) {
        document.querySelectorAll("input.user-select").forEach((checkbox) => {
            if(ele.checked){
                checkbox.checked = true;
            }else{
                checkbox.checked = false;
            }
        });
        bulkActionChecker();
    }

    function selectUser(input) {
       let totalCheckbox = document.querySelectorAll("input.user-select").length;
       let totalChecked = document.querySelectorAll("input.user-select:checked").length;
       if(totalChecked == totalCheckbox){
           document.querySelector("#selectAll").checked = true;
       }else{
           document.querySelector("#selectAll").checked = false;
       }
       bulkActionChecker();
    }

   function loginUser(uid,btn) {
       
       btn.disabled = true;
       btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
       $.post("/api/admin/login/user",{
           uid : uid
       },(data) => {
           notify("success","Session Generated Successfully !",() => {
               window.open('<?= env("frontendURL"); ?>auth/login/token?ad_token='+data.token);
               btn.disabled = false;
               btn.innerHTML = '<i class="ti ti-login"></i>';
           });
       }).fail((xhr) => {
           let error = xhr.responseJSON.message;
           notify("error",error);
       });
   }
   
   function lockSwitch(user_id){
      $.post("/api/users/update-lock-switch",{ user_id : user_id },(data) => {
           notify("success",data.message,() => {
              navigate('<?= $status == "active"?"/admin/users/active":"/admin/users/inactive"?>');
           });
       }).fail((xhr) => {
           let error = xhr.responseJSON.message;
           notify("error",error);
      }); 
   }

   function changeSwitch(user_id,service,value) {
       $.post("/api/users/update-switch",{ user_id : user_id , service : service, value : value?1:0 },(data) => {
           notify("success",data.message,() => {
               if(service == "active"){
                   navigate('<?= $status == "active"?"/admin/users/active":"/admin/users/inactive"?>');
               }
           });
       }).fail((xhr) => {
           let error = xhr.responseJSON.message;
           notify("error",error);
       });
   }

    window.dataTable = null;
    window.searchBy = "";
    window.searchValue = "";


    function getUsers() {
        window.dataTable = $("#dataTable").dataTable(
            {
                "bFilter": false,
                "bLengthChange": false,
                "serverSide": true,
                "processing": true,
                "paging": true,
                "ordering" : false,
                "pageLength": 100,
                "ajax": {
                    "url": "/api/tables/users?status=<?= $status ?>",
                    "type": "GET",
                    "data": function (d) {
                        d.searchBy = window.searchBy;
                        d.searchValue = window.searchValue;
                    }
                }
            }
        );

        document.querySelector(".card-body").append(document.querySelectorAll("#dataTable_wrapper .row")[2])
    }

    function changeLength(value) {
        window.dataTable.DataTable().page.len(value).draw();
    }

    function changeSearchBy(value) {
        window.searchBy = value;
        //refresh data
        if (window.searchValue != "") {
            window.dataTable.DataTable().ajax.reload();
        }

    }

    function changeSearchValue(value) {
        window.searchValue = value;
        //refresh data
        if (window.searchBy != "") {
            window.dataTable.DataTable().ajax.reload();
        }

    }

</script>
<script class="afterLoadScript" type="text/plain">
    getUsers();
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
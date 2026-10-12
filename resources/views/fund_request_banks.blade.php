<?php if (!isset($_GET['turbo'])) {
    $page = "banks";
    echo view('header', get_defined_vars());
}

?>

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-cash me-1 fs-15"></i>Fund Requests</a></li>
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-building-bank me-1 fs-15"></i>Banks</li>
    </ol>
</nav>



<div class="card">
    <div class="card-body">
         <div class="d-flex justify-content-between mb-2 flex-wrap gap-2">

            <div class="me-2">
                <select onchange="changeLength(this.value)" class="form-select" id="pageSize"
                    onchange="window.pageSize = this.value; getTransactions(window.pageNo,window.pageSize,window.searchBy,window.searchValue)">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

            <div class="d-flex">
                
                <div class="me-2">
                    <select class="form-select" id="searchBy" onchange="changeSearchBy(this.value)">
                        <option value="">Select</option>
                        <option value="1">Name</option>
                        <option value="2">Account Name</option>
                        <option value="3">Account Number</option>
                        <option value="4">IFSC Code</option>
                        <option value="5">Bank</option>
                        <option value="6">Branch</option>
                    </select>
                </div>
                
                <div class="me-2">
                    <input type="text" onkeyup="changeSearchValue(this.value)" class="form-control"
                        placeholder="Search">
                </div>
                
                <button onclick="navigate('/admin/fund-request-banks/add')" class="btn btn-primary" style="padding: .25rem .75rem !important;"><i class="ti ti-circle-plus"></i>&nbsp;&nbsp;Add bank</button>


            </div>
        </div>

        <div class="table-responsive">
            <table class="table text-nowrap table-bordered" id="dataTable">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Account Name</th>
                        <th scope="col">Account Number</th>
                        <th scope="col">IFSC Code</th>
                        <th scope="col">Bank</th>
                        <th scope="col">Branch</th>
                        <th scope="col">Status</th>
                        <th scope="col">Users</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $index = 1; foreach((new \App\Models\FundRequestBank())->get() as $bank) :  ?>
                      <tr>
                        <td><?= $index; ?></td>
                        <td><?= $bank['name']; ?></td>
                        <td><?= $bank['account_holder_name']; ?></td>
                        <td><?= $bank['account_number']; ?></td>
                        <td><?= $bank['ifsc_code']; ?></td>
                        <td><?= $bank['bank']; ?></td>
                        <td><?= $bank['branch']; ?></td>
                        <td>
                            <div class="custom-toggle-switch toggle-sm ms-2">
                               <input onchange="changeSwitch('<?= $bank['id']; ?>',this.checked);" id="bank-<?= $index ?>" name="toggleswitchsize" type="checkbox" <?= $bank['active'] == 1?"checked":""; ?>>
                               <label for="bank-<?= $index ?>" class="label-primary"></label>
                            </div>
                        </td>
                        <td>
                            <button onclick="navigate('/admin/bank-users/<?= bin2hex(openssl_encrypt($bank['id'], 'AES-256-CBC', env("encryption.key"), OPENSSL_RAW_DATA, substr(hash('sha256', env("encryption.key")), 0, 16))) ?>')" class="btn btn-sm btn-success-transparent"><i class="ti ti-users"></i> <?= count(explode(",",$bank['users'])) ?></button>
                        </td> 
                      </tr>
                    <?php $index++; endforeach; ?>
                </tbody>
            </table>
        </div>


    </div>
</div>

<script>
  window.dataTable = null;
  window.searchIndex = "";
  window.searchValue = "";
    function changeSwitch(bank_id,value) {
       $.post("/api/banks/update-switch",{ bank_id : bank_id , value : value?1:0 },(data) => {
           notify("success",data.message,() => {
               
           });
       }).fail((xhr) => {
           let error = xhr.responseJSON.message;
           notify("error",error);
       });
   }
   
   function changeLength(value) {
        window.dataTable.DataTable().page.len(value).draw();
    }
    
   
    function changeSearchBy(value) {
        window.searchIndex = value;
        searchTable();

    }

    function changeSearchValue(value) {
        window.searchValue = value;
        searchTable();
    }
    
   function searchTable() {
    if (window.searchIndex !== "" && window.searchValue !== "") {
        document.querySelectorAll("tbody tr").forEach((tr) => {
            const cells = tr.querySelectorAll("td");
            const cell = cells[window.searchIndex];
            
            if (cell && cell.textContent.toLowerCase().includes(window.searchValue.toLowerCase())) {
                $(tr).show();
            } else {
                $(tr).hide();
            }
        });
    } else {
        $("tbody tr").show();
    }
}

   
   function load() {
        window.dataTable = $("#dataTable").dataTable({
           ordering: false,
           bFilter : false,
           bLengthChange: false,
           
       });
       
      document.querySelector(".card-body").append(document.querySelectorAll("#dataTable_wrapper .row")[2])
   }
</script>

<script class="afterLoadScript" type="text/plain">
    load();
</script>


<?php if (!isset($_GET['turbo'])) {
    echo view('footer', get_defined_vars());
} ?>
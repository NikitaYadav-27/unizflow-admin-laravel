<?php
namespace App\Http\Controllers;
use App\Libraries\Validators;
use App\Libraries\Recaptcha;

class Home extends BaseController
{
    use \App\Traits\FileUploadTrait;

    function __construct() {
        $this->middleware(function ($request, $next) {
            $admin_id = session()->get("admin_id");
            if ($admin_id) {
                $adminModel = new \App\Models\Admin();
                $admin = $adminModel->find($admin_id);
                if (!$admin || $admin['active'] == 0 || $admin['current_session_id'] != session_id()) {
                    session()->flush();
                    return redirect('/admin/login');
                }
            }
            return $next($request);
        });
    }
    
    public function index()
    {
        $session = session();
        if (!$session->get('admin_id')) {
            return redirect('/admin/login');
        } else {
            $admin = new \App\Models\Admin();
            if($admin->find($session->get('admin_id'))['current_session_id'] != session_id()) {
                $session->flush();
                return redirect('/admin/login');
            }
            return redirect('/admin/dashboard');
        }
    }

    public function dashboard()
    {
        $session = session();
        if (!$session->get('admin_id')) {
            return redirect('/admin/login');
        } else {
            $admin = new \App\Models\Admin();
            $admin = $admin->find($session->get('admin_id'));
            $data['admin'] = $admin;
            return view('dashboard', $data);
        }
    }
    
    
    public function users($status)
    {
        $session = session();
        if (!$session->get('admin_id')) {
            return redirect('/admin/login');
        } else {
            if(!in_array($status,["active","inactive","locked"])) {
                 return redirect('/');
            }
            $admin = new \App\Models\Admin();
            $admin = $admin->find($session->get('admin_id'));
            $data['admin'] = $admin;
            $data['status'] = $status;
            return view('users', $data);
        }
    }
    
    public function hold_transactions($status){
        $session = session();
        if (!$session->get('admin_id')) {
            return redirect('/admin/login');
        } else {
            if(!in_array($status,["hold","released"])) {
                 return redirect('/');
            }
            $admin = new \App\Models\Admin();
            $admin = $admin->find($session->get('admin_id'));
            
            $data['admin'] = $admin;
            $data['status'] = $status;
            return view('hold_transactions', $data);
        } 
    }
    
    public function users_bulk_action() {
        $session = session();
        if (!$session->get('admin_id')) {
            return response()->json(["status" => "error", "message" => "Session Expired"], 401);
        } 
        
        $admin = new \App\Models\Admin();
        $admin = $admin->find($session->get('admin_id'));
       
        
        $input = json_decode(request()->getContent());
        if(!in_array($input->action,["payin_provider","payout_provider","status","payin","payout"])) {
            return response()->json(["status" => "error", "message" => "Invalid Action !"], 400);
        }
        
        if(in_array($input->action,["status","payin","payout"])) {
            if(!in_array($input->value,[0,1])){
                return response()->json(["status" => "error", "message" => "Invalid Value !"], 400);
            }
        }
        
        if($input->action == "payin_provider") {
            $providerModel = new \App\Models\Providers();
            $provider = $providerModel->where("id",$input->value)->where("type", "payin")->first();
            if(!isset($provider['id'])){
                return response()->json(["status" => "error", "message" => "Invalid Value !"], 400);
            }
        }
        
        if($input->action == "payout_provider") {
            $providerModel = new \App\Models\Providers();
            $provider = $providerModel->where("id",$input->value)->where("type", "payout")->first();
            if(!isset($provider['id'])){
                return response()->json(["status" => "error", "message" => "Invalid Value !"], 400);
            }
        }
        
        if(count($input->users) == 0) {
            return response()->json(["status" => "error", "message" => "Please select at least one user"], 400);
        }
        
        $userModel = new \App\Models\User();
        foreach($input->users as $user_id) {
           if($userModel->find($user_id)['locked'] == 0){
             $userModel->where("id",$user_id)->update([$input->action == "status"?"active":$input->action => $input->value]);
           }
        }
        
        return response()->json(["status" => "error", "message" => "Bulk Action Successfully Executed !"], 200);
    }
    
    public function add_package(){
        $session = session();
        if (!$session->get('admin_id')) {
            return redirect('/admin/login');
        } else {
            $admin = new \App\Models\Admin();
            $admin = $admin->find($session->get('admin_id'));
            $data['admin'] = $admin;
            return view('add_package', $data);
        }
    }
    
    
    
    public function fund_requests($status) {
         $session = session();
        if (!$session->get('admin_id')) {
            return redirect('/admin/login');
        } else {
            if(!in_array($status, ["completed","pending","rejected"])) {
                return redirect('/');
            }
            $admin = new \App\Models\Admin();
            $admin = $admin->find($session->get('admin_id'));
          
            
            $data['admin'] = $admin;
            $data['status'] = $status == "completed"?"Success":($status == "rejected"?"Failed":($status == "pending"?"Pending":""));
            $data['estatus'] = $status;
            return view('fund_requests', $data);
        }
    }
    
    public function fund_request_banks() {
        $session = session();
        if (!$session->get('admin_id')) {
            return redirect('/admin/login');
        } else {
            $admin = new \App\Models\Admin();
            $admin = $admin->find($session->get('admin_id'));
         
            $data['admin'] = $admin;
            return view('fund_request_banks', $data);
        }
    }
    
    public function add_bank(){
        $session = session();
        if (!$session->get('admin_id')) {
            return redirect('/admin/login');
        } else {
            $admin = new \App\Models\Admin();
            $admin = $admin->find($session->get('admin_id'));
            
            $data['admin'] = $admin;
            return view('add_bank', $data);
        }
    }
    
  
    
    public function add_credit() {
       $session = session();
        if (!$session->get('admin_id')) {
            return redirect('/admin/login');
        } else {
            $admin = new \App\Models\Admin();
            $admin = $admin->find($session->get('admin_id'));
           
            $data['admin'] = $admin;
            return view('add_credit', $data);
        } 
    }
    
    public function give_credits() {
       $session = session();
       if (!$session->get('admin_id')) {
           return response()->json(["status" => "error","message" => "Invalid Session"], 401);
       }
       $admin = new \App\Models\Admin();
       $admin = $admin->find($session->get('admin_id'));
       
       $input = json_decode(request()->getContent());
       $amount = $input->amount;
       $remarks = $input->remarks;
       $userId = $input->user_id;
       
       if(!Validators::amount($amount)) {
          return response()->json(["status" => "error","message" => "Invalid Amount"], 400);
       }
       
       if(!Validators::remarks($remarks)) {
          return response()->json(["status" => "error","message" => "Invalid Remarks"], 400);
       }
       
       if(!($userId > 0)) {
          return response()->json(["status" => "error","message" => "Invalid User"], 400);
       }
       
       $userModel = new \App\Models\User();
       $user = $userModel->where("active",1)->where("id",$userId)->first();
       
       if(!isset($user['id'])) {
          return response()->json(["status" => "error","message" => "Invalid User"], 400);
       }
       
       //add credit
       $creditModel = new \App\Models\Credits();
       $creditModel->create([
           "user_id" => $userId,
           "amount" => $amount,
           "credit_remarks" => $remarks,
           "txnid" => $this->generateTxnId()
       ]);
       return response()->json(["status" => "error","message" => "Credits Added Successfully !"], 200);
    }
    
    public function debit_credits() {
       $session = session();
       if (!$session->get('admin_id')) {
           return response()->json(["status" => "error","message" => "Invalid Session"], 401);
       }
       $admin = new \App\Models\Admin();
       $admin = $admin->find($session->get('admin_id'));
      
       $id = request()->input('id');
       $remarks = request()->input('remarks');
       
       if(!Validators::remarks($remarks)) {
          return response()->json(["status" => "error","message" => "Invalid Remarks"], 400);
       }
       
       $creditModel = new \App\Models\Credits();
       $creditModel->where('id', $id)->update([
          "debit_granted" => 1,
          "debit_remarks" => $remarks
       ]);
       return response()->json(["status" => "error","message" => "Credits Debited Successfully !"], 200);
    }
    
    private function generateTxnId() {
       return strtoupper(substr("RDP" . dechex(time()) . bin2hex(random_bytes(3)), 0, 20));
    }
    
    public function pending_credits() {
       $session = session();
        if (!$session->get('admin_id')) {
            return redirect('/admin/login');
        } else {
            $admin = new \App\Models\Admin();
            $admin = $admin->find($session->get('admin_id'));
           
            $data['admin'] = $admin;
            return view('pending_credits', $data);
        } 
    }
    
    public function completed_credits() {
       $session = session();
        if (!$session->get('admin_id')) {
            return redirect('/admin/login');
        } else {
            $admin = new \App\Models\Admin();
            $admin = $admin->find($session->get('admin_id'));
            $data['admin'] = $admin;
            return view('completed_credits', $data);
        } 
    }
    
    public function settlements($status) {
        $session = session();
        if (!$session->get('admin_id')) {
            return redirect('/admin/login');
        } else {
            if(!in_array($status,['pending','hold','completed'])){
                return redirect('/');
            }
            $admin = new \App\Models\Admin();
            $admin = $admin->find($session->get('admin_id'));
            $data['admin'] = $admin;
            $data['status'] = $status;
            return view('settlements', $data);
        }
    }
    
   
    
    public function create_package() {
       $session = session();
       if (!$session->get('admin_id')) {
           return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
       }
       $admin = new \App\Models\Admin();
       $admin = $admin->find($session->get('admin_id'));
      
       $input = json_decode(request()->getContent());
       $packageName = $input->name;
       if(strlen($packageName) < 4) {
           return response()->json(["status" => "error", "message" => "Package name should mininum 3 characters"], 400);
       }
       $packageModel = new \App\Models\Package();
       if($packageModel->packageNameExists($packageName)){
            return response()->json(["status" => "error", "message" => "Package Name already Exists"], 429);
       }
       
       $packageModel->create(["name" => $packageName]);
       return response()->json(["status" => "success", "message" => "Package Created Successfully"], 200);
    }
    
    public function create_bank(){
       $session = session();
       if (!$session->get('admin_id')) {
           return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
       }
       $admin = new \App\Models\Admin();
       $admin = $admin->find($session->get('admin_id'));
       
       $input = json_decode(request()->getContent());
       $name = trim($input->name);
       $account_name = trim($input->acname);
       $account_number = trim($input->account_number);
       $ifsc_code = trim($input->ifsc_code);
       $bank = trim($input->bank_name);
       $branch = trim($input->branch_name);
       
       if(strlen($name) < 5){
            return response()->json(["status" => "error", "message" => "Invalid Bank Name, Bank name should have more than 4 charcters."], 400);
       }
       
       if(strlen($account_name) < 5){
            return response()->json(["status" => "error", "message" => "Invalid Account Name, Account name should have more than 4 charcters."], 400);
       }
       
       if(!Validators::accountNumber($account_number)){
            return response()->json(["status" => "error", "message" => "Invalid Account Number"], 400);
       }
       
       if(!Validators::ifsc($ifsc_code)){
           return response()->json(["status" => "error", "message" => "Invalid IFSC Code"], 400);
       }
       
       if(strlen($bank) < 3){
            return response()->json(["status" => "error", "message" => "Invalid Bank name"], 400);
       }
       
       if(strlen($branch) < 3){
            return response()->json(["status" => "error", "message" => "Invalid Branch name"], 400);
       }
       
       $bankModel = new \App\Models\FundRequestBank();
       if($bankModel->where("account_number",$account_number)->where("ifsc_code",$ifsc_code)->count() > 0){
           return response()->json(["status" => "error", "message" => "Bank account is already exists !"], 400);
       }
       
       $bankModel->create([
          "name" => $name,
          "account_holder_name" => $account_name,
          "account_number" => $account_number,
          "ifsc_code" => $ifsc_code,
          "bank" => $bank,
          "branch" => $branch,
          "active" => 1
       ]);
       //insert bank
       return response()->json(["status" => "success", "message" => "Bank Account Added Successfully !"], 200);
       
    }
    
    
    public function packages(){
        $session = session();
        if (!$session->get('admin_id')) {
            return redirect('/admin/login');
        } else {
            $admin = new \App\Models\Admin();
            $admin = $admin->find($session->get('admin_id'));
            
            $data['admin'] = $admin;
           return view('packages', $data);
        }
    }
    
    public function add_cyber(){
        $session = session();
        if (!$session->get('admin_id')) {
            return redirect('/admin/login');
        } else {
            $admin = new \App\Models\Admin();
            $admin = $admin->find($session->get('admin_id'));
            
            $data['admin'] = $admin;
           return view('add_cyber', $data);
        }
    }
    
    public function settlements_batch($encid) {
        $session = session();
        if (!$session->get('admin_id')) {
            return redirect('/admin/login');
        } else {
            $admin = new \App\Models\Admin();
            $admin = $admin->find($session->get('admin_id'));
            $data['admin'] = $admin;
            $id = $this->decrypt($encid);
            if(!($id > 0)) {
                return redirect('/');
            }
            $settlementModel = new \App\Models\Settlement();
            $data['settlement'] = $settlementModel->find($id);
           return view('settlements_batch', $data);
        }
    }
    
    public function package_slabs($encId) {
        $session = session();
        if (!$session->get('admin_id')) {
            return redirect('/admin/login');
        } else {
            $packageId = $this->decrypt($encId);
            if(!($packageId > 0)){
                 return redirect('/admin/packages');
            }
            $packageModel = new \App\Models\Package();
            $package = $packageModel->find($packageId);
            if($package['name'] == ""){
                  return redirect('/admin/packages');
            }
            $admin = new \App\Models\Admin();
            $admin = $admin->find($session->get('admin_id'));
            $data['admin'] = $admin;
            $data['package'] = $package;
            $resultSlabs = [
               "payin" => [],
               "payout" => []
            ];
            $packageSlabModel = new \App\Models\Packageslab();
            $slabs = $packageSlabModel->where("package_id", $packageId)->get();
            foreach($slabs as $slab){
                $resultSlabs[$slab['service']][] = $slab;
            }
            $data['slabs'] = $resultSlabs;
            return view('package_slabs', $data);
        }
    }

    
    public function bank_users($encId){
        $session = session();
        if (!$session->get('admin_id')) {
            return redirect('/admin/login');
        } else {
            $admin = new \App\Models\Admin();
            $admin = $admin->find($session->get('admin_id'));
            
            $bankId = $this->decrypt($encId);
            if(!($bankId > 0)){
                 return redirect('/admin/fund-request-banks');
            }
            $bankModel = new \App\Models\FundRequestBank();
            $bank = $bankModel->find($bankId);
            if($bank['name'] == ""){
                  return redirect('/admin/fund-request-banks');
            }
            $admin = new \App\Models\Admin();
            $admin = $admin->find($session->get('admin_id'));
            $data['admin'] = $admin;
            $data['bank'] = $bank;
            return view('bank_users', $data);
        }
    }
    
    public function edit_package($encId) {
        $session = session();
        if (!$session->get('admin_id')) {
            return redirect('/admin/login');
        } else {
            $packageId = $this->decrypt($encId);
            if(!($packageId > 0)){
                 return redirect('/admin/packages');
            }
            $packageModel = new \App\Models\Package();
            $package = $packageModel->find($packageId);
            if($package['name'] == ""){
                  return redirect('/admin/packages');
            }
            $admin = new \App\Models\Admin();
            $admin = $admin->find($session->get('admin_id'));
            
            $data['admin'] = $admin;
            $data['package'] = $package;
            $data['package_id'] = $this->encrypt($package['id']);
            return view('edit_package', $data);
        }
    }
    
    public function update_package() {
        $session = session();
        if (!$session->get('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }
        
        $admin = new \App\Models\Admin();
        $admin = $admin->find($session->get('admin_id'));
       
        
        $input = json_decode(request()->getContent());
        $package_id = $this->decrypt($input->package_id);
        if(!($package_id > 0)) {
             return response()->json(["status" => "error", "message" => "Invalid Package ID"], 400);
        }
        
        $name = $input->name;
        if(strlen($name) < 3 || strlen($name) > 20) {
            return response()->json(["status" => "error", "message" => "Package Name should have characters between 3  - 20"], 400);
        }
        
        $packageModel = new \App\Models\Package();
        $packageModel->where('id', $package_id)->update(["name" => $name]);
        return response()->json(["status" => "success", "message" => "Package Updated Successfully"], 200);
        
    }

    public function settlement_packages()
    {
        $session = session();
        if (!$session->get('admin_id')) {
            return redirect('/admin/login');
        } else {
            \App\Models\SettlementPackage::ensureSlotsTableExists();
            $admin = new \App\Models\Admin();
            $admin = $admin->find($session->get('admin_id'));
            $data['admin'] = $admin;
            $data['page'] = 'settlement_packages';
            return view('settlement_packages', $data);
        }
    }

    public function add_settlement_package()
    {
        $session = session();
        if (!$session->get('admin_id')) {
            return redirect('/admin/login');
        } else {
            \App\Models\SettlementPackage::ensureSlotsTableExists();
            $admin = new \App\Models\Admin();
            $admin = $admin->find($session->get('admin_id'));
            $data['admin'] = $admin;
            $data['page'] = 'add_settlement_package';
            return view('add_settlement_package', $data);
        }
    }

    public function edit_settlement_package($encId)
    {
        $session = session();
        if (!$session->get('admin_id')) {
            return redirect('/admin/login');
        } else {
            \App\Models\SettlementPackage::ensureSlotsTableExists();
            $packageId = $this->decrypt($encId);
            if (!($packageId > 0)) {
                return redirect('/admin/settlement-packages');
            }
            $model = new \App\Models\SettlementPackage();
            $package = $model->with('slots')->find($packageId);
            if (!$package) {
                return redirect('/admin/settlement-packages');
            }
            $admin = new \App\Models\Admin();
            $admin = $admin->find($session->get('admin_id'));
            $data['admin'] = $admin;
            $data['package'] = $package;
            $data['package_id'] = $this->encrypt($package->id);
            $data['page'] = 'settlement_packages';
            return view('edit_settlement_package', $data);
        }
    }

    public function create_settlement_package()
    {
        $session = session();
        if (!$session->get('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        \App\Models\SettlementPackage::ensureSlotsTableExists();

        $input = json_decode(request()->getContent());
        $name = trim($input->name ?? '');

        if (strlen($name) < 3) {
            return response()->json(["status" => "error", "message" => "Package name must be at least 3 characters"], 400);
        }

        $packageModel = new \App\Models\SettlementPackage();
        if ($packageModel->packageNameExists($name)) {
            return response()->json(["status" => "error", "message" => "Settlement Package Name already exists"], 429);
        }

        $slots = $input->slots ?? [];
        if (!is_array($slots) || count($slots) === 0) {
            return response()->json(["status" => "error", "message" => "Please configure at least one settlement time slot"], 400);
        }

        $slotData = [];
        $slotNum = 1;
        foreach ($slots as $slot) {
            $settlementTime = trim($slot->settlement_time ?? '');
            $txnFrom = trim($slot->txn_from_time ?? '');
            $txnTo = trim($slot->txn_to_time ?? '');

            if (empty($settlementTime) || empty($txnFrom) || empty($txnTo)) {
                return response()->json([
                    "status" => "error",
                    "message" => "Slot {$slotNum}: All times (Settlement Time, From Time, To Time) are required"
                ], 400);
            }

            $slotData[] = [
                'slot_number' => $slotNum,
                'settlement_time' => $settlementTime,
                'txn_from_time' => $txnFrom,
                'txn_to_time' => $txnTo,
                'status' => 1
            ];
            $slotNum++;
        }

        \DB::beginTransaction();
        try {
            $createdPackage = $packageModel->create([
                'name' => $name
            ]);

            foreach ($slotData as $s) {
                $s['package_id'] = $createdPackage->id;
                \App\Models\SettlementPackageSlot::create($s);
            }

            \DB::commit();
            return response()->json([
                "status" => "success",
                "message" => "Settlement Package created successfully with " . count($slotData) . " time slots"
            ], 200);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                "status" => "error",
                "message" => "Failed to create settlement package: " . $e->getMessage()
            ], 500);
        }
    }

    public function update_settlement_package()
    {
        $session = session();
        if (!$session->get('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        \App\Models\SettlementPackage::ensureSlotsTableExists();

        $input = json_decode(request()->getContent());
        $packageId = $this->decrypt($input->package_id ?? '');

        if (!($packageId > 0)) {
            return response()->json(["status" => "error", "message" => "Invalid Package ID"], 400);
        }

        $name = trim($input->name ?? '');
        if (strlen($name) < 3) {
            return response()->json(["status" => "error", "message" => "Package Name must be at least 3 characters"], 400);
        }

        $packageModel = new \App\Models\SettlementPackage();
        $package = $packageModel->find($packageId);
        if (!$package) {
            return response()->json(["status" => "error", "message" => "Settlement Package not found"], 404);
        }

        if ($packageModel->packageNameExists($name, $packageId)) {
            return response()->json(["status" => "error", "message" => "Another Settlement Package with this name already exists"], 429);
        }

        $slots = $input->slots ?? [];
        if (!is_array($slots) || count($slots) === 0) {
            return response()->json(["status" => "error", "message" => "Please configure at least one settlement time slot"], 400);
        }

        $slotData = [];
        $slotNum = 1;
        foreach ($slots as $slot) {
            $settlementTime = trim($slot->settlement_time ?? '');
            $txnFrom = trim($slot->txn_from_time ?? '');
            $txnTo = trim($slot->txn_to_time ?? '');

            if (empty($settlementTime) || empty($txnFrom) || empty($txnTo)) {
                return response()->json([
                    "status" => "error",
                    "message" => "Slot {$slotNum}: All times (Settlement Time, From Time, To Time) are required"
                ], 400);
            }

            $slotData[] = [
                'package_id' => $packageId,
                'slot_number' => $slotNum,
                'settlement_time' => $settlementTime,
                'txn_from_time' => $txnFrom,
                'txn_to_time' => $txnTo,
                'status' => 1
            ];
            $slotNum++;
        }

        \DB::beginTransaction();
        try {
            $package->update(['name' => $name]);
            \App\Models\SettlementPackageSlot::where('package_id', $packageId)->delete();
            foreach ($slotData as $s) {
                \App\Models\SettlementPackageSlot::create($s);
            }
            \DB::commit();
            return response()->json([
                "status" => "success",
                "message" => "Settlement Package updated successfully"
            ], 200);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                "status" => "error",
                "message" => "Failed to update package: " . $e->getMessage()
            ], 500);
        }
    }

    public function delete_settlement_package()
    {
        $session = session();
        if (!$session->get('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        $input = json_decode(request()->getContent());
        $packageId = $this->decrypt($input->package_id ?? '');

        if (!($packageId > 0)) {
            return response()->json(["status" => "error", "message" => "Invalid Package ID"], 400);
        }

        $userCount = \App\Models\User::where('settlement_package', $packageId)->count();
        if ($userCount > 0) {
            return response()->json([
                "status" => "error",
                "message" => "Cannot delete package: It is currently assigned to {$userCount} user(s)."
            ], 400);
        }

        \DB::beginTransaction();
        try {
            \App\Models\SettlementPackageSlot::where('package_id', $packageId)->delete();
            \App\Models\SettlementPackage::where('id', $packageId)->delete();
            \DB::commit();
            return response()->json(["status" => "success", "message" => "Settlement Package deleted successfully"], 200);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(["status" => "error", "message" => "Failed to delete: " . $e->getMessage()], 500);
        }
    }
    
    public function update_bank_users() {
        $session = session();
        if (!$session->get('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }
        
       $admin = new \App\Models\Admin();
       $admin = $admin->find($session->get('admin_id'));
      
        $bankId = request()->input('bank');
        $users = request()->input('users');
        
        $bankModel = new \App\Models\FundRequestBank();
        $bankModel->where('id', $bankId)->update(["users" => $users]);
        
        return response()->json(["status" => "success", "message" => "Users Updated Successfully"], 200);
    }
    
    public function settle_to_wallet(){
        $session = session();
        if (!$session->get('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }
       $admin = new \App\Models\Admin();
       $admin = $admin->find($session->get('admin_id'));
       
        $encId = request()->input('id');
        $id = $this->decrypt($encId);
        if(!($id > 0)) {
             return response()->json(["status" => "error", "message" => "Invalid Settlement Id"], 400);
        }
        
        $settlementModel = new \App\Models\Settlement();
        if($settlementModel->find($id)['status'] != "Pending" && $settlementModel->find($id)['status'] != "Hold") {
             return response()->json(["status" => "error", "message" => "Settlement is not in Pending Stage."], 400);
        }
        
        $settlementModel->where('id', $id)->update(["status" => "Success", "settle_to" => "wallet"]);
        return response()->json(["status" => "success", "message" => "Amount Settled to User's Wallet !"], 200);
    }

    public function trigger_auto_settlements()
    {
        $session = session();
        if (!$session->get('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        $date = request()->input('date');
        $time = request()->input('time');

        $result = \App\Libraries\AutoSettlement::process($date, $time, true);

        return response()->json([
            "status" => "success",
            "message" => "Processed automatic settlements: {$result['settlements_created']} batch(es) created, ₹" . number_format($result['total_amount_settled'], 2) . " settled ({$result['eligible_auto_users_count']} auto users evaluated, {$result['skipped_manual_users_count']} manual users skipped).",
            "data" => $result
        ], 200);
    }
    
    public function settle_to_bank($encId) {
        $session = session();
        if (!$session->get('admin_id')) {
            return redirect('/admin/login');
        } else {
            $admin = new \App\Models\Admin();
            $admin = $admin->find($session->get('admin_id'));
            $data['admin'] = $admin;
            $id = $this->decrypt($encId);
            if(!($id > 0)) {
                return redirect('/');
            }
            $settlementModel = new \App\Models\Settlement();
            $data['settlement'] = $settlementModel->find($id);
            $data['settlement_id'] = $encId;
            if(!isset($data['settlement']['status']) || !in_array($data['settlement']['status'],["Pending","Hold"])) {
                return redirect('/');
            }
            return view('settle_to_bank', $data);
        }
    }
    
    public function hold_settlement() {
        $session = session();
        if (!$session->get('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }
        
       $admin = new \App\Models\Admin();
       $admin = $admin->find($session->get('admin_id'));
      
       
        $encId = request()->input('id');
        $id = $this->decrypt($encId);
        if(!($id > 0)) {
             return response()->json(["status" => "error", "message" => "Invalid Settlement Id"], 400);
        }
        
        $settlementModel = new \App\Models\Settlement();
        if($settlementModel->find($id)['status'] != "Pending") {
             return response()->json(["status" => "error", "message" => "Settlement is not in Pending Stage."], 400);
        }
        
        $settlementModel->where('id', $id)->update(["status" => "Hold"]);
        return response()->json(["status" => "success", "message" => "Settlement Hold Successfully !"], 200);
    }
    
    public function update_fund_request(){
        $session = session();
        if (!$session->get('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }
        
        $admin = new \App\Models\Admin();
        $admin = $admin->find($session->get('admin_id'));
        
        $encId = request()->input('id');
        $id = $this->decrypt($encId);
        $status = request()->input('status');
        $reason = request()->input('reason');
        $pin = request()->input('pin');
        if(!($id > 0)) {
             return response()->json(["status" => "error", "message" => "Invalid Settlement Id"], 400);
        }
        
        if(!in_array($status,["Success","Failed"])) {
             return response()->json(["status" => "error", "message" => "Invalid Status"], 400);
        }
        
        if(strlen($pin) != 6){
            return response()->json(["status" => "error", "message" => "Invalid or Wrong PIN"], 400);
        }
        
        $admin = new \App\Models\Admin();
        if($admin->find($session->get('admin_id'))['pin'] != $pin){
            return response()->json(["status" => "error", "message" => "Invalid or Wrong PIN"], 400);
        }
        
        $fundRequestModel = new \App\Models\FundRequest();
        if($fundRequestModel->find($id)['status'] != "Pending") {
             return response()->json(["status" => "error", "message" => "Fund request is not in Pending Stage."], 400);
        }
        
        $fundRequestModel->where('id', $id)->update([
           "status" => $status,
           "reason" => $reason
        ]);
        return response()->json(["status" => "success", "message" => $status == "Success"?"Fund Request Accepted Successfully !":"Fund Request Rejected Successfully !"], 200);
        
    }
    
    public function update_package_slab() {
       $session = session();
       if (!$session->get('admin_id')) {
           return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
       }
       $admin = new \App\Models\Admin();
       $admin = $admin->find($session->get('admin_id'));
     
       
       $input = json_decode(request()->getContent(), true) ?? [];
       $service = $input["service"] ?? "";
       $packageId = $input["package_id"] ?? "";
       if(!in_array($service,["payin","payout"])){
            return response()->json(["status" => "error", "message" => "Invalid Service Type"], 400);
       }
       $packageModel = new \App\Models\Package();
       $package = $packageModel->find($packageId);
       if(!$package || $package['name'] == ""){
           return response()->json(["status" => "error", "message" => "Invalid Package Id"], 400);
       }
       
       $packageSlabModel = new \App\Models\Packageslab();
       $packageSlabModel->where('service', $service)->where("package_id",$packageId)->delete();
       for($i = 1; $i < 7; $i++){
           $min_amount = $input["min_amount_$i"] ?? "";
           $max_amount = $input["max_amount_$i"] ?? "";
           $charge_amount = $input["charge_amount_$i"] ?? "";
           $percent = isset($input["percent_$i"])?1:0;
           $gst = isset($input["gst_$i"])?1:0;
           if($min_amount != "" && $max_amount != "" && $charge_amount != "") {
               $packageSlabModel->create([
                  "package_id" => $packageId,
                  "service" => $service,
                  "min_amount" => $min_amount,
                  "max_amount" => $max_amount,
                  "amount" => $charge_amount,
                  "percent" => $percent,
                  "gst" => $gst
               ]);
           }
       }
       
       return response()->json(["status" => "success", "message" => "Package Slabs Updated Successfully !"], 200);
       
    }
    
     public function update_provider_slab() {
        $session = session();
        if (!$session->get('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }
        $admin = new \App\Models\Admin();
        $admin = $admin->find($session->get('admin_id'));

        $input = request()->all();
        if (empty($input)) {
            $input = json_decode(request()->getContent(), true) ?? [];
        }

        $providerId = $input["provider_id"] ?? null;
        $providerModel = new \App\Models\Providers();
        $provider = $providerModel->find($providerId);
        if(!$provider){
            return response()->json(["status" => "error", "message" => "Invalid Provider Id"], 400);
        }

        $providerSlabModel = new \App\Models\Providerslab();
        $providerSlabModel->where("provider_id", $providerId)->delete();

        for($i = 1; $i < 7; $i++){
            $min_amount = $input["min_amount_$i"] ?? "";
            $max_amount = $input["max_amount_$i"] ?? "";
            $charge_amount = $input["charge_amount_$i"] ?? "";
            $percent = isset($input["percent_$i"]) ? 1 : 0;
            $gst = isset($input["gst_$i"]) ? 1 : 0;
            if($min_amount !== "" && $max_amount !== "" && $charge_amount !== "") {
                $providerSlabModel->create([
                   "provider_id" => $providerId,
                   "min_amount" => $min_amount,
                   "max_amount" => $max_amount,
                   "amount" => $charge_amount,
                   "percent" => $percent,
                   "gst" => $gst
                ]);
            }
        }

        return response()->json(["status" => "success", "message" => "Provider Slabs Updated Successfully !"], 200);
     }

    public function update_user_switch() {
       $session = session();
       if (!$session->get('admin_id')) {
           return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
       }
       $admin = new \App\Models\Admin();
       $admin = $admin->find($session->get('admin_id'));
      
       $user_id = request()->input('user_id');
       $service = request()->input('service');
       $value = request()->input('value');
       
       if(!in_array($service,["active","payin","payout"])) {
           return response()->json(["status" => "error", "message" => "Invalid Service"], 400);
       }
       
       if(!in_array($value,[0,1])) {
           return response()->json(["status" => "error", "message" => "Invalid Value"], 400);
       }
       
       if(!($user_id > 0)) {
           return response()->json(["status" => "error", "message" => "Invalid User Id"], 400);
       }
       
       $user = new \App\Models\User();
       $user->where('id', $user_id)->update([$service => $value]);
       return response()->json(["status" => "success", "message" => "Switch Updated Successfully !"], 200);
       
    }
    
    public function update_user_lock_switch() {
       $session = session();
       if (!$session->get('admin_id')) {
           return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
       }
       $admin = new \App\Models\Admin();
       $admin = $admin->find($session->get('admin_id'));
      
       $user_id = request()->input('user_id');
       if(!($user_id > 0)) {
           return response()->json(["status" => "error", "message" => "Invalid User Id"], 400);
       }
       $user = new \App\Models\User();
       $user->where('id', $user_id)->update(["locked" => $user->find($user_id)['locked'] == 1?0:1]);
       return response()->json(["status" => "success", "message" => "User Locked / Unlocked Successfully !"], 200);
       
    }
    
    
    public function update_bank_switch(){
       $session = session();
       if (!$session->get('admin_id')) {
           return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
       }
       $bank_id = request()->input('bank_id');
       $value = request()->input('value');
       $admin = new \App\Models\Admin();
       $admin = $admin->find($session->get('admin_id'));
     
       if(!in_array($value,[0,1])) {
           return response()->json(["status" => "error", "message" => "Invalid Value"], 400);
       }
       
       if(!($bank_id > 0)) {
           return response()->json(["status" => "error", "message" => "Invalid Bank Id"], 400);
       }
       
       $bank = new \App\Models\FundRequestBank();
       $bank->where('id', $bank_id)->update(["active" => $value]);
       return response()->json(["status" => "success", "message" => "Switch Updated Successfully !"], 200);
    }
    
    
    private function encrypt($text) {
        return bin2hex(openssl_encrypt($text, 'AES-256-CBC', env("encryption.key"), OPENSSL_RAW_DATA, substr(hash('sha256', env("encryption.key")), 0, 16)));
    }
    
    private function decrypt($encryptedText) {
       $key = env("encryption.key");
       $iv = substr(hash('sha256', $key), 0, 16);
       $cipherText = hex2bin($encryptedText);
       return openssl_decrypt($cipherText, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }
    
    public function user($encId){
       $session = session();
       if (!$session->get('admin_id')) {
           return redirect('/admin/login');
       }
       
       $user_id = $this->decrypt($encId);
       $userModel = new \App\Models\User();
       $admin = new \App\Models\Admin();
       $admin = $admin->find($session->get('admin_id'));
       $data['admin'] = $admin;
       $data['user'] = $userModel->find($user_id);
       return view("user",$data);
    }
    
    public function update_user(){
       $session = session();
       if (!$session->get('admin_id')) {
           return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
       }
       $admin = new \App\Models\Admin();
       $admin = $admin->find($session->get('admin_id'));
      
       $input = json_decode(request()->getContent());
       $userModel = new \App\Models\User();
       $settlementType = isset($input->settlement_type) && in_array(strtolower($input->settlement_type), ['automatic', 'manual']) ? strtolower($input->settlement_type) : 'manual';
       $userModel->where('id', $input->user_id)->update([
          "package" => $input->package,
          "payin_provider" => $input->payin_provider,
          "payout_provider" => $input->payout_provider,
          "payin_limit" => $input->payin_limit,
          "payout_limit" => $input->payout_limit,
          "payout_per_limit" => $input->payout_per_limit,
          "settlement_package" => $input->settlement_package,
          "settlement_type" => $settlementType
       ]);
       return response()->json(["status" => "success", "message" => "User Updated Successfully"], 200);
    }
    
    public function update_user_info(){
       $session = session();
       if (!$session->get('admin_id')) {
           return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
       }
       $admin = new \App\Models\Admin();
       $admin = $admin->find($session->get('admin_id'));
      
       $userModel = new \App\Models\User();
      
       $input = json_decode(request()->getContent());
       if (!Validators::name($input->name)) {
           return response()->json([
               "status" => "error",
               "message" => "Please enter a valid first and last name."
           ], 400);
       }

       if (!Validators::email($input->email)) {
           return response()->json([
               "status" => "error",
               "message" => "Please provide a valid email address."
           ], 400);
       }

       if (!Validators::phone($input->phone)) {
           return response()->json([
               "status" => "error",
               "message" => "Please provide a valid phone number."
           ], 400);
       }

       if($userModel->where('id', '!=', $input->user_id)->where("email",$input->email)->count() > 0) {
         return response()->json(["status" => "error", "message" => "This email address is already registered with another user."], 400); 
       }
       
       if($userModel->where('id', '!=', $input->user_id)->where("phone",$input->phone)->count() > 0) {
         return response()->json(["status" => "error", "message" => "This phone number is already registered with another user."], 400); 
       }
       
       $udata = [
          "name" => strtoupper($input->name),
          "email" => $input->email,
          "phone" => $input->phone,
       ];
       if($input->password != ""){
          if (!Validators::password($input->password)) {
             return response()->json([
                 "status" => "error",
                 "message" => "Password must be at least 8 characters long and include uppercase, lowercase, number, and special character."
             ], 400);
          }
       }
       $udata['password'] = password_hash($input->password, PASSWORD_DEFAULT);
       $userModel->where('id', $input->user_id)->update($udata);
       return response()->json(["status" => "success", "message" => "User Details Successfully"], 200);
    }
    
    public function manage_users_fund(){
       $session = session();
       if (!$session->get('admin_id')) {
           return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
       }
       $admin = new \App\Models\Admin();
       $admin = $admin->find($session->get('admin_id'));
      
       $userModel = new \App\Models\User();
       $walletModel = new \App\Models\Wallet();
       $holdWalletModel = new \App\Models\HoldWallet();
       
       $input = json_decode(request()->getContent());
       
       if(!in_array($input->action, ["DEBIT","HOLD"])){
          return response()->json([
               "status" => "error",
               "message" => "Please select valid action"
          ], 400); 
       }

       if (!Validators::amount($input->amount)) {
           return response()->json([
               "status" => "error",
               "message" => "Please provide a valid amount."
           ], 400);
       }
       
       if (strlen($input->remarks) > 100) {
           return response()->json([
               "status" => "error",
               "message" => "Length of remarks should be less than or equal to 100"
           ], 400);
       }
       
       $user = $userModel->find($input->user_id);
       if($user['wallet'] < $input->amount){
           return response()->json([
               "status" => "error",
               "message" => "Insufficient wallet balance to complete this transaction."
           ], 400);
       }
       
       if($input->action == "DEBIT") {
           $walletModel->create([
              "user_id" => $input->user_id,
              "type" => "DEBIT",
              "txn_type" => "DEBIT",
              "txnid" => $this->generateTxnId(),
              "amount" => $input->amount,
              "charges" => "0",
              "gst" => "0",
              "net_amount" => $input->amount,
              "processed" => 2,
              "remarks" => $input->remarks,
              "opening" => "",
              "closing" => ""
           ]);
       }
       
       if($input->action == "HOLD") {
           $holdWalletModel->create([
              "user_id" => $input->user_id,
              "type" => "hold",
              "txnid" => $this->generateTxnId(),
              "amount" => $input->amount,
              "processed" => 0,
              "reason" => $input->remarks,
              "opening" => "",
              "closing" => ""
           ]);
       }
    
      return response()->json(["status" => "success", "message" => "Transaction Submitted Successfully"], 200);   
    }
    
    public function providers($type) {
       $session = session();
       if (!$session->get('admin_id')) {
           return redirect('/admin/login');
       }
       
       if(!in_array($type,["payin","payout"])) {
           return redirect('/');
       }
        $admin = new \App\Models\Admin();
        $admin = $admin->find($session->get('admin_id'));
       
        $data['admin'] = $admin;
        $data['type'] = $type;
        return view("providers",$data);
    }
    

    
    public function wallet() {
         $session = session();
        if (!$session->get('admin_id')) {
            return redirect('/admin/login');
        } else {
            $admin = new \App\Models\Admin();
            $admin = $admin->find($session->get('admin_id'));
           
            $data['admin'] = $admin;
            return view('wallet', $data);
        }
    }
    
    
    public function settings() {
       $session = session();
        if (!$session->get('admin_id')) {
            return redirect('/admin/login');
        } else {
            $admin = new \App\Models\Admin();
            $admin = $admin->find($session->get('admin_id'));
           
            $data['admin'] = $admin;
            return view('settings', $data);
        } 
    }

    
    public function transactions($type) {
        $session = session();
        if (!$session->get('admin_id')) {
            return redirect('/admin/login');
        } else {
            if(!in_array($type,["all","payin","payout","qr"])) {
                return redirect('/');
            }
            $admin = new \App\Models\Admin();
            $admin = $admin->find($session->get('admin_id'));
           
            $data['admin'] = $admin;
            $data['type'] = $type;
            return view('transactions', $data);
        } 
    }
    

    public function login_user() {
        $session = session();
        if (!$session->get('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }
        $admin = new \App\Models\Admin();
        $admin = $admin->find($session->get('admin_id'));
      
        $user_id = request()->input('uid');
        $userModel = new \App\Models\User();
        $token = time().rand(11111111111,99999999999);
        $userModel->where('id', $user_id)->update(["ad_token" => $token]);
        return response()->json(["status" => "success", "message" => "Session Created !","token" => $token], 200);
    }
    
    public function fetch_dashboard_data() {
        $session = session();
        if (!$session->get('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }
        $adminModel = new \App\Models\Admin();
        $admin = $adminModel->find($session->get('admin_id'));
        $startDate = request()->query('startDate');
        $endDate = request()->query('endDate');
        $usersBy = request()->query('usersBy');
        $reportType = request()->query('reportType');
        $reportUser = request()->query('reportUser');
        $reportBy = request()->query('reportBy');
        if(!Validators::date($startDate) || !Validators::date($endDate)){
            return response()->json(["status" => "error", "message" => "Invalid Date Format"], 400);
        }
        $startDate = $startDate." 00:00:00";
        $endDate = $endDate." 23:59:59";
        
        if(!in_array($usersBy,["total","payin","payout"])) {
            return response()->json(["status" => "error", "message" => "Invalid Users By Filter"], 400);
        }
        
        if(!in_array($reportType,["all","payin","payout"])) {
            return response()->json(["status" => "error", "message" => "Invalid Report Type Filter"], 400);
        }
        
        if(!in_array($reportBy,["weekly","monthly"])) {
            return response()->json(["status" => "error", "message" => "Invalid Report By Filter"], 400);
        }
        
        if($reportUser != "all" && !($reportUser > 0)) {
            return response()->json(["status" => "error", "message" => "Invalid Report User Filter"], 400);
        }
        $transactionModel = new \App\Models\Transaction();
        $response = [];
        $response["summary"] = $transactionModel->getDashboardSummary($startDate,$endDate);
        $response["users"] = $transactionModel->getTopUsers($startDate,$endDate,$usersBy);
        $response["providers"] = $transactionModel->getProviders($startDate,$endDate);
        $response["status"] = $transactionModel->getStatus($startDate,$endDate);
        $response["report"] = $transactionModel->getReport($startDate,$endDate,$reportType,$reportUser,$reportBy);
        return response()->json($response, 200);
        
    }
    
    public function transaction_info() {
        $session = session();
        if (!$session->get('admin_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }
        $txnid = request()->input('txnid');
        if(strlen($txnid) < 5) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Txnid'
            ], 400);
        }
        $transactionModel = new \App\Models\Transaction();
        $transaction = $transactionModel->where("txnid", $txnid)->first();
        if($transaction['service'] == "payout" && $transaction['status'] == "Success") {
            $date = new \DateTime($transaction['txn_complete_time']);
            return response()->json([
                'status' => 'success',
                'message' => 'Transaction Found !',
                'data' => [
                   "date" => $date->format('F d, Y'),
                   "time" => $date->format('h:i A'),
                   "amount" => $transaction['amount'],
                   "inwords" => (new Validators())->convertNumberToWord($transaction['amount']),
                   "name" => $transaction['name'],
                   "account" => $transaction['account_number'],
                   "ifsc" => $transaction['ifsc_code'],
                   'remarks' => $transaction['remarks'],
                   'txnid' => $transaction['txnid'],
                   'utr' => $transaction['rrn'],
                   "reference_id" => $transaction['reference_id'],
                ]
            ], 200); 
        }else{
           return response()->json([
                'status' => 'error',
                'message' => 'Invalid Txnid'
           ], 400); 
        }
    }
    

    
    public function update_settings() {
        $session = session();
        if (!$session->get('admin_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }
        $adminModel = new \App\Models\Admin();
        $admin = $adminModel->find($session->get('admin_id'));
        //check permission
       
        $name = request()->input('name');
        $value = request()->input('value');
        $settingsModel = new \App\Models\Settings();
        $settingsModel->where("name",$name)->update(["value" => $value]);
        return response()->json(["status" => "success", "message" => "Settings Updated Successfully"], 200);
    }
    
    
    public function update_user_bank_status(){
        $session = session();
        if (!$session->get('admin_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }
        $adminModel = new \App\Models\Admin();
        $admin = $adminModel->find($session->get('admin_id'));
        $id = request()->input('id');
        $action = request()->input('status');
        if(!in_array($action, ["approve","reject"])) {
           return response()->json([
                'status' => 'error',
                'message' => 'Invalid Action Submitted'
            ], 400);
        }
        $db = \Illuminate\Support\Facades\DB::connection();
        if($db->table('users_bank')->where('id', $id)->where('status', 'Pending')->count() == 0){
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Bank Id'
            ], 400);
        }
        if($action == "approve") {
           $db->table('users_bank')->where("id",$id)->update(["status" => "Approved"]);
        }else{
          $db->table('users_bank')->where('id', $id)->delete();
        }
        
        return response()->json([
           'status' => 'success',
           'message' => 'Bank Updated Successfully !'
        ], 200);
    }
    
    public function update_settlement_to_bank() {
        $session = session();
        if (!$session->get('admin_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }
        $adminModel = new \App\Models\Admin();
        $admin = $adminModel->find($session->get('admin_id'));
       
        $input = json_decode(request()->getContent());
        $id = $this->decrypt($input->id);
        if(!($id > 0)){
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Settlement Id'
            ], 400);  
        }
        $settlementModel = new \App\Models\Settlement();
        $settlement = $settlementModel->find($id);
        if(!in_array($settlement['status'], ["Pending","Hold"])) {
             return response()->json([
                'status' => 'error',
                'message' => 'Settlement is not in pending or hold stage'
            ], 400);
        }
        $bankAccount = \Illuminate\Support\Facades\DB::connection()->table('users_bank')->where('status', 'Approved')->where('user_id', $settlement['user_id'])->get()->first();
        
        $settlementModel->where('id', $settlement['id'])->update([
          "settle_to" => "bank",
          "reference" => $input->utr,
          "bank_id" => $bankAccount['id'],
          "remarks" => $input->remarks,
          "credited" => 1,
          "status" => "Success"
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Settlement Updated Successfully !'
        ], 200); 

    }
    
    public function update_provider($id){
        $session = session();
        if (!$session->get('admin_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }
        $adminModel = new \App\Models\Admin();
        $admin = $adminModel->find($session->get('admin_id'));
        $input = json_decode(request()->getContent());
        $udata["name"] = $input->name;
        $udata["gateway"] = $input->gateway;
        if(isset($input->daily_limit) && $input->daily_limit != ""){
            $udata["daily_limit"] = $input->daily_limit;
        }

        $providerModel = new \App\Models\Providers();
        $provider = $providerModel->find($id);
        if (!$provider) {
            return response()->json([
                'status' => 'error',
                'message' => 'Provider not found'
            ], 404);
        }

        if(isset($input->logo) && strlen($input->logo) > 100) {
            $newLogo = $this->handleBase64Upload($input->logo, $provider->getRawOriginal('logo') ?? $provider->logo, 'assets', 'provider-logo-');
            if ($newLogo) {
                $udata['logo'] = $newLogo;
            }
        }

        $providerModel->where('id', $id)->update($udata);
        return response()->json([
            'status' => 'success',
            'message' => 'Provider Details Updated Successfully'
        ], 200);
    }

    public function create_provider() {
        $session = session();
        if (!$session->get('admin_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }
        $input = json_decode(request()->getContent());
        if(!$input || empty($input->name) || empty($input->gateway) || empty($input->type)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Provider Name, Gateway Name, and Type are required'
            ], 400);
        }
        if(!in_array($input->type, ['payin', 'payout'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid provider type'
            ], 400);
        }

        $pdata = [
            'name' => $input->name,
            'gateway' => $input->gateway,
            'type' => $input->type,
            'daily_limit' => !empty($input->daily_limit) ? $input->daily_limit : '0',
            'used_limit' => '0',
            'active' => 1,
            'logo' => ''
        ];

        if(isset($input->logo) && strlen($input->logo) > 100) {
            $newLogo = $this->uploadBase64File($input->logo, 'assets', 'provider-logo-');
            if ($newLogo) {
                $pdata['logo'] = $newLogo;
            }
        }

        $providerModel = new \App\Models\Providers();
        $newProvider = $providerModel->create($pdata);

        return response()->json([
            'status' => 'success',
            'message' => 'Provider Created Successfully',
            'provider_id' => $newProvider->id,
            'type' => $input->type
        ], 200);
    }

    public function error_logs()
    {
        $session = session();
        if (!$session->get('admin_id')) {
            return redirect('/admin/login');
        }
        $admin = (new \App\Models\Admin())->find($session->get('admin_id'));
        $providers = (new \App\Models\Providers())->get();
        return view('error_logs', [
            'admin' => $admin,
            'providers' => $providers
        ]);
    }
}

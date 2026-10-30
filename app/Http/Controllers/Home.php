<?php

namespace App\Http\Controllers;

use App\Libraries\Validators;
use App\Libraries\Recaptcha;
use App\Libraries\AutoSettlement;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Admin;
use App\Models\User;
use App\Models\Transaction;
use App\Models\HoldWallet;
use App\Models\Credits;
use App\Models\FundRequest;
use App\Models\FundRequestBank;
use App\Models\Package;
use App\Models\Packageslab;
use App\Models\Providers;
use App\Models\Providerslab;
use App\Models\Settings;
use App\Models\Settlement;
use App\Models\SettlementPackage;
use App\Models\SettlementPackageSlot;
use App\Models\Wallet;
use App\Models\ErrorLog;
use App\Models\AccountLedger;

class Home extends BaseController
{
    use FileUploadTrait;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $admin_id = session('admin_id');
            if ($admin_id) {
                $admin = Admin::find($admin_id);
                if (!$admin || $admin['active'] == 0 || $admin['current_session_id'] != $request->session()->getId()) {
                    session()->flush();
                    return redirect()->route('admin.login');
                }
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $admin = Admin::find(session('admin_id'));
        if (!$admin || $admin['current_session_id'] != $request->session()->getId()) {
            session()->flush();
            return redirect()->route('admin.login');
        }

        return redirect()->route('admin.dashboard');
    }

    public function dashboard()
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $admin = Admin::find(session('admin_id'));
        return view('dashboard', ['admin' => $admin]);
    }

    public function users($status)
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        if (!in_array($status, ["active", "inactive", "locked"])) {
            return redirect()->route('home');
        }

        $admin = Admin::find(session('admin_id'));
        return view('users', [
            'admin' => $admin,
            'status' => $status
        ]);
    }

    public function hold_transactions($status)
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        if (!in_array($status, ["hold", "released"])) {
            return redirect()->route('home');
        }

        $admin = Admin::find(session('admin_id'));
        return view('hold_transactions', [
            'admin' => $admin,
            'status' => $status
        ]);
    }

    public function release_hold_transaction(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json(["status" => "error", "message" => "Unauthorized access"], 401);
        }

        $id = $request->input('id');
        $remarks = $request->input('remarks');

        if (!$id) {
            return response()->json(["status" => "error", "message" => "Invalid Transaction ID"], 400);
        }

        $holdTxn = HoldWallet::find($id);

        if (!$holdTxn) {
            return response()->json(["status" => "error", "message" => "Hold transaction not found"], 404);
        }

        if ($holdTxn->type !== 'hold') {
            return response()->json(["status" => "error", "message" => "Transaction is not currently on hold"], 400);
        }

        $alreadyReleased = HoldWallet::where('txnid', $holdTxn->txnid)->where('type', 'released')->exists();
        if ($alreadyReleased) {
            return response()->json(["status" => "error", "message" => "This transaction has already been released"], 400);
        }

        DB::beginTransaction();
        try {
            $user = User::lockForUpdate()->find($holdTxn->user_id);
            if (!$user) {
                DB::rollBack();
                return response()->json(["status" => "error", "message" => "User not found"], 404);
            }

            $amount = floatval($holdTxn->amount);
            if ($amount <= 0) {
                DB::rollBack();
                return response()->json(["status" => "error", "message" => "Invalid transaction amount"], 400);
            }

            $userWalletOpening = floatval($user['wallet'] ?? 0);
            $userHoldOpening = floatval($user['hold_wallet'] ?? 0);

            $userWalletClosing = number_format($userWalletOpening + $amount, 2, '.', '');
            $userHoldClosing = number_format(max(0, $userHoldOpening - $amount), 2, '.', '');

            User::where('id', $user['id'])->update([
                'wallet' => $userWalletClosing,
                'hold_wallet' => $userHoldClosing
            ]);

            $releaseRemarks = !empty($remarks) ? $remarks : ("Released hold for " . $holdTxn->txnid);
            HoldWallet::create([
                'user_id' => $user['id'],
                'type' => 'released',
                'txnid' => $holdTxn->txnid,
                'amount' => number_format($amount, 2, '.', ''),
                'opening' => number_format($userHoldOpening, 2, '.', ''),
                'closing' => $userHoldClosing,
                'reason' => $releaseRemarks,
                'processed' => 1
            ]);

            $walletTxnId = $this->generateTxnId();
            Wallet::create([
                'user_id' => $user['id'],
                'type' => 'CREDIT',
                'txn_type' => 'HOLD-RELEASE',
                'txnid' => $walletTxnId,
                'amount' => number_format($amount, 2, '.', ''),
                'charges' => '0',
                'gst' => '0',
                'net_amount' => number_format($amount, 2, '.', ''),
                'processed' => 1,
                'remarks' => $releaseRemarks,
                'opening' => number_format($userWalletOpening, 2, '.', ''),
                'closing' => $userWalletClosing
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Hold transaction released successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to release hold: ' . $e->getMessage()
            ], 500);
        }
    }

    public function users_bulk_action(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json(["status" => "error", "message" => "Session Expired"], 401);
        }

        $input = (object) $request->all();
        if (!isset($input->action) || !in_array($input->action, ["payin_provider", "payout_provider", "status", "payin", "payout"])) {
            return response()->json(["status" => "error", "message" => "Invalid Action !"], 400);
        }

        if (in_array($input->action, ["status", "payin", "payout"])) {
            if (!isset($input->value) || !in_array($input->value, [0, 1])) {
                return response()->json(["status" => "error", "message" => "Invalid Value !"], 400);
            }
        }

        if ($input->action == "payin_provider") {
            $provider = Providers::where("id", $input->value)->where("type", "payin")->first();
            if (!$provider) {
                return response()->json(["status" => "error", "message" => "Invalid Value !"], 400);
            }
        }

        if ($input->action == "payout_provider") {
            $provider = Providers::where("id", $input->value)->where("type", "payout")->first();
            if (!$provider) {
                return response()->json(["status" => "error", "message" => "Invalid Value !"], 400);
            }
        }

        if (!isset($input->users) || count((array) $input->users) == 0) {
            return response()->json(["status" => "error", "message" => "Please select at least one user"], 400);
        }

        foreach ($input->users as $user_id) {
            $user = User::find($user_id);
            if ($user && $user['locked'] == 0) {
                User::where("id", $user_id)->update([$input->action == "status" ? "active" : $input->action => $input->value]);
            }
        }

        return response()->json(["status" => "success", "message" => "Bulk Action Successfully Executed !"], 200);
    }

    public function add_package()
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $admin = Admin::find(session('admin_id'));
        return view('add_package', ['admin' => $admin]);
    }

    public function fund_requests($status)
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        if (!in_array($status, ["completed", "pending", "rejected"])) {
            return redirect()->route('home');
        }

        $admin = Admin::find(session('admin_id'));
        return view('fund_requests', [
            'admin' => $admin,
            'status' => $status == "completed" ? "Success" : ($status == "rejected" ? "Failed" : ($status == "pending" ? "Pending" : "")),
            'estatus' => $status
        ]);
    }

    public function fund_request_banks()
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $admin = Admin::find(session('admin_id'));
        return view('fund_request_banks', ['admin' => $admin]);
    }

    public function add_bank()
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $admin = Admin::find(session('admin_id'));
        return view('add_bank', ['admin' => $admin]);
    }

    public function add_credit()
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $admin = Admin::find(session('admin_id'));
        return view('add_credit', ['admin' => $admin]);
    }

    public function give_credits(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        $input = (object) $request->all();
        $amount = $input->amount ?? 0;
        $remarks = $input->remarks ?? '';
        $userId = $input->user_id ?? 0;

        if (!Validators::amount($amount)) {
            return response()->json(["status" => "error", "message" => "Invalid Amount"], 400);
        }

        if (!Validators::remarks($remarks)) {
            return response()->json(["status" => "error", "message" => "Invalid Remarks"], 400);
        }

        if (!($userId > 0)) {
            return response()->json(["status" => "error", "message" => "Invalid User"], 400);
        }

        $user = User::where("active", 1)->where("id", $userId)->first();
        if (!$user) {
            return response()->json(["status" => "error", "message" => "Invalid User"], 400);
        }

        DB::beginTransaction();
        try {
            $user = User::lockForUpdate()->find($userId);
            $txnid = $this->generateTxnId();
            $opening = floatval($user['wallet'] ?? 0);
            $creditAmount = floatval($amount);
            $closing = number_format($opening + $creditAmount, 2, '.', '');

            User::where('id', $userId)->update(['wallet' => $closing]);

            Credits::create([
                "user_id" => $userId,
                "amount" => $amount,
                "credit_remarks" => $remarks,
                "credited" => 1,
                "credited_at" => now()->toDateTimeString(),
                "txnid" => $txnid
            ]);

            Wallet::create([
                "user_id" => $userId,
                "type" => "CREDIT",
                "txn_type" => "ADVANCE-CREDIT",
                "txnid" => $txnid,
                "opening" => number_format($opening, 2, '.', ''),
                "amount" => number_format($creditAmount, 2, '.', ''),
                "charges" => "0",
                "gst" => "0",
                "net_amount" => number_format($creditAmount, 2, '.', ''),
                "remarks" => $remarks,
                "processed" => 1,
                "closing" => $closing
            ]);

            DB::commit();
            return response()->json(["status" => "success", "message" => "Credits Added Successfully !"], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(["status" => "error", "message" => "Failed to add credits: " . $e->getMessage()], 500);
        }
    }

    public function debit_credits(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        $id = $request->input('id');
        $remarks = $request->input('remarks');

        if (!Validators::remarks($remarks)) {
            return response()->json(["status" => "error", "message" => "Invalid Remarks"], 400);
        }

        DB::beginTransaction();
        try {
            $credit = Credits::find($id);
            if (!$credit || $credit['debited'] == 1) {
                DB::rollBack();
                return response()->json(["status" => "error", "message" => "Credit record not found or already debited"], 400);
            }

            $user = User::lockForUpdate()->find($credit['user_id']);
            if (!$user) {
                DB::rollBack();
                return response()->json(["status" => "error", "message" => "User not found"], 404);
            }

            $opening = floatval($user['wallet'] ?? 0);
            $debitAmount = floatval($credit['amount']);
            if ($opening < $debitAmount) {
                DB::rollBack();
                return response()->json(["status" => "error", "message" => "Insufficient wallet balance to debit this credit."], 400);
            }

            $closing = number_format($opening - $debitAmount, 2, '.', '');

            User::where('id', $user['id'])->update(['wallet' => $closing]);

            Wallet::create([
                "user_id" => $user['id'],
                "type" => "DEBIT",
                "txn_type" => "ADVANCE-DEBIT",
                "txnid" => $credit['txnid'],
                "opening" => number_format($opening, 2, '.', ''),
                "amount" => number_format($debitAmount, 2, '.', ''),
                "charges" => "0",
                "gst" => "0",
                "net_amount" => number_format($debitAmount, 2, '.', ''),
                "remarks" => $remarks,
                "processed" => 1,
                "closing" => $closing
            ]);

            Credits::where('id', $id)->update([
                "debited" => 1,
                "debit_granted" => 1,
                "debited_at" => now()->toDateTimeString(),
                "debit_remarks" => $remarks
            ]);

            DB::commit();
            return response()->json(["status" => "success", "message" => "Credits Debited Successfully !"], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(["status" => "error", "message" => "Failed to debit credits: " . $e->getMessage()], 500);
        }
    }

    private function generateTxnId()
    {
        return strtoupper(substr("RDP" . dechex(time()) . bin2hex(random_bytes(3)), 0, 20));
    }

    public function pending_credits()
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $admin = Admin::find(session('admin_id'));
        return view('pending_credits', ['admin' => $admin]);
    }

    public function completed_credits()
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $admin = Admin::find(session('admin_id'));
        return view('completed_credits', ['admin' => $admin]);
    }

    public function settlements($status)
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        if (!in_array($status, ['pending', 'hold', 'completed'])) {
            return redirect()->route('home');
        }

        $admin = Admin::find(session('admin_id'));
        $users = User::select(['id', 'name', 'merchant_id', 'settlement_type'])->where('active', 1)->orderBy('name', 'ASC')->get();

        return view('settlements', [
            'admin' => $admin,
            'status' => $status,
            'users' => $users
        ]);
    }

    public function profit()
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $admin = Admin::find(session('admin_id'));
        $providers = Providers::where('active', 1)->orderBy('name', 'ASC')->get();
        $users = User::select(['id', 'name', 'merchant_id'])->orderBy('name', 'ASC')->get();

        return view('profit', [
            'admin' => $admin,
            'page' => 'profit',
            'providers' => $providers,
            'users' => $users
        ]);
    }

    public function create_package(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        $input = (object) $request->all();
        $packageName = $input->name ?? '';
        if (strlen($packageName) < 4) {
            return response()->json(["status" => "error", "message" => "Package name should mininum 3 characters"], 400);
        }

        if (Package::where("name", $packageName)->exists()) {
            return response()->json(["status" => "error", "message" => "Package Name already Exists"], 429);
        }

        Package::create(["name" => $packageName]);
        return response()->json(["status" => "success", "message" => "Package Created Successfully"], 200);
    }

    public function create_bank(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        $input = (object) $request->all();
        $name = trim($input->name ?? '');
        $account_name = trim($input->acname ?? '');
        $account_number = trim($input->account_number ?? '');
        $ifsc_code = trim($input->ifsc_code ?? '');
        $bank = trim($input->bank_name ?? '');
        $branch = trim($input->branch_name ?? '');

        if (strlen($name) < 5) {
            return response()->json(["status" => "error", "message" => "Invalid Bank Name, Bank name should have more than 4 charcters."], 400);
        }

        if (strlen($account_name) < 5) {
            return response()->json(["status" => "error", "message" => "Invalid Account Name, Account name should have more than 4 charcters."], 400);
        }

        if (!Validators::accountNumber($account_number)) {
            return response()->json(["status" => "error", "message" => "Invalid Account Number"], 400);
        }

        if (!Validators::ifsc($ifsc_code)) {
            return response()->json(["status" => "error", "message" => "Invalid IFSC Code"], 400);
        }

        if (strlen($bank) < 3) {
            return response()->json(["status" => "error", "message" => "Invalid Bank name"], 400);
        }

        if (strlen($branch) < 3) {
            return response()->json(["status" => "error", "message" => "Invalid Branch name"], 400);
        }

        if (FundRequestBank::where("account_number", $account_number)->where("ifsc_code", $ifsc_code)->exists()) {
            return response()->json(["status" => "error", "message" => "Bank account is already exists !"], 400);
        }

        FundRequestBank::create([
            "name" => $name,
            "account_holder_name" => $account_name,
            "account_number" => $account_number,
            "ifsc_code" => $ifsc_code,
            "bank" => $bank,
            "branch" => $branch,
            "active" => 1
        ]);

        return response()->json(["status" => "success", "message" => "Bank Account Added Successfully !"], 200);
    }

    public function packages()
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $admin = Admin::find(session('admin_id'));
        return view('packages', ['admin' => $admin]);
    }

    public function add_cyber()
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $admin = Admin::find(session('admin_id'));
        return view('add_cyber', ['admin' => $admin]);
    }

    public function settlements_batch($encid)
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $admin = Admin::find(session('admin_id'));
        $id = $this->decrypt($encid);
        if (!($id > 0)) {
            return redirect()->route('home');
        }

        $settlement = Settlement::find($id);
        if (!$settlement) {
            return redirect()->route('admin.settlements.index', ['status' => 'pending']);
        }

        return view('settlements_batch', [
            'admin' => $admin,
            'settlement' => $settlement
        ]);
    }

    public function package_slabs($encId)
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $packageId = $this->decrypt($encId);
        if (!($packageId > 0)) {
            return redirect()->route('admin.packages.index');
        }

        $package = Package::find($packageId);
        if (!$package || $package['name'] == "") {
            return redirect()->route('admin.packages.index');
        }

        $admin = Admin::find(session('admin_id'));
        $resultSlabs = [
            "payin" => [],
            "payout" => []
        ];

        $slabs = Packageslab::where("package_id", $packageId)->get();
        foreach ($slabs as $slab) {
            $resultSlabs[$slab['service']][] = $slab;
        }

        return view('package_slabs', [
            'admin' => $admin,
            'package' => $package,
            'slabs' => $resultSlabs
        ]);
    }

    public function provider_slabs(Request $request)
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $admin = Admin::find(session('admin_id'));
        $providers = Providers::where('active', 1)->orderBy('name', 'ASC')->get();
        $selectedProviderId = $request->query('provider');
        if (!$selectedProviderId && $providers->count() > 0) {
            $selectedProviderId = $providers->first()->id;
        }

        $currentProvider = $providers->firstWhere('id', $selectedProviderId);
        if (!$currentProvider && $providers->count() > 0) {
            $currentProvider = $providers->first();
        }

        $providerSlabs = $currentProvider ? Providerslab::where('provider_id', $currentProvider->id)->orderBy('min_amount', 'ASC')->get() : collect();

        return view('provider_slabs', [
            'admin' => $admin,
            'page' => 'provider_slabs',
            'providers' => $providers,
            'currentProvider' => $currentProvider,
            'providerSlabs' => $providerSlabs,
            'gstRate' => Settings::getGSTRate()
        ]);
    }

    public function bank_users($encId)
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $bankId = $this->decrypt($encId);
        if (!($bankId > 0)) {
            return redirect()->route('admin.fund-request-banks.index');
        }

        $bank = FundRequestBank::find($bankId);
        if (!$bank || $bank['name'] == "") {
            return redirect()->route('admin.fund-request-banks.index');
        }

        $admin = Admin::find(session('admin_id'));
        return view('bank_users', [
            'admin' => $admin,
            'bank' => $bank
        ]);
    }

    public function edit_package($encId)
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $packageId = $this->decrypt($encId);
        if (!($packageId > 0)) {
            return redirect()->route('admin.packages.index');
        }

        $package = Package::find($packageId);
        if (!$package || $package['name'] == "") {
            return redirect()->route('admin.packages.index');
        }

        $admin = Admin::find(session('admin_id'));
        return view('edit_package', [
            'admin' => $admin,
            'package' => $package,
            'package_id' => $this->encrypt($package['id'])
        ]);
    }

    public function update_package(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        $input = (object) $request->all();
        $package_id = $this->decrypt($input->package_id ?? '');
        if (!($package_id > 0)) {
            return response()->json(["status" => "error", "message" => "Invalid Package ID"], 400);
        }

        $name = $input->name ?? '';
        if (strlen($name) < 3 || strlen($name) > 20) {
            return response()->json(["status" => "error", "message" => "Package Name should have characters between 3  - 20"], 400);
        }

        Package::where('id', $package_id)->update(["name" => $name]);
        return response()->json(["status" => "success", "message" => "Package Updated Successfully"], 200);
    }

    public function settlement_packages()
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        SettlementPackage::ensureSlotsTableExists();
        $admin = Admin::find(session('admin_id'));
        return view('settlement_packages', [
            'admin' => $admin,
            'page' => 'settlement_packages'
        ]);
    }

    public function add_settlement_package()
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        SettlementPackage::ensureSlotsTableExists();
        $admin = Admin::find(session('admin_id'));
        return view('add_settlement_package', [
            'admin' => $admin,
            'page' => 'add_settlement_package'
        ]);
    }

    public function edit_settlement_package($encId)
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        SettlementPackage::ensureSlotsTableExists();
        $packageId = $this->decrypt($encId);
        if (!($packageId > 0)) {
            return redirect()->route('admin.settlement-packages.index');
        }

        $package = SettlementPackage::with('slots')->find($packageId);
        if (!$package) {
            return redirect()->route('admin.settlement-packages.index');
        }

        $admin = Admin::find(session('admin_id'));
        return view('edit_settlement_package', [
            'admin' => $admin,
            'package' => $package,
            'package_id' => $this->encrypt($package->id),
            'page' => 'settlement_packages'
        ]);
    }

    public function create_settlement_package(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        SettlementPackage::ensureSlotsTableExists();

        $input = (object) $request->all();
        $name = trim($input->name ?? '');

        if (strlen($name) < 3) {
            return response()->json(["status" => "error", "message" => "Package name must be at least 3 characters"], 400);
        }

        if (SettlementPackage::where('name', $name)->exists()) {
            return response()->json(["status" => "error", "message" => "Settlement Package Name already exists"], 429);
        }

        $slots = $input->slots ?? [];
        if (!is_array($slots) || count($slots) === 0) {
            return response()->json(["status" => "error", "message" => "Please configure at least one settlement time slot"], 400);
        }

        $slotData = [];
        $slotNum = 1;
        foreach ($slots as $slot) {
            $slotObj = (object) $slot;
            $settlementTime = trim($slotObj->settlement_time ?? '');
            $txnFrom = trim($slotObj->txn_from_time ?? '');
            $txnTo = trim($slotObj->txn_to_time ?? '');

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

        DB::beginTransaction();
        try {
            $createdPackage = SettlementPackage::create([
                'name' => $name
            ]);

            foreach ($slotData as $s) {
                $s['package_id'] = $createdPackage->id;
                SettlementPackageSlot::create($s);
            }

            DB::commit();
            return response()->json([
                "status" => "success",
                "message" => "Settlement Package created successfully with " . count($slotData) . " time slots"
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "status" => "error",
                "message" => "Failed to create settlement package: " . $e->getMessage()
            ], 500);
        }
    }

    public function update_settlement_package(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        SettlementPackage::ensureSlotsTableExists();

        $input = (object) $request->all();
        $packageId = $this->decrypt($input->package_id ?? '');

        if (!($packageId > 0)) {
            return response()->json(["status" => "error", "message" => "Invalid Package ID"], 400);
        }

        $name = trim($input->name ?? '');
        if (strlen($name) < 3) {
            return response()->json(["status" => "error", "message" => "Package Name must be at least 3 characters"], 400);
        }

        $package = SettlementPackage::find($packageId);
        if (!$package) {
            return response()->json(["status" => "error", "message" => "Settlement Package not found"], 404);
        }

        if (SettlementPackage::where('name', $name)->where('id', '!=', $packageId)->exists()) {
            return response()->json(["status" => "error", "message" => "Another Settlement Package with this name already exists"], 429);
        }

        $slots = $input->slots ?? [];
        if (!is_array($slots) || count($slots) === 0) {
            return response()->json(["status" => "error", "message" => "Please configure at least one settlement time slot"], 400);
        }

        $slotData = [];
        $slotNum = 1;
        foreach ($slots as $slot) {
            $slotObj = (object) $slot;
            $settlementTime = trim($slotObj->settlement_time ?? '');
            $txnFrom = trim($slotObj->txn_from_time ?? '');
            $txnTo = trim($slotObj->txn_to_time ?? '');

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

        DB::beginTransaction();
        try {
            $package->update(['name' => $name]);
            SettlementPackageSlot::where('package_id', $packageId)->delete();
            foreach ($slotData as $s) {
                SettlementPackageSlot::create($s);
            }
            DB::commit();
            return response()->json([
                "status" => "success",
                "message" => "Settlement Package updated successfully"
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "status" => "error",
                "message" => "Failed to update package: " . $e->getMessage()
            ], 500);
        }
    }

    public function delete_settlement_package(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        $input = (object) $request->all();
        $packageId = $this->decrypt($input->package_id ?? '');

        if (!($packageId > 0)) {
            return response()->json(["status" => "error", "message" => "Invalid Package ID"], 400);
        }

        $userCount = User::where('settlement_package', $packageId)->count();
        if ($userCount > 0) {
            return response()->json([
                "status" => "error",
                "message" => "Cannot delete package: It is currently assigned to {$userCount} user(s)."
            ], 400);
        }

        DB::beginTransaction();
        try {
            SettlementPackageSlot::where('package_id', $packageId)->delete();
            SettlementPackage::where('id', $packageId)->delete();
            DB::commit();
            return response()->json(["status" => "success", "message" => "Settlement Package deleted successfully"], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(["status" => "error", "message" => "Failed to delete: " . $e->getMessage()], 500);
        }
    }

    public function update_bank_users(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        $bankId = $request->input('bank');
        $users = $request->input('users');

        FundRequestBank::where('id', $bankId)->update(["users" => $users]);

        return response()->json(["status" => "success", "message" => "Users Updated Successfully"], 200);
    }

    public function settle_to_wallet(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        $encId = $request->input('id');
        $id = $this->decrypt($encId);
        if (!($id > 0)) {
            return response()->json(["status" => "error", "message" => "Invalid Settlement Id"], 400);
        }

        $settlement = Settlement::find($id);
        if (!$settlement || (!in_array($settlement['status'], ["Pending", "Hold"]) && $settlement['credited'] == 1)) {
            return response()->json(["status" => "error", "message" => "Settlement is not in Pending or Hold Stage."], 400);
        }

        DB::beginTransaction();
        try {
            $user = User::lockForUpdate()->find($settlement['user_id']);
            if (!$user) {
                DB::rollBack();
                return response()->json(["status" => "error", "message" => "User not found"], 404);
            }

            $opening = floatval($user['wallet'] ?? 0);
            $netAmount = floatval($settlement['net_amount']);
            $closing = number_format($opening + $netAmount, 2, '.', '');

            User::where('id', $user['id'])->update(['wallet' => $closing]);

            Wallet::create([
                'user_id' => $user['id'],
                'type' => 'CREDIT',
                'txn_type' => 'SETTLEMENT',
                'txnid' => $settlement['txnid'],
                'opening' => number_format($opening, 2, '.', ''),
                'amount' => number_format(floatval($settlement['amount']), 2, '.', ''),
                'charges' => number_format(floatval($settlement['charges']), 2, '.', ''),
                'gst' => number_format(floatval($settlement['gst']), 2, '.', ''),
                'net_amount' => number_format($netAmount, 2, '.', ''),
                'remarks' => 'Settlement credited to wallet (Batch: ' . $settlement['batch_id'] . ')',
                'processed' => 1,
                'closing' => $closing
            ]);

            Settlement::where('id', $id)->update([
                "status" => "Success",
                "settle_to" => "wallet",
                "credited" => 1
            ]);

            DB::commit();
            return response()->json(["status" => "success", "message" => "Amount Settled to User's Wallet !"], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(["status" => "error", "message" => "Failed to settle: " . $e->getMessage()], 500);
        }
    }

    public function trigger_auto_settlements(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        $date = $request->input('date');
        $time = $request->input('time');

        $result = AutoSettlement::process($date, $time, true);

        return response()->json([
            "status" => "success",
            "message" => "Processed automatic settlements: {$result['settlements_created']} batch(es) created, ₹" . number_format($result['total_amount_settled'], 2) . " settled ({$result['eligible_auto_users_count']} auto users evaluated, {$result['skipped_manual_users_count']} manual users skipped).",
            "data" => $result
        ], 200);
    }

    public function preview_manual_settlement(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        $userId = $request->input('user_id');
        $fromTime = $request->input('from_time');
        $toTime = $request->input('to_time');

        if (!($userId > 0)) {
            return response()->json(["status" => "error", "message" => "Please select a valid merchant"], 400);
        }

        if (empty($fromTime) || empty($toTime)) {
            return response()->json(["status" => "error", "message" => "Please select both From and To date & time"], 400);
        }

        $startDate = Carbon::parse($fromTime)->toDateTimeString();
        $endDate = Carbon::parse($toTime)->toDateTimeString();

        if ($startDate > $endDate) {
            return response()->json(["status" => "error", "message" => "From date & time cannot be after To date & time"], 400);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(["status" => "error", "message" => "Merchant not found"], 404);
        }

        $query = Transaction::selectRaw('count(id) as total_count, sum(amount) as total_amount, sum(charges) as total_charges, sum(gst) as total_gst')
            ->where('service', 'payin')
            ->where('status', 'Success')
            ->where('settled', 0)
            ->where('user_id', $userId)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where(function ($q1) use ($startDate, $endDate) {
                    $q1->whereNotNull('success_at')
                       ->where('success_at', '>=', $startDate)
                       ->where('success_at', '<=', $endDate);
                })->orWhere(function ($q2) use ($startDate, $endDate) {
                    $q2->whereNull('success_at')
                       ->whereNotNull('txn_complete_time')
                       ->where('txn_complete_time', '>=', $startDate)
                       ->where('txn_complete_time', '<=', $endDate);
                });
            });

        $result = $query->first();
        $count = (int) ($result['total_count'] ?? 0);
        $totalAmount = round((float) ($result['total_amount'] ?? 0), 2);
        $totalCharges = round((float) ($result['total_charges'] ?? 0), 2);
        $totalGst = round((float) ($result['total_gst'] ?? 0), 2);
        $netAmount = round($totalAmount - $totalCharges - $totalGst, 2);

        return response()->json([
            "status" => "success",
            "data" => [
                "user_name" => $user->name,
                "merchant_id" => $user->merchant_id,
                "settlement_type" => ucfirst($user->settlement_type ?? 'manual'),
                "count" => $count,
                "total_amount" => $totalAmount,
                "total_charges" => $totalCharges,
                "total_gst" => $totalGst,
                "net_amount" => $netAmount,
                "from_time" => $startDate,
                "to_time" => $endDate
            ]
        ], 200);
    }

    public function create_manual_settlement(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        $userId = $request->input('user_id');
        $fromTime = $request->input('from_time');
        $toTime = $request->input('to_time');

        if (!($userId > 0)) {
            return response()->json(["status" => "error", "message" => "Please select a valid merchant"], 400);
        }

        if (empty($fromTime) || empty($toTime)) {
            return response()->json(["status" => "error", "message" => "Please select both From and To date & time"], 400);
        }

        $startDate = Carbon::parse($fromTime)->toDateTimeString();
        $endDate = Carbon::parse($toTime)->toDateTimeString();

        if ($startDate > $endDate) {
            return response()->json(["status" => "error", "message" => "From date & time cannot be after To date & time"], 400);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(["status" => "error", "message" => "Merchant not found"], 404);
        }

        $settlement = AutoSettlement::process_settlement($startDate, $endDate, $userId);

        if (!$settlement) {
            return response()->json([
                "status" => "error",
                "message" => "No unsettled payin transactions found for {$user->name} between {$startDate} and {$endDate}."
            ], 400);
        }

        return response()->json([
            "status" => "success",
            "message" => "Settlement Batch created successfully! Batch ID: {$settlement->batch_id}, Net Amount: ₹" . number_format($settlement->net_amount, 2),
            "data" => [
                "settlement_id" => $settlement->id,
                "batch_id" => $settlement->batch_id,
                "txnid" => $settlement->txnid,
                "amount" => $settlement->amount,
                "charges" => $settlement->charges,
                "gst" => $settlement->gst,
                "net_amount" => $settlement->net_amount,
                "status" => $settlement->status
            ]
        ], 200);
    }

    public function settle_to_bank($encId)
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $admin = Admin::find(session('admin_id'));
        $id = $this->decrypt($encId);
        if (!($id > 0)) {
            return redirect()->route('home');
        }

        $settlement = Settlement::find($id);
        if (!$settlement || !isset($settlement['status']) || !in_array($settlement['status'], ["Pending", "Hold"])) {
            return redirect()->route('home');
        }

        return view('settle_to_bank', [
            'admin' => $admin,
            'settlement' => $settlement,
            'settlement_id' => $encId
        ]);
    }

    public function hold_settlement(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        $encId = $request->input('id');
        $id = $this->decrypt($encId);
        if (!($id > 0)) {
            return response()->json(["status" => "error", "message" => "Invalid Settlement Id"], 400);
        }

        $settlement = Settlement::find($id);
        if (!$settlement || $settlement['status'] != "Pending") {
            return response()->json(["status" => "error", "message" => "Settlement is not in Pending Stage."], 400);
        }

        Settlement::where('id', $id)->update(["status" => "Hold"]);
        return response()->json(["status" => "success", "message" => "Settlement Hold Successfully !"], 200);
    }

    public function update_fund_request(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        $encId = $request->input('id');
        $id = $this->decrypt($encId);
        $status = $request->input('status');
        $reason = $request->input('reason');
        $pin = $request->input('pin');

        if (!($id > 0)) {
            return response()->json(["status" => "error", "message" => "Invalid Settlement Id"], 400);
        }

        if (!in_array($status, ["Success", "Failed"])) {
            return response()->json(["status" => "error", "message" => "Invalid Status"], 400);
        }

        if (strlen($pin) != 6) {
            return response()->json(["status" => "error", "message" => "Invalid or Wrong PIN"], 400);
        }

        $admin = Admin::find(session('admin_id'));
        if ($admin['pin'] != $pin) {
            return response()->json(["status" => "error", "message" => "Invalid or Wrong PIN"], 400);
        }

        $fundRequest = FundRequest::find($id);
        if (!$fundRequest || $fundRequest['status'] != "Pending") {
            return response()->json(["status" => "error", "message" => "Fund request is not in Pending Stage."], 400);
        }

        FundRequest::where('id', $id)->update([
            "status" => $status,
            "reason" => $reason
        ]);
        return response()->json(["status" => "success", "message" => $status == "Success" ? "Fund Request Accepted Successfully !" : "Fund Request Rejected Successfully !"], 200);
    }

    public function update_package_slab(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        $input = $request->all();
        $service = $input["service"] ?? "";
        $packageId = $input["package_id"] ?? "";

        if (!in_array($service, ["payin", "payout"])) {
            return response()->json(["status" => "error", "message" => "Invalid Service Type"], 400);
        }

        $package = Package::find($packageId);
        if (!$package || $package['name'] == "") {
            return response()->json(["status" => "error", "message" => "Invalid Package Id"], 400);
        }

        Packageslab::where('service', $service)->where("package_id", $packageId)->delete();
        for ($i = 1; $i < 7; $i++) {
            $min_amount = $input["min_amount_$i"] ?? "";
            $max_amount = $input["max_amount_$i"] ?? "";
            $charge_amount = $input["charge_amount_$i"] ?? "";
            $percent = isset($input["percent_$i"]) ? 1 : 0;
            $gst = isset($input["gst_$i"]) ? 1 : 0;
            if ($min_amount != "" && $max_amount != "" && $charge_amount != "") {
                Packageslab::create([
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

    public function update_provider_slab(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        $input = $request->all();
        $providerId = $input["provider_id"] ?? null;
        $provider = Providers::find($providerId);
        if (!$provider) {
            return response()->json(["status" => "error", "message" => "Invalid Provider Id"], 400);
        }

        Providerslab::where("provider_id", $providerId)->delete();

        for ($i = 1; $i <= 8; $i++) {
            $min_amount = $input["min_amount_$i"] ?? "";
            $max_amount = $input["max_amount_$i"] ?? "";
            $charge_amount = $input["charge_amount_$i"] ?? "";
            $percent = isset($input["percent_$i"]) ? 1 : 0;
            $gst = isset($input["gst_$i"]) ? 1 : 0;
            if ($min_amount !== "" && $max_amount !== "" && $charge_amount !== "") {
                Providerslab::create([
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

    public function update_user_switch(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        $user_id = $request->input('user_id');
        $service = $request->input('service');
        $value = $request->input('value');

        if (!in_array($service, ["active", "payin", "payout"])) {
            return response()->json(["status" => "error", "message" => "Invalid Service"], 400);
        }

        if (!in_array($value, [0, 1])) {
            return response()->json(["status" => "error", "message" => "Invalid Value"], 400);
        }

        if (!($user_id > 0)) {
            return response()->json(["status" => "error", "message" => "Invalid User Id"], 400);
        }

        User::where('id', $user_id)->update([$service => $value]);
        return response()->json(["status" => "success", "message" => "Switch Updated Successfully !"], 200);
    }

    public function update_user_lock_switch(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        $user_id = $request->input('user_id');
        if (!($user_id > 0)) {
            return response()->json(["status" => "error", "message" => "Invalid User Id"], 400);
        }

        $user = User::find($user_id);
        if ($user) {
            $user->update(["locked" => $user['locked'] == 1 ? 0 : 1]);
        }
        return response()->json(["status" => "success", "message" => "User Locked / Unlocked Successfully !"], 200);
    }

    public function update_bank_switch(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        $bank_id = $request->input('bank_id');
        $value = $request->input('value');

        if (!in_array($value, [0, 1])) {
            return response()->json(["status" => "error", "message" => "Invalid Value"], 400);
        }

        if (!($bank_id > 0)) {
            return response()->json(["status" => "error", "message" => "Invalid Bank Id"], 400);
        }

        FundRequestBank::where('id', $bank_id)->update(["active" => $value]);
        return response()->json(["status" => "success", "message" => "Switch Updated Successfully !"], 200);
    }

    private function encrypt($text)
    {
        return bin2hex(openssl_encrypt($text, 'AES-256-CBC', env("encryption.key"), OPENSSL_RAW_DATA, substr(hash('sha256', env("encryption.key")), 0, 16)));
    }

    private function decrypt($encryptedText)
    {
        $key = env("encryption.key");
        $iv = substr(hash('sha256', $key), 0, 16);
        $cipherText = hex2bin($encryptedText);
        return openssl_decrypt($cipherText, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }

    public function user($encId)
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $user_id = $this->decrypt($encId);
        $admin = Admin::find(session('admin_id'));
        $user = User::find($user_id);
        $unsettled_amount = Transaction::where('user_id', $user_id)
            ->where('service', 'payin')
            ->where('status', 'Success')
            ->where('settled', 0)
            ->sum('net_amount');

        return view("user", [
            'admin' => $admin,
            'user' => $user,
            'unsettled_amount' => $unsettled_amount
        ]);
    }

    public function update_user(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        $userId = $request->input('user_id');
        $package = $request->input('package');
        $payinProvider = $request->input('payin_provider');
        $payoutProvider = $request->input('payout_provider');
        $payinLimit = $request->input('payin_limit', 0);
        $payoutLimit = $request->input('payout_limit', 0);
        $payoutPerLimit = $request->input('payout_per_limit', 0);
        $settlementPackage = $request->input('settlement_package');
        $rawSettlementType = $request->input('settlement_type', 'manual');
        $settlementType = in_array(strtolower($rawSettlementType), ['automatic', 'manual']) ? strtolower($rawSettlementType) : 'manual';

        if (!$userId) {
            return response()->json(["status" => "error", "message" => "Invalid User ID"], 400);
        }

        User::where('id', $userId)->update([
            "package" => $package,
            "payin_provider" => $payinProvider,
            "payout_provider" => $payoutProvider,
            "payin_limit" => $payinLimit,
            "payout_limit" => $payoutLimit,
            "payout_per_limit" => $payoutPerLimit,
            "settlement_package" => $settlementPackage,
            "settlement_type" => $settlementType
        ]);

        return response()->json(["status" => "success", "message" => "User Updated Successfully"], 200);
    }

    public function update_user_info(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        $input = (object) $request->all();
        if (!Validators::name($input->name ?? '')) {
            return response()->json([
                "status" => "error",
                "message" => "Please enter a valid first and last name."
            ], 400);
        }

        if (!Validators::email($input->email ?? '')) {
            return response()->json([
                "status" => "error",
                "message" => "Please provide a valid email address."
            ], 400);
        }

        if (!Validators::phone($input->phone ?? '')) {
            return response()->json([
                "status" => "error",
                "message" => "Please provide a valid phone number."
            ], 400);
        }

        if (User::where('id', '!=', $input->user_id)->where("email", $input->email)->exists()) {
            return response()->json(["status" => "error", "message" => "This email address is already registered with another user."], 400);
        }

        if (User::where('id', '!=', $input->user_id)->where("phone", $input->phone)->exists()) {
            return response()->json(["status" => "error", "message" => "This phone number is already registered with another user."], 400);
        }

        $udata = [
            "name" => strtoupper($input->name),
            "email" => $input->email,
            "phone" => $input->phone,
        ];

        if (!empty($input->password)) {
            if (!Validators::password($input->password)) {
                return response()->json([
                    "status" => "error",
                    "message" => "Password must be at least 8 characters long and include uppercase, lowercase, number, and special character."
                ], 400);
            }
            $udata['password'] = Hash::make($input->password);
        }

        User::where('id', $input->user_id)->update($udata);
        return response()->json(["status" => "success", "message" => "User Details Successfully"], 200);
    }

    public function manage_users_fund(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        $input = (object) $request->all();

        if (!in_array($input->action ?? '', ["CREDIT", "DEBIT", "HOLD"])) {
            return response()->json([
                "status" => "error",
                "message" => "Please select valid action (CREDIT, DEBIT, or HOLD)"
            ], 400);
        }

        if (!Validators::amount($input->amount ?? 0)) {
            return response()->json([
                "status" => "error",
                "message" => "Please provide a valid amount."
            ], 400);
        }

        if (strlen($input->remarks ?? '') > 100) {
            return response()->json([
                "status" => "error",
                "message" => "Length of remarks should be less than or equal to 100"
            ], 400);
        }

        DB::beginTransaction();
        try {
            $user = User::lockForUpdate()->find($input->user_id);
            if (!$user) {
                DB::rollBack();
                return response()->json(["status" => "error", "message" => "User not found"], 404);
            }

            $opening = floatval($user['wallet'] ?? 0);
            $amount = floatval($input->amount);
            $txnid = $this->generateTxnId();

            if ($input->action == "CREDIT") {
                $closing = number_format($opening + $amount, 2, '.', '');
                User::where('id', $user['id'])->update(['wallet' => $closing]);

                Wallet::create([
                    "user_id" => $user['id'],
                    "type" => "CREDIT",
                    "txn_type" => "CREDIT",
                    "txnid" => $txnid,
                    "amount" => number_format($amount, 2, '.', ''),
                    "charges" => "0",
                    "gst" => "0",
                    "net_amount" => number_format($amount, 2, '.', ''),
                    "processed" => 1,
                    "remarks" => $input->remarks,
                    "opening" => number_format($opening, 2, '.', ''),
                    "closing" => $closing
                ]);
            }

            if ($input->action == "DEBIT") {
                if ($opening < $amount) {
                    DB::rollBack();
                    return response()->json([
                        "status" => "error",
                        "message" => "Insufficient wallet balance to complete this transaction."
                    ], 400);
                }
                $closing = number_format($opening - $amount, 2, '.', '');
                User::where('id', $user['id'])->update(['wallet' => $closing]);

                Wallet::create([
                    "user_id" => $user['id'],
                    "type" => "DEBIT",
                    "txn_type" => "DEBIT",
                    "txnid" => $txnid,
                    "amount" => number_format($amount, 2, '.', ''),
                    "charges" => "0",
                    "gst" => "0",
                    "net_amount" => number_format($amount, 2, '.', ''),
                    "processed" => 1,
                    "remarks" => $input->remarks,
                    "opening" => number_format($opening, 2, '.', ''),
                    "closing" => $closing
                ]);
            }

            if ($input->action == "HOLD") {
                if ($opening < $amount) {
                    DB::rollBack();
                    return response()->json([
                        "status" => "error",
                        "message" => "Insufficient wallet balance to place on hold."
                    ], 400);
                }
                $closing = number_format($opening - $amount, 2, '.', '');
                $holdOpening = floatval($user['hold_wallet'] ?? 0);
                $holdClosing = number_format($holdOpening + $amount, 2, '.', '');

                User::where('id', $user['id'])->update([
                    'wallet' => $closing,
                    'hold_wallet' => $holdClosing
                ]);

                HoldWallet::create([
                    "user_id" => $user['id'],
                    "type" => "hold",
                    "txnid" => $txnid,
                    "amount" => number_format($amount, 2, '.', ''),
                    "processed" => 1,
                    "reason" => $input->remarks,
                    "opening" => number_format($holdOpening, 2, '.', ''),
                    "closing" => $holdClosing
                ]);

                Wallet::create([
                    "user_id" => $user['id'],
                    "type" => "DEBIT",
                    "txn_type" => "HOLD",
                    "txnid" => $txnid,
                    "amount" => number_format($amount, 2, '.', ''),
                    "charges" => "0",
                    "gst" => "0",
                    "net_amount" => number_format($amount, 2, '.', ''),
                    "processed" => 1,
                    "remarks" => $input->remarks,
                    "opening" => number_format($opening, 2, '.', ''),
                    "closing" => $closing
                ]);
            }

            DB::commit();
            return response()->json(["status" => "success", "message" => "Transaction Completed Successfully"], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(["status" => "error", "message" => "Transaction Failed: " . $e->getMessage()], 500);
        }
    }

    public function providers($type)
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        if (!in_array($type, ["payin", "payout"])) {
            return redirect()->route('home');
        }

        $admin = Admin::find(session('admin_id'));
        return view("providers", [
            'admin' => $admin,
            'type' => $type
        ]);
    }

    public function wallet()
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $admin = Admin::find(session('admin_id'));
        return view('wallet', ['admin' => $admin]);
    }

    public function settings()
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $admin = Admin::find(session('admin_id'));
        return view('settings', ['admin' => $admin]);
    }

    public function transactions($type)
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        if (!in_array($type, ["all", "payin", "payout", "qr"])) {
            return redirect()->route('home');
        }

        $admin = Admin::find(session('admin_id'));
        return view('transactions', [
            'admin' => $admin,
            'type' => $type
        ]);
    }

    public function login_user(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        $user_id = $request->input('uid');
        $token = time() . rand(11111111111, 99999999999);
        User::where('id', $user_id)->update(["ad_token" => $token]);
        return response()->json(["status" => "success", "message" => "Session Created !", "token" => $token], 200);
    }

    public function fetch_dashboard_data(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json(["status" => "error", "message" => "Invalid Session"], 401);
        }

        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');
        $usersBy = $request->query('usersBy');
        $reportType = $request->query('reportType');
        $reportUser = $request->query('reportUser');
        $reportBy = $request->query('reportBy');

        if (!Validators::date($startDate) || !Validators::date($endDate)) {
            return response()->json(["status" => "error", "message" => "Invalid Date Format"], 400);
        }
        $startDate = $startDate . " 00:00:00";
        $endDate = $endDate . " 23:59:59";

        if (!in_array($usersBy, ["total", "payin", "payout"])) {
            return response()->json(["status" => "error", "message" => "Invalid Users By Filter"], 400);
        }

        if (!in_array($reportType, ["all", "payin", "payout"])) {
            return response()->json(["status" => "error", "message" => "Invalid Report Type Filter"], 400);
        }

        if (!in_array($reportBy, ["weekly", "monthly"])) {
            return response()->json(["status" => "error", "message" => "Invalid Report By Filter"], 400);
        }

        if ($reportUser != "all" && !($reportUser > 0)) {
            return response()->json(["status" => "error", "message" => "Invalid Report User Filter"], 400);
        }

        $transactionModel = new Transaction();
        $response = [
            "summary" => $transactionModel->getDashboardSummary($startDate, $endDate),
            "users" => $transactionModel->getTopUsers($startDate, $endDate, $usersBy),
            "providers" => $transactionModel->getProviders($startDate, $endDate),
            "status" => $transactionModel->getStatus($startDate, $endDate),
            "report" => $transactionModel->getReport($startDate, $endDate, $reportType, $reportUser, $reportBy)
        ];
        return response()->json($response, 200);
    }

    public function transaction_info(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        $txnid = $request->input('txnid');
        if (strlen($txnid) < 5) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Txnid'
            ], 400);
        }

        $transaction = Transaction::where("txnid", $txnid)->first();
        if ($transaction && $transaction['service'] == "payout" && $transaction['status'] == "Success") {
            $date = Carbon::parse($transaction['txn_complete_time']);
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
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Txnid'
            ], 400);
        }
    }

    public function update_settings(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        $name = $request->input('name');
        $value = $request->input('value');
        if (empty($name)) {
            return response()->json(["status" => "error", "message" => "Setting name is required"], 400);
        }

        Settings::updateOrCreate(["name" => $name], ["value" => $value]);
        return response()->json(["status" => "success", "message" => "Settings Updated Successfully"], 200);
    }

    public function update_user_bank_status(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        $id = $request->input('id');
        $action = $request->input('status');
        if (!in_array($action, ["approve", "reject"])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Action Submitted'
            ], 400);
        }

        $db = DB::connection();
        if ($db->table('users_bank')->where('id', $id)->where('status', 'Pending')->count() == 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Bank Id'
            ], 400);
        }

        if ($action == "approve") {
            $db->table('users_bank')->where("id", $id)->update(["status" => "Approved"]);
        } else {
            $db->table('users_bank')->where('id', $id)->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Bank Updated Successfully !'
        ], 200);
    }

    public function update_settlement_to_bank(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        $input = (object) $request->all();
        $id = $this->decrypt($input->id ?? '');
        if (!($id > 0)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Settlement Id'
            ], 400);
        }

        $settlement = Settlement::find($id);
        if (!$settlement || !in_array($settlement['status'], ["Pending", "Hold"])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Settlement is not in pending or hold stage'
            ], 400);
        }

        $bankAccount = DB::connection()->table('users_bank')->where('status', 'Approved')->where('user_id', $settlement['user_id'])->first();

        Settlement::where('id', $settlement['id'])->update([
            "settle_to" => "bank",
            "reference" => $input->utr ?? '',
            "bank_id" => $bankAccount ? $bankAccount->id : null,
            "remarks" => $input->remarks ?? '',
            "credited" => 1,
            "status" => "Success"
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Settlement Updated Successfully !'
        ], 200);
    }

    public function update_provider(Request $request, $id)
    {
        if (!session('admin_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        $input = (object) $request->all();
        $udata = [];
        $udata["name"] = $input->name ?? '';
        $udata["gateway"] = $input->gateway ?? '';
        if (isset($input->daily_limit) && $input->daily_limit != "") {
            $udata["daily_limit"] = $input->daily_limit;
        }

        $provider = Providers::find($id);
        if (!$provider) {
            return response()->json([
                'status' => 'error',
                'message' => 'Provider not found'
            ], 404);
        }

        if (isset($input->logo) && strlen($input->logo) > 100) {
            $newLogo = $this->handleBase64Upload($input->logo, $provider->getRawOriginal('logo') ?? $provider->logo, 'assets', 'provider-logo-');
            if ($newLogo) {
                $udata['logo'] = $newLogo;
            }
        }

        Providers::where('id', $id)->update($udata);
        return response()->json([
            'status' => 'success',
            'message' => 'Provider Details Updated Successfully'
        ], 200);
    }

    public function create_provider(Request $request)
    {
        if (!session('admin_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        $input = (object) $request->all();
        if (!$input || empty($input->name) || empty($input->gateway) || empty($input->type)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Provider Name, Gateway Name, and Type are required'
            ], 400);
        }

        if (!in_array($input->type, ['payin', 'payout'])) {
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

        if (isset($input->logo) && strlen($input->logo) > 100) {
            $newLogo = $this->uploadBase64File($input->logo, 'assets', 'provider-logo-');
            if ($newLogo) {
                $pdata['logo'] = $newLogo;
            }
        }

        $newProvider = Providers::create($pdata);

        return response()->json([
            'status' => 'success',
            'message' => 'Provider Created Successfully',
            'provider_id' => $newProvider->id,
            'type' => $input->type
        ], 200);
    }

    public function error_logs()
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $admin = Admin::find(session('admin_id'));
        $providers = Providers::all();
        return view('error_logs', [
            'admin' => $admin,
            'providers' => $providers
        ]);
    }

    public function accounting_ledger(Request $request)
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $admin = Admin::find(session('admin_id'));
        $providers = Providers::all();
        $panels = AccountLedger::select('panel')->distinct()->pluck('panel');

        return view('accounting_ledger', [
            'admin' => $admin,
            'providers' => $providers,
            'panels' => $panels,
            'page' => 'accounting_ledger',
        ]);
    }
}

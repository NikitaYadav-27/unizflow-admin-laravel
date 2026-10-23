<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Home;
use App\Http\Controllers\Tables;
// use App\Http\Controllers\Demo;
// use App\Http\Controllers\AutoCommission;
// use App\Http\Controllers\Checker;

Route::get('/', [Home::class, 'index']);
Route::get('/admin/login', [Auth::class, 'login']);
Route::get('/admin/register', [Auth::class, 'register']);
Route::get('/admin/dashboard', [Home::class, 'dashboard']);
Route::post('/api/admin/login', [Auth::class, 'login_admin']);
Route::get('/admin/login-verification', [Auth::class, 'verify_login']);
Route::post('/api/admin/login/verification', [Auth::class, 'verify_login_otp']);
Route::get('/admin/logout', [Auth::class, 'logout']);
Route::get('/auth/login/token', [Auth::class, 'ad_login']);

Route::get('/admin/packages/add', [Home::class, 'add_package']);
Route::get('/admin/packages', [Home::class, 'packages']);
Route::get('/admin/package/slabs/{id}', [Home::class, 'package_slabs']);
Route::get('/admin/package/edit/{id}', [Home::class, 'edit_package']);
Route::post('/api/admin/update_package_slab', [Home::class, 'update_package_slab']);
Route::post('/api/admin/package/update', [Home::class, 'update_package']);

Route::get('/admin/wallet', [Home::class, 'wallet']);
Route::get('/admin/staff/add', [Home::class, 'add_staff']);

Route::get('/api/tables/wallet', [Tables::class, 'wallet']);

Route::get('/api/admin/dashboard/fetch-data', [Home::class, 'fetch_dashboard_data']);
Route::post('/api/admin/update-provider-slab', [Home::class, 'update_provider_slab']);

Route::post('/api/admin/package/add', [Home::class, 'create_package']);
Route::get('/api/tables/packages', [Tables::class, 'packages']);

Route::post('/api/admin/get-bill-data', [Home::class, 'get_bill_data']);

Route::get('/admin/users/{status}', [Home::class, 'users']);
Route::get('/admin/user/{id}', [Home::class, 'user']);
Route::post('/api/admin/update-user', [Home::class, 'update_user']);
Route::get('/api/tables/users', [Tables::class, 'users']);
Route::post('/api/users/update-switch', [Home::class, 'update_user_switch']);

Route::post('/api/admin/update-bank-users', [Home::class, 'update_bank_users']);

Route::post('/api/banks/update-switch', [Home::class, 'update_bank_switch']);
Route::get('/admin/bank-users/{id}', [Home::class, 'bank_users']);

Route::get('/admin/providers/{type}', [Home::class, 'providers']);
Route::get('/admin/provider-slabs', [Home::class, 'provider_slabs']);

// Route::get('/demo', [Demo::class, 'index']);

Route::post('/api/admin/create-static-qr', [Home::class, 'create_static_qr']);
Route::post('/api/admin/update-qr-user', [Home::class, 'update_qr_user']);

Route::post('/api/admin/user/bulk-action', [Home::class, 'users_bulk_action']);

Route::get('/admin/fund-request-banks', [Home::class, 'fund_request_banks']);

Route::get('/admin/chargebacks', [Home::class, 'chargebacks']);
Route::get('/admin/cyber-complaints', [Home::class, 'cyber_complaints']);

Route::get('/admin/chargebacks/add', [Home::class, 'add_chargeback']);
Route::post('/api/admin/add_chargeback', [Home::class, 'create_chargeback']);
Route::get('/api/tables/chargebacks', [Tables::class, 'chargebacks']);
Route::get('/api/tables/transactions', [Tables::class, 'transactions']);

Route::get('/admin/cyber-complaints/add', [Home::class, 'add_cyber']);
Route::post('/api/admin/add_cyber', [Home::class, 'create_cyber']);
Route::get('/api/tables/cybers', [Tables::class, 'cybers']);
Route::post('/api/admin/login/user', [Home::class, 'login_user']);

Route::get('/admin/transactions/{type}', [Home::class, 'transactions']);


Route::get('/admin/settlements/{status}', [Home::class, 'settlements']);

Route::get('/admin/settlement-packages', [Home::class, 'settlement_packages']);
Route::get('/admin/settlement-packages/add', [Home::class, 'add_settlement_package']);
Route::get('/admin/settlement-package/edit/{id}', [Home::class, 'edit_settlement_package']);
Route::get('/api/tables/settlement-packages', [Tables::class, 'settlement_packages']);
Route::post('/api/admin/settlement-package/create', [Home::class, 'create_settlement_package']);
Route::post('/api/admin/settlement-package/update', [Home::class, 'update_settlement_package']);
Route::post('/api/admin/settlement-package/delete', [Home::class, 'delete_settlement_package']);

Route::get('/api/tables/settlements', [Tables::class, 'settlements']);
Route::post('/api/admin/settlement/settle-to-wallet', [Home::class, 'settle_to_wallet']);
Route::post('/api/admin/settlements/trigger-auto', [Home::class, 'trigger_auto_settlements']);
Route::post('/api/admin/settlement/preview-manual', [Home::class, 'preview_manual_settlement']);
Route::post('/api/admin/settlement/create-manual', [Home::class, 'create_manual_settlement']);

Route::get('/admin/settlements-batch/{id}', [Home::class, 'settlements_batch']);
Route::get('/api/tables/payin_transactions', [Tables::class, 'payin_transactions']);

Route::get('/export-transactions', [Tables::class, 'export_transactions']);
Route::get('/admin/profit', [Home::class, 'profit']);
Route::get('/api/tables/profit', [Tables::class, 'profit']);
Route::get('/export-profit', [Tables::class, 'export_profit']);

Route::get('/admin/fund-request-banks/add', [Home::class, 'add_bank']);
Route::post('/api/admin/banks/add', [Home::class, 'create_bank']);

Route::get('/admin/fund-requests/{status}', [Home::class, 'fund_requests']);

Route::get('/api/tables/fund-requests', [Tables::class, 'fund_requests']);

Route::post('/api/admin/fund-requests/update', [Home::class, 'update_fund_request']);

Route::get('/admin/credits/add', [Home::class, 'add_credit']);
Route::get('/admin/credits/pending', [Home::class, 'pending_credits']);
Route::get('/admin/credits/completed', [Home::class, 'completed_credits']);
Route::get('/api/tables/credits', [Tables::class, 'credits']);
Route::post('/api/admin/credits/add', [Home::class, 'give_credits']);
Route::post('/api/admin/advance-credit/debit', [Home::class, 'debit_credits']);

Route::post('/api/transaction/info', [Home::class, 'transaction_info']);

Route::post('/api/admin/qrcodes/update-status', [Home::class, 'qr_update_status']);

Route::get('/admin/accounts/payments', [Home::class, 'account_payments']);
Route::get('/admin/accounts/holds', [Home::class, 'account_holds']);
Route::get('/admin/accounts/journals', [Home::class, 'account_journals']);

Route::get('/admin/accounts', [Home::class, 'account_accounts']);
Route::get('/admin/accounts/add', [Home::class, 'add_account']);
Route::post('/api/admin/accounts/add', [Home::class, 'create_account']);
Route::get('/api/tables/account_accounts', [Tables::class, 'account_accounts']);
Route::get('/admin/accounts/edit/{id}', [Home::class, 'edit_account']);
Route::post('/api/admin/accounts/edit/{id}', [Home::class, 'update_account']);
Route::get('/api/tables/account_payments', [Tables::class, 'account_payments']);
Route::get('/api/tables/account_holds', [Tables::class, 'account_holds']);
Route::get('/api/tables/account_journals', [Tables::class, 'account_journals']);
Route::post('/api/admin/accounts/entries/add/{id}', [Home::class, 'add_account_entry']);
Route::get('/admin/accounts/{id}/edit/{entry_id}', [Home::class, 'edit_entries']);
Route::post('/api/admin/accounts/{id}/update/{entry_id}', [Home::class, 'update_entries']);
Route::get('/admin/accounts/view/{id}', [Home::class, 'view_account']);
Route::post('/api/admin/settlement/hold-settlement', [Home::class, 'hold_settlement']);
Route::post('/api/admin/staff/add', [Home::class, 'create_staff']);

Route::get('/api/admin/chargebacks/download', [Tables::class, 'export_chargebacks']);
Route::get('/admin/staff', [Home::class, 'staffs']);

Route::get('/export-wallet-transactions', [Tables::class, 'export_wallet_transactions']);
Route::get('/api/tables/staff', [Tables::class, 'staff']);

Route::post('/api/staff/update-switch', [Home::class, 'update_staff_switch']);

Route::post('/admin/accounts/delete', [Home::class, 'delete_account']);
Route::get('/admin/accounts/transactions/add/{id}', [Home::class, 'add_account_transaction']);

Route::post('/api/admin/accounts/transactions/add', [Home::class, 'create_account_transaction']);

Route::get('/api/tables/account/transactions', [Tables::class, 'account_transactions']);

Route::post('/api/admin/accounts/transaction/delete', [Home::class, 'delete_account_transaction']);

Route::get('/admin/accounts/transaction/update/{id}', [Home::class, 'edit_account_transaction']);
Route::post('/api/admin/accounts/transactions/update', [Home::class, 'update_account_transaction']);

Route::get('/api/tables/account_ledger', [Tables::class, 'accounts_ledger']);

Route::get('/admin/accounts/ledger/{id}', [Home::class, 'accounts_ledger']);

Route::get('/admin/accounts/auto/commission', [Home::class, 'auto_commission']);
Route::get('/admin/accounts/auto/commission/add', [Home::class, 'add_auto_commission']);
Route::post('/api/admin/accounts/auto/commission/add', [Home::class, 'create_auto_commission']);

Route::get('/api/tables/accounts_auto_commission', [Tables::class, 'accounts_auto_commission']);

Route::post('/admin/accounts/auto/commission/delete', [Home::class, 'delete_auto_commission']);

// Route::get('/api/internal/auto_commission', [AutoCommission::class, 't00_02']);

Route::get('/admin/settings', [Home::class, 'settings']);

Route::post('/api/admin/settings/update', [Home::class, 'update_settings']);

Route::get('/admin/staff/edit/{id}', [Home::class, 'edit_staff']);
Route::post('/api/admin/staff/update/{id}', [Home::class, 'update_staff']);

Route::post('/api/users/update-lock-switch', [Home::class, 'update_user_lock_switch']);

Route::post('/api/admin/update-user-info', [Home::class, 'update_user_info']);
Route::post('/api/admin/user/fund-manage', [Home::class, 'manage_users_fund']);

Route::get('/admin/holdtransactions/{status}', [Home::class, 'hold_transactions']);

Route::get('/api/tables/hold_transactions', [Tables::class, 'hold_transactions']);
Route::post('/api/admin/hold-transactions/release', [Home::class, 'release_hold_transaction']);

Route::get('/admin/settlement/bank/{id}', [Home::class, 'settle_to_bank']);

Route::post('/api/admin/user/profile/update-bank-status', [Home::class, 'update_user_bank_status']);
Route::post('/api/admin/settlements/settle-to-bank', [Home::class, 'update_settlement_to_bank']);

// Route::get('/api/admin/transactions/checker/{id}', [Checker::class, 'index']);

Route::post('/api/admin/providers/update/{id}', [Home::class, 'update_provider']);
Route::post('/api/admin/providers/create', [Home::class, 'create_provider']);

Route::get('/admin/error-logs', [Home::class, 'error_logs']);
Route::get('/api/tables/error_logs', [Tables::class, 'error_logs']);


$resetHandler = function () {
    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');

    \Illuminate\Support\Facades\DB::table('transactions')->truncate();
    if (\Illuminate\Support\Facades\Schema::hasTable('transactionres')) {
        \Illuminate\Support\Facades\DB::table('transactionres')->truncate();
    }

    \Illuminate\Support\Facades\DB::table('wallet_transactions')->truncate();

    \Illuminate\Support\Facades\DB::table('fund_requests')->truncate();

    \Illuminate\Support\Facades\DB::table('settlements')->truncate();
    if (\Illuminate\Support\Facades\Schema::hasTable('settlement_runtimes')) {
        \Illuminate\Support\Facades\DB::table('settlement_runtimes')->truncate();
    }

    if (\Illuminate\Support\Facades\Schema::hasTable('hold_wallets')) {
        \Illuminate\Support\Facades\DB::table('hold_wallets')->truncate();
    }
    if (\Illuminate\Support\Facades\Schema::hasTable('credits')) {
        \Illuminate\Support\Facades\DB::table('credits')->truncate();
    }
    if (\Illuminate\Support\Facades\Schema::hasTable('hold_wallet_transactions')) {
        \Illuminate\Support\Facades\DB::table('hold_wallet_transactions')->truncate();
    }

    \Illuminate\Support\Facades\DB::table('users')->update([
        'wallet' => 5000.00,
        'hold_wallet' => 0.00,
        'todays_payin' => 0.00,
        'todays_payout' => 0.00
    ]);

    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    return response()->json([
        'status' => 'success',
        'message' => 'All transactions, wallet ledger entries, fund requests, settlements, and runtime records cleared. All users wallet balance set to 5000.00!',
        'users_count' => \Illuminate\Support\Facades\DB::table('users')->count(),
        'users' => \Illuminate\Support\Facades\DB::table('users')->select('id', 'name', 'merchant_id', 'wallet')->get()
    ]);
};

Route::get('/test1', $resetHandler);

$fakeTxnHandler = function () {
    $targetUserId = request()->input('user_id');

    $user = $targetUserId 
        ? \App\Models\User::find($targetUserId) 
        : (\App\Models\User::where('active', 1)->first() ?? \App\Models\User::first());

    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'No user found in database!'
        ], 404);
    }

    $packageId = (int)($user->package ?? 1);
    $payinProviderId = (int)($user->payin_provider ?? 1);
    $payoutProviderId = (int)($user->payout_provider ?? 1);
    $amount = 10000.00;

    $chargeModel = new \App\Models\Charge();
    $gstRate = class_exists('\App\Models\Settings') ? \App\Models\Settings::getGSTRate() : 18;
    if (!$gstRate) {
        $gstRate = 18;
    }

    $payinChargeData = $chargeModel->payin($amount, $packageId, $payinProviderId);
    if (!$payinChargeData) {
        $slab = \Illuminate\Support\Facades\DB::table('package_slabs')
            ->where('service', 'payin')
            ->where('package_id', $packageId)
            ->first();
        $rate = $slab ? (float)$slab->amount : 2.0;
        $isPercent = $slab ? $slab->percent : 1;
        $hasGst = $slab ? $slab->gst : 1;
        $cVal = $isPercent ? ($amount * $rate / 100) : $rate;
        $cGst = $hasGst ? ($cVal * $gstRate / 100) : 0.0;
        $payinChargeData = [
            'charge' => round($cVal, 2),
            'charge_gst' => round($cGst, 2),
            'provider_charge' => 0.0,
            'provider_gst' => 0.0,
        ];
    }

    $payinCharges = round((float)$payinChargeData['charge'], 2);
    $payinGst = round((float)$payinChargeData['charge_gst'], 2);
    $payinProvCharge = round((float)($payinChargeData['provider_charge'] ?? 0), 2);
    $payinProvGst = round((float)($payinChargeData['provider_gst'] ?? 0), 2);
    $payinNet = round($amount - $payinCharges - $payinGst, 2);

    $payoutChargeData = $chargeModel->payout($amount, $packageId, $payoutProviderId);
    if (!$payoutChargeData) {
        $slab = \Illuminate\Support\Facades\DB::table('package_slabs')
            ->where('service', 'payout')
            ->where('package_id', $packageId)
            ->first();
        $rate = $slab ? (float)$slab->amount : 2.0;
        $isPercent = $slab ? $slab->percent : 1;
        $hasGst = $slab ? $slab->gst : 1;
        $cVal = $isPercent ? ($amount * $rate / 100) : $rate;
        $cGst = $hasGst ? ($cVal * $gstRate / 100) : 0.0;
        $payoutChargeData = [
            'charge' => round($cVal, 2),
            'charge_gst' => round($cGst, 2),
            'provider_charge' => 0.0,
            'provider_gst' => 0.0,
        ];
    }

    $payoutCharges = round((float)$payoutChargeData['charge'], 2);
    $payoutGst = round((float)$payoutChargeData['charge_gst'], 2);
    $payoutProvCharge = round((float)($payoutChargeData['provider_charge'] ?? 0), 2);
    $payoutProvGst = round((float)($payoutChargeData['provider_gst'] ?? 0), 2);
    $payoutNet = round($amount + $payoutCharges + $payoutGst, 2);

    $time = date('Y-m-d H:i:s');
    $randomHex1 = strtoupper(substr(dechex(time()) . bin2hex(random_bytes(4)), 0, 16));
    $payinTxnId = 'TXN' . $randomHex1 . '1';

    \Illuminate\Support\Facades\DB::table('transactions')->insert([
        'user_id'           => $user->id,
        'service'           => 'payin',
        'txnid'             => $payinTxnId,
        'merchant_txnid'    => 'MTX' . $randomHex1 . '1',
        'reference_id'      => 'REF' . $randomHex1 . '1',
        'rrn'               => (string) mt_rand(100000000000, 999999999999),
        'status'            => 'Success',
        'name'              => 'Demo Payin Customer',
        'email'             => 'payin.customer@example.com',
        'phone'             => '98' . mt_rand(10000000, 99999999),
        'amount'            => number_format($amount, 2, '.', ''),
        'charges'           => number_format($payinCharges, 2, '.', ''),
        'gst'               => number_format($payinGst, 2, '.', ''),
        'provider_charge'   => number_format($payinProvCharge, 2, '.', ''),
        'provider_gst'      => number_format($payinProvGst, 2, '.', ''),
        'net_amount'        => number_format($payinNet, 2, '.', ''),
        'payee_name'        => 'Customer 1',
        'payee_vpa'         => 'customer1@upi',
        'intent'            => 'upi',
        'order_id'          => 'ORD' . $randomHex1 . '1',
        'source'            => 'api',
        'settled'           => 0,
        'settlement_id'     => 0,
        'payin_provider'    => $payinProviderId,
        'txn_complete_time' => $time,
        'success_at'        => $time,
        'created_at'        => $time,
        'updated_at'        => $time,
    ]);

    $randomHex2 = strtoupper(substr(dechex(time() + 1) . bin2hex(random_bytes(4)), 0, 16));
    $payoutTxnId = 'TXN' . $randomHex2 . '2';

    \Illuminate\Support\Facades\DB::table('transactions')->insert([
        'user_id'           => $user->id,
        'service'           => 'payout',
        'txnid'             => $payoutTxnId,
        'merchant_txnid'    => 'MTX' . $randomHex2 . '2',
        'reference_id'      => 'REF' . $randomHex2 . '2',
        'rrn'               => (string) mt_rand(100000000000, 999999999999),
        'status'            => 'Success',
        'name'              => 'Demo Payout Beneficiary',
        'email'             => 'payout.beneficiary@example.com',
        'phone'             => '98' . mt_rand(10000000, 99999999),
        'account_number'    => '9198' . mt_rand(10000000, 99999999),
        'ifsc_code'         => 'HDFC0001234',
        'bank_name'         => 'HDFC BANK',
        'amount'            => number_format($amount, 2, '.', ''),
        'charges'           => number_format($payoutCharges, 2, '.', ''),
        'gst'               => number_format($payoutGst, 2, '.', ''),
        'provider_charge'   => number_format($payoutProvCharge, 2, '.', ''),
        'provider_gst'      => number_format($payoutProvGst, 2, '.', ''),
        'net_amount'        => number_format($payoutNet, 2, '.', ''),
        'payee_name'        => 'Demo Payout Beneficiary',
        'order_id'          => 'ORD' . $randomHex2 . '2',
        'source'            => 'api',
        'settled'           => 0,
        'settlement_id'     => 0,
        'payout_provider'   => $payoutProviderId,
        'txn_complete_time' => $time,
        'success_at'        => $time,
        'created_at'        => $time,
        'updated_at'        => $time,
    ]);

    return response()->json([
        'status' => 'success',
        'message' => "Successfully created 2 fake success transactions (1 Payin and 1 Payout of ₹10,000 each) for User ID: {$user->id} ({$user->name})!",
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'merchant_id' => $user->merchant_id,
            'package' => $packageId,
            'payin_provider' => $payinProviderId,
            'payout_provider' => $payoutProviderId,
        ],
        'transactions' => [
            [
                'service' => 'payin',
                'txnid' => $payinTxnId,
                'amount' => 10000.00,
                'charges' => $payinCharges,
                'gst' => $payinGst,
                'provider_charge' => $payinProvCharge,
                'provider_gst' => $payinProvGst,
                'net_amount' => $payinNet,
                'payin_provider' => $payinProviderId,
                'status' => 'Success',
                'success_at' => $time
            ],
            [
                'service' => 'payout',
                'txnid' => $payoutTxnId,
                'amount' => 10000.00,
                'charges' => $payoutCharges,
                'gst' => $payoutGst,
                'provider_charge' => $payoutProvCharge,
                'provider_gst' => $payoutProvGst,
                'net_amount' => $payoutNet,
                'payout_provider' => $payoutProviderId,
                'status' => 'Success',
                'success_at' => $time
            ]
        ]
    ]);
};

Route::get('/test2', $fakeTxnHandler);



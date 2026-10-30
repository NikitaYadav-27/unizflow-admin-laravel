<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Home;
use App\Http\Controllers\Tables;
// use App\Http\Controllers\Demo;
// use App\Http\Controllers\AutoCommission;
// use App\Http\Controllers\Checker;

Route::get('/', [Home::class, 'index'])->name('home');
Route::get('/admin/login', [Auth::class, 'login'])->name('admin.login');
Route::get('/admin/register', [Auth::class, 'register'])->name('admin.register');
Route::get('/admin/dashboard', [Home::class, 'dashboard'])->name('admin.dashboard');
Route::post('/api/admin/login', [Auth::class, 'login_admin'])->name('admin.auth.login');
Route::get('/admin/login-verification', [Auth::class, 'verify_login'])->name('admin.login.verification');
Route::post('/api/admin/login/verification', [Auth::class, 'verify_login_otp'])->name('admin.auth.login.verification');
Route::get('/admin/logout', [Auth::class, 'logout'])->name('admin.logout');
Route::get('/auth/login/token', [Auth::class, 'ad_login'])->name('admin.auth.login.token');

Route::get('/admin/packages/add', [Home::class, 'add_package'])->name('admin.packages.add');
Route::get('/admin/packages', [Home::class, 'packages'])->name('admin.packages.index');
Route::get('/admin/package/slabs/{id}', [Home::class, 'package_slabs'])->name('admin.package.slabs');
Route::get('/admin/package/edit/{id}', [Home::class, 'edit_package'])->name('admin.package.edit');
Route::post('/api/admin/update_package_slab', [Home::class, 'update_package_slab'])->name('admin.package.slabs.update');
Route::post('/api/admin/package/update', [Home::class, 'update_package'])->name('admin.package.update');

Route::get('/admin/wallet', [Home::class, 'wallet'])->name('admin.wallet');
Route::get('/admin/staff/add', [Home::class, 'add_staff'])->name('admin.staff.add');

Route::get('/api/tables/wallet', [Tables::class, 'wallet'])->name('api.tables.wallet');

Route::get('/api/admin/dashboard/fetch-data', [Home::class, 'fetch_dashboard_data'])->name('api.admin.dashboard.fetch-data');
Route::post('/api/admin/update-provider-slab', [Home::class, 'update_provider_slab'])->name('admin.provider-slabs.update');

Route::post('/api/admin/package/add', [Home::class, 'create_package'])->name('admin.packages.store');
Route::get('/api/tables/packages', [Tables::class, 'packages'])->name('api.tables.packages');

Route::post('/api/admin/get-bill-data', [Home::class, 'get_bill_data'])->name('api.admin.bill-data');

Route::get('/admin/users/{status}', [Home::class, 'users'])->name('admin.users.index');
Route::get('/admin/user/{id}', [Home::class, 'user'])->name('admin.user.show');
Route::post('/api/admin/update-user', [Home::class, 'update_user'])->name('admin.users.update');
Route::get('/api/tables/users', [Tables::class, 'users'])->name('api.tables.users');
Route::post('/api/users/update-switch', [Home::class, 'update_user_switch'])->name('admin.users.switch');

Route::post('/api/admin/update-bank-users', [Home::class, 'update_bank_users'])->name('admin.bank-users.update');

Route::post('/api/banks/update-switch', [Home::class, 'update_bank_switch'])->name('admin.banks.switch');
Route::get('/admin/bank-users/{id}', [Home::class, 'bank_users'])->name('admin.bank-users.show');

Route::get('/admin/providers/{type}', [Home::class, 'providers'])->name('admin.providers.index');
Route::get('/admin/provider-slabs', [Home::class, 'provider_slabs'])->name('admin.provider-slabs.index');

// Route::get('/demo', [Demo::class, 'index']);

Route::post('/api/admin/create-static-qr', [Home::class, 'create_static_qr'])->name('admin.qr.create-static');
Route::post('/api/admin/update-qr-user', [Home::class, 'update_qr_user'])->name('admin.qr.update-user');

Route::post('/api/admin/user/bulk-action', [Home::class, 'users_bulk_action'])->name('admin.users.bulk-action');

Route::get('/admin/fund-request-banks', [Home::class, 'fund_request_banks'])->name('admin.fund-request-banks.index');

Route::get('/admin/chargebacks', [Home::class, 'chargebacks'])->name('admin.chargebacks.index');
Route::get('/admin/cyber-complaints', [Home::class, 'cyber_complaints'])->name('admin.cyber-complaints.index');

Route::get('/admin/chargebacks/add', [Home::class, 'add_chargeback'])->name('admin.chargebacks.add');
Route::post('/api/admin/add_chargeback', [Home::class, 'create_chargeback'])->name('admin.chargebacks.store');
Route::get('/api/tables/chargebacks', [Tables::class, 'chargebacks'])->name('api.tables.chargebacks');
Route::get('/api/tables/transactions', [Tables::class, 'transactions'])->name('api.tables.transactions');

Route::get('/admin/cyber-complaints/add', [Home::class, 'add_cyber'])->name('admin.cyber-complaints.add');
Route::post('/api/admin/add_cyber', [Home::class, 'create_cyber'])->name('admin.cyber-complaints.store');
Route::get('/api/tables/cybers', [Tables::class, 'cybers'])->name('api.tables.cybers');
Route::post('/api/admin/login/user', [Home::class, 'login_user'])->name('admin.login.user');

Route::get('/admin/transactions/{type}', [Home::class, 'transactions'])->name('admin.transactions.index');

Route::get('/admin/settlements/{status}', [Home::class, 'settlements'])->name('admin.settlements.index');
Route::get('/admin/settlement-packages', [Home::class, 'settlement_packages'])->name('admin.settlement-packages.index');
Route::get('/admin/settlement-packages/add', [Home::class, 'add_settlement_package'])->name('admin.settlement-packages.add');
Route::get('/admin/settlement-package/edit/{id}', [Home::class, 'edit_settlement_package'])->name('admin.settlement-packages.edit');
Route::get('/api/tables/settlement-packages', [Tables::class, 'settlement_packages'])->name('api.tables.settlement-packages');
Route::post('/api/admin/settlement-package/create', [Home::class, 'create_settlement_package'])->name('admin.settlement-packages.store');
Route::post('/api/admin/settlement-package/update', [Home::class, 'update_settlement_package'])->name('admin.settlement-packages.update');
Route::post('/api/admin/settlement-package/delete', [Home::class, 'delete_settlement_package'])->name('admin.settlement-packages.delete');

Route::get('/api/tables/settlements', [Tables::class, 'settlements'])->name('api.tables.settlements');
Route::post('/api/admin/settlement/settle-to-wallet', [Home::class, 'settle_to_wallet'])->name('admin.settlement.settle-to-wallet');
Route::post('/api/admin/settlements/trigger-auto', [Home::class, 'trigger_auto_settlements'])->name('admin.settlements.trigger-auto');
Route::post('/api/admin/settlement/preview-manual', [Home::class, 'preview_manual_settlement'])->name('admin.settlement.preview-manual');
Route::post('/api/admin/settlement/create-manual', [Home::class, 'create_manual_settlement'])->name('admin.settlement.create-manual');

Route::get('/admin/settlements-batch/{id}', [Home::class, 'settlements_batch'])->name('admin.settlements.batch');
Route::get('/api/tables/payin_transactions', [Tables::class, 'payin_transactions'])->name('api.tables.payin_transactions');

Route::get('/export-transactions', [Tables::class, 'export_transactions'])->name('export.transactions');
Route::get('/admin/profit', [Home::class, 'profit'])->name('admin.profit');
Route::get('/api/tables/profit', [Tables::class, 'profit'])->name('api.tables.profit');
Route::get('/export-profit', [Tables::class, 'export_profit'])->name('export.profit');

Route::get('/admin/fund-request-banks/add', [Home::class, 'add_bank'])->name('admin.fund-request-banks.add');
Route::post('/api/admin/banks/add', [Home::class, 'create_bank'])->name('admin.banks.store');

Route::get('/admin/fund-requests/{status}', [Home::class, 'fund_requests'])->name('admin.fund-requests.index');
Route::get('/api/tables/fund-requests', [Tables::class, 'fund_requests'])->name('api.tables.fund-requests');
Route::post('/api/admin/fund-requests/update', [Home::class, 'update_fund_request'])->name('admin.fund-requests.update');

Route::get('/admin/credits/add', [Home::class, 'add_credit'])->name('admin.credits.add');
Route::get('/admin/credits/pending', [Home::class, 'pending_credits'])->name('admin.credits.pending');
Route::get('/admin/credits/completed', [Home::class, 'completed_credits'])->name('admin.credits.completed');
Route::get('/api/tables/credits', [Tables::class, 'credits'])->name('api.tables.credits');
Route::post('/api/admin/credits/add', [Home::class, 'give_credits'])->name('admin.credits.store');
Route::post('/api/admin/advance-credit/debit', [Home::class, 'debit_credits'])->name('admin.credits.debit');

Route::post('/api/transaction/info', [Home::class, 'transaction_info'])->name('api.transaction.info');

Route::post('/api/admin/qrcodes/update-status', [Home::class, 'qr_update_status']);

Route::get('/admin/accounts/payments', [Home::class, 'account_payments'])->name('admin.accounts.payments');
Route::get('/admin/accounts/holds', [Home::class, 'account_holds'])->name('admin.accounts.holds');
Route::get('/admin/accounts/journals', [Home::class, 'account_journals'])->name('admin.accounts.journals');

Route::get('/admin/accounts', [Home::class, 'account_accounts'])->name('admin.accounts.index');
Route::get('/admin/accounts/add', [Home::class, 'add_account'])->name('admin.accounts.add');
Route::post('/api/admin/accounts/add', [Home::class, 'create_account'])->name('admin.accounts.store');
Route::get('/api/tables/account_accounts', [Tables::class, 'account_accounts'])->name('api.tables.account_accounts');
Route::get('/admin/accounts/edit/{id}', [Home::class, 'edit_account'])->name('admin.accounts.edit');
Route::post('/api/admin/accounts/edit/{id}', [Home::class, 'update_account'])->name('admin.accounts.update');
Route::get('/api/tables/account_payments', [Tables::class, 'account_payments'])->name('api.tables.account_payments');
Route::get('/api/tables/account_holds', [Tables::class, 'account_holds'])->name('api.tables.account_holds');
Route::get('/api/tables/account_journals', [Tables::class, 'account_journals'])->name('api.tables.account_journals');
Route::post('/api/admin/accounts/entries/add/{id}', [Home::class, 'add_account_entry'])->name('admin.accounts.entries.store');
Route::get('/admin/accounts/{id}/edit/{entry_id}', [Home::class, 'edit_entries'])->name('admin.accounts.entries.edit');
Route::post('/api/admin/accounts/{id}/update/{entry_id}', [Home::class, 'update_entries'])->name('admin.accounts.entries.update');
Route::get('/admin/accounts/view/{id}', [Home::class, 'view_account'])->name('admin.accounts.view');
Route::post('/api/admin/settlement/hold-settlement', [Home::class, 'hold_settlement'])->name('admin.settlement.hold-settlement');
Route::post('/api/admin/staff/add', [Home::class, 'create_staff'])->name('admin.staff.store');

Route::get('/api/admin/chargebacks/download', [Tables::class, 'export_chargebacks'])->name('api.admin.chargebacks.download');
Route::get('/admin/staff', [Home::class, 'staffs'])->name('admin.staff.index');

Route::get('/export-wallet-transactions', [Tables::class, 'export_wallet_transactions'])->name('export.wallet-transactions');
Route::get('/api/tables/staff', [Tables::class, 'staff'])->name('api.tables.staff');

Route::post('/api/staff/update-switch', [Home::class, 'update_staff_switch'])->name('admin.staff.switch');

Route::post('/admin/accounts/delete', [Home::class, 'delete_account'])->name('admin.accounts.delete');
Route::get('/admin/accounts/transactions/add/{id}', [Home::class, 'add_account_transaction'])->name('admin.accounts.transactions.add');

Route::post('/api/admin/accounts/transactions/add', [Home::class, 'create_account_transaction'])->name('admin.accounts.transactions.store');

Route::get('/api/tables/account/transactions', [Tables::class, 'account_transactions']);

Route::post('/api/admin/accounts/transaction/delete', [Home::class, 'delete_account_transaction']);

Route::get('/admin/accounts/transaction/update/{id}', [Home::class, 'edit_account_transaction']);
Route::post('/api/admin/accounts/transactions/update', [Home::class, 'update_account_transaction']);

Route::get('/api/tables/account_ledger', [Tables::class, 'accounting_ledger']);
Route::get('/api/tables/accounting_ledger', [Tables::class, 'accounting_ledger'])->name('api.tables.accounting_ledger');

Route::get('/admin/accounting/ledger', [Home::class, 'accounting_ledger'])->name('admin.accounting.ledger');
Route::get('/admin/accounts/ledger/{id?}', [Home::class, 'accounting_ledger']);
Route::get('/admin/accounts/auto/commission', [Home::class, 'accounting_ledger']);

Route::get('/api/admin/accounting/export/excel', [Tables::class, 'export_accounting_ledger_excel'])->name('api.admin.accounting.export.excel');
Route::get('/api/admin/accounting/export/pdf', [Tables::class, 'export_accounting_ledger_pdf'])->name('api.admin.accounting.export.pdf');

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

Route::get('/admin/settlement/bank/{id}', [Home::class, 'settle_to_bank'])->name('admin.settlement.bank');

Route::post('/api/admin/user/profile/update-bank-status', [Home::class, 'update_user_bank_status'])->name('admin.user.profile.update-bank-status');
Route::post('/api/admin/settlements/settle-to-bank', [Home::class, 'update_settlement_to_bank'])->name('admin.settlements.settle-to-bank');

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



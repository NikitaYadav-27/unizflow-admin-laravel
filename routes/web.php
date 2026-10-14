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

Route::get('/admin/settlement/bank/{id}', [Home::class, 'settle_to_bank']);

Route::post('/api/admin/user/profile/update-bank-status', [Home::class, 'update_user_bank_status']);
Route::post('/api/admin/settlements/settle-to-bank', [Home::class, 'update_settlement_to_bank']);

// Route::get('/api/admin/transactions/checker/{id}', [Checker::class, 'index']);

Route::post('/api/admin/providers/update/{id}', [Home::class, 'update_provider']);
Route::post('/api/admin/providers/create', [Home::class, 'create_provider']);

Route::get('/admin/error-logs', [Home::class, 'error_logs']);
Route::get('/api/tables/error_logs', [Tables::class, 'error_logs']);

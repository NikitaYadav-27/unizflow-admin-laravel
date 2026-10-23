<?php
namespace App\Http\Controllers;

class Tables extends BaseController
{
    private $session;
    function __construct() {
    $this->middleware(function ($request, $next) {
        if (!session()->get("admin_id")) {
            return response()->json(["status" => "error", "message" => "Unauthorized access"], 401);
        }
        return $next($request);
    });
    }

    public function users()
    {
        $session = session();
        $adminModel = new \App\Models\Admin();
        $admin = $adminModel->find($session->get('admin_id'));
       

      
        $availableSearchKeys = ["merchant_id", "name", "email", "phone", "wallet"];
        $request = request();

        $offset = (int) $request->input('start');
        $limit = (int) $request->input('length');
        $searchBy = $request->input('searchBy');
        $searchValue = $request->input('searchValue');
        $draw = (int) $request->input('draw');
        //$active = $request->input('status') === "active" ? 1 : 0;
        $status = $request->input('status');
        

        $startTime = date("Y-m-d 00:00:00");
        $endTime = date("Y-m-d 23:59:59");

        $userModel = new \App\Models\User();
        if($status == "active"){
            $totalUsers = $userModel->where('active', 1)->where("locked",0)->count();
        }elseif($status == "inactive"){
             $totalUsers = $userModel->where('active', 0)->count();
        }elseif($status == "locked"){
            $totalUsers = $userModel->where('active', 1)->where("locked",1)->count();
        }
        

        $filteredCountBuilder = $userModel->query();
        
        if($status == "active"){
            $filteredCountBuilder->where('users.active', 1);
            $filteredCountBuilder->where('users.locked', 0);
        }elseif($status == "inactive"){
            $filteredCountBuilder->where('users.active', 0);
        }elseif($status == "locked"){
            $filteredCountBuilder->where('users.active', 1);
            $filteredCountBuilder->where('users.locked', 1);
        }
        
        
        
        if (!empty($searchBy) && !empty($searchValue) && in_array($searchBy, $availableSearchKeys)) {
            $filteredCountBuilder->like("users." . $searchBy, $searchValue);
        }
        $filteredUsers = $filteredCountBuilder->count();

        $builder = $userModel->query();
        $builder->selectRaw("
            users.*, 
            settlement_packages.name as settlement_package,
            packages.name AS package,
            COALESCE(tx.todays_collection, 0) AS todays_collection,
            COALESCE(unsettled_tx.unsettled_amount, 0) AS unsettled_amount
        ");
        $builder->leftJoin("packages", "packages.id", "=", "users.package");
        $builder->join("settlement_packages", "settlement_packages.id", "=", "users.settlement_package");

        $builder->leftJoin(\Illuminate\Support\Facades\DB::raw("( SELECT  user_id, SUM(amount) AS todays_collection 
                FROM transactions 
                WHERE status = 'Success' 
                  AND service = 'payin' 
                  AND txn_complete_time BETWEEN '$startTime' AND '$endTime'
                GROUP BY user_id
             ) AS tx"), "tx.user_id", "=", "users.id");

        $builder->leftJoin(\Illuminate\Support\Facades\DB::raw("( SELECT user_id, SUM(net_amount) AS unsettled_amount 
                FROM transactions 
                WHERE status = 'Success' 
                  AND service = 'payin' 
                  AND settled = 0
                GROUP BY user_id
             ) AS unsettled_tx"), "unsettled_tx.user_id", "=", "users.id");

        if($status == "active"){
            $builder->where('users.active', 1);
            $builder->where('users.locked', 0);
        }elseif($status == "inactive"){
            $builder->where('users.active', 0);
        }elseif($status == "locked"){
            $builder->where('users.active', 1);
            $builder->where('users.locked', 1);
        }
       
        
        if (!empty($searchBy) && !empty($searchValue) && in_array($searchBy, $availableSearchKeys)) {
            $builder->like("users." . $searchBy, $searchValue);
        }
        $builder->orderBy("users.id", "DESC");
        $builder->limit($limit, $offset);

        $users = $builder->get();
        $data = [];
        $index = $offset + 1;

        foreach ($users as $user) {
            $actions = '';
          
                $actions .= '<button class="btn btn-sm btn-primary-transparent" onclick="loginUser(\'' . $user->id . '\',this);"><i class="ti ti-login"></i></button>';
            
                $actions .= '&nbsp;<button class="btn btn-sm btn-success-transparent" onclick="navigate(\'/admin/user/' . $this->encrypt($user->id) . '\');"><i class="ti ti-list"></i></button>';
            

            $data[] = [
                '<input class="form-check-input user-select" onclick="selectUser(this)" data-uid="' . $user->id . '" type="checkbox">',
                $index,
                $user->locked == 1
                    ? '<button onclick="lockSwitch(\'' . $user->id . '\')" class="btn btn-danger-transparent btn-sm"><i class="ti ti-lock"></i></button>'
                    : '<button onclick="lockSwitch(\'' . $user->id . '\')" class="btn btn-success-transparent btn-sm"><i class="ti ti-lock-open"></i></button>',
                $user->merchant_id,
                $user->name,
                $user->phone,
                $user->email,
                "<i class='ti ti-wallet'></i>&nbsp;&nbsp;₹ " . $user->wallet . "<br><span class='badge bg-warning-transparent' title='Unsettled Payin Collections'>Unsettled: ₹ " . number_format($user->unsettled_amount, 2) . "</span>",
                $user->package,

                '<div class="custom-toggle-switch toggle-sm ms-2">
                    <input onchange="changeSwitch(\'' .
                $user->id .
                '\',\'active\',this.checked);" id="active-' .
                $index .
                '" name="toggleswitchsize" type="checkbox" ' .
                ($user->active ? "checked" : "") .
                '>
                    <label for="active-' .
                $index .
                '" class="label-primary"></label>
                 </div>',

                '<div class="custom-toggle-switch toggle-sm ms-2">
                    <input onchange="changeSwitch(\'' .
                $user->id .
                '\',\'payin\',this.checked);" id="payin-' .
                $index .
                '" name="toggleswitchsize" type="checkbox" ' .
                ($user->payin ? "checked" : "") .
                '>
                    <label for="payin-' .
                $index .
                '" class="label-primary"></label>
                 </div>',

                '<div class="custom-toggle-switch toggle-sm ms-2">
                    <input onchange="changeSwitch(\'' .
                $user->id .
                '\',\'payout\',this.checked);" id="payout-' .
                $index .
                '" name="toggleswitchsize" type="checkbox" ' .
                ($user->payout ? "checked" : "") .
                '>
                    <label for="payout-' .
                $index .
                '" class="label-primary"></label>
                 </div>',

                "₹ " . round($user->todays_collection, 2),

                '<div><span class="fw-semibold">' . htmlspecialchars($user->settlement_package ?? 'N/A') . '</span><br>' .
                ((($user->settlement_type ?? 'manual') === 'automatic')
                    ? '<span class="badge bg-success-transparent fs-10"><i class="ti ti-bolt me-1"></i>Automatic</span>'
                    : '<span class="badge bg-secondary-transparent fs-10"><i class="ti ti-hand-stop me-1"></i>Manual</span>') .
                '</div>',
                $actions,
            ];

            $index++;
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalUsers,
            'recordsFiltered' => $filteredUsers,
            'data' => $data,
        ], 200);
    }

    private function encrypt($text)
    {
        return bin2hex(openssl_encrypt($text, 'AES-256-CBC', env("encryption.key"), OPENSSL_RAW_DATA, substr(hash('sha256', env("encryption.key")), 0, 16)));
    }

    public function packages()
    {
        $session = session();
        $admin = new \App\Models\Admin();
        $admin = $admin->find($session->get('admin_id'));
    
        $availableSearchKeys = [];
        $request = request();
        $session = session();
        $offset = $request->input('start');
        $limit = $request->input('length');
        $searchBy = $request->input('searchBy');
        $searchValue = $request->input('searchValue');
        $draw = (int) $request->input('draw');
        $packageModel = new \App\Models\Package();
        $totalPackages = $packageModel->count();
        $builder = $packageModel->query();

        if (!empty($searchBy) && !empty($searchValue)) {
            if (in_array($searchBy, $availableSearchKeys)) {
                $builder->like('packages.' . $searchBy, $searchValue);
            }
        }
        $filteredBuilder = clone $builder;
        $filteredPackages = $filteredBuilder->count();
        $builder->orderBy('packages.id', "DESC")->limit($limit, $offset);
        $query = $builder->get();
        $packages = $query;
        $data = [];
        $index = 1;

        foreach ($packages as $package) {
            $actions = '';
            
            $actions .= '<button onclick="navigate(\'/admin/package/edit/' . $this->encrypt($package->id) . '\');" class="btn btn-sm btn-warning-transparent"><i class="ti ti-edit"></i></button>';
            
            $actions .= ' <button onclick="navigate(\'/admin/package/slabs/' . $this->encrypt($package->id) . '\');" class="btn btn-sm btn-success-transparent"><i class="ti ti-list"></i></button>';
            $data[] = [$index, $package->name, $package->updated_at, $package->created_at, $actions];
            $index++;
        }

        // Return JSON response
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalPackages,
            'recordsFiltered' => $filteredPackages,
            'data' => $data,
        ], 200);
    }

    public function settlement_packages()
    {
        $session = session();
        $admin = new \App\Models\Admin();
        $admin = $admin->find($session->get('admin_id'));

        \App\Models\SettlementPackage::ensureSlotsTableExists();

        $request = request();
        $offset = (int) $request->input('start', 0);
        $limit = (int) $request->input('length', 10);
        $searchBy = $request->input('searchBy');
        $searchValue = $request->input('searchValue');
        $draw = (int) $request->input('draw');

        $model = new \App\Models\SettlementPackage();
        $totalPackages = $model->count();
        $builder = $model->query()->with('slots');

        if (!empty($searchValue)) {
            $builder->where('name', 'like', "%{$searchValue}%");
        }

        $filteredPackages = $builder->count();
        $packages = $builder->orderBy('id', 'DESC')->skip($offset)->take($limit)->get();

        $data = [];
        $index = $offset + 1;

        foreach ($packages as $package) {
            $slotCount = $package->slots->count();
            $slotsHtml = '<div class="d-flex flex-wrap gap-1">';
            if ($slotCount == 0) {
                $slotsHtml .= '<span class="badge bg-light text-muted">No slots configured</span>';
            } else {
                foreach ($package->slots as $slot) {
                    $slotsHtml .= '<span class="badge bg-primary-transparent me-1 p-2 text-start" style="font-size: 11px; line-height: 1.4;">'
                        . '<i class="ti ti-clock me-1 text-primary"></i><strong>Slot ' . $slot->slot_number . ': ' . htmlspecialchars($slot->settlement_time) . '</strong><br>'
                        . '<small class="text-muted"><i class="ti ti-calendar-time me-1"></i>Txns: ' . htmlspecialchars($slot->txn_from_time) . ' - ' . htmlspecialchars($slot->txn_to_time) . '</small>'
                        . '</span>';
                }
            }
            $slotsHtml .= '</div>';

            $frequencyBadge = '<span class="badge bg-info-transparent fs-12 px-2 py-1"><i class="ti ti-repeat me-1"></i>' . $slotCount . ' ' . ($slotCount === 1 ? 'Time' : 'Times') . ' / Day</span>';

            $actions = '';
            $actions .= '<button onclick="navigate(\'/admin/settlement-package/edit/' . $this->encrypt($package->id) . '\');" class="btn btn-sm btn-warning-transparent me-1" title="Edit Package"><i class="ti ti-edit"></i> Edit</button>';
            $actions .= '<button onclick="deleteSettlementPackage(\'' . $this->encrypt($package->id) . '\', \'' . htmlspecialchars(addslashes($package->name)) . '\');" class="btn btn-sm btn-danger-transparent" title="Delete Package"><i class="ti ti-trash"></i> Delete</button>';

            $data[] = [
                $index,
                '<strong>' . htmlspecialchars($package->name) . '</strong>',
                $frequencyBadge,
                $slotsHtml,
                $package->created_at ? date('d M Y, h:i A', strtotime($package->created_at)) : 'N/A',
                $actions
            ];
            $index++;
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalPackages,
            'recordsFiltered' => $filteredPackages,
            'data' => $data,
        ], 200);
    }

    public function settlements()
    {
        $session = session();
        $admin = new \App\Models\Admin();
        $admin = $admin->find($session->get('admin_id'));
       
        $availableSearchKeys = ["txnid", "batch_id", "user"];
        $request = request();
        $session = session();
        $offset = $request->input('start');
        $limit = $request->input('length');
        $searchBy = $request->input('searchBy');
        $searchValue = $request->input('searchValue');
        $status = $request->input('status');
        $startDate = $request->input('startDate') . " 00:00:00";
        $endDate = $request->input('endDate') . " 23:59:59";

        $draw = (int) $request->input('draw');
        $settlementModel = new \App\Models\Settlement();
        $totalSettlement = $settlementModel->whereBetween('settlements.created_at', [$startDate, $endDate])->count();
        $builder = $settlementModel->query();
        $builder->selectRaw("settlements.* , users.name as name");
        $builder->whereBetween('settlements.created_at', [$startDate, $endDate]);
        $builder->where("settlements.status", $status);
        $builder->join("users", "users.id", "=", "settlements.user_id");

        if (!empty($searchBy) && !empty($searchValue)) {
            if (in_array($searchBy, $availableSearchKeys)) {
                if ($searchBy == "user") {
                    $builder->where('users.name', 'like', '%' . $searchValue . '%');
                } else {
                    $builder->like('settlements.' . $searchBy, $searchValue);
                }
            }
        }

        $filteredBuilder = clone $builder;
        $filteredSettlements = $filteredBuilder->count();
        $builder->orderBy('settlements.id', "DESC")->limit($limit, $offset);
        $query = $builder->get();
        $settlements = $query;
        $data = [];
        $index = 1;

        foreach ($settlements as $settlement) {
            $totalTransactions = (new \App\Models\Transaction())
                ->selectRaw("count(id) as total")
                ->where('settlement_id', $settlement->id)
                ->first()['total'];
            $encId = bin2hex(openssl_encrypt($settlement->id, 'AES-256-CBC', env("encryption.key"), OPENSSL_RAW_DATA, substr(hash('sha256', env("encryption.key")), 0, 16)));
            $actions =
                '<div class="d-flex gap-2">
              <button onclick="settleToWallet(\'' .
                $encId .
                '\');" class="btn btn-sm btn-primary-transparent"><i class="ti ti-wallet"></i></button>
               <button onclick="settleToBank(\'' .
                $encId .
                '\');" class="btn btn-sm btn-info-transparent"><i class="ti ti-building-bank"></i></button>';

            if ($settlement->status == "Pending") {
                $actions .= '<button onclick="holdSettlement(\'' . $encId . '\');" class="btn btn-sm btn-danger-transparent"><i class="ti ti-forbid-2"></i></button>';
            }
            $actions .= '</div>';

          

            $row = [
                $index,
                $settlement->name ?? "N/A",
                $settlement->batch_id ?? "N/A",
                "₹ " . ($settlement->amount ?? 0),
                "₹ " . ($settlement->charges ?? 0),
                "₹ " . ($settlement->gst ?? 0),
                "₹ " . ($settlement->net_amount ?? 0),
                ($settlement->status == "Success")
                    ? ($settlement->settle_to == "wallet"
                        ? '<span class="badge bg-primary-transparent"><i class="ti ti-wallet"></i>&nbsp;&nbsp; Wallet</span>'
                        : '<span class="badge bg-success-transparent"><i class="ti ti-building-bank"></i>&nbsp;&nbsp; Bank</span>')
                    : 'NA',
                $settlement->txnid ?? 'NA',
                $this->getStatusSpan($settlement->status ?? 'Pending'),
                $settlement->created_at ?? 'NA',
                ($settlement->status == "Success" && $settlement->updated_at) ? $settlement->updated_at : "NA",
                '<button onclick="navigate(\'/admin/settlements-batch/' . $encId . '\')" class="btn btn-sm btn-primary-transparent"><i class="ti ti-list"></i>&nbsp;&nbsp;' . $totalTransactions . '</button>',
            ];

            if (strtolower($status) == "pending" || strtolower($status) == "hold") {
                $row[] = $actions;
            }

            $data[] = $row;
            $index++;
        }

        // Return JSON response
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalSettlement,
            'recordsFiltered' => $filteredSettlements,
            'data' => $data,
        ], 200);
    }



    public function fund_requests()
    {
        $session = session();
        $admin = new \App\Models\Admin();
        $admin = $admin->find($session->get('admin_id'));
       
        $availableSearchKeys = ["txnid", "rrn", "user"];
        $request = request();
        $session = session();
        $offset = $request->input('start');
        $limit = $request->input('length');
        $searchBy = $request->input('searchBy');
        $searchValue = $request->input('searchValue');
        $status = $request->input('status');
        $startDate = $request->input('startDate') . " 00:00:00";
        $endDate = $request->input('endDate') . " 23:59:59";

        $draw = (int) $request->input('draw');
        $fundrequestModel = new \App\Models\FundRequest();
        $totalFundrequest = $fundrequestModel->whereBetween('fund_requests.created_at', [$startDate, $endDate])->count();
        $builder = $fundrequestModel->query();
        $builder->selectRaw("fund_requests.* , users.name as name, fund_request_banks.name as bank_name");
        $builder->whereBetween('fund_requests.created_at', [$startDate, $endDate]);
        $builder->where("fund_requests.status", $status);
        $builder->join("users", "users.id", "=", "fund_requests.user_id");
        $builder->join("fund_request_banks", "fund_request_banks.id", "=", "fund_requests.bank");

        if (!empty($searchBy) && !empty($searchValue)) {
            if (in_array($searchBy, $availableSearchKeys)) {
                if ($searchBy == "user") {
                    $builder->where('users.name', 'like', '%' . $searchValue . '%');
                } else {
                    $builder->like('fund_requests.' . $searchBy, $searchValue);
                }
            }
        }

        $filteredBuilder = clone $builder;
        $filteredFundrequests = $filteredBuilder->count();
        $builder->orderBy('fund_requests.id', "DESC")->limit($limit, $offset);
        $query = $builder->get();
        $fundrequests = $query;
        $data = [];
        $index = 1;

        foreach ($fundrequests as $fundrequest) {
            $encId = bin2hex(openssl_encrypt($fundrequest->id, 'AES-256-CBC', env("encryption.key"), OPENSSL_RAW_DATA, substr(hash('sha256', env("encryption.key")), 0, 16)));

            $row = [
                $index,
                $fundrequest->name,
                $fundrequest->bank_name,
                $fundrequest->mode,
                $fundrequest->txnid,
                $fundrequest->rrn,
                "₹ " . $fundrequest->amount,
                $this->getStatusSpan($fundrequest->status),
                '<button onclick="window.open(\'' . env("frontendURL") . 'uploads/' . $fundrequest->receipt . '\');" class="btn btn-sm btn-primary-transparent"><i class="ti ti-receipt"></i></button>',
                $fundrequest->remarks,
                $fundrequest->created_at,
                $fundrequest->status != "Pending" ? $fundrequest->updated_at : "NA",
            ];

            if ($status == "Pending") {
                $row[] = '<div class="d-flex gap-2">
                    <button onclick="acceptRequest(\'' . $encId . '\');" class="btn btn-sm btn-success-transparent"><i class="ti ti-check"></i></button>
                    <button onclick="rejectRequest(\'' . $encId . '\');" class="btn btn-sm btn-danger-transparent"><i class="ti ti-x"></i></button>
                </div>';
            } elseif ($status == "Failed") {
                $row[] = $fundrequest->reason ?? '';
            }

            $data[] = $row;
            $index++;
        }

        // Return JSON response
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalFundrequest,
            'recordsFiltered' => $filteredFundrequests,
            'data' => $data,
        ], 200);
    }

    public function transactions()
    {
        $session = session();
        $admin = new \App\Models\Admin();
        $admin = $admin->find($session->get('admin_id'));
       
        $availableSearchKeys = [
            "txnid",
            "merchant_txnid",
            "reference_id",
            "rrn",
            "name",
            "email",
            "phone",
            "amount",
            "payee_name",
            "payee_vpa",
            "payee_phone",
            "account_number",
            "ifsc_code",
            "order_id",
            "source",
            "remarks",
            "qr_name",
            "settlement_txnid",
        ];
        $request = request();
        $session = session();
        $offset = $request->input('start');
        $limit = $request->input('length');
        $searchBy = $request->input('searchBy');
        $searchValue = $request->input('searchValue');
        $draw = (int) $request->input('draw');
        $type = $request->input('type');

        $startDate = $request->input('startDate') . " 00:00:00";
        $endDate = $request->input('endDate') . " 23:59:59";
        $status = $request->input('status');
        $user_id = $request->input('user_id');
        $provider = $request->input('provider');

        $transactionModel = new \App\Models\Transaction();

        if ($type == "payin") {
            $transactionModel->where('transactions.service', 'payin');
            $transactionModel->where('qr_id', 0);
        } elseif ($type == "payout") {
            $transactionModel->where('transactions.service', 'payout');
        } elseif ($type == "qr") {
            $transactionModel->where('transactions.service', 'payin');
            $transactionModel->where('transactions.qr_id', '!=', 0);
        }

        $transactionModel->where('transactions.created_at', '>=', $startDate);
        $transactionModel->where('transactions.created_at', '<=', $endDate);

        if ($status != "" && in_array($status, ["Success", "Pending", "Failed", "Expired"])) {
            $transactionModel->where("transactions.status", $status);
        }

        if ($user_id > 0) {
            $transactionModel->where("transactions.user_id", $user_id);
        }

        if ($provider > 0) {
            $builder = $transactionModel->query();
            $builder
                ->where(function($q) use ($provider) { $q->orWhere('transactions.payin_provider', $provider)->orWhere('transactions.payout_provider', $provider); });
        }

        $totalTransactions = $transactionModel->count();
        $builder = $transactionModel->query();

        $builder->selectRaw("transactions.*, users.name as user, COALESCE(payin_provider.name, payout_provider.name) as provider_name, settlements.txnid as settlement_txn_id");
        $builder->join("users", "users.id", "=", "transactions.user_id");

        $builder->leftJoin("providers as payin_provider", function($join) { $join->on("payin_provider.id", "=", "transactions.payin_provider")->where("transactions.service", "=", "payin"); });
        $builder->leftJoin("providers as payout_provider", function($join) { $join->on("payout_provider.id", "=", "transactions.payout_provider")->where("transactions.service", "=", "payout"); });
       

        $builder->leftJoin("settlements", function($join) { $join->on("settlements.id", "=", "transactions.settlement_id")->where("transactions.settlement_id", "!=", 0); });

        if ($type == "payin") {
            $builder->where('transactions.service', 'payin');
            $builder->where('qr_id', 0);
        } elseif ($type == "payout") {
            $builder->where('transactions.service', 'payout');
        } elseif ($type == "qr") {
            $builder->where('transactions.service', 'payin');
            $builder->where('transactions.qr_id', '!=', 0);
        }

        $builder->where('transactions.created_at', '>=', $startDate);
        $builder->where('transactions.created_at', '<=', $endDate);

        if ($status != "" && in_array($status, ["Success", "Pending", "Failed", "Expired"])) {
            $builder->where("transactions.status", $status);
        }

        if ($user_id > 0) {
            $builder->where("transactions.user_id", $user_id);
        }

        if ($provider > 0) {
            $builder
                ->where(function($q) use ($provider) { $q->orWhere('transactions.payin_provider', $provider)->orWhere('transactions.payout_provider', $provider); });
        }

        if (!empty($searchBy) && !empty($searchValue)) {
            if (in_array($searchBy, $availableSearchKeys)) {
                if ($searchBy == "qr_name") {
                    $builder->where('qr_codes.name', 'like', '%' . $searchValue . '%');
                } elseif ($searchBy == "settlement_txnid") {
                    $builder->where('settlements.txnid', 'like', '%' . $searchValue . '%');
                } else {
                    $builder->like('transactions.' . $searchBy, $searchValue);
                }
            }
        }

        $filteredBuilder = clone $builder;
        $filteredTransactions = $filteredBuilder->count();

        $builder->orderBy('transactions.id', "DESC");
        if ($limit != -1) {
            $builder->limit($limit, $offset);
        }

        $query = $builder->get();
        $transactions = $query;

        $extraBuiler = (new \App\Models\Transaction())->query();
        $extraBuiler->selectRaw("count(*) as total_transactions, sum(transactions.amount) as total_amount, sum(transactions.charges) as total_charges, sum(transactions.gst) as total_gst");

        

        $extraBuiler->leftJoin("settlements", function($join) { $join->on("settlements.id", "=", "transactions.settlement_id")->where("transactions.settlement_id", "!=", 0); });

        if ($type == "payin") {
            $extraBuiler->where('transactions.service', 'payin');
            $extraBuiler->where('qr_id', 0);
        } elseif ($type == "payout") {
            $extraBuiler->where('transactions.service', 'payout');
        } elseif ($type == "qr") {
            $extraBuiler->where('transactions.service', 'payin');
            $extraBuiler->where('transactions.qr_id', '!=', 0);
        }

        $extraBuiler->where('transactions.created_at', '>=', $startDate);
        $extraBuiler->where('transactions.created_at', '<=', $endDate);

        if ($status != "" && in_array($status, ["Success", "Pending", "Failed", "Expired"])) {
            $extraBuiler->where("transactions.status", $status);
        }

        if ($user_id > 0) {
            $extraBuiler->where("transactions.user_id", $user_id);
        }

        if ($provider > 0) {
            $extraBuiler
                ->where(function($q) use ($provider) { $q->orWhere('transactions.payin_provider', $provider)->orWhere('transactions.payout_provider', $provider); });
        }

        if (!empty($searchBy) && !empty($searchValue)) {
            if (in_array($searchBy, $availableSearchKeys)) {
                if ($searchBy == "qr_name") {
                    $extraBuiler->where('qr_codes.name', 'like', '%' . $searchValue . '%');
                } elseif ($searchBy == "settlement_txnid") {
                    $extraBuiler->where('settlements.txnid', 'like', '%' . $searchValue . '%');
                } else {
                    $extraBuiler->like('transactions.' . $searchBy, $searchValue);
                }
            }
        }

        $extraData = $extraBuiler->get()[0];

        $data = [];
        $index = 1;

        foreach ($transactions as $transaction) {
            $row = [];
            $row[] = $index;
            $row[] = $transaction->user;
            $row[] = $transaction->service == "payout" ? "PAYOUT" : ($transaction->service == "payin" && $transaction->qr_id == 0 ? "PAYIN" : ($transaction->service == "payin" && $transaction->qr_id != 0 ? "QR" : "OTHER"));
            $row[] = $transaction->txnid;

            if (in_array($type, ['payin', 'payout', 'all'])) {
                $row[] = $transaction->merchant_txnid;
            }
            $row[] = $transaction->reference_id;
            $row[] = $transaction->rrn;
            $row[] = $this->getStatusSpan($transaction->status);
            $row[] = $transaction->name;
            if (in_array($type, ['payin', 'all'])) {
                $row[] = $transaction->email;
            }
            $row[] = $transaction->phone;
            $row[] = "₹ " . $transaction->amount;
            $row[] = "₹ " . $transaction->charges;
            $row[] = "₹ " . $transaction->gst;
            $row[] = "₹ " . $transaction->net_amount;
            if (in_array($type, ['payin', 'qr', 'all'])) {
                $row[] = $transaction->payee_name;
                $row[] = $transaction->payee_vpa;
                $row[] = $transaction->payee_phone;
            }

            if (in_array($type, ['payout', 'all'])) {
                $row[] = $transaction->account_number;
                $row[] = $transaction->ifsc_code;
                $row[] = $transaction->bank_name;
            }

            $row[] = $transaction->order_id;
            $row[] = $this->getSourceSpan($transaction->source);

            if (in_array($type, ['payout', 'all'])) {
                $row[] = $transaction->service == "payout" ? ($transaction->debited == 1 ? "<span class='badge bg-success-transparent'>Yes</span>" : "<span class='badge bg-danger-transparent'>No</span>") : "";
                $row[] = $transaction->service == "payout" ? ($transaction->refunded == 1 ? "<span class='badge bg-success-transparent'>Yes</span>" : "<span class='badge bg-danger-transparent'>No</span>") : "";
                $row[] = $transaction->service == "payout" ? ($transaction->processed == 1 ? "<span class='badge bg-success-transparent'>Yes</span>" : "<span class='badge bg-danger-transparent'>No</span>") : "";
            }

            $row[] = str_contains(strtolower($transaction->reason), 'insufficient') ? "" : $transaction->reason;
            $row[] = $transaction->remarks;
            $row[] = $transaction->provider_name; // provider name
           

            if (in_array($type, ['payin', 'qr', 'all'])) {
                $row[] =
                    $transaction->service == "payin" && $transaction->status == "Success"
                        ? ($transaction->settled == 1
                            ? "<span class='badge bg-success-transparent'>Yes</span>"
                            : "<span class='badge bg-warning-transparent'>Pending</span>")
                        : "";
                $row[] = $transaction->settlement_txn_id;
            }

            $row[] = $transaction->txn_complete_time;
            $row[] = $transaction->updated_at;
            $row[] = $transaction->created_at;

            if (in_array($type, ['payout', 'all'])) {
                $row[] = $transaction->status == "Success" ? '<button class="btn btn-success-transparent btn-sm" onclick="downloadReceipt(this,\'' . $transaction->txnid . '\')"><i class="ti ti-receipt"></i></button>' : "";
            }

            $data[] = $row;
            $index++;
        }

        // Return JSON response
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalTransactions,
            'recordsFiltered' => $filteredTransactions,
            'data' => $data,
            'extra' => [
                "total_transactions" => $extraData->total_transactions,
                "total_amount" => "₹ " . round($extraData->total_amount, 2),
                "total_charges" => "₹ " . round($extraData->total_charges, 2),
                "total_gst" => "₹ " . round($extraData->total_gst, 2),
            ],
        ], 200);
    }

    private function getSourceSpan($source)
    {
        if ($source == "panel") {
            return '<span class="badge bg-primary-transparent"><i class="ti ti-browser"></i>&nbsp;PANEL</span>';
        }
        if ($source == "api") {
            return '<span class="badge bg-success-transparent"><i class="ti ti-code"></i>&nbsp;API</span>';
        }
    }
    
    private function getStatusSpan($status)
    {
        if ($status == "Success") {
            return '<span class="badge bg-success-transparent">Success</span>';
        }
        if ($status == "Pending") {
            return '<span class="badge bg-warning-transparent">Pending</span>';
        }
        if ($status == "Failed") {
            return '<span class="badge bg-danger-transparent">Failed</span>';
        }
        if ($status == "Hold") {
            return '<span class="badge bg-danger-transparent">Hold</span>';
        }
        if ($status == "Expired") {
            return '<span class="badge bg-danger-transparent">Expired</span>';
        }
        if ($status == "Processing") {
            return '<span class="badge bg-info-transparent">Processing</span>';
        }

        if ($status == "Uploaded") {
            return '<span class="badge bg-primary-transparent">Uploaded</span>';
        }

        if ($status == "Approved") {
            return '<span class="badge bg-success-transparent">Approved</span>';
        }

        if ($status == "Rejected") {
            return '<span class="badge bg-danger-transparent">Rejected</span>';
        }
    }

    public function export_transactions()
    {
        $session = session();
        $admin = new \App\Models\Admin();
        $admin = $admin->find($session->get('admin_id'));
       

        // Set CSV filename
        $filename = 'export_' . date('Ymd_His') . '.csv';

        $availableSearchKeys = [
            "txnid",
            "merchant_txnid",
            "reference_id",
            "rrn",
            "name",
            "email",
            "phone",
            "amount",
            "payee_name",
            "payee_vpa",
            "payee_phone",
            "account_number",
            "ifsc_code",
            "order_id",
            "source",
            "remarks",
            "qr_name",
            "settlement_txnid",
        ];
        $request = request();
        $session = session();
        $searchBy = $request->input('searchBy');
        $searchValue = $request->input('searchValue');
        $type = $request->input('type');
        $startDate = $request->input('startDate') . " 00:00:00";
        $endDate = $request->input('endDate') . " 23:59:59";
        $status = $request->input('status');
        $user_id = $request->input('user_id');
        $provider = $request->input('provider');

        $transactionModel = new \App\Models\Transaction();

        $builder = $transactionModel->query();

        $builder->selectRaw("transactions.*, users.name as user, COALESCE(payin_provider.name, payout_provider.name) as provider_name, settlements.txnid as settlement_txn_id");
        $builder->join("users", "users.id", "=", "transactions.user_id");

        $builder->leftJoin("providers as payin_provider", function($join) { $join->on("payin_provider.id", "=", "transactions.payin_provider")->where("transactions.service", "=", "payin"); });
        $builder->leftJoin("providers as payout_provider", function($join) { $join->on("payout_provider.id", "=", "transactions.payout_provider")->where("transactions.service", "=", "payout"); });
      

        $builder->leftJoin("settlements", function($join) { $join->on("settlements.id", "=", "transactions.settlement_id")->where("transactions.settlement_id", "!=", 0); });

        if ($type == "payin") {
            $builder->where('transactions.service', 'payin');
            $builder->where('qr_id', 0);
        } elseif ($type == "payout") {
            $builder->where('transactions.service', 'payout');
        } elseif ($type == "qr") {
            $builder->where('transactions.service', 'payin');
            $builder->where('transactions.qr_id', '!=', 0);
        }

        $builder->where('transactions.created_at', '>=', $startDate);
        $builder->where('transactions.created_at', '<=', $endDate);

        if ($status != "" && in_array($status, ["Success", "Pending", "Failed", "Expired"])) {
            $builder->where("transactions.status", $status);
        }

        if ($user_id > 0) {
            $builder->where("transactions.user_id", $user_id);
        }

        if ($provider > 0) {
            $builder
                ->where(function($q) use ($provider) { $q->orWhere('transactions.payin_provider', $provider)->orWhere('transactions.payout_provider', $provider); });
        }

        if (!empty($searchBy) && !empty($searchValue)) {
            if (in_array($searchBy, $availableSearchKeys)) {
                if ($searchBy == "qr_name") {
                    $builder->where('qr_codes.name', 'like', '%' . $searchValue . '%');
                } elseif ($searchBy == "settlement_txnid") {
                    $builder->where('settlements.txnid', 'like', '%' . $searchValue . '%');
                } else {
                    $builder->where('transactions.' . $searchBy, 'like', '%' . $searchValue . '%');
                }
            }
        }

        $builder->orderBy('transactions.id', "DESC");

        $columns = ["SR", "USER", "SERVICE", "TXNID"];
        if (in_array($type, ['payin', 'payout', 'all'])) {
            $columns[] = "MERCHANT TXNID";
        }
        $columns[] = "REFERENCE ID";
        $columns[] = "RRN";
        $columns[] = "STATUS";
        $columns[] = "NAME";
        if (in_array($type, ['payin', 'all'])) {
            $columns[] = "EMAIL";
        }
        $columns[] = "PHONE";
        $columns[] = "AMOUNT";
        $columns[] = "CHARGES";
        $columns[] = "GST";
        $columns[] = "NET AMOUNT";
        if (in_array($type, ['payin', 'qr', 'all'])) {
            $columns[] = "PAYEE NAME";
            $columns[] = "PAYEE VPA";
            $columns[] = "PAYEE PHONE";
        }
        if (in_array($type, ['payout', 'all'])) {
            $columns[] = "ACCOUNT NUMBER";
            $columns[] = "IFSC CODE";
            $columns[] = "BANK NAME";
        }
        $columns[] = "ORDER ID";
        $columns[] = "SOURCE";
        if (in_array($type, ['payout', 'all'])) {
            $columns[] = "IS DEBITED";
            $columns[] = "IS REFUNDED";
            $columns[] = "IS PROCESSED";
        }
        $columns[] = "REASON";
        $columns[] = "REMARKS";
        $columns[] = "PROVIDER NAME";
        if (in_array($type, ['qr', 'all'])) {
            $columns[] = "QR NAME";
        }
        if (in_array($type, ['payin', 'qr', 'all'])) {
            $columns[] = "IS SETTLED";
            $columns[] = "SETTLEMENT TXNID";
        }
        $columns[] = "TRANSACTION COMPLETE TIME";
        $columns[] = "UPDATED AT";
        $columns[] = "CREATED AT";

        return response()->streamDownload(function() use ($builder, $type, $columns) {
            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF");
            fputcsv($output, $columns);
            $index = 1;

            foreach ($builder->cursor() as $transaction) {
                $row = [];
                $row[] = $index++;
                $row[] = $transaction->user;
                $row[] = $transaction->service == "payout" ? "PAYOUT" : ($transaction->service == "payin" && $transaction->qr_id == 0 ? "PAYIN" : ($transaction->service == "payin" && $transaction->qr_id != 0 ? "QR" : "OTHER"));
                $row[] = $transaction->txnid;

                if (in_array($type, ['payin', 'payout', 'all'])) {
                    $row[] = $transaction->merchant_txnid;
                }
                $row[] = $transaction->reference_id;
                $row[] = $transaction->rrn;
                $row[] = $transaction->status;
                $row[] = $transaction->name;
                if (in_array($type, ['payin', 'all'])) {
                    $row[] = $transaction->email;
                }
                $row[] = $transaction->phone;
                $row[] = $transaction->amount;
                $row[] = $transaction->charges;
                $row[] = $transaction->gst;
                $row[] = $transaction->net_amount;
                if (in_array($type, ['payin', 'qr', 'all'])) {
                    $row[] = $transaction->payee_name;
                    $row[] = $transaction->payee_vpa;
                    $row[] = $transaction->payee_phone;
                }

                if (in_array($type, ['payout', 'all'])) {
                    $row[] = $transaction->account_number;
                    $row[] = $transaction->ifsc_code;
                    $row[] = $transaction->bank_name;
                }

                $row[] = $transaction->order_id;
                $row[] = $transaction->source;

                if (in_array($type, ['payout', 'all'])) {
                    $row[] = $transaction->service == "payout" ? ($transaction->debited == 1 ? "Yes" : "No") : "";
                    $row[] = $transaction->service == "payout" ? ($transaction->refunded == 1 ? "Yes" : "No") : "";
                    $row[] = $transaction->service == "payout" ? ($transaction->processed == 1 ? "Yes" : "No") : "";
                }

                $row[] = str_contains(strtolower($transaction->reason ?? ''), 'insufficient') ? "" : ($transaction->reason ?? '');
                $row[] = $transaction->remarks ?? '';
                $row[] = $transaction->provider_name ?? '';

                if (in_array($type, ['payin', 'qr', 'all'])) {
                    $row[] = $transaction->service == "payin" && $transaction->status == "Success" ? ($transaction->settled == 1 ? "Yes" : "Pending") : "";
                    $row[] = $transaction->settlement_txn_id ?? '';
                }

                $row[] = $transaction->txn_complete_time ?? '';
                $row[] = $transaction->updated_at ?? '';
                $row[] = $transaction->created_at ?? '';

                fputcsv($output, $row);
            }
            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    public function wallet()
    {
        $session = session();
        $admin = new \App\Models\Admin();
        $admin = $admin->find($session->get('admin_id'));
       

        $availableSearchKeys = ["txnid"];
        $request = request();
        $session = session();
        $offset = $request->input('start');
        $limit = $request->input('length');
        $searchBy = $request->input('searchBy');
        $searchValue = $request->input('searchValue');
        $draw = (int) $request->input('draw');
        $type = $request->input('type');
        $startDate = $request->input('startDate') . " 00:00:00";
        $endDate = $request->input('endDate') . " 23:59:59";
        $user_id = $request->input('user_id');

        $transactionModel = new \App\Models\Wallet();

        if ($type != "all") {
            if($type == "REFUND-DEBITED"){
                $transactionModel->where('wallet_transactions.remarks', $type);
            }else{
                $transactionModel->where('wallet_transactions.txn_type', $type);
            }
        }
        

        $transactionModel->where('wallet_transactions.created_at', '>=', $startDate);
        $transactionModel->where('wallet_transactions.created_at', '<=', $endDate);

        if ($user_id > 0) {
            $transactionModel->where("wallet_transactions.user_id", $user_id);
        }

        $totalTransactions = $transactionModel->count();
        $builder = $transactionModel->query();

        $builder->selectRaw("wallet_transactions.*, users.name as user");
        $builder->join("users", "users.id", "=", "wallet_transactions.user_id");

        if ($type != "all") {
            if($type == "REFUND-DEBITED"){
               $builder->where('wallet_transactions.remarks', $type); 
            }else{
            $builder->where('wallet_transactions.txn_type', $type);
            }
        }

        $builder->where('wallet_transactions.created_at', '>=', $startDate);
        $builder->where('wallet_transactions.created_at', '<=', $endDate);

        if ($user_id > 0) {
            $builder->where("wallet_transactions.user_id", $user_id);
        }

        if (!empty($searchBy) && !empty($searchValue)) {
            if (in_array($searchBy, $availableSearchKeys)) {
                $builder->like('wallet_transactions.' . $searchBy, $searchValue);
            }
        }

        $filteredBuilder = clone $builder;
        $filteredTransactions = $filteredBuilder->count();

        $builder->orderBy('wallet_transactions.id', "DESC");
        if ($limit != -1) {
            $builder->limit($limit, $offset);
        }

        $query = $builder->get();
        $transactions = $query;

        $extraBuiler = (new \App\Models\Wallet())->query();
        $extraBuiler->selectRaw("SUM(net_amount) as total_amount, txn_type");
        $extraBuiler->groupBy('txn_type');

        if ($type != "all") {
            $extraBuiler->where('wallet_transactions.txn_type', $type);
        }

        $extraBuiler->where('wallet_transactions.created_at', '>=', $startDate);
        $extraBuiler->where('wallet_transactions.created_at', '<=', $endDate);

        if ($user_id > 0) {
            $extraBuiler->where("wallet_transactions.user_id", $user_id);
        }

        if (!empty($searchBy) && !empty($searchValue)) {
            if (in_array($searchBy, $availableSearchKeys)) {
                $extraBuiler->like('wallet_transactions.' . $searchBy, $searchValue);
            }
        }

        $extraData = $extraBuiler->get();

        $extraResult = [];
        foreach ($extraData as $ex) {
            $extraResult[strtolower($ex->txn_type)] = round($ex->total_amount, 2);
        }

        $data = [];
        $index = 1;

        foreach ($transactions as $transaction) {
            $row = [];
            $row[] = $index;
            $row[] = $transaction->user;
            $row[] =
                $transaction->type == "CREDIT"
                    ? '<span class="badge bg-success-transparent"><i class="ti ti-circle-plus"></i>&nbsp;CREDIT</span>'
                    : '<span class="badge bg-danger-transparent"><i class="ti ti-circle-minus"></i>&nbsp;DEBIT</span>';
            $row[] = $this->getTxnTypeSpan($transaction->txn_type);
            $row[] = $transaction->txnid;
            $row[] = "₹ " . $transaction->opening;
            $row[] = "₹ " . $transaction->net_amount;
            $row[] = $transaction->remarks;
            $row[] = "₹ " . $transaction->closing;
            $row[] = $transaction->created_at;

            $data[] = $row;
            $index++;
        }

        // Return JSON response
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalTransactions,
            'recordsFiltered' => $filteredTransactions,
            'data' => $data,
            'extra' => [
                "settlement" => $extraResult['settlement'] ?? 0,
                "payout" => $extraResult['payout'] ?? 0,
                "refund" => $extraResult['refund'] ?? 0,
                "topup" => $extraResult['topup'] ?? 0,
                "hold" => $extraResult['hold'] ?? 0,
                "holdReleased" => $extraResult['hold-release'] ?? 0,
                "advanceCredited" => $extraResult['advance-credit'] ?? 0,
                "advanceDebited" => $extraResult['advance-debit'] ?? 0,
            ],
        ], 200);
    }

    public function export_wallet_transactions()
    {
        $session = session();
        $admin = new \App\Models\Admin();
        $admin = $admin->find($session->get('admin_id'));
        if (!$admin) {
            abort(401, 'Unauthorized');
        }

        $availableSearchKeys = ["txnid"];
        $request = request();
        $searchBy = $request->input('searchBy');
        $searchValue = $request->input('searchValue');
        $type = $request->input('type');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $user_id = $request->input('user_id');

        $transactionModel = new \App\Models\Wallet();
        $builder = $transactionModel->query();

        $builder->selectRaw("wallet_transactions.*, users.name as user");
        $builder->join("users", "users.id", "=", "wallet_transactions.user_id");

        if (!empty($type) && $type != "all") {
            if ($type == "REFUND-DEBITED") {
                $builder->where('wallet_transactions.remarks', $type);
            } else {
                $builder->where('wallet_transactions.txn_type', $type);
            }
        }

        if (!empty($startDate)) {
            $builder->where('wallet_transactions.created_at', '>=', $startDate . " 00:00:00");
        }
        if (!empty($endDate)) {
            $builder->where('wallet_transactions.created_at', '<=', $endDate . " 23:59:59");
        }

        if (!empty($user_id) && $user_id > 0) {
            $builder->where("wallet_transactions.user_id", $user_id);
        }

        if (!empty($searchBy) && !empty($searchValue)) {
            if (in_array($searchBy, $availableSearchKeys)) {
                $builder->like('wallet_transactions.' . $searchBy, $searchValue);
            }
        }

        $builder->orderBy('wallet_transactions.id', "DESC");

        $filename = 'wallet_transactions_' . date('Ymd_His') . '.csv';

        $columns = [
            "SR",
            "USER",
            "TYPE",
            "TXN TYPE",
            "TXN ID",
            "OPENING",
            "AMOUNT",
            "REMARKS",
            "CLOSING",
            "DATE TIME"
        ];

        return response()->streamDownload(function() use ($builder, $columns) {
            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF");
            fputcsv($output, $columns);

            $index = 1;
            foreach ($builder->cursor() as $transaction) {
                fputcsv($output, [
                    $index++,
                    $transaction->user ?? '',
                    $transaction->type ?? '',
                    $transaction->txn_type ?? '',
                    $transaction->txnid ?? '',
                    $transaction->opening ?? '0',
                    $transaction->net_amount ?? '0',
                    $transaction->remarks ?? '',
                    $transaction->closing ?? '0',
                    $transaction->created_at ?? ''
                ]);
            }
            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    public function credits()
    {
        $session = session();
        $admin = new \App\Models\Admin();
        $admin = $admin->find($session->get('admin_id'));
       
        $availableSearchKeys = ["txnid"];
        $request = request();
        $session = session();
        $offset = $request->input('start');
        $limit = $request->input('length');
        $searchBy = $request->input('searchBy');
        $searchValue = $request->input('searchValue');
        $draw = (int) $request->input('draw');
        $type = $request->input('type');

        $creditsModel = new \App\Models\Credits();

        if ($type == "pending") {
            $creditsModel->where("debited", 0);
        } elseif ($type == "completed") {
            $creditsModel->where("debited", 1);
        }

        $totalCredits = $creditsModel->count();
        $builder = $creditsModel->query();

        $builder->selectRaw("credits.*, users.name as user");
        $builder->join("users", "users.id", "=", "credits.user_id");

        if ($type == "pending") {
            $builder->where("debited", 0);
        } elseif ($type == "completed") {
            $builder->where("debited", 1);
        }

        if (!empty($searchBy) && !empty($searchValue)) {
            if (in_array($searchBy, $availableSearchKeys)) {
                $builder->like('credits.' . $searchBy, $searchValue);
            }
        }

        $filteredBuilder = clone $builder;
        $filteredCredits = $filteredBuilder->count();

        $builder->orderBy('credits.id', "DESC");
        if ($limit != -1) {
            $builder->limit($limit, $offset);
        }

        $query = $builder->get();
        $transactions = $query;

        $data = [];
        $index = 1;

        foreach ($transactions as $transaction) {
            $row = [];
            $row[] = $index;
            $row[] = $transaction->user;
            $row[] = $transaction->txnid;
            $row[] = "₹ " . $transaction->amount;
            $row[] = $transaction->credit_remarks;
            if ($type == "completed") {
                $row[] = $transaction->debit_remarks;
            }
            $row[] = $transaction->credited_at;
            if ($type == "completed") {
                $row[] = $transaction->debited_at;
            }
            $row[] =  '<button onclick="debitCredits(\'' . $transaction->id . '\')" class="btn btn-sm btn-primary-transparent"><i class="ti ti-circle-minus"></i></button>' ;

            $data[] = $row;
            $index++;
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalCredits,
            'recordsFiltered' => $filteredCredits,
            'data' => $data,
        ], 200);
    }

    public function hold_transactions()
    {
        $session = session();
        $admin = new \App\Models\Admin();
        $admin = $admin->find($session->get('admin_id'));
        $availableSearchKeys = ["txnid"];
        $request = request();
        $offset = (int) $request->input('start');
        $limit = (int) $request->input('length');
        $searchBy = $request->input('searchBy');
        $searchVal = $request->input('searchValue');
        $draw = (int) $request->input('draw');
        $status = $request->input('status'); // hold or released
        $userId = $request->input('userId');

        $holdWalletModel = new \App\Models\HoldWallet();
        $builder = $holdWalletModel->query();

        $builder->selectRaw("hold_wallet_transactions.*, users.name as username");
        $builder->join("users", "users.id", "=", "hold_wallet_transactions.user_id");

        if ($status == "hold") {
            $builder->where("hold_wallet_transactions.type", "hold");
            $builder->whereRaw("NOT EXISTS (
            SELECT 1 FROM hold_wallet_transactions h2
            WHERE h2.txnid = hold_wallet_transactions.txnid
              AND h2.type = 'released'
        )");
        } elseif ($status == "released") {
            $builder->where("hold_wallet_transactions.type", "released");
        }

        if (!empty($userId)) {
            $builder->where("hold_wallet_transactions.user_id", $userId);
        }

        if (!empty($searchBy) && !empty($searchVal)) {
            if (in_array($searchBy, $availableSearchKeys)) {
                $builder->like("hold_wallet_transactions." . $searchBy, $searchVal);
            }
        }

        $totalBuilder = clone $builder;
        $recordsFiltered = $totalBuilder->count();
        $recordsTotal = $holdWalletModel->count();

        $builder->orderBy('hold_wallet_transactions.id', "DESC");
        if ($limit != -1) {
            $builder->limit($limit, $offset);
        }

        $transactions = $builder->get();
       
        $data = [];
        $index = $offset + 1;
        foreach ($transactions as $txn) {
            $row = [];
            $row[] = $index;
            $row[] = $txn->username ?? ("User #" . $txn->user_id);
            $row[] = '<span class="badge bg-' . ($txn->type == 'hold' ? 'danger' : 'success') . '-transparent text-uppercase">' . ($txn->type ?? 'HOLD') . '</span>';
            $row[] = $txn->txnid ?? 'N/A';
            $row[] = "₹ " . ($txn->amount ?? 0);
            $row[] = ($txn->type == 'hold')
                ? '<span class="badge bg-warning-transparent">On Hold</span>'
                : '<span class="badge bg-success-transparent">Released</span>';
            $row[] = $txn->reason ?? 'N/A';
            $row[] = $txn->created_at ?? 'N/A';
            if ($status == "hold") {
                $row[] = '<button onclick="releaseHold(\'' . $txn->id . '\')" class="btn btn-sm btn-primary-transparent" title="Release Hold"><i class="ti ti-check"></i> Release</button>';
            } else {
                $row[] = $txn->updated_at ?? 'N/A';
            }

            $data[] = $row;
            $index++;
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ], 200);
    }

    private function getTxnTypeSpan($type)
    {
        if ($type == "SETTLEMENT") {
            return '<span class="badge bg-success-transparent"><i class="ti ti-receipt"></i>&nbsp;' . $type . '</span>';
        }

        if ($type == "PAYOUT") {
            return '<span class="badge bg-orange-transparent"><i class="ti ti-cash"></i>&nbsp;' . $type . '</span>';
        }

        if ($type == "REFUND") {
            return '<span class="badge bg-secondary-transparent"><i class="ti ti-receipt-refund"></i>&nbsp;' . $type . '</span>';
        }

        if ($type == "TOPUP") {
            return '<span class="badge bg-primary-transparent"><i class="ti ti-wallet"></i>&nbsp;' . $type . '</span>';
        }

        if ($type == "HOLD") {
            return '<span class="badge bg-danger-transparent"><i class="ti ti-forbid-2"></i>&nbsp;' . $type . '</span>';
        }

        if ($type == "HOLD-RELEASE") {
            return '<span class="badge bg-teal-transparent"><i class="ti ti-circle-check"></i>&nbsp;' . $type . '</span>';
        }

        if ($type == "ADVANCE-CREDIT") {
            return '<span class="badge bg-purple-transparent"><i class="ti ti-building-bank"></i>&nbsp;' . $type . '</span>';
        }

        if ($type == "ADVANCE-DEBIT") {
            return '<span class="badge bg-indigo-transparent"><i class="ti ti-credit-card"></i>&nbsp;' . $type . '</span>';
        }
    }

    public function export_chargebacks()
    {
        $session = session();
        $admin = new \App\Models\Admin();
        $admin = $admin->find($session->get('admin_id'));
       
        $availableSearchKeys = ["rrn", "txnid", "amount", "vpa"];
        $request = request();
        $session = session();
        $searchBy = $request->input('searchBy');
        $searchValue = $request->input('searchValue');
        $userId = $request->input('user_id');
        $status = $request->input('status');
        $startDate = $request->input('startDate') . " 00:00:00";
        $endDate = $request->input('endDate') . " 23:59:59";
        $chargebackModel = new \App\Models\Chargeback();
        $totalChargebacks = $chargebackModel->count();
        $builder = $chargebackModel->query();
        $builder->selectRaw("chargebacks.*, transactions.* , transactions.user_id as user_id, users.name as user_name, chargebacks.status as status, chargebacks.created_at as chargeback_time, chargebacks.updated_at as last_update");
        $builder->join("transactions", "transactions.rrn", "=", "chargebacks.rrn");
        $builder->join("users", "users.id", "=", "chargebacks.user_id");

        if (!empty($searchBy) && !empty($searchValue)) {
            if (in_array($searchBy, $availableSearchKeys)) {
                if ($searchBy == "txnid") {
                    $builder->where('transactions.txnid', 'like', '%' . $searchValue . '%');
                } elseif ($searchBy == "amount") {
                    $builder->where('transactions.amount', 'like', '%' . $searchValue . '%');
                } elseif ($searchBy == "vpa") {
                    $builder->where('transactions.payee_vpa', 'like', '%' . $searchValue . '%');
                } else {
                    $builder->where('chargebacks.' . $searchBy, 'like', '%' . $searchValue . '%');
                }
            }
        }

        $builder->where('chargebacks.created_at', '>=', $startDate);
        $builder->where('chargebacks.created_at', '<=', $endDate);

        if ($userId != "") {
            $builder->where("chargebacks.user_id", $userId);
        }

        if ($status != "") {
            $builder->where("chargebacks.status", $status);
        }

        $builder->orderBy('chargebacks.id', "DESC");
        $columns = ["ID", "USER", "TXNID", "PAYEE NAME", "PAYEE PHONE", "PAYEE VPA", "RRN", "AMOUNT", "REFUND RRN", "STATUS", "TXN COMPLETE TIME", "CHARGEBACK TIME", "NO OF CHARGEBACK", "LAST UPDATE TIME"];
        $filename = 'chargebacks_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function() use ($builder, $columns) {
            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF");
            fputcsv($output, $columns);
            $index = 1;

            foreach ($builder->cursor() as $chargeback) {
                fputcsv($output, [
                    $index++,
                    $chargeback->user_name ?? '',
                    $chargeback->txnid ?? '',
                    $chargeback->payee_name ?? '',
                    $chargeback->payee_phone ?? '',
                    $chargeback->payee_vpa ?? '',
                    $chargeback->rrn ?? '',
                    $chargeback->amount ?? '',
                    $chargeback->refund_rrn ?? '',
                    $chargeback->status ?? '',
                    $chargeback->txn_complete_time ?? '',
                    $chargeback->chargeback_time ?? '',
                    $chargeback->no ?? '',
                    $chargeback->last_update ?? '',
                ]);
            }
            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    public function error_logs()
    {
        $session = session();
        $admin = new \App\Models\Admin();
        $admin = $admin->find($session->get('admin_id'));

        $availableSearchKeys = ["txnid", "type"];
        $request = request();
        $offset = (int) $request->input('start', 0);
        $limit = (int) $request->input('length', 10);
        $searchBy = $request->input('searchBy');
        $searchValue = $request->input('searchValue');
        $provider = $request->input('provider');
        $type = $request->input('type');
        $startDate = $request->input('startDate') . " 00:00:00";
        $endDate = $request->input('endDate') . " 23:59:59";
        $draw = (int) $request->input('draw');

        $errorLogModel = new \App\Models\ErrorLog();
        $totalErrorLogs = $errorLogModel->whereBetween('error_logs.created_at', [$startDate, $endDate])->count();

        $builder = $errorLogModel->query();
        $builder->selectRaw("error_logs.*, COALESCE(providers.name, 'N/A') as provider_name");
        $builder->leftJoin("providers", "providers.id", "=", "error_logs.provider");
        $builder->whereBetween('error_logs.created_at', [$startDate, $endDate]);

        if (!empty($provider)) {
            $builder->where('error_logs.provider', $provider);
        }

        if (!empty($type)) {
            $builder->where('error_logs.type', $type);
        }

        if (!empty($searchBy) && !empty($searchValue) && in_array($searchBy, $availableSearchKeys)) {
            $builder->like('error_logs.' . $searchBy, $searchValue);
        }

        $filteredBuilder = clone $builder;
        $filteredErrorLogs = $filteredBuilder->count();

        $builder->orderBy('error_logs.id', "DESC");
        if ($limit != -1) {
            $builder->limit($limit, $offset);
        }

        $errorLogs = $builder->get();
        $data = [];
        $index = $offset + 1;

        foreach ($errorLogs as $log) {
            $typeBadge = '<span class="badge bg-danger-transparent">' . htmlspecialchars($log->type) . '</span>';
            if ($log->type === 'PAYIN') {
                $typeBadge = '<span class="badge bg-info-transparent">PAYIN</span>';
            } elseif ($log->type === 'PAYOUT') {
                $typeBadge = '<span class="badge bg-warning-transparent">PAYOUT</span>';
            }

            $detailsBtn = '<button class="btn btn-sm btn-primary-transparent" onclick="viewLogModal(' . $log->id . ')"><i class="ti ti-eye"></i> View</button>'
                . '<div id="log_data_' . $log->id . '" style="display:none;" '
                . 'data-type="' . htmlspecialchars($log->type, ENT_QUOTES) . '" '
                . 'data-txnid="' . htmlspecialchars($log->txnid, ENT_QUOTES) . '" '
                . 'data-provider="' . htmlspecialchars($log->provider_name, ENT_QUOTES) . '" '
                . 'data-time="' . htmlspecialchars($log->created_at, ENT_QUOTES) . '" '
                . 'data-log="' . htmlspecialchars($log->log ?? '', ENT_QUOTES) . '" '
                . 'data-request="' . htmlspecialchars($log->request ?? '', ENT_QUOTES) . '"></div>';

            $data[] = [
                $index,
                $typeBadge,
                $log->txnid ? '<span class="fw-semibold">' . htmlspecialchars($log->txnid) . '</span>' : 'N/A',
                htmlspecialchars($log->provider_name),
                $detailsBtn,
                $log->created_at,
            ];
            $index++;
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalErrorLogs,
            'recordsFiltered' => $filteredErrorLogs,
            'data' => $data,
        ], 200);
    }

    public function profit()
    {
        $session = session();
        $admin = new \App\Models\Admin();
        $admin = $admin->find($session->get('admin_id'));

        $request = request();
        $offset = (int) $request->input('start', 0);
        $limit = (int) $request->input('length', 10);
        $draw = (int) $request->input('draw', 1);

        $startDate = $request->input('startDate') ? ($request->input('startDate') . " 00:00:00") : date('Y-m-d 00:00:00', strtotime('-6 days'));
        $endDate = $request->input('endDate') ? ($request->input('endDate') . " 23:59:59") : date('Y-m-d 23:59:59');

        $service = $request->input('service');
        $providerId = $request->input('provider');
        $userId = $request->input('user_id');
        $searchValue = trim($request->input('searchValue', ''));

        $transactionModel = new \App\Models\Transaction();
        $builder = $transactionModel->query();
        $builder->selectRaw("transactions.*, users.name as user_name, users.merchant_id as merchant_id");
        $builder->join("users", "users.id", "=", "transactions.user_id");
        $builder->where("transactions.status", "Success");
        $builder->whereBetween("transactions.created_at", [$startDate, $endDate]);

        if (!empty($service) && in_array($service, ['payin', 'payout'])) {
            $builder->where("transactions.service", $service);
        }

        if (!empty($userId)) {
            $builder->where("transactions.user_id", $userId);
        }

        if (!empty($providerId)) {
            $builder->where(function($q) use ($providerId) {
                $q->where("transactions.payin_provider", $providerId)
                  ->orWhere("transactions.payout_provider", $providerId);
            });
        }

        if (!empty($searchValue)) {
            $builder->where(function($q) use ($searchValue) {
                $q->where("transactions.txnid", "like", "%{$searchValue}%")
                  ->orWhere("transactions.merchant_txnid", "like", "%{$searchValue}%")
                  ->orWhere("transactions.reference_id", "like", "%{$searchValue}%")
                  ->orWhere("users.name", "like", "%{$searchValue}%");
            });
        }

        $allProviders = \App\Models\Providers::all()->keyBy('id');
        $allSlabs = \App\Models\Providerslab::all();
        $gstRate = \App\Models\Settings::getGSTRate();

        $filteredTransactionsCount = (clone $builder)->count();
        $allFilteredList = (clone $builder)->select([
            'transactions.amount',
            'transactions.charges',
            'transactions.gst',
            'transactions.provider_charge',
            'transactions.provider_gst',
            'transactions.service',
            'transactions.payin_provider',
            'transactions.payout_provider'
        ])->get();

        $totalVolume = 0.0;
        $totalPartnerCharge = 0.0;
        $totalProviderCharge = 0.0;

        foreach ($allFilteredList as $item) {
            $amt = floatval($item->amount);
            $totalVolume += $amt;
            $pTotal = floatval($item->charges) + floatval($item->gst);
            $totalPartnerCharge += $pTotal;

            $provBase = floatval($item->provider_charge ?? 0);
            $provGst = floatval($item->provider_gst ?? 0);
            $provTotal = $provBase + $provGst;

            // Fallback for legacy rows if not stored
            if ($provTotal <= 0 && $amt > 0) {
                $pId = ($item->service === 'payout') ? $item->payout_provider : $item->payin_provider;
                $matchedSlab = $allSlabs->first(function($s) use ($pId, $amt) {
                    return $s->provider_id == $pId && $amt >= floatval($s->min_amount) && $amt <= floatval($s->max_amount);
                });
                if ($matchedSlab) {
                    $c = $matchedSlab->percent ? ($matchedSlab->amount * $amt) / 100 : floatval($matchedSlab->amount);
                    $g = $matchedSlab->gst ? ($c * $gstRate) / 100 : 0.0;
                    $provTotal = $c + $g;
                }
            }

            $totalProviderCharge += $provTotal;
        }

        $totalProfit = $totalPartnerCharge - $totalProviderCharge;
        $overallMargin = ($totalVolume > 0) ? ($totalProfit / $totalVolume) * 100 : 0.0;

        $builder->orderBy('transactions.id', 'DESC');
        if ($limit != -1) {
            $builder->skip($offset)->take($limit);
        }
        $transactions = $builder->get();

        $data = [];
        $index = $offset + 1;

        foreach ($transactions as $txn) {
            $amt = floatval($txn->amount);
            $pCharge = floatval($txn->charges);
            $pGst = floatval($txn->gst);
            $partnerTotal = $pCharge + $pGst;

            $provId = ($txn->service === 'payout') ? $txn->payout_provider : $txn->payin_provider;
            $providerObj = $allProviders->get($provId);
            $providerName = $providerObj ? $providerObj->name : 'N/A';

            // Fetch directly from transaction table entry
            $provBase = floatval($txn->provider_charge ?? 0);
            $provGst = floatval($txn->provider_gst ?? 0);
            $providerTotal = $provBase + $provGst;

            if ($providerTotal <= 0 && $amt > 0) {
                $matchedSlab = $allSlabs->first(function($s) use ($provId, $amt) {
                    return $s->provider_id == $provId && $amt >= floatval($s->min_amount) && $amt <= floatval($s->max_amount);
                });
                if ($matchedSlab) {
                    $provBase = $matchedSlab->percent ? ($matchedSlab->amount * $amt) / 100 : floatval($matchedSlab->amount);
                    $provGst = $matchedSlab->gst ? ($provBase * $gstRate) / 100 : 0.0;
                    $providerTotal = $provBase + $provGst;
                    $rateLabel = $matchedSlab->percent ? ($matchedSlab->amount . '%') : ('₹' . $matchedSlab->amount);
                } else {
                    $rateLabel = 'No Slab';
                }
            } else {
                $rateLabel = 'Base: ₹' . number_format($provBase, 2);
            }

            $profit = $partnerTotal - $providerTotal;
            $marginPct = ($amt > 0) ? ($profit / $amt) * 100 : 0.0;

            $serviceBadge = ($txn->service === 'payin')
                ? '<span class="badge bg-success-transparent"><i class="ti ti-arrow-down-left me-1"></i>PayIn</span>'
                : '<span class="badge bg-danger-transparent"><i class="ti ti-arrow-up-right me-1"></i>PayOut</span>';

            $profitBadge = ($profit >= 0)
                ? '<span class="badge bg-success-transparent fs-12 fw-semibold text-success"><i class="ti ti-trending-up me-1"></i>+₹ ' . number_format($profit, 2) . '</span>'
                : '<span class="badge bg-danger-transparent fs-12 fw-semibold text-danger"><i class="ti ti-trending-down me-1"></i>-₹ ' . number_format(abs($profit), 2) . '</span>';

            $partnerCell = '<div><strong>₹ ' . number_format($partnerTotal, 2) . '</strong><br>'
                . '<small class="text-muted">Fee: ₹' . number_format($pCharge, 2) . ($pGst > 0 ? (' + GST ₹' . number_format($pGst, 2)) : '') . '</small></div>';

            $providerCell = '<div><strong>₹ ' . number_format($providerTotal, 2) . '</strong><br>'
                . '<small class="text-muted">' . htmlspecialchars($rateLabel) . ($provGst > 0 ? (' + GST ₹' . number_format($provGst, 2)) : '') . '</small></div>';

            $marginCell = '<span class="fw-semibold ' . ($marginPct >= 0 ? 'text-success' : 'text-danger') . '">'
                . number_format($marginPct, 2) . '%</span>';

            $data[] = [
                $index,
                '<div><span class="fw-semibold">' . htmlspecialchars($txn->txnid) . '</span><br>' . $serviceBadge . '</div>',
                '<div>' . date('d M Y', strtotime($txn->created_at)) . '<br><small class="text-muted">' . date('h:i A', strtotime($txn->created_at)) . '</small></div>',
                '<div><span class="fw-semibold">' . htmlspecialchars($txn->user_name) . '</span><br><small class="text-muted">' . htmlspecialchars($txn->merchant_id ?? '') . '</small></div>',
                '<span class="badge bg-info-transparent">' . htmlspecialchars($providerName) . '</span>',
                '<strong class="fs-13">₹ ' . number_format($amt, 2) . '</strong>',
                $partnerCell,
                $providerCell,
                $profitBadge,
                $marginCell
            ];

            $index++;
        }

        $totalOverall = \App\Models\Transaction::where('status', 'Success')->count();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalOverall,
            'recordsFiltered' => $filteredTransactionsCount,
            'data' => $data,
            'summary' => [
                'total_volume' => '₹ ' . number_format($totalVolume, 2),
                'partner_charge' => '₹ ' . number_format($totalPartnerCharge, 2),
                'provider_charge' => '₹ ' . number_format($totalProviderCharge, 2),
                'profit' => '₹ ' . number_format($totalProfit, 2),
                'profit_raw' => $totalProfit,
                'margin' => number_format($overallMargin, 2) . '%'
            ]
        ], 200);
    }

    public function export_profit()
    {
        $session = session();
        $admin = new \App\Models\Admin();
        $admin = $admin->find($session->get('admin_id'));

        $filename = 'admin_profit_report_' . date('Ymd_His') . '.csv';

        $request = request();
        $startDate = $request->input('startDate') ? ($request->input('startDate') . " 00:00:00") : date('Y-m-d 00:00:00', strtotime('-6 days'));
        $endDate = $request->input('endDate') ? ($request->input('endDate') . " 23:59:59") : date('Y-m-d 23:59:59');
        $service = $request->input('service');
        $providerId = $request->input('provider');
        $userId = $request->input('user_id');
        $searchValue = trim($request->input('searchValue', ''));

        $builder = (new \App\Models\Transaction())->query();
        $builder->selectRaw("transactions.*, users.name as user_name, users.merchant_id as merchant_id");
        $builder->join("users", "users.id", "=", "transactions.user_id");
        $builder->where("transactions.status", "Success");
        $builder->whereBetween("transactions.created_at", [$startDate, $endDate]);

        if (!empty($service) && in_array($service, ['payin', 'payout'])) {
            $builder->where("transactions.service", $service);
        }
        if (!empty($userId)) {
            $builder->where("transactions.user_id", $userId);
        }
        if (!empty($providerId)) {
            $builder->where(function($q) use ($providerId) {
                $q->where("transactions.payin_provider", $providerId)
                  ->orWhere("transactions.payout_provider", $providerId);
            });
        }
        if (!empty($searchValue)) {
            $builder->where(function($q) use ($searchValue) {
                $q->where("transactions.txnid", "like", "%{$searchValue}%")
                  ->orWhere("transactions.merchant_txnid", "like", "%{$searchValue}%")
                  ->orWhere("users.name", "like", "%{$searchValue}%");
            });
        }

        $allProviders = \App\Models\Providers::all()->keyBy('id');
        $allSlabs = \App\Models\Providerslab::all();
        $gstRate = \App\Models\Settings::getGSTRate();

        return response()->streamDownload(function() use ($builder, $allProviders, $allSlabs, $gstRate) {
            $transactions = $builder->orderBy('transactions.id', 'DESC')->get();

            $output = fopen('php://output', 'w');
        fputcsv($output, [
            '#',
            'Txn ID',
            'Merchant Txn ID',
            'Date Time',
            'Service',
            'Partner Name',
            'Merchant ID',
            'Provider',
            'Amount',
            'Partner Base Charge',
            'Partner GST',
            'Partner Total Charge',
            'Provider Base Charge',
            'Provider GST',
            'Provider Total Charge',
            'Admin Profit',
            'Margin %'
        ]);

        $i = 1;
        foreach ($transactions as $txn) {
            $amt = floatval($txn->amount);
            $pCharge = floatval($txn->charges);
            $pGst = floatval($txn->gst);
            $partnerTotal = $pCharge + $pGst;

            $provId = ($txn->service === 'payout') ? $txn->payout_provider : $txn->payin_provider;
            $providerObj = $allProviders->get($provId);
            $providerName = $providerObj ? $providerObj->name : 'N/A';

            // Fetch directly from transaction table entry
            $provBase = floatval($txn->provider_charge ?? 0);
            $provGst = floatval($txn->provider_gst ?? 0);
            $providerTotal = $provBase + $provGst;

            if ($providerTotal <= 0 && $amt > 0) {
                $matchedSlab = $allSlabs->first(function($s) use ($provId, $amt) {
                    return $s->provider_id == $provId && $amt >= floatval($s->min_amount) && $amt <= floatval($s->max_amount);
                });
                if ($matchedSlab) {
                    $provBase = $matchedSlab->percent ? ($matchedSlab->amount * $amt) / 100 : floatval($matchedSlab->amount);
                    $provGst = $matchedSlab->gst ? ($provBase * $gstRate) / 100 : 0.0;
                    $providerTotal = $provBase + $provGst;
                }
            }

            $profit = $partnerTotal - $providerTotal;
            $marginPct = ($amt > 0) ? ($profit / $amt) * 100 : 0.0;

            fputcsv($output, [
                $i,
                $txn->txnid,
                $txn->merchant_txnid,
                $txn->created_at,
                strtoupper($txn->service),
                $txn->user_name,
                $txn->merchant_id,
                $providerName,
                number_format($amt, 2, '.', ''),
                number_format($pCharge, 2, '.', ''),
                number_format($pGst, 2, '.', ''),
                number_format($partnerTotal, 2, '.', ''),
                number_format($provBase, 2, '.', ''),
                number_format($provGst, 2, '.', ''),
                number_format($providerTotal, 2, '.', ''),
                number_format($profit, 2, '.', ''),
                number_format($marginPct, 2, '.', '') . '%'
            ]);
            $i++;
        }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function payin_transactions()
    {
        $availableSearchKeys = ["rrn", "txnid", "merchant_txnid", "name", "phone", "status"];
        $request = request();
        $offset = (int) $request->input('start', 0);
        $limit = (int) $request->input('length', 10);
        if ($limit <= 0) {
            $limit = 10;
        }
        $searchBy = $request->input('searchBy');
        $searchValue = $request->input('searchValue');
        $draw = (int) $request->input('draw');
        $settlementId = $request->input('settlementId');

        $builder = \App\Models\Transaction::query()->where('service', 'payin');

        if (!empty($settlementId)) {
            $decryptedSettlementId = openssl_decrypt(
                hex2bin($settlementId),
                'AES-256-CBC',
                env("encryption.key"),
                OPENSSL_RAW_DATA,
                substr(hash('sha256', env("encryption.key")), 0, 16)
            );
            $targetId = ($decryptedSettlementId !== false && $decryptedSettlementId > 0) ? $decryptedSettlementId : $settlementId;
            $builder->where('settlement_id', $targetId);
        }

        $totalTransactions = (clone $builder)->count();

        if (!empty($searchBy) && !empty($searchValue) && in_array($searchBy, $availableSearchKeys)) {
            $builder->where($searchBy, "LIKE", "%" . trim($searchValue, "\"'") . "%");
        }

        $filteredTransactions = (clone $builder)->count();

        $transactions = $builder->orderBy('id', "DESC")->offset($offset)->limit($limit)->get();
        $data = [];
        $index = $offset + 1;

        foreach ($transactions as $transaction) {
            $data[] = [
                $index,
                $transaction->name ?? '',
                $transaction->phone ?? '',
                $transaction->txnid ?? '',
                $transaction->rrn ?? '',
                "₹ " . number_format((float) ($transaction->amount ?? 0), 2),
                "₹ " . number_format((float) ($transaction->charges ?? 0), 2),
                "₹ " . number_format((float) ($transaction->gst ?? 0), 2),
                "₹ " . number_format((float) ($transaction->net_amount ?? 0), 2),
                $this->getStatusSpan($transaction->status ?? 'Pending'),
                $transaction->payee_name ?? '',
                $transaction->payee_vpa ?? '',
                $transaction->created_at ? (string) $transaction->created_at : '',
                $transaction->txn_complete_time ? (string) $transaction->txn_complete_time : '',
                $transaction->remarks ?? ''
            ];
            $index++;
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalTransactions,
            'recordsFiltered' => $filteredTransactions,
            'data' => $data
        ]);
    }
}


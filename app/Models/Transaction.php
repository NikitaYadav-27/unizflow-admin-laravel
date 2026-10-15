<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Transaction extends Model
{
    protected $table = 'transactions';
    protected $primaryKey = 'id';
    protected $fillable = [ 'user_id', 'service', 'txnid', 'merchant_txnid', 'reference_id', 'rrn', 'status', 'name', 'email', 'phone', 'amount', 'charges', 'gst', 'provider_charge', 'provider_gst', 'net_amount', 'payee_name', 'payee_vpa', 'payee_phone', 'intent', 'account_number', 'ifsc_code', 'bank_name', 'order_id', 'refunded', 'source', 'debited', 'credited', 'processed', 'reason', 'remarks', 'chargeback', 'created_at', 'updated_at', 'txn_complete_time', 'payin_provider', 'payout_provider', 'qr_id', 'settled', 'settlement_id' ];
    public $timestamps = true;
    
    

    public function isTxnIdExists($txnid) {
        return $this->where('txnid', $txnid)->count() > 0;
    }
    
    public function isReferenceIdExists($referenceId) {
        return $this->where('reference_id', $referenceId)->count() > 0;
    }
    
    public function isOrderIdExists($orderId) {
        return $this->where('order_id', $orderId)->count() > 0;
    }
    
    public function isRrnExists($rrn) {
        return $this->where('rrn', $rrn)->count() > 0;
    }
    
    public function getTodaysCollection($user_id) {
       $startTime = date("Y-m-d 00:00:00");
       $endTime = date("Y-m-d 23:59:59");
   
       return $this->selectRaw("SUM(amount) AS total")
                   ->where('user_id', $user_id)
                   ->where('status', 'Success')
                   ->where('service', 'payin')
                   ->where('txn_complete_time', '>=', $startTime)
                   ->where('txn_complete_time', '<=', $endTime)
                   ->first()['total'] ?? 0;
   }
    
    public function getTodaysQRTransaction($qrId) {
        $result = $this->selectRaw('SUM(amount) as amount')
           ->where('qr_id', $qrId)
           ->where('created_at', '>=', date('Y-m-d 00:00:00'))
           ->where('created_at', '<=', date('Y-m-d 23:59:59'))
           ->first();

        return $result['amount'] ?? 0;
    }
    
    public function getTodayData($user_id) {
        $startTime = date("Y-m-d 00:00:00");
        $endTime = date("Y-m-d 23:59:59");
    

        $result = $this->selectRaw('
            SUM(CASE WHEN service = "payin" THEN amount ELSE 0 END) AS collection,
            SUM(CASE WHEN service = "payout" THEN amount ELSE 0 END) AS payout,
            SUM(charges + gst) AS total_charges
        ')
        ->where('user_id', $user_id)
        ->where('status', 'Success')
        ->where('txn_complete_time', '>=', $startTime)
        ->where('txn_complete_time', '<=', $endTime)
        ->first();
    
        return [
            'collection' => (float) ($result['collection'] ?? 0),
            'payout'     => (float) ($result['payout'] ?? 0),
            'charges'    => (float) ($result['total_charges'] ?? 0),
        ];
    }
    
    public function getStatusData($user_id) {
        $result = $this->selectRaw('
            COUNT(*) AS total_transactions,
            SUM(CASE WHEN status = "Success" THEN 1 ELSE 0 END) AS success_transactions,
            SUM(CASE WHEN status = "Pending" THEN 1 ELSE 0 END) AS pending_transactions,
            SUM(CASE WHEN status = "Failed" THEN 1 ELSE 0 END) AS failed_transactions,
            SUM(CASE WHEN status = "Processing" THEN 1 ELSE 0 END) AS processing_transactions,
            SUM(CASE WHEN status = "Expired" THEN 1 ELSE 0 END) AS expired_transactions
        ')
        ->where('user_id', $user_id)
        ->first();
    
        return [
            'total_transactions'    => (int) ($result['total_transactions'] ?? 0),
            'success_transactions'  => (int) ($result['success_transactions'] ?? 0),
            'pending_transactions'  => (int) ($result['pending_transactions'] ?? 0),
            'failed_transactions'   => (int) ($result['failed_transactions'] ?? 0),
            'processing_transactions' => (int) ($result['processing_transactions'] ?? 0),
            'expired_transactions'  => (int) ($result['expired_transactions'] ?? 0),
        ];
    }
    
    public function getRecentTransactions($user_id, $limit) {
        return $this->select(['service', 'txnid', 'status', 'amount', 'created_at'])
                    ->where('user_id', $user_id)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->get();
    }
    
    public function getWeeklySummary($user_id) {
        // Get raw data from DB
        $data = $this->selectRaw('
                DATE(created_at) as date,
                SUM(CASE WHEN service = "payin" THEN amount ELSE 0 END) AS payin,
                SUM(CASE WHEN service = "payout" THEN amount ELSE 0 END) AS payout
            ')
            ->where('user_id', $user_id)
            ->where('status', 'Success')
            ->where('created_at', '>=', date('Y-m-d 00:00:00', strtotime('-6 days')))
            ->groupByRaw('DATE(created_at)')
            ->get()
            ->toArray();
    
        // Map results by date for fast lookup
        $map = array_column($data, null, 'date');
    
        // Build final 7-day summary
        $summary = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $summary[] = [
                'txn_date'     => $date,
                'total_payin'  => isset($map[$date]) ? (float)$map[$date]['payin'] : 0.0,
                'total_payout' => isset($map[$date]) ? (float)$map[$date]['payout'] : 0.0,
            ];
        }
    
        return $summary;
    }
    
    public function getDashboardSummary($startDate, $endDate)
    {
        $transactions = $this->selectRaw("
            COUNT(*) AS total_transaction_count,
            SUM(amount) AS total_transaction_amount,
            COUNT(CASE WHEN service = 'payin' THEN 1 END) AS total_payin_transactions,
            COUNT(CASE WHEN service = 'payout' THEN 1 END) AS total_payout_transactions,
            SUM(CASE WHEN service = 'payin' THEN amount ELSE 0 END) AS total_payin_amount,
            SUM(CASE WHEN service = 'payout' THEN amount ELSE 0 END) AS total_payout_amount,
            SUM(charges) AS total_charges_amount
        ")
        ->where('txn_complete_time', '>=', $startDate)
        ->where('txn_complete_time', '<=', $endDate)
        ->where('status', 'Success')
        ->get()
        ->first();
        
        $settlements = DB::table("settlements")
         ->selectRaw("count(*) as total_transactions, sum(net_amount) as total_amount")
         ->where("status", "Success")
         ->where("updated_at", ">=", $startDate)
         ->where("updated_at", "<=", $endDate)
         ->first();
         
        $topups = DB::table("fund_requests")
         ->selectRaw("count(*) as total_transactions, sum(amount) as total_amount")
         ->where("status", "Success")
         ->where("updated_at", ">=", $startDate)
         ->where("updated_at", "<=", $endDate)
         ->first();
         
        $settlements = (array) $settlements;
        $topups = (array) $topups;
         
      
        
        $result = [
           "transactions" =>[
              "count" => round($transactions['total_transaction_count']),
              "amount" => round($transactions['total_transaction_amount'],2),
           ],
           "payin" => [
              "count" => round($transactions['total_payin_transactions']),
              "amount" => round($transactions['total_payin_amount'],2)
           ],
           "payout" => [
              "count" => round($transactions['total_payout_transactions']),
              "amount" => round($transactions['total_payout_amount'],2)
           ],
           "charges" => [
              "count" => round($transactions['total_transaction_count']),
              "amount" => round($transactions['total_charges_amount'],2)
           ],
           "settlement" => [
              "count" => round($settlements['total_transactions']),
              "amount" => round($settlements['total_amount'],2)
           ],
           "topup" => [
              "count" => round($topups['total_transactions']),
              "amount" => round($topups['total_amount'],2)
           ]
        ];
        
        return $result;
    }
    
   public function getTopUsers($startDate, $endDate, $orderBy)
   {
       $db = \Illuminate\Support\Facades\DB::connection();
       // Base condition
       $baseWhere = [
           'status' => 'Success'
       ];

       // For PAYIN query
       $payinBuilder = $db->table('transactions as t');
       $payinBuilder->selectRaw("t.user_id, SUM(t.amount) as transaction_total_payin_amount");
       $payinBuilder->where($baseWhere);
       $payinBuilder->where('t.service', 'payin');
       $payinBuilder->whereBetween("t.txn_complete_time", [$startDate, $endDate]);
       $payinBuilder->groupBy('t.user_id');

       // For PAYOUT query
       $payoutBuilder = $db->table('transactions as t');
       $payoutBuilder->selectRaw("t.user_id, SUM(t.amount) as transaction_total_payout_amount");
       $payoutBuilder->where($baseWhere);
       $payoutBuilder->where('t.service', 'payout');
       $payoutBuilder->whereBetween("t.txn_complete_time", [$startDate, $endDate]);
       $payoutBuilder->groupBy('t.user_id');
   
       // Execute separately
       $payinResults  = $payinBuilder->get()->map(function ($item) { return (array) $item; })->toArray();
       $payoutResults = $payoutBuilder->get()->map(function ($item) { return (array) $item; })->toArray();
   
       // Merge results by user_id
       $merged = [];
       foreach ($payinResults as $row) {
           $merged[$row['user_id']]['transaction_total_payin_amount'] = $row['transaction_total_payin_amount'];
           $merged[$row['user_id']]['transaction_total_payout_amount'] = 0;
       }
       foreach ($payoutResults as $row) {
           if (!isset($merged[$row['user_id']])) {
               $merged[$row['user_id']]['transaction_total_payin_amount'] = 0;
           }
           $merged[$row['user_id']]['transaction_total_payout_amount'] = $row['transaction_total_payout_amount'];
       }
   
       // Add total and user info
       foreach ($merged as $userId => &$data) {
           $user = $db->table('users')->selectRaw('name, merchant_id')->where('id', $userId)->get()->map(function ($item) { return (array) $item; })->first();
           $data['user_name'] = $user['name'] ?? '';
           $data['user_merchant_id'] = $user['merchant_id'] ?? '';
           $data['transaction_total_amount'] = 
               ($data['transaction_total_payin_amount'] ?? 0) + 
               ($data['transaction_total_payout_amount'] ?? 0);
       }
   
       // Sorting logic in PHP instead of SQL
       switch (strtolower($orderBy)) {
           case 'payin':
               usort($merged, fn($a, $b) => $b['transaction_total_payin_amount'] <=> $a['transaction_total_payin_amount']);
               break;
           case 'payout':
               usort($merged, fn($a, $b) => $b['transaction_total_payout_amount'] <=> $a['transaction_total_payout_amount']);
               break;
           default:
               usort($merged, fn($a, $b) => $b['transaction_total_amount'] <=> $a['transaction_total_amount']);
               break;
       }
   
       // Limit top 5
       return array_slice($merged, 0, 5);
   }

    
   public function getProviders($startDate, $endDate)
   {
       $db = \Illuminate\Support\Facades\DB::connection();

       // PAYIN PROVIDERS
       $payinBuilder = $db->table('transactions as t');
       $payinBuilder->selectRaw("
           p.logo as logo, p.name as name, p.type as type,
           COUNT(t.id) as total_transactions, 
           SUM(t.amount) as total_amount
       ");
       $payinBuilder->join('providers as p', 'p.id', '=', 't.payin_provider');
       $payinBuilder->where('t.service', 'payin');
       $payinBuilder->where('t.status', 'Success');
       $payinBuilder->where('t.txn_complete_time', '>=', $startDate);
       $payinBuilder->where('t.txn_complete_time', '<=', $endDate);
       $payinBuilder->groupBy('t.payin_provider');
       $payinResults = $payinBuilder->get()->map(function($i){return (array)$i;})->toArray();

       // PAYOUT PROVIDERS
       $payoutBuilder = $db->table('transactions as t');
       $payoutBuilder->selectRaw("
           p.logo as logo, p.name as name, p.type as type,
           COUNT(t.id) as total_transactions, 
           SUM(t.amount) as total_amount
       ");
       $payoutBuilder->join('providers as p', 'p.id', '=', 't.payout_provider');
       $payoutBuilder->where('t.service', 'payout');
       $payoutBuilder->where('t.status', 'Success');
       $payoutBuilder->where('t.txn_complete_time', '>=', $startDate);
       $payoutBuilder->where('t.txn_complete_time', '<=', $endDate);
       $payoutBuilder->groupBy('t.payout_provider');
       $payoutResults = $payoutBuilder->get()->map(function($i){return (array)$i;})->toArray();

       // Merge both
       return array_merge($payinResults, $payoutResults);
    }
    
    
    public function getStatus($startDate, $endDate)
    {
        return $this->selectRaw('status, COUNT(*) as transaction_count')
            ->where('updated_at', '>=', $startDate)
            ->where('updated_at', '<=', $endDate)
            ->groupBy('status')
            ->get()
            ->map(function($i){return (array)$i;})->toArray();
    }
    
    public function getReport($startDate,$endDate,$reportType,$reportUser,$reportBy){
        if($reportBy == "monthly") {
            return $this->monthlyReport($startDate,$endDate,$reportUser,$reportType);
        }elseif($reportBy == "weekly") {
            return $this->weeklyReport($startDate,$endDate,$reportUser,$reportType);
        }
    }
    
    private function monthlyReport($startDate,$endDate,$reportUser = 'all', $reportType = 'all')
    {
        $labels = [];
        $data = [];
        // Start from 12 months ago
        $start = new \DateTime('first day of -11 months');
        $end = new \DateTime('last day of this month');
        
        $startDate = $start->format('Y-m-01') . " 00:00:00";
        $endDate = $end->format('Y-m-t') . " 23:59:59";
        
        $days = (new \DateTime($startDate))->diff(new \DateTime($endDate))->days + 1;

        $row = $this->selectRaw("SUM(amount) as total_amount, COUNT(*) as total_count")
            ->where('status', 'Success')
            ->where('txn_complete_time', '>=', $startDate)
            ->where('txn_complete_time', '<=', $endDate)
            ->first();
        
        $totalAmount = (float) ($row['total_amount'] ?? 0);
        $totalCount = (int) ($row['total_count'] ?? 0);

        $dailyAvgPayment = $days > 0 ? round($totalAmount / $days, 2) : 0;
        $dailyAvgCount = $days > 0 ? round($totalCount / $days, 2) : 0;
        $avgTxnValue = $totalCount > 0 ? round($totalAmount / $totalCount, 2) : 0;
        
    
        while ($start <= $end) {
            $monthLabel = $start->format('M Y');
            $monthStart = $start->format('Y-m-01');
            $monthEnd = $start->format('Y-m-t');
            
            $monthStart .= " 00:00:00";
            $monthEnd .= " 23:59:59";

    
            $builder = $this->selectRaw("SUM(amount) as total_amount")
                            ->where('status', 'Success')
                            ->where('txn_complete_time', '>=', $monthStart)
                            ->where('txn_complete_time', '<=', $monthEnd);
    
            if ($reportUser !== 'all') {
                $builder->where('user_id', $reportUser);
            }
    
            if ($reportType !== 'all') {
                $builder->where('service', $reportType);
            }
    
            $row = $builder->first();
           
            $total = round(($row['total_amount'] ?? 0) / 100000,2);
    
            $labels[] = $monthLabel;
            $data[] = (float)$total;
    
            $start->modify('+1 month');
        }
    
        return [
            'labels' => $labels,
            'data' => $data,
            'dailyAvgPayment' => $dailyAvgPayment,
            'dailyAvgCount' => $dailyAvgCount,
            'avgTxnValue' => $avgTxnValue
        ];
    }

    
    private function weeklyReport($startDate, $endDate, $reportUser = 'all', $reportType = 'all')
    {
        $labels = [];
        $data = [];
    
        $start = new \DateTime('-6 days'); // 7 days including today
        $end = new \DateTime(); // Today
    
        $startDate = $start->format('Y-m-d') . " 00:00:00";
        $endDate = $end->format('Y-m-d') . " 23:59:59";
    
        $days = 7;
    
        $row = $this->selectRaw("SUM(amount) as total_amount, COUNT(*) as total_count")
            ->where('status', 'Success')
            ->where('txn_complete_time', '>=', $startDate)
            ->where('txn_complete_time', '<=', $endDate);
    
        if ($reportUser !== 'all') {
            $row->where('user_id', $reportUser);
        }
    
        if ($reportType !== 'all') {
            $row->where('service', $reportType);
        }
    
        $row = $row->first();
    
        $totalAmount = (float) ($row['total_amount'] ?? 0);
        $totalCount = (int) ($row['total_count'] ?? 0);
    
        $dailyAvgPayment = $days > 0 ? round($totalAmount / $days, 2) : 0;
        $dailyAvgCount = $days > 0 ? round($totalCount / $days, 2) : 0;
        $avgTxnValue = $totalCount > 0 ? round($totalAmount / $totalCount, 2) : 0;
    
        // Loop through each day
        $loopDate = clone $start;
        while ($loopDate <= $end) {
            $label = $loopDate->format('D'); // e.g., Mon, Tue
            $dayStart = $loopDate->format('Y-m-d') . " 00:00:00";
            $dayEnd = $loopDate->format('Y-m-d') . " 23:59:59";
    
            $builder = $this->selectRaw("SUM(amount) as total_amount")
                ->where('status', 'Success')
                ->where('txn_complete_time', '>=', $dayStart)
                ->where('txn_complete_time', '<=', $dayEnd);
    
            if ($reportUser !== 'all') {
                $builder->where('user_id', $reportUser);
            }
    
            if ($reportType !== 'all') {
                $builder->where('service', $reportType);
            }
    
            $row = $builder->first();
            $total = round(($row['total_amount'] ?? 0) / 100000, 2); // in lakhs
    
            $labels[] = $label;
            $data[] = $total;
    
            $loopDate->modify('+1 day');
        }
    
        return [
            'labels' => $labels,
            'data' => $data,
            'dailyAvgPayment' => $dailyAvgPayment,
            'dailyAvgCount' => $dailyAvgCount,
            'avgTxnValue' => $avgTxnValue
        ];
    }


}
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Settlement extends Model {
    protected $table = 'settlements';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    
    protected $fillable = [
       'user_id', 'batch_id', 'amount', 'charges', 'gst', 'net_amount', 'settle_to', 'txnid','reference','bank_id','remarks','credited', 'status', 'created_at', 'updated_at'
    ];
    
    public function getRecent($user_id, $limit) {
        
        return $this->select(['txnid', 'status','net_amount','settle_to','created_at'])
                    ->where('user_id', $user_id)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->get();
    }

    

}

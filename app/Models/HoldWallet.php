<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class HoldWallet extends Model
{
    protected $table = 'hold_wallet_transactions';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    
    protected $fillable = [
      'type', 'reason', 'txnid', 'opening', 'amount', 'closing', 'user_id', 'processed', 'created_at', 'updated_at'
    ];
    


}

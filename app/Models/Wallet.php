<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Wallet extends Model
{
    protected $table = 'wallet_transactions';
    protected $primaryKey = 'id';
    protected $fillable = [ 
        'user_id', 'type', 'txn_type', 'txnid', 'opening', 'amount', 'charges', 'gst', 'net_amount', 'remarks','processed','closing', 'created_at', 'updated_at'
        ];
    public $timestamps = true;
    
    

   

}
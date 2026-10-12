<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FundRequest extends Model
{
    protected $table = 'fund_requests';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    
    protected $fillable = [
      'user_id', 'bank', 'mode', 'rrn', 'amount', 'remarks', 'receipt', 'txnid', 'status', 'reason','credited', 'created_at', 'updated_at'
    ];

    
}

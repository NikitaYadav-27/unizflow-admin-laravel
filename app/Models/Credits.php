<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Credits extends Model
{
    protected $table = 'credits';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    
    protected $fillable = [
      'user_id', 'txnid', 'amount', 'credited', 'debited', 'credit_remarks', 'debit_remarks', 'debit_granted', 'credited_at', 'debited_at', 'created_at', 'updated_at'
    ];
    


    

}

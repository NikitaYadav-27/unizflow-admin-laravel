<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Transactionres extends Model
{
    protected $table = 'transaction_res';
    protected $primaryKey = 'id';
    protected $fillable = [ 'txnid', 'type', 'data', 'webhook_ip', 'created_at', 'updated_at'];
    public $timestamps = true;
    
    

}
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FundRequestBank extends Model
{
    protected $table = 'fund_request_banks';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    
    protected $fillable = [
        'users', 'active', 'name', 'account_holder_name', 'account_number', 'ifsc_code', 'bank', 'branch', 'created_at', 'updated_at'
    ];

    
}

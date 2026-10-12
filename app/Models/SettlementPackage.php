<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SettlementPackage extends Model
{
    protected $table = 'settlement_packages';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    
    protected $fillable = [
        'name', 'created_at', 'updated_at'
    ];
    


}

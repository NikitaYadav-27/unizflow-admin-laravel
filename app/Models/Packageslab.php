<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Packageslab extends Model
{
    protected $table = 'package_slabs';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    
    protected $fillable = [
       'package_id', 'min_amount', 'max_amount', 'amount', 'service', 'percent', 'gst', 'created_at', 'updated_at'
    ];
    


}

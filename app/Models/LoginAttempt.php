<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LoginAttempt extends Model
{
    protected $table = 'admin_login_attempts';
    protected $primaryKey = 'id';
    protected $fillable = [ 
         'ip', 'browser', 'device', 'lat', 
         'log', 'session_id', 'verified', 'admin_id', 'created_at', 'updated_at'
    ];
    
    public $timestamps = true;
    
}
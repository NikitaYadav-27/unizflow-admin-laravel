<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Login extends Model
{
    protected $table = 'admin_login_logs';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    
    protected $fillable = [
        'admin_id', 'ip', 'browser', 'device', 'os', 'lat', 'lon', 
        'email_verified', 'phone_verified', 'verified', 'session_id', 
        'created_at', 'updated_at'
    ];
    
    public function getHistory($user_id, $limit) {
        return $this->select(['browser', 'ip', 'created_at'])
                    ->where('user_id', $user_id)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->get();
    }

    

}

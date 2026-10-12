<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LoginBrowser extends Model {
    protected $table         = 'admin_login_browsers';
    protected $primaryKey    = 'id';
    protected $fillable = [
        'admin_id',
        'browser_token',
        'phone_verified',
        'email_verified',
        'created_at',
        'updated_at',
    ];
    
    public $timestamps = true;
    
    

    public function findBrowserToken(string $browserToken): ?self
    {
        return $this->where('browser_token', $browserToken)
                    ->where('created_at', '>=', date('Y-m-d 00:00:00'))
                    ->where('created_at', '<=', date('Y-m-d 23:59:59'))
                    ->first();
    }
}

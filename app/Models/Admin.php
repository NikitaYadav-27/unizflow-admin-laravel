<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Admin extends Model
{
    protected $table = 'admins';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    
    protected $fillable = [
        'name', 'email', 'phone', 'password', 'active','pin', 'current_session_id'
    ];

    public function checkPhoneExists($phone)
    {
        return $this->where('phone', $phone)->count() > 0;
    }
    
    public function checkEmailExists($email)
    {
        return $this->where('email', $email)->count() > 0;
    }
    
}

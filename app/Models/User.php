<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    
    protected $fillable = [
      'merchant_id', 'name', 'email', 'phone', 'password', 'active', 'wallet', 'hold_wallet', 'reset_password_token', 'reset_password_token_time', 'package', 'payin', 'payin_provider', 'payout_provider', 'payout', 'api_access', 'api_access_key', 'settlement_package', 'settlement_type', 'payin_webhook', 'previous_payin_webhook', 'payout_webhook', 'previous_payout_webhook', 'created_at', 'updated_at','ad_token', 'payin_limit','payout_limit', 'payout_per_limit','locked'
    ];

    public function scopeAutomaticSettlement($query)
    {
        return $query->where('settlement_type', 'automatic');
    }

    public function scopeManualSettlement($query)
    {
        return $query->where(function($q) {
            $q->where('settlement_type', 'manual')->orWhereNull('settlement_type');
        });
    }

    public function isAutomaticSettlement()
    {
        return ($this->settlement_type === 'automatic');
    }


    public function checkPhoneExists($phone)
    {
        return $this->where('phone', $phone)->count() > 0;
    }
    
    public function checkEmailExists($email)
    {
        return $this->where('email', $email)->count() > 0;
    }
    
    public function checkMerchantIdExists($merchantId)
    {
        return $this->where('merchant_id', $merchantId)->count() > 0;
    }

    public function getUserByResetToken($token)
    {
        return $this->where('reset_password_token', $token)->first();
    }

    public function updateWallet($userId, $amount)
    {
        return $this->update($userId, ['wallet' => $amount]);
    }

    public function hasApiAccess($userId)
    {
        $user = $this->find($userId);
        return $user && $user['api_access'] == 1;
    }
}

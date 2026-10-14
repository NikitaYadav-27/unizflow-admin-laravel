<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Charge extends Model
{
    public function payin(float $amount, int $packageId, int $providerId)
    {
        $db = \Illuminate\Support\Facades\DB::connection();

        $packageSlab = $db->table('package_slabs')
            ->where('service', 'payin')
            ->where('package_id', $packageId)
            ->whereRaw('CAST(min_amount AS DECIMAL(15,2)) <= ?', [$amount])
            ->whereRaw('CAST(max_amount AS DECIMAL(15,2)) >= ?', [$amount])
            ->first();

        if (!$packageSlab) {
            return false;
        }

        $gstRate = \App\Models\Settings::getGSTRate();
        $chargeValue = $packageSlab->percent ? ($packageSlab->amount * $amount) / 100 : $packageSlab->amount;
        $chargeGST = $packageSlab->gst ? ($chargeValue * $gstRate) / 100 : 0;

        $provCharge = 0.0;
        $provGst = 0.0;
        if ($providerId > 0) {
            $provSlab = $db->table('provider_slabs')
                ->where('provider_id', $providerId)
                ->whereRaw('CAST(min_amount AS DECIMAL(15,2)) <= ?', [$amount])
                ->whereRaw('CAST(max_amount AS DECIMAL(15,2)) >= ?', [$amount])
                ->first();

            if ($provSlab) {
                $provCharge = $provSlab->percent ? ($provSlab->amount * $amount) / 100 : (float)$provSlab->amount;
                $provGst = $provSlab->gst ? ($provCharge * $gstRate) / 100 : 0.0;
            }
        }
        
        return [
            'charge' => round($chargeValue, 2),
            'charge_gst' => round($chargeGST, 2),
            'provider_charge' => round($provCharge, 2),
            'provider_gst' => round($provGst, 2),
        ];
    }
    
    public function payout(float $amount, int $packageId, int $providerId)
    {
        $db = \Illuminate\Support\Facades\DB::connection();

        $packageSlab = $db->table('package_slabs')
            ->where('service', 'payout')
            ->where('package_id', $packageId)
            ->whereRaw('CAST(min_amount AS DECIMAL(15,2)) <= ?', [$amount])
            ->whereRaw('CAST(max_amount AS DECIMAL(15,2)) >= ?', [$amount])
            ->first();

        if (!$packageSlab) {
            return false;
        }

        $gstRate = \App\Models\Settings::getGSTRate();
        $chargeValue = $packageSlab->percent ? ($packageSlab->amount * $amount) / 100 : $packageSlab->amount;
        $chargeGST = $packageSlab->gst ? ($chargeValue * $gstRate) / 100 : 0;

        $provCharge = 0.0;
        $provGst = 0.0;
        if ($providerId > 0) {
            $provSlab = $db->table('provider_slabs')
                ->where('provider_id', $providerId)
                ->whereRaw('CAST(min_amount AS DECIMAL(15,2)) <= ?', [$amount])
                ->whereRaw('CAST(max_amount AS DECIMAL(15,2)) >= ?', [$amount])
                ->first();

            if ($provSlab) {
                $provCharge = $provSlab->percent ? ($provSlab->amount * $amount) / 100 : (float)$provSlab->amount;
                $provGst = $provSlab->gst ? ($provCharge * $gstRate) / 100 : 0.0;
            }
        }
       
        return [
            'charge' => round($chargeValue, 2),
            'charge_gst' => round($chargeGST, 2),
            'provider_charge' => round($provCharge, 2),
            'provider_gst' => round($provGst, 2),
        ];
    }
}

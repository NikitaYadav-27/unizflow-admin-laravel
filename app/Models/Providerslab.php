<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Providerslab extends Model
{
    protected $table = 'provider_slabs';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'provider_id', 'min_amount', 'max_amount', 'amount', 'percent', 'gst', 'created_at', 'updated_at'
    ];

    public static function calculateCharge($providerId, $amount, $gstRate = null): array
    {
        $amount = floatval($amount);
        if (empty($providerId) || $amount <= 0) {
            return [
                'charge' => 0.0,
                'gst' => 0.0,
                'total' => 0.0,
                'has_slab' => false,
                'rate_label' => 'N/A'
            ];
        }

        $slab = self::where('provider_id', $providerId)
            ->where('min_amount', '<=', $amount)
            ->where('max_amount', '>=', $amount)
            ->first();

        if (!$slab) {
            return [
                'charge' => 0.0,
                'gst' => 0.0,
                'total' => 0.0,
                'has_slab' => false,
                'rate_label' => 'No Slab'
            ];
        }

        if ($gstRate === null) {
            $gstRate = Settings::getGSTRate();
        }

        $slabAmount = floatval($slab->amount);
        $charge = $slab->percent ? ($slabAmount * $amount) / 100 : $slabAmount;
        $gst = $slab->gst ? ($charge * $gstRate) / 100 : 0.0;

        return [
            'charge' => round($charge, 2),
            'gst' => round($gst, 2),
            'total' => round($charge + $gst, 2),
            'has_slab' => true,
            'rate_label' => $slab->percent ? ($slabAmount . '%') : ('₹ ' . $slabAmount)
        ];
    }
}

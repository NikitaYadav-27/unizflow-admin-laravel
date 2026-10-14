<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Settings extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = ['name', 'value'];

    public static function getGSTRate(): float
    {
        $row = self::where('name', 'gst_rate')->first();
        if ($row && $row->value !== null && $row->value !== '' && is_numeric($row->value)) {
            return floatval($row->value);
        }
        return 18.0;
    }

    public function isPayinEnabled(): bool
    {
        $row = $this->where('name', 'payin_enable')->first();
        return $row && (int)$row['value'] === 1;
    }

    public function isPayoutEnabled(): bool
    {
        $row = $this->where('name', 'payout_enable')->first();
        return $row && (int)$row['value'] === 1;
    }
}

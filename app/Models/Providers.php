<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Providers extends Model
{
    protected $table = 'providers';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    
    protected $fillable = [
        'name','gateway', 'logo', 'type', 'active','daily_limit', 'created_at', 'updated_at'
    ];

    public static function formatLogo(?string $logo): string
    {
        if (empty($logo)) {
            return '/assets/images/company-logos/1.png';
        }
        if (preg_match('/^https?:\/\//i', $logo)) {
            return $logo;
        }
        return '/' . ltrim($logo, '/');
    }

    public function getLogoAttribute($value): string
    {
        return self::formatLogo($value);
    }
}

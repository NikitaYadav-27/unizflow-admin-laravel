<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettlementRuntime extends Model
{
    protected $table = 'settlement_runtimes';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'time', 'created_at', 'updated_at'
    ];
}

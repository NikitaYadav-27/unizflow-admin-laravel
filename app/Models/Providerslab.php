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
}

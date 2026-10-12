<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    protected $table = 'error_logs';
    protected $primaryKey = 'id';
    protected $fillable = [
        'type',
        'txnid',
        'log',
        'request',
        'provider',
        'created_at',
        'updated_at'
    ];
    public $timestamps = true;
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountLedger extends Model
{
    protected $table = 'account_ledgers';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'panel',
        'provider_id',
        'txn_type',
        'type',
        'amount',
        'mode',
        'notes',
        'opening',
        'closing',
        'date',
    ];

    protected $casts = [
        'amount' => 'float',
        'opening' => 'float',
        'closing' => 'float',
        'date' => 'date',
    ];
}


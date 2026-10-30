<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chargeback extends Model
{
    protected $table = 'chargebacks';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $guarded = [];
}


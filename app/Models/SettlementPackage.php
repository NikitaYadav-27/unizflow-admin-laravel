<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SettlementPackage extends Model
{
    protected $table = 'settlement_packages';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    
    protected $fillable = [
        'name', 'created_at', 'updated_at'
    ];
    
    public function slots()
    {
        return $this->hasMany(SettlementPackageSlot::class, 'package_id', 'id')->orderBy('slot_number', 'asc');
    }

    public static function ensureSlotsTableExists()
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('settlement_package_slots')) {
            \Illuminate\Support\Facades\Schema::create('settlement_package_slots', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->increments('id');
                $table->integer('package_id')->unsigned()->index();
                $table->integer('slot_number')->default(1);
                $table->string('settlement_time', 20)->comment('Execution time e.g. 14:00');
                $table->string('txn_from_time', 20)->comment('Transaction window start time e.g. 00:00');
                $table->string('txn_to_time', 20)->comment('Transaction window end time e.g. 12:00');
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
            });
        }
    }

    public function packageNameExists($name, $excludeId = null)
    {
        $query = $this->where('name', $name);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }
}

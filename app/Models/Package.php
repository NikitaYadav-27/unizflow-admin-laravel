<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Package extends Model
{
    protected $table = 'packages';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    
    protected $fillable = [
       'name', 'created_at', 'updated_at'
    ];
    
    public function packageNameExists($name) {
        return $this->where("name", $name)->count() > 0;
    }

}

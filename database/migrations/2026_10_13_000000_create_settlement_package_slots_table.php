<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        if (Schema::hasTable('settlement_packages')) {
            Schema::table('settlement_packages', function (Blueprint $table) {
                if (!Schema::hasColumn('settlement_packages', 'times_per_day')) {
                    $table->integer('times_per_day')->default(1)->after('name');
                }
            });
        } else {
            Schema::create('settlement_packages', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 100);
                $table->integer('times_per_day')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('settlement_package_slots')) {
            Schema::create('settlement_package_slots', function (Blueprint $table) {
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settlement_package_slots');
        if (Schema::hasTable('settlement_packages') && Schema::hasColumn('settlement_packages', 'times_per_day')) {
            Schema::table('settlement_packages', function (Blueprint $table) {
                $table->dropColumn('times_per_day');
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
     
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'success_at')) {
                $table->timestamp('success_at')->nullable()->after('txn_complete_time');
            }
        });

        DB::table('transactions')
            ->where('status', 'Success')
            ->whereNull('success_at')
            ->update([
                'success_at' => DB::raw("COALESCE(txn_complete_time, updated_at, created_at)")
            ]);
    }

    
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'success_at')) {
                $table->dropColumn('success_at');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'provider_charge')) {
                $table->decimal('provider_charge', 10, 2)->default(0.00)->after('gst');
            }
            if (!Schema::hasColumn('transactions', 'provider_gst')) {
                $table->decimal('provider_gst', 10, 2)->default(0.00)->after('provider_charge');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['provider_charge', 'provider_gst']);
        });
    }
};

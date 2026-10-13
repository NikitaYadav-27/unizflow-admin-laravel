<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('settlement:process-auto {--date=} {--time=}', function () {
    $date = $this->option('date');
    $time = $this->option('time');
    $this->info("Processing automatic settlements" . ($time ? " for time {$time}" : "") . "...");
    $res = \App\Libraries\AutoSettlement::process($date, $time);
    $this->info("Eligible Automatic Users: " . $res['eligible_auto_users_count']);
    $this->info("Skipped Manual Users: " . $res['skipped_manual_users_count']);
    $this->info("Settlements Created: " . $res['settlements_created']);
    $this->info("Transactions Settled: " . $res['transactions_settled']);
    $this->info("Total Amount Settled: ₹" . number_format($res['total_amount_settled'], 2));
})->purpose('Process settlements automatically for users with settlement_type = automatic');

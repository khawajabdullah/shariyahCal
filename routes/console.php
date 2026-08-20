<?php

use App\Services\BookingSyncService;
use App\Services\ScholarSyncService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('scholars:sync', function (ScholarSyncService $sync) {
    $result = $sync->sync();

    $this->info("Synced {$result['total']} scholars ({$result['created']} new, {$result['updated']} updated).");
})->purpose('Sync scholars and schedules from Cal.com into the database');

Artisan::command('bookings:sync', function (BookingSyncService $sync) {
    $result = $sync->syncFromCal();

    $this->info("Synced {$result['total']} bookings ({$result['created']} new, {$result['updated']} updated).");
})->purpose('Sync bookings from Cal.com into the database');

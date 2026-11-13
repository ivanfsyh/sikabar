<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Setting;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jam kirim dibaca dari pengaturan admin (aman jika tabel belum dimigrasi)
$jamKirim = rescue(fn() => Setting::get('jam_kirim', '08:00'), '08:00', false);
if (! preg_match('/^\d{2}:\d{2}$/', (string) $jamKirim)) {
    $jamKirim = '08:00';
}

// 1) Cari pengingat yang jatuh tempo, lalu antrikan
Schedule::command('pengingat:proses')
    ->dailyAt($jamKirim)
    ->withoutOverlapping(10);

// 2) Jalankan worker antrian tiap menit (cocok untuk shared hosting tanpa supervisor)
Schedule::command('queue:work --stop-when-empty --max-time=55 --tries=1')
    ->everyMinute()
    ->withoutOverlapping(10);

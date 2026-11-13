<?php

namespace App\Console\Commands;

use App\Services\PengingatService;
use Illuminate\Console\Command;

class ProsesPengingat extends Command
{
    protected $signature = 'pengingat:proses';
    protected $description = 'Jadwalkan pengingat KGB yang jatuh tempo hari ini ke antrian pengiriman';

    public function handle(PengingatService $service): int
    {
        $jumlah = $service->proses();
        $this->info("{$jumlah} pengingat dijadwalkan.");

        return self::SUCCESS;
    }
}

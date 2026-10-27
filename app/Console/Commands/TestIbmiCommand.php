<?php

namespace App\Console\Commands;

use App\Integrations\Ibmi\IbmiConnection;
use Illuminate\Console\Command;

class TestIbmiCommand extends Command
{
    protected $signature = 'test:ibmi';

    protected $description = 'Inspecciona todas las tablas AS400';

    public function handle(IbmiConnection $ibmi): int
    {
        $tables = ['PLAARC', 'PLAAR1', 'TECARC', 'TECAR1', 'CERAR2', 'LINARC', 'CERA01', 'LINAR6', 'SOLAR1', 'LINAR2', 'LINAR1'];

        foreach ($tables as $t) {
            $rows = $ibmi->select("SELECT * FROM DGACDATPRO.{$t} FETCH FIRST 1 ROWS ONLY");
            $count = $ibmi->select("SELECT COUNT(*) AS CNT FROM DGACDATPRO.{$t}");
            $total = $count[0]['CNT'] ?? 0;
            $cols = implode(', ', array_slice(array_keys($rows[0] ?? []), 0, 15));
            $this->info("Tabla: {$t} | Total filas: {$total} | Cols: {$cols}...");
        }

        return self::SUCCESS;
    }
}

<?php

namespace App\Integrations\Ibmi;

use App\DTOs\Legacy\LegacyBudgetCeilingDto;
use App\DTOs\Legacy\LegacyPoaActivityDto;
use Illuminate\Support\Collection;

class IbmiPoaRepository
{
    public function __construct(
        private readonly IbmiConnection $ibmi
    ) {}

    /**
     * Extraer actividades anuales (PLAARC) y mensualización agrupada (PLAAR1) desde IBM i.
     *
     * @return Collection<int, LegacyPoaActivityDto>
     */
    public function getPoaActivities(int $year): Collection
    {
        $rows = $this->ibmi->select('SELECT * FROM DGACDATPRO.PLAARC WHERE PLAANI = ?', [$year]);

        if (empty($rows)) {
            return collect();
        }

        $monthRows = collect($this->ibmi->select('SELECT * FROM DGACDATPRO.PLAAR1 WHERE PLAAN1 = ?', [$year]))
            ->groupBy(fn ($m) => trim((string) ($m['PLANU1'] ?? '')));

        return collect($rows)->map(function ($row) use ($monthRows, $year) {
            $num = trim((string) ($row['PLANUM'] ?? ''));
            $unit = trim((string) ($row['PLACO3'] ?? $row['PLACO2'] ?? 'DPGE'));
            $name = trim(implode(' ', array_filter([
                $row['PLANOM'] ?? '',
                $row['PLANO1'] ?? '',
                $row['PLANO2'] ?? '',
                $row['PLANO3'] ?? '',
                $row['PLANO4'] ?? '',
            ])));
            $objective = trim(implode(' ', array_filter([
                $row['PLAJUS'] ?? '',
                $row['PLAJU1'] ?? '',
                $row['PLAJU2'] ?? '',
            ])));
            $amount = (float) ($row['PLATOT'] ?? $row['PLAVAL'] ?? 0.0);
            $status = trim((string) ($row['PLAEST'] ?? $row['PLAES1'] ?? 'APROBADA'));

            // Agrupar por mes 1..12 y sumar montos para garantizar unicidad por mes
            $months = ($monthRows->get($num) ?? collect())
                ->groupBy(fn ($m) => (int) ($m['PLAMES'] ?? 1))
                ->map(fn ($group, $mNum) => [
                    'month' => (int) $mNum,
                    'target' => 0.0,
                    'amount' => (float) $group->sum(fn ($m) => (float) ($m['PLAVA1'] ?? 0.0)),
                ])->values()->all();

            return LegacyPoaActivityDto::fromLegacy([
                'plaani' => $year,
                'pla03' => $unit,
                'planum' => $num,
                'plaan2' => $name ?: "Actividad POA {$num}",
                'pla10' => $objective ?: "Objetivo institucional {$num}",
                'monto' => $amount,
                'pla12' => $status,
            ], $months);
        });
    }

    /**
     * Extraer techos presupuestarios (TECARC / TECAR1) desde IBM i.
     *
     * @return Collection<int, LegacyBudgetCeilingDto>
     */
    public function getBudgetCeilings(int $year): Collection
    {
        $rows = $this->ibmi->select('SELECT * FROM DGACDATPRO.TECARC WHERE TECANI = ?', [$year]);

        if (empty($rows)) {
            return collect();
        }

        return collect($rows)->map(function ($row) use ($year) {
            return LegacyBudgetCeilingDto::fromLegacy([
                'tecani' => $year,
                'tecval' => (float) ($row['TECVAL'] ?? 0.0),
                'tecfue' => trim((string) ($row['TECPAR'] ?? 'MEF')),
                'tectip' => 'GASTO_CORRIENTE',
                'tecstt' => 'APROBADO',
            ], []);
        });
    }
}

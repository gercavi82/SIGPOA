<?php

namespace App\Integrations\Ibmi;

use App\DTOs\Legacy\LegacyBudgetCertificationDto;
use App\DTOs\Legacy\LegacyPoaCertificationDto;
use Illuminate\Support\Collection;

class IbmiCertificationRepository
{
    public function __construct(
        private readonly IbmiConnection $ibmi
    ) {}

    /**
     * Extraer Certificaciones POA (CERAR2 / LINARC) desde IBM i.
     *
     * @return Collection<int, LegacyPoaCertificationDto>
     */
    public function getPoaCertifications(int $year): Collection
    {
        $rows = $this->ibmi->select('SELECT * FROM DGACDATPRO.CERAR2 WHERE CERANI = ?', [$year]);

        if (empty($rows)) {
            return collect();
        }

        $lineRows = collect($this->ibmi->select('SELECT * FROM DGACDATPRO.LINARC WHERE LINAN1 = ?', [$year]))
            ->groupBy(fn ($l) => trim((string) ($l['LINNU1'] ?? '')));

        return collect($rows)->map(function ($row) use ($lineRows, $year) {
            $num = trim((string) ($row['CERNU2'] ?? ''));
            $lines = ($lineRows->get($num) ?? collect())->map(fn ($l) => [
                'activity_number' => trim((string) ($l['LINACT'] ?? '')),
                'amount' => (float) ($l['LINMON'] ?? 0.0),
                'description' => trim((string) ($l['LINAC1'] ?? '')),
            ])->toArray();

            $totalAmount = (float) collect($lines)->sum('amount');

            return LegacyPoaCertificationDto::fromLegacy([
                'cerani' => $year,
                'cernu2' => $num,
                'cerdir' => 'DPGE',
                'cermot' => "Certificación POA {$num}",
                'total_amount' => $totalAmount,
                'status' => 'APROBADA',
            ], $lines);
        });
    }

    /**
     * Extraer Certificaciones Presupuestarias (CERA01 / LINAR6) desde IBM i.
     *
     * @return Collection<int, LegacyBudgetCertificationDto>
     */
    public function getBudgetCertifications(int $year): Collection
    {
        $rows = $this->ibmi->select('SELECT * FROM DGACDATPRO.CERA01 WHERE CERAN3 = ?', [$year]);

        if (empty($rows)) {
            return collect();
        }

        $lineRows = collect($this->ibmi->select('SELECT * FROM DGACDATPRO.LINAR6 WHERE LINA05 = ?', [$year]))
            ->groupBy(fn ($l) => trim((string) ($l['LINN16'] ?? '')));

        return collect($rows)->map(function ($row) use ($lineRows, $year) {
            $num = trim((string) ($row['CERN01'] ?? ''));
            $lines = ($lineRows->get($num) ?? collect())->map(fn ($l, $idx) => [
                'line_number' => $idx + 1,
                'activity_number' => null,
                'amount' => (float) ($l['LINM05'] ?? 0.0),
                'concept' => trim((string) ($l['LINE02'] ?? "Línea {$num}")),
            ])->toArray();

            $totalAmount = (float) ($row['CERTO4'] ?? collect($lines)->sum('amount'));

            return LegacyBudgetCertificationDto::fromLegacy([
                'cerani' => $year,
                'cernu2' => $num,
                'cerfe1' => now()->toDateString(),
                'total_amount' => $totalAmount,
                'concept' => trim((string) ($row['CERDE4'] ?? "Certificación Presupuestaria {$num}")),
                'status' => 'ACTIVA',
            ], $lines);
        });
    }
}

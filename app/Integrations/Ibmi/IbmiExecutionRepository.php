<?php

namespace App\Integrations\Ibmi;

use App\DTOs\Legacy\LegacyExecutionDto;
use Illuminate\Support\Collection;

class IbmiExecutionRepository
{
    public function __construct(
        private readonly IbmiConnection $ibmi
    ) {}

    /**
     * Extraer Devengados y CURs (LINAR1) desde IBM i.
     *
     * @return Collection<int, LegacyExecutionDto>
     */
    public function getExecutionAccruals(int $year, ?int $month = null): Collection
    {
        $sql = 'SELECT * FROM DGACDATPRO.LINAR1 WHERE LINAN3 = ?';
        $params = [$year];

        if ($month) {
            $sql .= ' AND LINMES = ?';
            $params[] = $month;
        }

        $rows = $this->ibmi->select($sql, $params);

        if (empty($rows)) {
            return collect();
        }

        return collect($rows)->map(function ($row) use ($year) {
            $curNumber = trim((string) ($row['LINNU3'] ?? '0'));
            $curLine = (int) ($row['LINNU4'] ?? 1);
            $amount = (float) ($row['LINMO1'] ?? $row['LINMO2'] ?? 0.0);
            $date = trim((string) ($row['LINFE3'] ?? now()->toDateString()));

            return LegacyExecutionDto::fromLegacy([
                'linani' => $year,
                'linmes' => 1,
                'linco1' => $curNumber,
                'linco3' => $curLine,
                'linact' => 'ACT-000',
                'date' => $date,
                'amount' => $amount,
                'status' => 'ACTIVO',
            ]);
        });
    }
}

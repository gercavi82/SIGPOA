<?php

namespace App\Integrations\Ibmi;

use App\DTOs\Legacy\LegacyModificationDto;
use Illuminate\Support\Collection;

class IbmiModificationRepository
{
    public function __construct(
        private readonly IbmiConnection $ibmi
    ) {}

    /**
     * Extraer Solicitudes y Modificaciones POA (SOLAR1 / LINAR2) desde IBM i.
     *
     * @return Collection<int, LegacyModificationDto>
     */
    public function getModifications(int $year): Collection
    {
        $rows = $this->ibmi->select('SELECT * FROM DGACDATPRO.SOLAR1 WHERE SOLAN1 = ?', [$year]);

        if (empty($rows)) {
            return collect();
        }

        return collect($rows)->map(function ($row) use ($year) {
            $num = trim((string) ($row['SOLNU3'] ?? ''));

            return LegacyModificationDto::fromLegacy([
                'solani' => $year,
                'solnu3' => $num,
                'soltip' => 'SIN_AFECTACION',
                'soldir' => 'DPGE',
                'solest' => 'APROBADA',
            ], []);
        });
    }
}

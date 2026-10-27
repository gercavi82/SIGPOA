<?php

namespace App\DTOs\Legacy;

readonly class LegacyModificationDto
{
    /**
     * @param  array<int, array{source_activity: ?string, destination_activity: ?string, debit_amount: float, credit_amount: float, description: ?string}>  $lines
     */
    public function __construct(
        public int $year,
        public string $requestNumber,
        public string $type,
        public string $unitCode,
        public string $status,
        public ?string $esigefReformNumber = null,
        public ?string $reformCur = null,
        public array $lines = [],
    ) {}

    /**
     * @param  array<string, mixed>  $row
     * @param  array<int, array{source_activity: ?string, destination_activity: ?string, debit_amount: float, credit_amount: float, description: ?string}>  $lines
     */
    public static function fromLegacy(array $row, array $lines = []): self
    {
        $rawStatus = strtoupper(trim((string) ($row['solest'] ?? $row['status'] ?? 'APROBADA')));
        $status = match ($rawStatus) {
            'IN', 'BO', 'BORRADOR' => 'BORRADOR',
            'EA', 'ENVIADA', 'ENVIADA_AREA' => 'ENVIADA_AREA',
            'VF', 'VERIFICACION_FINANCIERA' => 'VERIFICACION_FINANCIERA',
            'RD', 'REVISION_DPGE', 'EN_REVISION_DPGE' => 'REVISION_DPGE',
            'DE', 'DEVUELTA' => 'DEVUELTA',
            'NE', 'NEGADA' => 'NEGADA',
            'VI', 'VIABLE' => 'VIABLE',
            'AU', 'AUTORIZADA' => 'AUTORIZADA',
            'RP', 'REFORMA_PENDIENTE' => 'REFORMA_PENDIENTE',
            default => 'APROBADA',
        };

        $rawType = strtoupper(trim((string) ($row['soltip'] ?? $row['type'] ?? 'SIN_AFECTACION')));
        $type = match ($rawType) {
            '1', 'CON_AFECTACION', 'CA' => 'CON_AFECTACION',
            '2', 'ASIGNACION_RECURSOS', 'AR' => 'ASIGNACION_RECURSOS',
            default => 'SIN_AFECTACION',
        };

        return new self(
            year: (int) ($row['solani'] ?? $row['year'] ?? date('Y')),
            requestNumber: trim((string) ($row['solnu3'] ?? $row['number'] ?? 'MOD-000')),
            type: $type,
            unitCode: trim((string) ($row['soldir'] ?? $row['unit_code'] ?? 'GENERAL')),
            status: $status,
            esigefReformNumber: isset($row['esigef_reform']) ? trim((string) $row['esigef_reform']) : null,
            reformCur: isset($row['reform_cur']) ? trim((string) $row['reform_cur']) : null,
            lines: $lines,
        );
    }
}

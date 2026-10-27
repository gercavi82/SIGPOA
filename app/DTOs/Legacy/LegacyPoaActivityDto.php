<?php

namespace App\DTOs\Legacy;

readonly class LegacyPoaActivityDto
{
    /**
     * @param  array<int, array{month: int, target: float, amount: float}>  $months
     */
    public function __construct(
        public int $year,
        public string $unitCode,
        public string $activityNumber,
        public string $description,
        public string $objective,
        public ?int $priority,
        public float $annualTarget,
        public float $annualAmount,
        public string $status,
        public array $months = [],
    ) {}

    /**
     * @param  array<string, mixed>  $row
     * @param  array<int, array{month: int, target: float, amount: float}>  $months
     */
    public static function fromLegacy(array $row, array $months = []): self
    {
        $rawStatus = strtoupper(trim((string) ($row['pla12'] ?? $row['status'] ?? 'APROBADA')));
        $status = match ($rawStatus) {
            'IN', 'BORRADOR', 'DRAFT', 'B' => 'BORRADOR',
            'EA', 'ENVIADA', 'ENVIADA_AREA', 'E' => 'ENVIADA_AREA',
            'AA', 'APROBADA_AREA' => 'APROBADA_AREA',
            'DA', 'DEVUELTA_AREA' => 'DEVUELTA_AREA',
            'DP', 'DEVUELTA_DPGE' => 'DEVUELTA_DPGE',
            default => 'APROBADA',
        };

        return new self(
            year: (int) ($row['plaani'] ?? $row['year'] ?? date('Y')),
            unitCode: trim((string) ($row['pla03'] ?? $row['unit_code'] ?? 'GENERAL')),
            activityNumber: trim((string) ($row['planum'] ?? $row['number'] ?? 'ACT-000')),
            description: trim((string) ($row['plaan2'] ?? $row['description'] ?? '')),
            objective: trim((string) ($row['pla10'] ?? $row['objective'] ?? '')),
            priority: isset($row['pla11']) ? (int) $row['pla11'] : null,
            annualTarget: (float) ($row['target'] ?? 0.0),
            annualAmount: (float) ($row['monto'] ?? $row['amount'] ?? 0.0),
            status: $status,
            months: $months,
        );
    }
}

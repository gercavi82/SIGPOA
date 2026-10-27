<?php

namespace App\DTOs\Legacy;

readonly class LegacyBudgetCeilingDto
{
    /**
     * @param  array<int, array{unit_code: string, amount: float}>  $unitAllocations
     */
    public function __construct(
        public int $year,
        public float $totalAmount,
        public string $source,
        public string $expenditureType,
        public string $status,
        public array $unitAllocations = [],
    ) {}

    /**
     * @param  array<string, mixed>  $row
     * @param  array<int, array{unit_code: string, amount: float}>  $unitAllocations
     */
    public static function fromLegacy(array $row, array $unitAllocations = []): self
    {
        return new self(
            year: (int) ($row['tecani'] ?? $row['year'] ?? date('Y')),
            totalAmount: (float) ($row['tecval'] ?? $row['total_amount'] ?? 0.0),
            source: trim((string) ($row['tecfue'] ?? $row['source'] ?? 'MEF')),
            expenditureType: trim((string) ($row['tectip'] ?? $row['type'] ?? 'GASTO_CORRIENTE')),
            status: trim((string) ($row['tecstt'] ?? $row['status'] ?? 'APROBADO')),
            unitAllocations: $unitAllocations,
        );
    }
}

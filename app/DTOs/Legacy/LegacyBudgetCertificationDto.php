<?php

namespace App\DTOs\Legacy;

readonly class LegacyBudgetCertificationDto
{
    /**
     * @param  array<int, array{line_number: int, activity_number: ?string, amount: float, concept: ?string}>  $lines
     */
    public function __construct(
        public int $year,
        public string $certificationNumber,
        public string $certificationDate,
        public float $totalAmount,
        public string $concept,
        public string $status,
        public array $lines = [],
    ) {}

    /**
     * @param  array<string, mixed>  $row
     * @param  array<int, array{line_number: int, activity_number: ?string, amount: float, concept: ?string}>  $lines
     */
    public static function fromLegacy(array $row, array $lines = []): self
    {
        $rawStatus = strtoupper(trim((string) ($row['cerest'] ?? $row['status'] ?? 'ACTIVA')));
        $status = match ($rawStatus) {
            'AN', 'ANULADA' => 'ANULADA',
            'LI', 'LIQUIDADA' => 'LIQUIDADA',
            default => 'ACTIVA',
        };

        return new self(
            year: (int) ($row['cerani'] ?? $row['year'] ?? date('Y')),
            certificationNumber: trim((string) ($row['cernu2'] ?? $row['number'] ?? 'CP-000')),
            certificationDate: trim((string) ($row['cerfe1'] ?? $row['date'] ?? now()->toDateString())),
            totalAmount: (float) ($row['cermon'] ?? $row['total_amount'] ?? 0.0),
            concept: trim((string) ($row['cercon'] ?? $row['concept'] ?? 'Certificación Presupuestaria Migrada')),
            status: $status,
            lines: $lines,
        );
    }
}

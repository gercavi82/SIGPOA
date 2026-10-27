<?php

namespace App\DTOs\Legacy;

readonly class LegacyExecutionDto
{
    public function __construct(
        public int $year,
        public int $month,
        public string $curNumber,
        public int $curLine,
        public string $activityNumber,
        public ?string $certificationNumber,
        public string $accrualDate,
        public float $accruedAmount,
        public string $status,
    ) {}

    /**
     * @param  array<string, mixed>  $row
     */
    public static function fromLegacy(array $row): self
    {
        return new self(
            year: (int) ($row['linan3'] ?? $row['year'] ?? date('Y')),
            month: (int) ($row['linmes'] ?? $row['month'] ?? date('n')),
            curNumber: trim((string) ($row['linnu3'] ?? $row['cur_number'] ?? 'CUR-000')),
            curLine: (int) ($row['linord'] ?? $row['linse1'] ?? $row['cur_line'] ?? 1),
            activityNumber: trim((string) ($row['linn13'] ?? $row['activity_number'] ?? 'ACT-000')),
            certificationNumber: isset($row['linnu5']) && trim((string) $row['linnu5']) !== '' ? trim((string) $row['linnu5']) : null,
            accrualDate: trim((string) ($row['linfe3'] ?? $row['date'] ?? now()->toDateString())),
            accruedAmount: (float) ($row['linmo1'] ?? $row['amount'] ?? 0.0),
            status: trim((string) ($row['linest'] ?? $row['status'] ?? 'ACTIVO')),
        );
    }
}

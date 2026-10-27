<?php

namespace App\DTOs\Legacy;

readonly class LegacyPoaCertificationDto
{
    /**
     * @param  array<int, array{activity_number: string, amount: float, description: ?string}>  $lines
     */
    public function __construct(
        public int $year,
        public string $certificationNumber,
        public string $unitCode,
        public string $subject,
        public float $totalAmount,
        public string $status,
        public array $lines = [],
    ) {}

    /**
     * @param  array<string, mixed>  $row
     * @param  array<int, array{activity_number: string, amount: float, description: ?string}>  $lines
     */
    public static function fromLegacy(array $row, array $lines = []): self
    {
        $rawStatus = strtoupper(trim((string) ($row['cerest'] ?? $row['status'] ?? 'APROBADA')));
        $status = match ($rawStatus) {
            'IN', 'BO', 'BORRADOR', 'DRAFT' => 'BORRADOR',
            'EA', 'ENVIADA', 'ENVIADA_AREA' => 'ENVIADA_AREA',
            'AA', 'APROBADA_AREA' => 'APROBADA_AREA',
            'RD', 'REVISION_DPGE', 'EN_REVISION_DPGE' => 'EN_REVISION_DPGE',
            'DA', 'DEVUELTA_AREA' => 'DEVUELTA_AREA',
            'DP', 'DEVUELTA_DPGE' => 'DEVUELTA_DPGE',
            'NE', 'NEGADA', 'RECHAZADA' => 'NEGADA',
            'PR', 'PREAPROBADA' => 'PREAPROBADA',
            'FI', 'FINALIZADA' => 'FINALIZADA',
            default => 'APROBADA',
        };

        return new self(
            year: (int) ($row['cerani'] ?? $row['year'] ?? date('Y')),
            certificationNumber: trim((string) ($row['cernu2'] ?? $row['number'] ?? 'CER-000')),
            unitCode: trim((string) ($row['cerdir'] ?? $row['unit_code'] ?? 'GENERAL')),
            subject: trim((string) ($row['cermot'] ?? $row['subject'] ?? 'Certificación POA Migrada')),
            totalAmount: (float) ($row['cermon'] ?? $row['total_amount'] ?? 0.0),
            status: $status,
            lines: $lines,
        );
    }
}

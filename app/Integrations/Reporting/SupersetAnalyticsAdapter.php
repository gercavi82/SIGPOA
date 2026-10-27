<?php

namespace App\Integrations\Reporting;

use App\Repositories\AnalyticsRepository;

class SupersetAnalyticsAdapter
{
    public function __construct(
        private readonly AnalyticsRepository $analyticsRepository
    ) {}

    /**
     * Devuelve el payload estructurado conforme a la vista analítica V_PRO_SIGPOA_EJE.
     *
     * @return array{
     *     meta: array<string, mixed>,
     *     schema: array<int, string>,
     *     data: array<int, array<string, mixed>>
     * }
     */
    public function getExecutionDataset(?int $year = null): array
    {
        $year = $year ?: (int) date('Y');
        $data = $this->analyticsRepository->getAnalyticalDataSet($year);

        return [
            'meta' => [
                'view' => 'V_PRO_SIGPOA_EJE',
                'year' => $year,
                'total_records' => count($data),
                'generated_at' => now()->toIso8601String(),
            ],
            'schema' => [
                'ejercicio',
                'unidad_codigo',
                'unidad_nombre',
                'actividad_numero',
                'actividad_descripcion',
                'monto_planificado',
                'monto_devengado',
                'saldo_disponible',
                'porcentaje_ejecucion',
                'estado',
            ],
            'data' => $data,
        ];
    }
}

<?php

namespace App\Services;

use App\Models\BudgetAccrual;
use App\Models\BudgetCertification;
use App\Models\PoaActivity;
use App\Models\PoaCertification;
use App\Models\PoaModificationRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportingService
{
    /**
     * Reporte 1: POA por Dirección / Unidad Organizacional.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPoaByUnitReport(int $year, ?int $unitId = null): array
    {
        $activities = PoaActivity::query()
            ->with(['organizationalUnit', 'responsible'])
            ->whereHas('process.planningPeriod', fn ($q) => $q->where('year', $year))
            ->when($unitId, fn ($q) => $q->where('organizational_unit_id', $unitId))
            ->orderBy('number')
            ->get();

        return $activities->map(fn (PoaActivity $act) => [
            'numero' => $act->number,
            'direccion' => $act->organizationalUnit?->name ?? 'N/A',
            'descripcion' => $act->description,
            'objetivo' => $act->objective,
            'meta_anual' => (float) $act->annual_target,
            'monto_anual' => (float) $act->annual_amount,
            'responsable' => $act->responsible?->name ?? 'N/A',
            'estado' => $act->status->value,
        ])->toArray();
    }

    /**
     * Reporte 2: Consolidado de Ejecución (Planificado vs Devengado vs Saldo).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getExecutionComparisonReport(int $year, ?int $unitId = null): array
    {
        $activities = PoaActivity::query()
            ->with(['organizationalUnit'])
            ->whereHas('process.planningPeriod', fn ($q) => $q->where('year', $year))
            ->when($unitId, fn ($q) => $q->where('organizational_unit_id', $unitId))
            ->orderBy('number')
            ->get();

        $accruals = BudgetAccrual::query()
            ->where('year', $year)
            ->where('status', 'ACTIVO')
            ->selectRaw('poa_activity_id, SUM(accrued_amount) as total')
            ->groupBy('poa_activity_id')
            ->pluck('total', 'poa_activity_id')
            ->toArray();

        return $activities->map(function (PoaActivity $act) use ($accruals) {
            $planned = (float) $act->annual_amount;
            $accrued = (float) ($accruals[$act->id] ?? 0.0);
            $balance = max(0, $planned - $accrued);
            $rate = $planned > 0 ? round(($accrued / $planned) * 100, 2) : 0.0;

            return [
                'numero' => $act->number,
                'direccion' => $act->organizationalUnit?->name ?? 'N/A',
                'descripcion' => $act->description,
                'planificado' => $planned,
                'devengado' => $accrued,
                'saldo' => $balance,
                'porcentaje_ejecucion' => $rate,
                'estado' => $act->status->value,
            ];
        })->toArray();
    }

    /**
     * Reporte 3: Certificaciones POA y Presupuestarias.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCertificationsReport(int $year, ?int $unitId = null): array
    {
        $poaCerts = PoaCertification::query()
            ->with(['details', 'documents'])
            ->whereHas('process.planningPeriod', fn ($q) => $q->where('year', $year))
            ->when($unitId, fn ($q) => $q->where('organizational_unit_id', $unitId))
            ->latest('id')
            ->get();

        $budgetCerts = BudgetCertification::query()
            ->where('year', $year)
            ->get()
            ->keyBy('poa_certification_id');

        return $poaCerts->map(function (PoaCertification $cert) use ($budgetCerts) {
            $budgetCert = $budgetCerts->get($cert->id);

            return [
                'numero_poa' => $cert->number,
                'asunto' => $cert->subject,
                'monto_poa' => (float) $cert->total_amount,
                'estado_poa' => $cert->status->value,
                'numero_presupuestario' => $budgetCert?->certification_number ?? 'Pendiente',
                'monto_presupuestario' => $budgetCert ? (float) $budgetCert->total_amount : 0.0,
                'fecha_presupuestaria' => $budgetCert?->certification_date?->format('d/m/Y') ?? 'N/A',
            ];
        })->toArray();
    }

    /**
     * Reporte 4: Modificaciones Presupuestarias.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getModificationsReport(int $year, ?int $unitId = null): array
    {
        $modifications = PoaModificationRequest::query()
            ->with(['organizationalUnit', 'lines', 'requester'])
            ->whereHas('process.planningPeriod', fn ($q) => $q->where('year', $year))
            ->when($unitId, fn ($q) => $q->where('organizational_unit_id', $unitId))
            ->latest('id')
            ->get();

        return $modifications->map(fn (PoaModificationRequest $mod) => [
            'numero' => $mod->number,
            'tipo' => $mod->type->value,
            'direccion' => $mod->organizationalUnit?->name ?? 'N/A',
            'monto_debito' => (float) $mod->lines->sum('debit_amount'),
            'monto_credito' => (float) $mod->lines->sum('credit_amount'),
            'reforma_esigef' => $mod->esigef_reform_number ?? 'N/A',
            'cur_reforma' => $mod->reform_cur ?? 'N/A',
            'estado' => $mod->status->value,
            'solicitante' => $mod->requester?->name ?? 'N/A',
            'fecha' => $mod->created_at?->format('d/m/Y H:i'),
        ])->toArray();
    }

    /**
     * Generar respuesta de descarga CSV formateada.
     *
     * @param  array<int, string>  $headers
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function exportToCsv(array $headers, array $rows, string $filename): StreamedResponse
    {
        $callback = function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 for Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers, ';');

            foreach ($rows as $row) {
                fputcsv($handle, array_values($row), ';');
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\OrganizationalUnit;
use App\Services\ReportingService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportingService $reportingService
    ) {}

    public function index(): View
    {
        return view('reports.index');
    }

    public function poaByUnit(Request $request): View|StreamedResponse
    {
        $year = (int) $request->query('year', date('Y'));
        $unitId = $request->query('unit_id') ? (int) $request->query('unit_id') : null;

        $data = $this->reportingService->getPoaByUnitReport($year, $unitId);

        if ($request->query('export') === 'csv') {
            $headers = ['Número', 'Dirección', 'Descripción', 'Objetivo', 'Meta Anual', 'Monto Anual', 'Responsable', 'Estado'];

            return $this->reportingService->exportToCsv($headers, $data, "reporte_poa_direccion_{$year}.csv");
        }

        $units = OrganizationalUnit::query()->where('is_active', true)->orderBy('name')->get();

        return view('reports.poa-by-unit', [
            'data' => $data,
            'units' => $units,
            'selectedYear' => $year,
            'selectedUnitId' => $unitId,
        ]);
    }

    public function executionComparison(Request $request): View|StreamedResponse
    {
        $year = (int) $request->query('year', date('Y'));
        $unitId = $request->query('unit_id') ? (int) $request->query('unit_id') : null;

        $data = $this->reportingService->getExecutionComparisonReport($year, $unitId);

        if ($request->query('export') === 'csv') {
            $headers = ['Número', 'Dirección', 'Descripción', 'Planificado ($)', 'Devengado ($)', 'Saldo ($)', '% Ejecución', 'Estado'];

            return $this->reportingService->exportToCsv($headers, $data, "reporte_ejecucion_comparada_{$year}.csv");
        }

        $units = OrganizationalUnit::query()->where('is_active', true)->orderBy('name')->get();

        return view('reports.execution-comparison', [
            'data' => $data,
            'units' => $units,
            'selectedYear' => $year,
            'selectedUnitId' => $unitId,
        ]);
    }

    public function certifications(Request $request): View|StreamedResponse
    {
        $year = (int) $request->query('year', date('Y'));
        $unitId = $request->query('unit_id') ? (int) $request->query('unit_id') : null;

        $data = $this->reportingService->getCertificationsReport($year, $unitId);

        if ($request->query('export') === 'csv') {
            $headers = ['Nro. Certif. POA', 'Asunto', 'Monto POA ($)', 'Estado POA', 'Nro. Certif. Presupuestaria', 'Monto Presupuestario ($)', 'Fecha Presupuestaria'];

            return $this->reportingService->exportToCsv($headers, $data, "reporte_certificaciones_{$year}.csv");
        }

        $units = OrganizationalUnit::query()->where('is_active', true)->orderBy('name')->get();

        return view('reports.certifications', [
            'data' => $data,
            'units' => $units,
            'selectedYear' => $year,
            'selectedUnitId' => $unitId,
        ]);
    }

    public function modifications(Request $request): View|StreamedResponse
    {
        $year = (int) $request->query('year', date('Y'));
        $unitId = $request->query('unit_id') ? (int) $request->query('unit_id') : null;

        $data = $this->reportingService->getModificationsReport($year, $unitId);

        if ($request->query('export') === 'csv') {
            $headers = ['Número', 'Tipo', 'Dirección', 'Monto Débito ($)', 'Monto Crédito ($)', 'Reforma eSIGEF', 'CUR Reforma', 'Estado', 'Solicitante', 'Fecha'];

            return $this->reportingService->exportToCsv($headers, $data, "reporte_modificaciones_{$year}.csv");
        }

        $units = OrganizationalUnit::query()->where('is_active', true)->orderBy('name')->get();

        return view('reports.modifications', [
            'data' => $data,
            'units' => $units,
            'selectedYear' => $year,
            'selectedUnitId' => $unitId,
        ]);
    }
}

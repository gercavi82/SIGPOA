<?php

namespace App\Http\Controllers;

use App\Integrations\Reporting\SupersetAnalyticsAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsApiController extends Controller
{
    public function __construct(
        private readonly SupersetAnalyticsAdapter $supersetAdapter
    ) {}

    /**
     * Endpoint desacoplado que expone la vista analítica V_PRO_SIGPOA_EJE.
     */
    public function executionDataset(Request $request): JsonResponse
    {
        $year = $request->query('year') ? (int) $request->query('year') : (int) date('Y');
        $dataset = $this->supersetAdapter->getExecutionDataset($year);

        return response()->json($dataset);
    }
}

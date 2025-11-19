<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use App\Http\Resources\DailySettlementCollection;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function __construct(private ReportService $reportService) { }

    public function dailySettlement(Request $request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $report = $this->reportService->dailySettlement($request->get('date'));

        return response()->json([
            'status' => 'success',
            'data' => new DailySettlementCollection($report)
        ]);
    }
}

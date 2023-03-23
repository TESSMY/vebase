<?php

namespace App\Http\Controllers\Admin;

use App\Models\SalesReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Vecapital\Vebase\Http\Controllers\VeController;
use Illuminate\Support\Str;

class SalesReportController extends VeController
{
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from') ?? null;
        $dateTo = $request->input('date_to') ?? null;
        $zeroValue = $request->input('zero_value');

        $limit = $request->input('limit') ?? 10;
        $salesReports = SalesReport::query();

        if (!empty($search)) {
            if (!empty($this->model->searchable)) {
                $salesReports = $salesReports->where(function($query) use ($search) {
                    foreach ($this->model->searchable as $value) {
                        $query->orWhere($value, 'LIKE', '%' . $search . '%');
                    }
                });
            }
        }

        if (!empty($dateFrom)) {
            $startDate = Carbon::parse($dateFrom);
            $salesReports = $salesReports->where('month', '>=', $startDate->month)->where('year', '>=', $startDate->year);
        }
        if (!empty($dateTo)) {
            $endDate = Carbon::parse($dateTo);
            $salesReports = $salesReports->where('month', '<=', $endDate->month)->where('year', '<=', $endDate->year);
        }

        if (!empty($zeroValue)) {
            $salesReports = $salesReports->where('total_revenue', '<>', 0);
        }

        $salesReports = $salesReports->paginate($limit)->withQueryString();

        $compact = [
            'salesReports' => $salesReports,
            'model' => $this->model,
            'modelName' => $this->modelName,
            'routeName' => $this->routeName,
            'routePrefix' => $this->folder,
            'limit' => $limit,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'zeroValue' => $zeroValue,
        ];

        return view('admin.sales-reports.index', $compact);
    }
}

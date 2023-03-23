<?php

namespace App\Http\Controllers\Admin;

use App\Models\SalesReport;
use Illuminate\Http\Request;
use Vecapital\Vebase\Http\Controllers\VeController;
use Illuminate\Support\Str;

class SalesReportController extends VeController
{
    public function index(Request $request)
    {
        $input = $request->input();
        $limit = $request->input('limit') ?? 10;
        $salesReports = SalesReport::query();

        $salesReports = $salesReports->paginate($limit)->withQueryString();

        $compact = [
            // 'routeModel' => Str::singular($this->routeName),
            'salesReports' => $salesReports,
            'model' => $this->model,
            'modelName' => $this->modelName,
            'routeName' => $this->routeName,
            'routePrefix' => $this->folder,
        ];

        return view('admin.sales-reports.index', $compact);
    }
}

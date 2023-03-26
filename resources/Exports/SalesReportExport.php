<?php

namespace App\Exports;

use App\Models\SalesReport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesReportExport implements FromCollection, WithMapping, ShouldAutoSize, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return SalesReport::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Client ID',
            'Client Name',
            'Day',
            'Week of Year',
            'Month',
            'Year',
            'Total Sales Order',
            'Total Cost',
            'Total Profit',
            'Total Revenue',
        ];
    }

    public function map($salesReport): array
    {
        $data = [
            $salesReport->id,
            $salesReport->client_id,
            $salesReport->client ? $salesReport->client->name : null,
            $salesReport->day,
            $salesReport->week_of_year,
            $salesReport->month,
            $salesReport->year,
            $salesReport->total_sales_order,
            $salesReport->total_cost,
            $salesReport->total_profit,
            $salesReport->total_revenue,
        ];
        return $data;
    }
}

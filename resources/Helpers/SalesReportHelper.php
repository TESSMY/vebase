<?php

namespace App\Helpers;

use Carbon\Carbon;

class SalesReportHelper
{
    protected $salesOrder;
    protected $date;

    public function __construct($salesOrder) {
        $this->salesOrder = $salesOrder;
        $this->date = Carbon::parse($salesOrder->date);
    }

    public function recalculateDaily()
    {
        $dailyReport = SalesReport::firstOrCreate([
            'client_id' => null,
            'day' => $this->date->day, 
            'week_of_year' => $this->date->weekOfMonth, 
            'month' => $this->date->month, 
            'year' => $this->date->year, 
        ]);

        $dailySalesOrder = SalesOrder::whereNull('client_id')->where('date', $this->date->toDateString())->get();
        $cost = $dailySalesOrder->sum('total_cost');
        $revenue = $dailySalesOrder->sum('sub_total');
        $discount = $dailySalesOrder->sum('discount_amount');

        $dailyReport->update([
            'total_sales_order' => $dailySalesOrder->count(),
            'total_cost' => $cost,
            'total_profit' => $revenue - $cost - $discount,
            'total_revenue' => $revenue,
        ]);

        $clientDailyReport = SalesReport::firstOrCreate([
            'client_id' => $this->salesOrder->client_id,
            'day' => $this->date->day, 
            'week_of_year' => $this->date->weekOfMonth, 
            'month' => $this->date->month, 
            'year' => $this->date->year, 
        ]);

        $dailySalesOrder = SalesOrder::where('client_id', $this->salesOrder->client_id)->where('date', $this->date->toDateString())->get();
        $cost = $dailySalesOrder->sum('total_cost');
        $revenue = $dailySalesOrder->sum('sub_total');
        $discount = $dailySalesOrder->sum('discount_amount');

        $clientDailyReport->update([
            'total_sales_order' => $dailySalesOrder->count(),
            'total_cost' => $cost,
            'total_profit' => $revenue - $cost - $discount,
            'total_revenue' => $revenue,
        ]);
        
    }

    public function recalculateWeekly()
    {
        $weeklyReport = SalesReport::firstOrCreate([
            'client_id' => null,
            'day' => null, 
            'week_of_year' => $this->date->weekOfMonth, 
            'month' => $this->date->month, 
            'year' => $this->date->year, 
        ]);

        $weeklySalesOrder = SalesOrder::whereNull('client_id')->where('date', $this->date->toDateString())->get();
        $cost = $weeklySalesOrder->sum('total_cost');
        $revenue = $weeklySalesOrder->sum('sub_total');
        $discount = $weeklySalesOrder->sum('discount_amount');

        $weeklyReport->update([
            'total_sales_order' => $weeklySalesOrder->count(),
            'total_cost' => $cost,
            'total_profit' => $revenue - $cost - $discount,
            'total_revenue' => $revenue,
        ]);

        $clientWeeklyReport = SalesReport::firstOrCreate([
            'client_id' => $this->salesOrder->client_id,
            'day' => null, 
            'week_of_year' => $this->date->weekOfMonth, 
            'month' => $this->date->month, 
            'year' => $this->date->year, 
        ]);

        $weeklySalesOrder = SalesOrder::where('client_id', $this->salesOrder->client_id)->where('date', $this->date->toDateString())->get();
        $cost = $weeklySalesOrder->sum('total_cost');
        $revenue = $weeklySalesOrder->sum('sub_total');
        $discount = $weeklySalesOrder->sum('discount_amount');

        $clientWeeklyReport->update([
            'total_sales_order' => $weeklySalesOrder->count(),
            'total_cost' => $cost,
            'total_profit' => $revenue - $cost - $discount,
            'total_revenue' => $revenue,
        ]);
    }

    public function recalculateMonthly()
    {
        $monthlyReport = SalesReport::firstOrCreate([
            'client_id' => null,
            'day' => null,
            'week_of_year' => null,  
            'month' => $this->date->month, 
            'year' => $this->date->year, 
        ]);

        $monthlySalesOrder = SalesOrder::whereNull('client_id')->where('date', $this->date->toDateString())->get();
        $cost = $monthlySalesOrder->sum('total_cost');
        $revenue = $monthlySalesOrder->sum('sub_total');
        $discount = $monthlySalesOrder->sum('discount_amount');

        $monthlyReport->update([
            'total_sales_order' => $monthlySalesOrder->count(),
            'total_cost' => $cost,
            'total_profit' => $revenue - $cost - $discount,
            'total_revenue' => $revenue,
        ]);

        $clientMonthlyReport = SalesReport::firstOrCreate([
            'client_id' => $this->salesOrder->client_id,
            'day' => null,
            'week_of_year' => null,  
            'month' => $this->date->month, 
            'year' => $this->date->year,
        ]);

        $monthlySalesOrder = SalesOrder::where('client_id', $this->salesOrder->client_id)->where('date', $this->date->toDateString())->get();
        $cost = $monthlySalesOrder->sum('total_cost');
        $revenue = $monthlySalesOrder->sum('sub_total');
        $discount = $monthlySalesOrder->sum('discount_amount');

        $clientMonthlyReport->update([
            'total_sales_order' => $monthlySalesOrder->count(),
            'total_cost' => $cost,
            'total_profit' => $revenue - $cost - $discount,
            'total_revenue' => $revenue,
        ]);
    }

    public function recalculateYearly()
    {
        $yearlyReport = SalesReport::firstOrCreate([
            'client_id' => null,
            'day' => null,
            'week_of_year' => null, 
            'month' => null,
            'year' => $this->date->year,
        ]);

        $yearlySalesOrder = SalesOrder::whereNull('client_id')->where('date', $this->date->toDateString())->get();
        $cost = $yearlySalesOrder->sum('total_cost');
        $revenue = $yearlySalesOrder->sum('sub_total');
        $discount = $yearlySalesOrder->sum('discount_amount');

        $yearlyReport->update([
            'total_sales_order' => $yearlySalesOrder->count(),
            'total_cost' => $cost,
            'total_profit' => $revenue - $cost - $discount,
            'total_revenue' => $revenue,
        ]);

        $clientYearlyReport = SalesReport::firstOrCreate([
            'client_id' => $this->salesOrder->client_id,
            'day' => null,
            'week_of_year' => null,  
            'month' => null,
            'year' => $this->date->year, 
        ]);

        $yearlySalesOrder = SalesOrder::where('client_id', $this->salesOrder->client_id)->where('date', $this->date->toDateString())->get();
        $cost = $yearlySalesOrder->sum('total_cost');
        $revenue = $yearlySalesOrder->sum('sub_total');
        $discount = $yearlySalesOrder->sum('discount_amount');

        $clientYearlyReport->update([
            'total_sales_order' => $yearlySalesOrder->count(),
            'total_cost' => $cost,
            'total_profit' => $revenue - $cost - $discount,
            'total_revenue' => $revenue,
        ]);
    }

    public function recalculateOverall()
    {
        $overallReport = SalesReport::firstOrCreate([
            'client_id' => null,
            'day' => null, 
            'week_of_year' => null,
            'month' => null,
            'year' => null,
        ]);

        $overallSalesOrder = SalesOrder::whereNull('client_id')->where('date', $this->date->toDateString())->get();
        $cost = $overallSalesOrder->sum('total_cost');
        $revenue = $overallSalesOrder->sum('sub_total');
        $discount = $overallSalesOrder->sum('discount_amount');

        $overallReport->update([
            'total_sales_order' => $overallSalesOrder->count(),
            'total_cost' => $cost,
            'total_profit' => $revenue - $cost - $discount,
            'total_revenue' => $revenue,
        ]);

        $clientOverallReport = SalesReport::firstOrCreate([
            'client_id' => $this->salesOrder->client_id,
            'day' => null, 
            'week_of_year' => null,
            'month' => null,
            'year' => null,
        ]);

        $overallSalesOrder = SalesOrder::where('client_id', $this->salesOrder->client_id)->where('date', $this->date->toDateString())->get();
        $cost = $overallSalesOrder->sum('total_cost');
        $revenue = $overallSalesOrder->sum('sub_total');
        $discount = $overallSalesOrder->sum('discount_amount');

        $clientOverallReport->update([
            'total_sales_order' => $overallSalesOrder->count(),
            'total_cost' => $cost,
            'total_profit' => $revenue - $cost - $discount,
            'total_revenue' => $revenue,
        ]);
    }
}

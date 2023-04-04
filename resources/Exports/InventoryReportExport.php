<?php

namespace App\Exports;

use App\Models\InventoryReportItem;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\SalesReport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InventoryReportExport implements FromCollection, WithMapping, ShouldAutoSize, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $productVariants = ProductVariant::query();

        $productVariants->withSum(
            [
                'purchaseOrderItem as qtyOnOrder' => function ($query) {
                    $query->whereHas('purchaseOrder', function ($q) {
                        $q->where('status', PurchaseOrder::STATUS_ORDER_CONFIRMED);
                    });
                }
            ],
            'quantity'
        );

        $productVariants->withSum(
            [
                'salesOrderItem as qtyPendingOrder' => function ($query) {
                    $query->whereHas('salesOrder', function ($q) {
                        $q->where('status', SalesOrder::STATUS_DRAFT);
                    });
                }
            ],
            'quantity'
        );

        $productVariants->withSum(
            [
                'salesOrderItem as qtyBackOrder' => function ($query) {
                    $query->whereHas('salesOrder', function ($q) {
                        $q->where('status', SalesOrder::STATUS_PENDING);
                    });
                }
            ],
            'quantity'
        );

        return $productVariants->get();
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'SKU',
            'Qty On Hand',
            'Qty On Order',
            'Qty Pending Back Order',
            'Qty Back Order',
            'Free Balance',
        ];
    }

    public function map($productVariant): array
    {
        $data = [
            $productVariant->name,
            $productVariant->sku,
            $productVariant->total_stock ?? 0,
            $productVariant->qtyOnOrder ?? 0,
            $productVariant->qtyPendingOrder ?? 0,
            $productVariant->qtyBackOrder ?? 0,
            $productVariant->total_stock + $productVariant->qtyOnOrder - $productVariant->qtyPendingOrder - $productVariant->qtyBackOrder,
        ];
        return $data;
    }
}

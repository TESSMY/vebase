<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Dashboard</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-md-5">
                <div class="row">
                    <div class="col-12 col-sm-6">
                        <div class="card widget-flat">
                            <div class="card-body">
                                <div class="float-end">
                                    <i class="mdi mdi-truck-outline widget-icon"></i>
                                </div>
                                <h5 class="text-muted fw-normal mt-0" title="Number of Customers">Shipment</h5>
                                <h3 class="mt-3 mb-3">
                                    {{ currentMonthStatistics.total_shipment[0].toLocaleString() }}
                                </h3>
                                <p class="mb-0 text-muted">
                                    <span class="text-success me-2"
                                        v-if="currentMonthStatistics.total_shipment[1] > 0"><span
                                            class="mdi mdi-arrow-up-bold"></span> {{
                                                currentMonthStatistics.total_shipment[1] }}%</span>
                                    <span class="text-danger me-2" v-else><span class="mdi mdi-arrow-down-bold"></span> {{
                                        currentMonthStatistics.total_shipment[1] }}%</span>
                                    <span class="text-nowrap">Since last month</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="card widget-flat">
                            <div class="card-body">
                                <div class="float-end">
                                    <i class="mdi mdi-clipboard-text-outline widget-icon"></i>
                                </div>
                                <h5 class="text-muted fw-normal mt-0" title="Number of Customers">Purchase Order</h5>
                                <h3 class="mt-3 mb-3">
                                    {{ currentMonthStatistics.total_purchase_order[0].toLocaleString() }}
                                </h3>
                                <p class="mb-0 text-muted">
                                    <span class="text-success me-2"
                                        v-if="currentMonthStatistics.total_purchase_order[1] > 0"><span
                                            class="mdi mdi-arrow-up-bold"></span> {{
                                                currentMonthStatistics.total_purchase_order[1] }}%</span>
                                    <span class="text-danger me-2" v-else><span class="mdi mdi-arrow-down-bold"></span> {{
                                        currentMonthStatistics.total_purchase_order[1] }}%</span>
                                    <span class="text-nowrap">Since last month</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="card widget-flat">
                            <div class="card-body">
                                <div class="float-end">
                                    <i class="mdi mdi-currency-usd widget-icon"></i>
                                </div>
                                <h5 class="text-muted fw-normal mt-0" title="Number of Customers">Revenue</h5>
                                <h3 class="mt-3 mb-3">
                                    {{ currentMonthStatistics.total_revenue[0].toLocaleString() }}
                                </h3>
                                <p class="mb-0 text-muted">
                                    <span class="text-success me-2" v-if="currentMonthStatistics.total_revenue[1] > 0"><span
                                            class="mdi mdi-arrow-up-bold"></span> {{ currentMonthStatistics.total_revenue[1]
                                            }}%</span>
                                    <span class="text-danger me-2" v-else><span class="mdi mdi-arrow-down-bold"></span> {{
                                        currentMonthStatistics.total_revenue[1] }}%</span>
                                    <span class="text-nowrap">Since last month</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="card widget-flat">
                            <div class="card-body">
                                <div class="float-end">
                                    <i class="mdi mdi-package-variant-closed widget-icon"></i>
                                </div>
                                <h5 class="text-muted fw-normal mt-0" title="Number of Customers">Sales Order</h5>
                                <h3 class="mt-3 mb-3">
                                    {{ currentMonthStatistics.total_sales_order[0].toLocaleString() }}
                                </h3>
                                <p class="mb-0 text-muted">
                                    <span class="text-success me-2"
                                        v-if="currentMonthStatistics.total_sales_order[1] > 0"><span
                                            class="mdi mdi-arrow-up-bold"></span> {{
                                                currentMonthStatistics.total_sales_order[1] }}%</span>
                                    <span class="text-danger me-2" v-else><span class="mdi mdi-arrow-down-bold"></span> {{
                                        currentMonthStatistics.total_sales_order[1] }}%</span>
                                    <span class="text-nowrap">Since last month</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-7">
                <div class="card card-h-100">
                    <div class="d-flex card-header justify-content-between align-items-center">
                        <h4 class="header-title">MONTHLY ORDERS</h4>
                    </div>
                    <div class="card-body pt-0">
                        <div dir="ltr">
                            <canvas style="height: 280px; width:100%" ref="chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">OVERVIEW</h4>

                        <ul class="nav nav-tabs nav-bordered mb-3">
                            <li class="nav-item">
                                <a href="#purchase-order-section" data-bs-toggle="tab" aria-expanded="false"
                                    class="nav-link active">
                                    Purchase Orders
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#sales-order-section" data-bs-toggle="tab" aria-expanded="true" class="nav-link">
                                    Sales Orders
                                </a>
                            </li>
                        </ul> <!-- end nav-->
                        <div class="tab-content">
                            <div class="tab-pane show active" id="purchase-order-section">
                                <table class="table table-striped dt-responsive nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>Purchase Order ID</th>
                                            <th>No. of Product</th>
                                            <th>Supplier</th>
                                            <th>Currency</th>
                                            <th>Status</th>
                                            <th>Completion (D/T)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="purchaseOrder in purchaseOrders">
                                            <td>PO{{ purchaseOrder.id }}</td>
                                            <td>{{ purchaseOrder.item_count }}</td>
                                            <td>{{ purchaseOrder.supplier.name }}</td>
                                            <td>SGD</td>
                                            <td>
                                                <span class="badge"
                                                    :class="{ 'bg-success': purchaseOrder.status == 2, 'bg-primary': purchaseOrder.status == 0, 'bg-warning': purchaseOrder.status == 1 }">
                                                    {{ purchaseOrder.status_text }}
                                                </span>
                                            </td>
                                            <td>{{ moment(purchaseOrder.updated_at).format('DD/MM/YYYY HH:mm a') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="tab-pane code" id="sales-order-section">
                                <table class="table table-striped dt-responsive nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>Sales Order ID</th>
                                            <th>Customer Name</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="salesOrder in salesOrders">
                                            <td>S.O #{{ salesOrder.id }}</td>
                                            <td></td>
                                            <td>{{ moment(salesOrder.created_at).format('DD/MM/YYYY HH:mm a') }}</td>
                                            <td>
                                                <span class="badge"
                                                    :class="{ 'bg-success': salesOrder.status == 3, 'bg-info': salesOrder.status == 2, 'bg-warning': salesOrder.status == 0, 'bg-primary': salesOrder.status == 1 }">
                                                    {{ salesOrder.status_text }}
                                                </span>
                                            </td>
                                            <td>{{ salesOrder.grant_total.toLocaleString() }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import Chart from 'chart.js/auto';
import moment from 'moment';
import { onMounted, ref, computed } from 'vue';

const props = defineProps({
    monthlyOrders: {
        required: true,
        type: Array
    },
    purchaseOrders: {
        required: true,
        type: Array
    },
    salesOrders: {
        required: true,
        type: Array
    }
})

const chart = ref(null)

const initChart = () => {
    return new Chart(
        chart.value,
        {
            type: 'line',
            data: {
                labels: props.monthlyOrders.map(row => moment(row.month, 'MM').format('MMM')),
                datasets: [
                    {
                        label: '',
                        data: props.monthlyOrders.map(row => row.total_cost)
                    }
                ]
            }
        }
    );
}

onMounted(() => {
    if (props.monthlyOrders) {
        initChart()
    }
})

const currentMonthStatistics = computed(() => {
    let data = {
        total_revenue: [0, 0],
        total_shipment: [0, 0],
        total_purchase_order: [0, 0],
        total_sales_order: [0, 0]
    }
    let current_data = props.monthlyOrders.at(-1)
    let prev_data = props.monthlyOrders.at(-2)

    if (prev_data) {
        const total_revenue_percentage = (parseFloat(prev_data.total_revenue) - parseFloat(current_data.total_revenue)) / parseFloat(prev_data.total_revenue) * 100
        const total_shipment_percentage = (parseFloat(prev_data.total_shipment) - parseFloat(current_data.total_shipment)) / parseFloat(prev_data.total_shipment) * 100
        const total_purchase_order_percentage = (parseFloat(prev_data.total_purchase_order) - parseFloat(current_data.total_purchase_order)) / parseFloat(prev_data.total_purchase_order) * 100
        const total_sales_order_percentage = (parseFloat(prev_data.total_sales_order) - parseFloat(current_data.total_sales_order)) / parseFloat(prev_data.total_sales_order) * 100
        data = {
            total_revenue: [parseFloat(current_data.total_revenue), total_revenue_percentage.toFixed(2)],
            total_shipment: [parseFloat(current_data.total_shipment), total_shipment_percentage.toFixed(2)],
            total_purchase_order: [parseFloat(current_data.total_purchase_order), total_purchase_order_percentage.toFixed(2)],
            total_sales_order: [parseFloat(current_data.total_sales_order), total_sales_order_percentage.toFixed(2)]
        }
    } else if (current_data) {
        data = {
            total_revenue: [parseFloat(current_data.total_revenue), 100],
            total_shipment: [parseFloat(current_data.total_shipment), 100],
            total_purchase_order: [parseFloat(current_data.total_purchase_order), 100],
            total_sales_order: [parseFloat(current_data.total_sales_order), 100]
        }
    }
    return data
})
</script>
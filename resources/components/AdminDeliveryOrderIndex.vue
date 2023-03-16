<style scoped>
    .more-lg {
        width: 80% !important;
    }
</style>

<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <span class="page-title h4">Delivery Order</span>
                </div>
            </div>
        </div>
        <div class="border my-2 mb-3"></div>
        <div class="bg-white card shadow py-3 px-4">
            <div class="d-flex flex-row justify-content-between mb-3">
                <a href="/admin/delivery-orders/create" class="col-12 col-md-2 mb-3 mb-md-0 btn btn-primary rounded">Create Delivery Order</a>
                <button type="button" class="btn btn-sm btn-light col-md-1">Print</button>
            </div>
            <form action="/admin/delivery-orders/index" method="GET" id="form">
                <div class="row mb-3">
                    <div class="col-12 col-md-2 mb-3 mb-md-0 d-flex">
                        <span class="my-auto">Display:</span>
                        <select class="form-select form-select-sm mx-2" name="limit">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span class="my-auto">rows</span>
                    </div>
                    <div class="col-md-3 d-flex">
                        <span class="my-auto">Filter</span>
                        <select name="filter" class="form-select form-select-sm rounded mx-2">
                            <option value="">Choose</option>
                        </select>
                    </div>
                    <div class="col-md-4"></div>
                    <div class="col-12 col-md-2 p-0 d-md-flex" style="margin-left: auto;">
                        <label class="form-label my-auto me-md-2">Search: </label>
                        <input class="form-control input-sm" type="search" placeholder="Search" name="search" value="">
                    </div>
                </div>
            </form>
            <div class="overflow-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input" v-model="checkedAll">
                            </td>
                            <td>Delivery Order ID</td>
                            <td>Sales Order ID</td>
                            <td>Customer Name</td>
                            <td>Date</td>
                            <td>Status</td>
                            <td>Amount (SGD)</td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="data.length">
                            <tr v-for="deliveryOrder in data">
                                <td><input type="checkbox" v-model="deliveryOrder.checked" class="form-check-input check-do"></td>
                                <td>DO{{ deliveryOrder.id }}</td>
                                <td>SO{{ deliveryOrder.sales_order.id }}</td>
                                <td></td>
                                <td>{{ moment(deliveryOrder.created_at).format('DD/MM/YYYY HH:mm a') }}</td>
                                <td>
                                    <span class="badge" :class="{ 'bg-danger': deliveryOrder.status == 0, 'bg-primary': deliveryOrder.status == 1, 'bg-warning': deliveryOrder.status == 2, 'bg-info': deliveryOrder.status == 3, 'bg-success': deliveryOrder.status == 4 }">
                                        {{ deliveryOrder.status_text }}
                                    </span>
                                </td>
                                <td>
                                    {{ deliveryOrder.grand_total.toLocaleString() }}
                                </td>
                                <td>
                                    <a v-if="parseInt(deliveryOrder.status) == 0" :href="`/admin/delivery-orders/${deliveryOrder.id}/edit`" class="btn btn-sm btn-primary">View</a>
                                    <button v-else @click="deliveryOrderSelected=deliveryOrder" type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-detail">View</button>
                                </td>
                            </tr>
                        </template>
                        <tr v-else>
                            <td colspan="100%" class="text-center">There are no delivery order found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-2">

        </div>
    </div>

    <div class="modal fade" id="modal-detail" tabindex="-1" role="dialog" aria-labelledby="modalDetail" aria-hidden="true">
        <div class="modal-dialog modal-lg more-lg">
            <div class="modal-content">
                <admin-delivery-order-summary v-if="deliveryOrderSelected"
                    :key="deliveryOrderSelected.id"
                    :delivery-order="deliveryOrderSelected"
                    :show-print-button="true"
                    :show-download-button="true"/>
            </div>
        </div>
    </div>
</template>

<script setup>
import moment from 'moment'
import { ref, watch } from 'vue';

const props = defineProps(['deliveryOrders'])
const data = ref(props.deliveryOrders.data.map(deliveryOrder => {
    deliveryOrder.checked = false
    return deliveryOrder
}))
const deliveryOrderSelected = ref(null)
const checkedAll = ref(false)
watch(checkedAll, (newVal) => {
    for (let d of data.value) {
        d.checked = newVal
    }
})
</script>

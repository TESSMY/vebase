<template>
    <div class="bg-white card shadow py-3 px-4">
        <div class="row border-bottom mb-2">
            <span class="h5">Shipment Information</span>
        </div>
        <div class="row">
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Shipping ID</label>
                <input class="form-control" type="text" placeholder="Auto Generated">
            </div>
            <div class="col-12 col-md-6 mb-md-0 mb-2">
                <label class="form-label">Shipper (3PL) - Optional</label>
                <input class="form-control" type="text" placeholder="Shipper (3PL)">
            </div>
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Purchase Order</label>
                <!-- <input type="hidden" name="sales_order_id" :value="purchaseOrder.id">
                <multi-select v-model="purchaseOrder" track-by="id" label="id" :options="purchaseOrders"></multi-select> -->
            </div>
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Supplier</label>
                <!-- <input type="hidden" name="supplier_id" :name="purchaseOrder.supplier_id"> -->
                <!-- <input class="form-control" type="text" placeholder="Supplier" :name="purchaseOrder.supplier.name" readonly> -->
            </div>
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Shipment Value</label>
                <input class="form-control" type="number" name="name" placeholder="number" :value="purchaseOrder.grand_total" required>
            </div>
            
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">ETA</label>
                <input class="form-control" type="date" name="eta_date" placeholder="date" required>
            </div>
        </div>
        <div class="row col-12">
            <button type="submit" class="col-12 col-md-1 btn btn-success m-2">Create</button>
            <a href="/admin/shipments" class="col-12 col-md-1 btn btn-dark m-2">Back</a>
        </div>
    </div>
</template>


<script setup>
import { defineComponent, ref } from 'vue';

let props = defineProps({
    purchaseOrders: Array,
});

const purchaseOrder = ref({});

</script>
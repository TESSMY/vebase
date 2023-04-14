<template>
    <div class="row">
        <div class="bg-white card shadow py-3 px-4">
            <div class="row border-bottom mb-2">
                <span class="h5">SALES ORDER DETAILS</span>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Sales Order ID</label>
                    <input class="form-control" type="text" v-model="salesOrder.id" readonly>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Date</label>
                    <input class="form-control" type="date" name="date" v-model="salesOrder.date" required disabled>
                </div>
                <div class="col-md-6">
                    <div class="form-group row mb-3">
                        <label class="col-md-4 text-right form-label text-sm-start">Client</label>
                        <div class="col-md-12">
                            <input type="hidden" name="client_id" :value="salesOrder.client_id">
                            <multi-select placeholder="Search Client" v-model="salesOrder.client" label="name" :options="clientArray.options" @search-change="fetchClients" disabled></multi-select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="border my-2 mb-3"></div>
        <div class="bg-white card shadow py-3 px-4">
            <div class="row mb-2">
                <span class="h4">PRODUCTS</span>
            </div>
            <div class="overflow-auto">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>SKU</th>
                            <th>Quantity</th>
                            <th>UOM</th>
                            <th>Unit Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in products">
                            <input type="hidden" :name="'products[' + index + '][sales_order_item_id]'" :value="item.sales_order_item_id">
                            <td>{{ item.product.name }}</td>
                            <td>{{ item.product.sku }}</td>
                            <td>
                                <input class="form-control" type="number" min="0" :name="'products[' + index + '][quantity]'" v-model="item.quantity" @input="updateProductSubTotal(item)" disabled>
                            </td>
                            <td>{{ item.product.measurement_unit }}</td>
                            <td>{{ item.product.selling_price }}</td>
                            <td>
                                <select :name="'products[' + index + '][status]'" class="form-select">
                                    <option value="0" :selected="item.status == 0">Pending Supplier Code</option>
                                    <option value="10" :selected="item.status == 10">Quote Received</option>
                                    <option value="20" :selected="item.status == 20">Rejected by Client</option>
                                    <option value="40" :selected="item.status == 40">Client Approved</option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="row container-fluid">
                <div class="col-12 col-md-4 mb-md-0 mb-3">
                    <div class="row px-0">
                        <div class="px-0">
                            <label class="form-label px-0">Notes and instructions</label>
                            <textarea class="form-control" placeholder="Will be displayed on Sales Order" rows="5" style="resize: none" name="notes">{{ salesOrder.notes }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row col-12">
                <button type="submit" class="col-12 col-md-1 btn btn-success m-2">Update</button>
            </div>
        </div>
    </div>
</template>
<script setup>
import { defineComponent, reactive, ref, onBeforeMount, computed } from 'vue';

let props = defineProps({
    salesOrder: Object,
});

const salesOrder = ref({
    'client': '',
    'billing_name': '',
    'billing_address_1': '',
    'billing_contact_number': '',
    'billing_contact_email': '',
    'billing_address_1': '',
    'billing_city': '',
    'billing_state': '',
    'billing_postcode': '',
    'billing_country': '',
    'ship_to_name': '',
    'ship_to_contact_number': '',
    'ship_to_contact_email': '',
    'ship_to_address_1': '',
    'ship_to_city': '',
    'ship_to_state': '',
    'ship_to_postcode': '',
    'ship_to_country': '',
    'date': '',
    'notes': '',
});
const products = ref([{
    'product': '',
    'quantity': 0,
    'subTotal': 0,
}]);

const productArray = reactive({ options: [] });
const fetchProducts = (query) => {
    if (query) {
        axios.get(`/web/products?search=${query}`).then((response) => {
            productArray.options = []
            response.data.response.items.forEach(product => {
                if (product.type == 3) { // bundle type
                    productArray.options.push(product);
                } else {
                    product.variants.forEach(variant => {
                        productArray.options.push(variant)
                    });
                }
            });
        });
    }
};

const clientArray = reactive({ options: [] });
const fetchClients = (query) => {
    if (query) {
        axios.get(`/web/clients?search=${query}`).then((response) => {
            clientArray.options = response.data.response.items;
        });
    }
};

onBeforeMount(() => {
    if (props.salesOrder !== undefined) {
        salesOrder.value = props.salesOrder;

        if (props.salesOrder.sales_order_items !== undefined) {
            products.value = []
            props.salesOrder.sales_order_items.forEach(salesOrderItem => {
                if (salesOrderItem.product_variant == null) {
                    // bundles
                    products.value.push({
                        'sales_order_item_id': salesOrderItem.id,
                        'product': salesOrderItem.product,
                        'supplier_id': salesOrderItem.product.supplier_id,
                        'quantity': salesOrderItem.quantity,
                        'subTotal': salesOrderItem.quantity * salesOrderItem.product.cost_price,
                        'shipmentType': salesOrderItem.shipment_type,
                        'status': salesOrderItem.status,
                        'deliveryOrderChecked' : false,
                        'purchaseOrderChecked' : false,
                    });
                } else {
                    // product variants & single products
                    products.value.push({
                        'sales_order_item_id': salesOrderItem.id,
                        'product': salesOrderItem.product_variant,
                        'supplier_id': salesOrderItem.product.supplier_id,
                        'quantity': salesOrderItem.quantity,
                        'subTotal': salesOrderItem.quantity * salesOrderItem.product_variant.selling_price,
                        'shipmentType': salesOrderItem.shipment_type,
                        'status': salesOrderItem.status,
                        'deliveryOrderChecked' : false,
                        'purchaseOrderChecked' : false,
                    });
                }
            });
        }
    }
})

</script>

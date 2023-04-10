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
                    <input class="form-control" type="date" name="date" v-model="date" required>
                </div>
                <div class="col-md-6">
                    <div class="form-group row mb-3">
                        <label class="col-md-4 text-right form-label text-sm-start">Client</label>
                        <div class="col-md-12">
                            <input type="hidden" name="client_id" :value="client.id">
                            <multi-select placeholder="Search Client" v-model="client" label="name" :options="clientArray.options" @search-change="fetchClients" disabled></multi-select>
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
                            <th v-if="salesOrder.status != 70">Select</th>
                            <th>Product Name</th>
                            <th>SKU</th>
                            <th>Quantity</th>
                            <th>UOM</th>
                            <th>Unit Price</th>
                            <th v-if="salesOrder.status == 70">Status</th>
                            <th v-if="salesOrder.status != 70">P.O</th>
                            <th v-if="salesOrder.status != 70">P.O Supplier</th>
                            <th v-if="salesOrder.status != 70">Type of Shipment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in products">
                            <td v-if="salesOrder.status != 70">
                                <input v-if="item.shipmentType == 0 && item.deliveryOrderChecked" type="hidden" :name="'delivery_order_products[' + index + '][sales_order_item_id]'" :value="item.sales_order_item_id">
                                <input v-if="item.shipmentType == 0 && item.status == 0" type="checkbox" :name="'delivery_order_products[' + index + '][generate_delivery_order]'" value="1" v-model="item.deliveryOrderChecked">
                            </td>
                            <td>{{ item.product.name }}</td>
                            <td>{{ item.product.sku }}</td>
                            <td>
                                <input class="form-control" type="number" min="0" :name="'products[' + index + '][quantity]'" v-model="item.quantity" @input="updateProductSubTotal(item)" disabled>
                            </td>
                            <td>{{ item.product.measurement_unit }}</td>
                            <td>{{ item.product.selling_price }}</td>
                            <td v-if="salesOrder.status == 70">
                                <select :name="'products[' + index + '][status]'" class="form-select">
                                    <option value="0" :selected="item.status == 0">Pending Supplier Code</option>
                                    <option value="10" :selected="item.status == 10">Quote Received</option>
                                    <option value="20" :selected="item.status == 20">Rejected by Client</option>
                                    <option value="40" :selected="item.status == 40">Client Approved</option>
                                </select>
                            </td>
                            <td v-if="salesOrder.status != 70">
                                <input v-if="item.shipmentType == 1 && item.purchaseOrderChecked" type="hidden" :name="'purchase_order_products[' + index + '][sales_order_item_id]'" :value="item.sales_order_item_id">
                                <input v-if="item.shipmentType == 1 && item.status == 0" type="checkbox" :name="'purchase_order_products[' + index + '][generate_purchase_order]'" value="1" v-model="item.purchaseOrderChecked">
                                <input v-if="item.shipmentType == 1" type="hidden" :name="'purchase_order_products[' + index + '][supplier_id]'" :value="item.supplier_id">
                            </td>
                            <td v-if="salesOrder.status != 70">
                            </td>
                            <td v-if="salesOrder.status != 70">
                                <select class="form-select" name="shipment_type" :disabled="item.status == 80" required v-model="item.shipmentType">
                                    <option selected value="0">Direct</option>
                                    <option value="1">Non Direct</option>
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
                            <textarea class="form-control" placeholder="Will be displayed on Sales Order" rows="5" style="resize: none" name="notes_and_instructions">{{ notes }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row col-12">
                <button type="submit" class="col-12 col-md-1 btn btn-success m-2">Next</button>
                <a href="/admin/sales-orders" class="col-12 col-md-1 btn btn-dark m-2">Close</a>
            </div>
        </div>
    </div>
    <input type="hidden" name="tax_rate" v-model="tax_rate">
</template>
<script setup>
import { defineComponent, reactive, ref, onBeforeMount, computed } from 'vue';

let props = defineProps({
    salesOrder: Object,
    tax_rate: Number,
});

const subTotal = ref(0);
const salesOrder = ref(props.salesOrder);
const client = ref({});
const date = ref('');
const customerPo = ref('');
const customerName = ref('');
const paymentTerm = ref('');
const paymentDue = ref('');
const packedByDate = ref('');
const notes = ref('');
const grandTotal = ref(0);
const tax_rate = ref(props.tax_rate);
const taxRate1 = ref('');
const taxRate2 = ref('');
const products = ref([{
    'product': '',
    'quantity': 0,
    'subTotal': 0,
}]);

function addProduct() {
    products.value.push({
        'product': '',
        'quantity': 0,
        'subTotal': 0,
    })
}

function removeProduct(index) {
    products.value.splice(index, 1);
    updateTotalPrice();
}

function updateProductSubTotal(item) {
    item.subTotal = item.product.selling_price * item.quantity;
    updateTotalPrice();
}

function updateTotalPrice() {
    subTotal.value = 0;
    grandTotal.value = 0;
    products.value.forEach(item => {
        subTotal.value += item.subTotal;
        grandTotal.value += item.subTotal;
    });
}

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

function selectShipmentType(item) {
    if (item.shipmentType == 1) {
        item.shipmentType = 0;
    } else {
        item.shipmentType = 1;
    }
}

onBeforeMount(() => {
    if (props.salesOrder !== undefined) {
        if (props.salesOrder.sales_order_items !== undefined) {
            client.value = props.salesOrder.client
            date.value = props.salesOrder.date
            customerPo.value = props.salesOrder.client_address
            customerName.value = props.salesOrder.client_name
            paymentTerm.value = props.salesOrder.payment_terms
            paymentDue.value = props.salesOrder.payment_due
            packedByDate.value = props.salesOrder.packed_by_date
            notes.value = props.salesOrder.notes_and_instructions
            products.value = []
            subTotal.value = parseInt(props.salesOrder.sub_total)
            grandTotal.value = parseInt(props.salesOrder.total_amount)

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

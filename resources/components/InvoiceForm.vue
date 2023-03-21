<template>
    <div class="bg-white card shadow py-3 px-4">
        <div class="row border-bottom mb-2">
            <span class="h5">Invoice Information</span>
        </div>
        <div class="row">
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Sales Order (Optional)</label>
                <input type="hidden" name="sales_order_id" :value="salesOrder.id">
                <multi-select placeholder="Search Sales Order" v-model="salesOrder" label="name" :options="salesOrderArray.options" @search-change="fetchSalesOrder"></multi-select>
            </div>
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Client</label>
                <input type="hidden" name="client_id" :value="client.id">
                <multi-select placeholder="Search Client" v-model="client" label="name" :options="clientArray.options" @search-change="fetchClients"></multi-select>
            </div>
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Date</label>
                <input class="form-control" type="date" name="date" placeholder="date" v-model="date" required>
            </div>
            <div class="col-12 col-md-6 mb-md-0 mb-2">
                <label class="form-label">Client Address</label>
                <input class="form-control" type="text" placeholder="Client Address" name="client_address" :value="client.address_1 + ' ' + client.address_2" readonly>
            </div>
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Payment Term</label>
                <input class="form-control" type="text" name="payment_term" placeholder="Payment Term" v-model="paymentTerm">
            </div>
        </div>
    </div>
    <div class="border my-2 mb-3"></div>
    <div class="bg-white card shadow py-3 px-4">
        <div class="row mb-2">
            <span class="h4">Add PRODUCT</span>
        </div>
        <div class="">
            <table class="table table-bordered text-center">
                <thead>
                    <tr>
                        <th>Product Details</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Sub Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index) in products">
                        <td>
                            <input type="hidden" :name="'products[' + index + '][invoice_item_id]'" :value="item.invoice_item_id">
                            <template v-if="item.product.product_id === undefined"> <!-- bundle type  -->
                                <input type="hidden" :name="'products[' + index + '][product_id]'" :value="item.product.id">
                            </template>
                            <template v-else>
                                <input type="hidden" :name="'products[' + index + '][product_id]'" :value="item.product.product_id">
                                <input type="hidden" :name="'products[' + index + '][product_variant_id]'" :value="item.product.id">
                            </template>
                            <multi-select placeholder="Search Products"
                                v-model="item.product"
                                label="name"
                                :options="productArray.options"
                                @search-change="fetchProducts">
                            </multi-select>
                        </td>
                        <td>
                            <input class="form-control" type="number" min="0" :name="'products[' + index + '][quantity]'" v-model="item.quantity" @input="updateProductSubTotal(item)" required>
                        </td>
                        <td>{{ item.product.selling_price }}</td>
                        <td>{{ item.subTotal.toFixed(2) }}</td>
                        <td>
                            <span class="btn" @click="removeProduct(index)">
                                <i class="uil-trash" style="color: red"></i>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="row container-fluid">
            <div class="col-12 col-md-4 mb-md-0 mb-3">
                <div class="row px-0">
                    <span class="btn px-0 text-start text-primary text-decoration-underline" @click="addProduct()">Add another line</span>
                    <div class="px-0">
                        <label class="form-label px-0">Notes and instructions</label>
                        <textarea class="form-control" name="notes" placeholder="Notes and instructions" rows="5" style="resize: none"></textarea>
                    </div>
                </div>
            </div>
            <div class="col-md-5"></div>
            <div class="col-12 col-md-3">
                <div class="row text-end">
                    <span class="col-7 fw-bold my-auto">Sub Total: </span>
                    <span class="col-5">{{ subTotal.toFixed(2) }}</span>
                    <div class="border my-2"></div>
                    <span class="col-7 fw-bold my-auto">Tax %: </span>
                    <span class="col-5"><input class="form-control" type="number" v-model="taxRate" min="0" max="100" step="1" required></span>
                    <div class="border my-2"></div>
                    <span class="col-7 fw-bold my-auto">Total (SGD): </span>
                    <span class="col-5">{{ grandTotal.toFixed(2) }}</span>
                </div>
            </div>
        </div>
        <div class="row col-12">
            <button type="submit" class="col-12 col-md-1 btn btn-success m-2">
                <template v-if="props.invoice == undefined">
                    Create
                </template>
                <template v-else>
                    Update
                </template>
            </button>
            <a href="/admin/invoices" class="col-12 col-md-1 btn btn-dark m-2">Back</a>
        </div>
    </div>
</template>

<script setup>
import { defineComponent, reactive, ref, onBeforeMount } from 'vue';

let props = defineProps({
    taxRate: Number,
    invoice: Object,
});

const salesOrder = ref({});
const date = ref('');
const paymentTerm = ref('');
const client = ref({});
const subTotal = ref(0);
const grandTotal = ref(0);
const taxRate = ref(props.taxRate);
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
    grandTotal.value *= 1 + (props.taxRate / 100);
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

const salesOrderArray = reactive({ options: [] });
const fetchSalesOrder = (query) => {
    if (query) {
        axios.get(`/web/sales-order?search=${query}`).then((response) => {
            salesOrderArray.options = response.data.response.items;
        });
    }
};

onBeforeMount(() => {
    if (props.invoice !== undefined) {
        if (props.invoice.invoice_items !== undefined) {
            client.value = props.invoice.client
            date.value = props.invoice.date
            paymentTerm.value = props.invoice.payment_term
            products.value = [];
            props.invoice.invoice_items.forEach(invoiceItem => {
                if (invoiceItem.product_variant == null) {
                    // bundles
                    products.value.push({
                        'invoice_item_id': invoiceItem.id,
                        'product': invoiceItem.product,
                        'quantity': invoiceItem.quantity,
                        'subTotal': invoiceItem.quantity * invoiceItem.product.cost_price,
                    });
                } else {
                    // product variants & single products
                    products.value.push({
                        'invoice_item_id': invoiceItem.id,
                        'product': invoiceItem.product_variant,
                        'quantity': invoiceItem.quantity,
                        'subTotal': invoiceItem.quantity * invoiceItem.product_variant.selling_price,
                    });
                }
            });
            updateTotalPrice();
        }
    }
})

</script>
<template>
    <div class="bg-white card shadow py-3 px-4">
        <div class="row border-bottom mb-2">
            <span class="h5">Invoice Information</span>
        </div>
        <div class="row">
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Sales Order (Optional)</label>
                <input type="hidden" name="sales_order_id" :value="salesOrder.id">
                <multi-select v-model="salesOrder" track-by="id" label="id" :options="salesOrders"></multi-select>
            </div>
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Client</label>
                <input type="hidden" name="client_id" :value="client.id">
                <multi-select v-model="client" track-by="name" label="name" :options="clients"></multi-select>
            </div>
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Date</label>
                <input class="form-control" type="date" name="name" placeholder="date" value="{{ old('date') ?? (!empty($invoice) ? $invoice->date : '') }}" required>
            </div>
            <div class="col-12 col-md-6 mb-md-0 mb-2">
                <label class="form-label">Client Address</label>
                <input class="form-control" type="text" placeholder="Client Address" :value="client.address_1 + ' ' + client.address_2" disabled>
            </div>
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Payment Term</label>
                <input class="form-control" type="text" name="payment_term" placeholder="Payment Term" value="">
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
                            <input type="hidden" :name="'product[' + index + '][product_id]'" :value="item.product.id">
                            <input type="hidden" :name="'product[' + index + '][product_variant_id]'" :value="item.product.id" v-if="item.product.id !== 'undefined'">
                            <multi-select v-model="item.product" track-by="name" label="name" :options="props.products"></multi-select>
                        </td>
                        <td>
                            <input class="form-control" type="number" min="0" :name="'product[' + index + '][quantity]'" v-model="item.quantity" @input="updateProductSubTotal(item)" required>
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
            <button type="submit" class="col-12 col-md-1 btn btn-success m-2">Create</button>
            <a href="/admin/invoices" class="col-12 col-md-1 btn btn-dark m-2">Back</a>
        </div>
    </div>
</template>

<script setup>
import { defineComponent, ref } from 'vue';

let props = defineProps({
    salesOrders: Array,
    products: Array,
    taxRate: Number,
    clients: Array,
});

const subTotal = ref(0);
const salesOrder = ref({});
const client = ref({});
const grandTotal = ref(0);
const taxRate = ref(props.taxRate);
const products = ref([{
    'product': '',
    'quantity': 0,
    'subTotal': 0,
}]);

function addProduct() {
    this.products.push({
        'product': '',
        'quantity': 0,
        'subTotal': 0,
    })
}

function removeProduct(index) {
    this.products.splice(index, 1);
    this.updateTotalPrice();
}

function updateProductSubTotal(item) {
    item.subTotal = item.product.selling_price * item.quantity;
    this.updateTotalPrice();
}

function updateTotalPrice() {
    this.subTotal = 0;
    this.grandTotal = 0;
    this.products.forEach(item => {
        this.subTotal += item.subTotal;
        this.grandTotal += item.subTotal;
    });
    this.grandTotal *= 1 + (props.taxRate / 100);
}

</script>
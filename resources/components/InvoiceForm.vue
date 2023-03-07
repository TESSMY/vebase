<template>
    <div class="row mb-2">
        <span class="h4">ADD PRODUCT</span>
    </div>
    <div class="overflow-auto">
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
                    <td>Product Name</td>
                    <td>
                        <input class="form-control" type="number" min="0" :name="'product[' + index + '][quantity]'" v-model="item.quantity" @input="updateProductSubTotal(item)" required>
                    </td>
                    <td>{{ item.product.unit_price }}</td>
                    <td>{{ item.subTotal }}</td>
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
                <span class="btn px-0 text-start text-primary text-decoration-underline" @click="addProducts()">Add another line</span>
                <div class="px-0">
                    <label class="form-label px-0">Notes and instructions</label>
                    <textarea class="form-control" placeholder="Notes and instructions" rows="5" style="resize: none"></textarea>
                </div>
            </div>
        </div>
        <div class="col-md-5"></div>
        <div class="col-12 col-md-3">
            <div class="row text-end">
                <span class="col-8 fw-bold">Sub Total: </span>
                <span class="col-4">{{ subTotal }}</span>
                <div class="border my-2"></div>
                <span class="col-8 fw-bold">Tax: </span>
                <span class="col-4">15%</span>
                <div class="border my-2"></div>
                <span class="col-8 fw-bold">Total (SGD): </span>
                <span class="col-4">{{ grandTotal }}</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'

const subTotal = ref(0);
const grandTotal = ref(0);
const products = ref([{
    'product': {
        unit_price: 1,
    },
    'quantity': 0,
    'subTotal': 0,
}])

function addProducts() {
    this.products.push({
        'product': {
            unit_price: 1,
        },
        'quantity': 0,
        'subTotal': 0,
    })
}

function removeProduct(index) {
    this.products.splice(index, 1);
}

function updateProductSubTotal(item) {
    item.subTotal = item.product.unit_price * item.quantity;
    this.updateTotalPrice();
}

function updateTotalPrice() {
    this.subTotal = 0;
    this.grandTotal = 0;
    this.products.forEach(item => {
        this.subTotal += item.subTotal;
        this.grandTotal += item.subTotal;
    });
}

</script>
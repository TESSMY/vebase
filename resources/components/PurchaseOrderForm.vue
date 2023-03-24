<template>
    <div class="row">
        <div class="bg-white card shadow py-3 px-4">
            <div class="row border-bottom mb-2">
                <span class="h5">PURCHASE ORDER DETAILS</span>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Supplier</label>
                    <!-- <input type="hidden" name="supplier_id" :value="supplier.id">
                    <multi-select v-model="supplier" track-by="id" label="id" :options="suppliers"></multi-select> -->
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Date</label>
                    <input v-if="purchaseOrder" class="form-control" type="date" name="date" placeholder="date" v-model="purchaseOrder.date" required>
                    <input v-else class="form-control" type="date" name="date" placeholder="date" required>
                </div>
                <div class="col-12 col-md-6 mb-md-0 mb-2">
                    <label class="form-label">Supplier Code</label>
                    <input v-if="purchaseOrder" class="form-control" type="text" name="supplier_code" v-model="purchaseOrder.supplier_code" readonly>
                    <input v-else class="form-control" type="text" name="supplier_code" readonly>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Payment Term</label>
                    <input v-if="purchaseOrder" class="form-control" type="text" name="payment_terms" placeholder="Payment Term" v-model="purchaseOrder.payment_terms" required>
                    <input v-else class="form-control" type="text" name="payment_terms" placeholder="Payment Term" required>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Payment Due</label>
                    <input v-if="purchaseOrder" class="form-control" type="date" name="payment_due" v-model="purchaseOrder.payment_due" required>
                    <input v-else class="form-control" type="date" name="payment_due" required>
                </div>
            </div>
        </div>
        <div class="border my-2 mb-3"></div>
        <div class="bg-white card shadow py-3 px-4">
            <div class="row mb-2">
                <span class="h4">ADD PRODUCT</span>
            </div>
            <div class="overflow-auto">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Product Details</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Taxes</th>
                            <th>Total Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in products">
                            <td>
                                <input type="hidden" :name="'product[' + index + '][product_variant_id]'" :value="item.productVariant.id">
                                <input type="hidden" :name="'product[' + index + '][product_id]'" :value="item.id">
                                <multi-select v-model="item.productVariant" track-by="name" label="name" :options="props.productVariants"></multi-select>
                            </td>
                            <td>{{ item.description }}</td>
                            <td>
                                <input class="form-control" type="number" min="0" :name="'product[' + index + '][quantity]'" v-model="item.quantity" @input="updateProductSubTotal(item)" required>
                            </td>
                            <td>{{ item.productVariant.unit_price }}</td>
                            <td>Taxes</td>
                            <td>{{ item.grandTotal }}</td>
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
                            <textarea class="form-control" placeholder="Will be displayed on Purchase Order" rows="5" style="resize: none" name="notes_and_instructions"></textarea>
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
                        <span class="col-7 fw-bold my-auto">GST: </span>
                        <span class="col-5">0.00</span>
                        <div class="border my-2"></div>
                        <span class="col-7 fw-bold my-auto">Total (SGD): </span>
                        <span class="col-5">{{ grandTotal.toFixed(2) }}</span>
                    </div>
                </div>
            </div>

            <input type="hidden" name="status" v-model="status">

            <div class="row col-12">
                <button type="submit" @click="status = 10" class="col-12 col-md-1 btn btn-success m-2">Generate P.O.</button>
                <button type="submit" class="col-12 col-md-1 btn btn-success m-2">Submit Draft</button>
                <a href="/admin/purchase-orders" class="col-12 col-md-1 btn btn-dark m-2">Close</a>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, defineComponent, ref } from 'vue';
let props = defineProps({
    purchaseOrder: Object,
    taxRate: Number,
});
const subTotal = ref(0);
const purchaseOrder = ref(props.purchaseOrder);
const supplier = ref({});
const taxRate = ref(props.taxRate);
const status = ref(0);

const subTotalItem = computed(() => {
    let total = 0
    if (purchaseOrder.value) {
        for (const item of purchaseOrder.value.items) {
        total += (parseFloat(item.product_variant.selling_price) * parseInt(item.quantity))
        }
    }
    return total
});

const grandTotal = computed(() => {
    if (purchaseOrder.value) {
        return subTotalItem.value + (subTotalItem.value * purchaseOrder.value.tax_rate / 100) + gst.value - discount.value
    } else {
        return 0;
    }
});

const products = ref([{
    'productVariant': '',
    'quantity': 0,
    'subTotal': 0,
}]);
function addProducts() {
    this.products.push({
        'productVariant': '',
        'quantity': 0,
        'subTotal': 0,
    })
}
function removeProduct(index) {
    this.products.splice(index, 1);
    this.updateTotalPrice();
}
function updateProductSubTotal(item) {
    item.subTotal = item.productVariant.unit_price * item.quantity;
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

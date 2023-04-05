<template>
    <div class="row">
        <div class="bg-white card shadow py-3 px-4">
            <div class="row border-bottom mb-2">
                <span class="h5">PURCHASE ORDER DETAILS</span>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row mb-3">
                        <label class="col-md-4 text-right form-label text-sm-start">Supplier</label>
                        <div class="col-md-12">
                            <input type="hidden" name="supplier_id" :value="supplier.id">
                            <multi-select placeholder="Search Supplier" v-model="supplier" label="name" :options="supplierArray.options" @search-change="fetchSuppliers" :disabled="purchaseOrder"></multi-select>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Date</label>
                    <input class="form-control" type="date" name="date" v-model="date" required>
                </div>
                <div class="col-12 col-md-6 mb-md-0 mb-2">
                    <label class="form-label">Supplier Code</label>
                    <input class="form-control" type="text" name="supplier_code" v-model="supplierCode" required>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Payment Term</label>
                    <input class="form-control" type="text" name="payment_terms" v-model="paymentTerm" required>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Payment Due</label>
                    <input class="form-control" type="date" name="payment_due" v-model="paymentDue" required>
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
                            <th>Sub Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in products">
                            <td>
                                <input type="hidden" :name="'products[' + index + '][purchase_order_item_id]'" :value="item.purchase_order_item_id">
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
                            <td>{{ item.description }}</td>
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
                            <textarea class="form-control" placeholder="Will be displayed on Purchase Order" rows="5" style="resize: none" name="notes_and_instructions"></textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-4"></div>
                <div class="col-12 col-md-4">
                    <div class="row text-end">
                        <span class="col-4 fw-bold my-auto">Sub Total: </span>
                        <span class="col-8">{{ subTotal.toFixed(2) }}</span>
                        <!-- <div class="border my-2"></div>
                        <span class="col-4 fw-bold my-auto">Tax %: </span>
                        <span class="col-8">
                            <div class="row">
                                <multi-select class="col-7" placeholder="Search Tax"
                                    v-model="taxRate1"
                                    label="name"
                                    :options="taxArray.options"
                                    @search-change="fetchTax"
                                    @input="updateTotalPrice">
                                </multi-select>
                                <div class="col-5" v-if="taxRate1"><input class="form-control" type="number" v-model="taxRate1.tax_rate" min="0" max="100" step="1" required></div>
                            </div>
                        </span>
                        <span class="col-4 fw-bold my-auto">Tax %: </span>
                        <span class="col-8">
                            <div class="row">
                                <multi-select class="col-7" placeholder="Search Tax"
                                    v-model="taxRate2"
                                    label="name"
                                    :options="taxArray.options"
                                    @search-change="fetchTax">
                                </multi-select>
                                <div class="col-5" v-if="taxRate2"><input class="form-control" type="number" v-model="taxRate2.tax_rate" min="0" max="100" step="1" required></div>
                            </div>
                        </span> -->
                        <div class="border my-2"></div>
                        <span class="col-4 fw-bold my-auto">Total (SGD): </span>
                        <span class="col-8">{{ grandTotal.toFixed(2) }}</span>
                    </div>
                </div>
            </div>

            <input type="hidden" name="status" v-model="status">
            <input type="hidden" name="tax_rate" v-model="tax_rate">

            <div class="row col-12">
                <button type="submit" @click="status = 10" class="col-12 col-md-1 btn btn-success m-2">Generate P.O.</button>
                <button type="submit" class="col-12 col-md-1 btn btn-success m-2">Submit Draft</button>
                <a href="/admin/purchase-orders" class="col-12 col-md-1 btn btn-dark m-2">Close</a>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineComponent, reactive, ref, onBeforeMount, computed } from 'vue';

let props = defineProps({
    purchaseOrder: Object,
    tax_rate: Number,
});

const subTotal = ref(0);
const purchaseOrder = ref(props.purchaseOrder);
const supplier = ref({});
const date = ref({});
const supplierCode = ref({});
const paymentTerm = ref({});
const paymentDue = ref({});
const grandTotal = ref(0);
const tax_rate = ref(props.tax_rate);
const taxRate1 = ref('');
const taxRate2 = ref('');
const status = ref(0);
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

const supplierArray = reactive({ options: [] });
const fetchSuppliers = (query) => {
    if (query) {
        axios.get(`/web/suppliers?search=${query}`).then((response) => {
            supplierArray.options = response.data.response.items;
        });
    }
};

onBeforeMount(() => {
    if (props.purchaseOrder !== undefined) {
        if (props.purchaseOrder.purchase_order_items !== undefined) {
            supplier.value = props.purchaseOrder.supplier
            date.value = props.purchaseOrder.date
            supplierCode.value = props.purchaseOrder.supplier_code
            paymentTerm.value = props.purchaseOrder.payment_terms
            paymentDue.value = props.purchaseOrder.payment_due
            products.value = [];

            props.purchaseOrder.purchase_order_items.forEach(purchaseOrderItem => {
                if (purchaseOrderItem.product_variant == null) {
                    // bundles
                    products.value.push({
                        'purchase_order_item_id': purchaseOrderItem.id,
                        'product': purchaseOrderItem.product,
                        'quantity': purchaseOrderItem.quantity,
                        'subTotal': purchaseOrderItem.quantity * purchaseOrderItem.product.cost_price,
                    });
                } else {
                    // product variants & single products
                    products.value.push({
                        'purchase_order_item_id': purchaseOrderItem.id,
                        'product': purchaseOrderItem.product_variant,
                        'quantity': purchaseOrderItem.quantity,
                        'subTotal': purchaseOrderItem.quantity * purchaseOrderItem.product_variant.selling_price,
                    });
                }
            });
        }
    }
})

</script>

<template>
    <div class="row">
        <div class="bg-white card shadow py-3 px-4">
            <div class="row border-bottom mb-2">
                <span class="h5">QUOTATION REQUEST DETAILS</span>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row mb-3">
                        <label class="col-md-4 text-right form-label text-sm-start">Supplier</label>
                        <div class="col-md-12">
                            <input type="hidden" name="supplier_id" :value="supplier.id">
                            <multi-select placeholder="Search Supplier" v-model="supplier" label="name" :options="supplierArray.options" @search-change="fetchSuppliers"></multi-select>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Order Deadline</label>
                    <input v-if="quotationRequest" class="form-control" type="date" name="delivery_date" placeholder="delivery_date" v-model="quotationRequest.delivery_date" required>
                    <input v-else class="form-control" type="date" name="delivery_date" placeholder="Order Deadline" required>
                </div>
                <div class="col-12 col-md-6 mb-md-0 mb-2">
                    <label class="form-label">RFQ Name</label>
                    <input v-if="quotationRequest" class="form-control" type="text" name="rfq_name" v-model="quotationRequest.rfq_name" readonly>
                    <input v-else class="form-control" type="text" name="rfq_name" readonly>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">RFQ Template</label>
                    <input class="form-control" type="text" name="rfq_template" placeholder="" disabled>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Invoice Address</label>
                    <input v-if="quotationRequest" class="form-control" type="text" name="ship_from" v-model="quotationRequest.ship_from" required>
                    <input v-else class="form-control" type="text" name="ship_from" required>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Delivery Address</label>
                    <input v-if="quotationRequest" class="form-control" type="text" name="ship_to" v-model="quotationRequest.ship_to" required>
                    <input v-else class="form-control" type="text" name="ship_to" required>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Payment Term</label>
                    <input v-if="quotationRequest" class="form-control" type="text" name="payment_term" v-model="quotationRequest.payment_term" required>
                    <input v-else class="form-control" type="text" name="payment_term" required>
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
                            <th>Quantity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in products">
                            <td>
                                <template v-if="item.product.product_id === undefined">
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
                                <input class="form-control" type="number" min="0" :name="'products[' + index + '][quantity]'" v-model="item.quantity" required>
                            </td>
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
                            <label class="form-label px-0">Notes and Instructions</label>
                            <textarea class="form-control" placeholder="Will be displayed on Quotation Request" rows="5" style="resize: none" name="notes_and_instructions"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" name="status" v-model="status">
            <div class="row">
                <div class="row col-6">
                    <button type="button" class="col-12 col-md-1 btn btn-primary m-2">Send Email</button>
                    <button type="submit" class="col-12 col-md-1 btn btn-success m-2">Save as Draft</button>
                    <a href="/admin/quotation-requests" class="col-12 col-md-1 btn btn-dark m-2">Close</a>
                </div>
                <div class="row col-6">
                    <button type="submit" @click="status = 2" class="col-12 col-md-1 btn btn-success m-2">Generate P.O.</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineComponent, reactive, ref, onBeforeMount, computed } from 'vue';
let props = defineProps({
    quotationRequest: Object,
    taxRate: Number,
});
const quotationRequest = ref(props.quotationRequest);
const supplier = ref({});
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
const fetchSupplliers = (query) => {
    if (query) {
        axios.get(`/web/suppliers?search=${query}`).then((response) => {
            supplierArray.options = response.data.response.items;
        });
    }
};
</script>

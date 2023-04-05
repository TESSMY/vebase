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
                            <multi-select placeholder="Search Supplier" v-model="supplier" label="name" :options="supplierArray.options" @search-change="fetchSuppliers" :disabled="quotationRequest"></multi-select>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Order Deadline</label>
                    <input class="form-control" type="date" name="quotation_deadline" placeholder="Quotation Deadline" v-model="quotationDeadline" required>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Billing Address</label>
                    <input class="form-control" type="text" name="billing_address" placeholder="Billing Address" v-model="billingAddress" required>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Shipping Address</label>
                    <input class="form-control" type="text" name="shipping_address" placeholder="Shipping Address" v-model="shippingAddress" required>
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
                                    @search-change="fetchProducts"
                                    :disabled="quotationRequest">
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
                            <textarea class="form-control" placeholder="Will be displayed on Quotation Request" rows="5" style="resize: none" name="notes_and_instructions">{{ notes }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" name="status" v-model="status">
            <div class="row">
                <div class="row col-6">
                    <button type="button" @click="status = 30" data-bs-toggle="modal" data-bs-target="#sendEmailModal" class="col-12 col-md-2 btn btn-primary m-2">Send Email</button>
                    <button type="submit" class="col-12 col-md-2 btn btn-success m-2">Save as Draft</button>
                    <a href="/admin/quotation-requests" class="col-12 col-md-2"><button class="btn btn-dark m-2">Close</button></a>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="sendEmailModal" tabindex="-1" aria-labelledby="sendEmailModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Send Quotation Request</h4>
                        </div>
                        <div class="modal-body">
                            <div class="row p-2">
                                <div class="col-md-4 md:text-right">
                                    <label class="py-2">To Email: </label>
                                </div>
                                <div class="col-md-8">
                                    <input type="email" name="to_email" class="form-control"/>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-primary ml-auto">
                                Submit Quotation Request
                            </button>
                        </div>
                    </div>
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
const quotationDeadline = ref('');
const billingAddress = ref('');
const shippingAddress = ref('');
const notes = ref('');
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

onBeforeMount(() => {
    if (props.quotationRequest !== undefined) {
        if (props.quotationRequest.quotation_request_items !== undefined) {
            supplier.value = props.quotationRequest.supplier
            quotationDeadline.value = props.quotationRequest.quotation_deadline
            billingAddress.value = props.quotationRequest.billing_address
            shippingAddress.value = props.quotationRequest.shipping_address
            notes.value = props.quotationRequest.notes_and_instructions
            products.value = [];

            props.quotationRequest.quotation_request_items.forEach(quotationRequestItem => {
                if (quotationRequestItem.product_variant == null) {
                    // bundles
                    products.value.push({
                        'quotation_request_id': quotationRequest.id,
                        'product': quotationRequestItem.product,
                        'quantity': quotationRequestItem.quantity,
                        'subTotal': quotationRequestItem.quantity * quotationRequestItem.product.cost_price,
                    });
                } else {
                    // product variants & single products
                    products.value.push({
                        'quotation_request_id': quotationRequest.id,
                        'product': quotationRequestItem.product_variant,
                        'quantity': quotationRequestItem.quantity,
                        'subTotal': quotationRequestItem.quantity * quotationRequestItem.product_variant.selling_price,
                    });
                }
            });
        }
    }
})
</script>

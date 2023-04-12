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
                            <input type="hidden" name="supplier_id" :value="quotationRequest.supplier.id">
                            <multi-select placeholder="Search Supplier" v-model="quotationRequest.supplier" label="name" :options="supplierArray.options" @search-change="fetchSuppliers" :disabled="quotationRequest"></multi-select>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Order Deadline</label>
                    <input class="form-control" type="date" name="quotation_deadline" placeholder="Quotation Deadline" v-model="quotationRequest.quotation_deadline" :disabled="isNotEditable" required>
                </div>
                <div class="col-md-12">
                    <div class="form-group row mb-3">
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Billing Name</label>
                            <input class="form-control" type="text" name="billing_name" v-model="quotationRequest.billing_name" :disabled="isNotEditable" required>
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Billing Contact Number</label>
                            <input class="form-control" type="text" name="billing_contact_number" v-model="quotationRequest.billing_contact_number" :disabled="isNotEditable" required>
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Billing Contact Email</label>
                            <input class="form-control" type="email" name="billing_contact_email" v-model="quotationRequest.billing_contact_email" :disabled="isNotEditable" required>
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Billing Address</label>
                            <input class="form-control" type="text" name="billing_address_1" v-model="quotationRequest.billing_address_1" :disabled="isNotEditable" required>
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Billing City</label>
                            <input class="form-control" type="text" name="billing_city" v-model="quotationRequest.billing_city" :disabled="isNotEditable">
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Billing State</label>
                            <input class="form-control" type="text" name="billing_state" v-model="quotationRequest.billing_state" :disabled="isNotEditable">
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Billing Postcode</label>
                            <input class="form-control" type="text" name="billing_postcode" v-model="quotationRequest.billing_postcode" :disabled="isNotEditable">
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Billing Country</label>
                            <input class="form-control" type="text" name="billing_country" v-model="quotationRequest.billing_country" :disabled="isNotEditable">
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group row mb-3">
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Ship To Name</label>
                            <input class="form-control" type="text" name="ship_to_name" v-model="quotationRequest.ship_to_name" required :disabled="isNotEditable">
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Ship To Contact Number</label>
                            <input class="form-control" type="text" name="ship_to_contact_number" v-model="quotationRequest.ship_to_contact_number" required :disabled="isNotEditable">
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Ship To Contact Email</label>
                            <input class="form-control" type="email" name="ship_to_contact_email" v-model="quotationRequest.ship_to_contact_email" required :disabled="isNotEditable">
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Ship To Address</label>
                            <input class="form-control" type="text" name="ship_to_address_1" v-model="quotationRequest.ship_to_address_1" required :disabled="isNotEditable">
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Ship To City</label>
                            <input class="form-control" type="text" name="ship_to_city" v-model="quotationRequest.ship_to_city" :disabled="isNotEditable">
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Ship To State</label>
                            <input class="form-control" type="text" name="ship_to_state" v-model="quotationRequest.ship_to_state" :disabled="isNotEditable">
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Ship To Postcode</label>
                            <input class="form-control" type="text" name="ship_to_postcode" v-model="quotationRequest.ship_to_postcode" :disabled="isNotEditable">
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Ship To Country</label>
                            <input class="form-control" type="text" name="ship_to_country" v-model="quotationRequest.ship_to_country" :disabled="isNotEditable">
                        </div>
                    </div>
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
                                <input class="form-control" type="number" min="0" :name="'products[' + index + '][quantity]'" v-model="item.quantity" :disabled="isNotEditable" required>
                            </td>
                            <td>
                                <span class="btn" @click="removeProduct(index)" v-if="!isNotEditable">
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
                        <span class="btn px-0 text-start text-primary text-decoration-underline" @click="addProduct()" v-if="!isNotEditable">Add another line</span>
                        <div class="px-0">
                            <label class="form-label px-0">Notes and Instructions</label>
                            <textarea class="form-control" placeholder="Will be displayed on Quotation Request" rows="5" style="resize: none" name="notes" :disabled="isNotEditable">{{ quotationRequest.notes }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" name="send_email" v-model="send_email">
            <div class="row">
                <div class="row col-6">
                    <button type="button" v-if="!isNotEditable" @click="send_email = true" data-bs-toggle="modal" data-bs-target="#sendEmailModal" class="col-12 col-md-2 btn btn-primary m-2">Send Email</button>
                    <button type="submit" v-if="!isNotEditable && !isNotDraft" class="col-12 col-md-2 btn btn-success m-2">Save as Draft</button>
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
const quotationRequest = ref({
    'supplier': '',
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
    'notes': '',
});
const send_email = ref(0);
const products = ref([{
    'product': '',
    'quantity': 0,
    'subTotal': 0,
}]);
const isNotEditable = ref(0);
const isNotDraft = ref(0);

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
        quotationRequest.value = props.quotationRequest;

        if (quotationRequest.value.status == 20) {
            isNotEditable.value = true;
        } else {
            isNotEditable.value = false;
        }

        if (quotationRequest.value.status != 0) {
            isNotDraft.value = true;
        } else {
            isNotDraft.value = false;
        }

        if (props.quotationRequest.quotation_request_items !== undefined) {
            products.value = [];
            props.quotationRequest.quotation_request_items.forEach(quotationRequestItem => {
                if (quotationRequestItem.product_variant == null) {
                    // bundles
                    products.value.push({
                        'quotation_request_id': quotationRequest.id,
                        'product': quotationRequestItem.product,
                        'quantity': quotationRequestItem.quantity,
                    });
                } else {
                    // product variants & single products
                    products.value.push({
                        'quotation_request_id': quotationRequest.id,
                        'product': quotationRequestItem.product_variant,
                        'quantity': quotationRequestItem.quantity,
                    });
                }
            });
        }
    }
})
</script>

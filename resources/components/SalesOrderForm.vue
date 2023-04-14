<template>
    <div class="row">
        <div class="bg-white card shadow py-3 px-4">
            <div class="row border-bottom mb-2">
                <span class="h5">SALES ORDER DETAILS</span>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row mb-3">
                        <label class="col-md-4 text-right form-label text-sm-start">Client</label>
                        <div class="col-md-12">
                            <input type="hidden" name="client_id" :value="salesOrder.client_id">
                            <multi-select placeholder="Search Client" v-model="salesOrder.client" label="name" :options="clientArray.options" @search-change="fetchClients" :disabled="salesOrder"></multi-select>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Date</label>
                    <input class="form-control" type="date" name="date" v-model="salesOrder.date" required :disabled="isNotEditable">
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Billing Name</label>
                    <input class="form-control" type="text" name="billing_name" v-model="salesOrder.billing_name" required :disabled="isNotEditable">
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Billing Contact Number</label>
                    <input class="form-control" type="text" name="billing_contact_number" v-model="salesOrder.billing_contact_number" required :disabled="isNotEditable">
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Billing Contact Email</label>
                    <input class="form-control" type="email" name="billing_contact_email" v-model="salesOrder.billing_contact_email" required :disabled="isNotEditable">
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Billing Address</label>
                    <input class="form-control" type="text" name="billing_address_1" v-model="salesOrder.billing_address_1" required :disabled="isNotEditable">
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Billing City</label>
                    <input class="form-control" type="text" name="billing_city" v-model="salesOrder.billing_city" :disabled="isNotEditable">
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Billing State</label>
                    <input class="form-control" type="text" name="billing_state" v-model="salesOrder.billing_state" :disabled="isNotEditable">
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Billing Postcode</label>
                    <input class="form-control" type="text" name="billing_postcode" v-model="salesOrder.billing_postcode" :disabled="isNotEditable">
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Billing Country</label>
                    <input class="form-control" type="text" name="billing_country" v-model="salesOrder.billing_country" :disabled="isNotEditable">
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Ship To Name</label>
                    <input class="form-control" type="text" name="ship_to_name" v-model="salesOrder.ship_to_name" required :disabled="isNotEditable">
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Ship To Contact Number</label>
                    <input class="form-control" type="text" name="ship_to_contact_number" v-model="salesOrder.ship_to_contact_number" required :disabled="isNotEditable">
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Ship To Contact Email</label>
                    <input class="form-control" type="email" name="ship_to_contact_email" v-model="salesOrder.ship_to_contact_email" required :disabled="isNotEditable">
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Ship To Address</label>
                    <input class="form-control" type="text" name="ship_to_address_1" v-model="salesOrder.ship_to_address_1" required :disabled="isNotEditable">
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Ship To City</label>
                    <input class="form-control" type="text" name="ship_to_city" v-model="salesOrder.ship_to_city" :disabled="isNotEditable">
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Ship To State</label>
                    <input class="form-control" type="text" name="ship_to_state" v-model="salesOrder.ship_to_state" :disabled="isNotEditable">
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Ship To Postcode</label>
                    <input class="form-control" type="text" name="ship_to_postcode" v-model="salesOrder.ship_to_postcode" :disabled="isNotEditable">
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Ship To Country</label>
                    <input class="form-control" type="text" name="ship_to_country" v-model="salesOrder.ship_to_country" :disabled="isNotEditable">
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
                                <input type="hidden" :name="'products[' + index + '][sales_order_item_id]'" :value="item.sales_order_item_id">
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
                                    @search-change="fetchProducts"
                                    :disabled="isNotEditable">
                                </multi-select>
                            </td>
                            <td>{{ item.description }}</td>
                            <td>
                                <input class="form-control" type="number" min="0" :name="'products[' + index + '][quantity]'" v-model="item.quantity" @input="updateProductSubTotal(item)" required :disabled="isNotEditable">
                            </td>
                            <td>{{ item.product.selling_price }}</td>
                            <td>{{ item.subTotal.toFixed(2) }}</td>
                            <td v-if="!isNotEditable">
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
                        <span v-if="!isNotEditable" class="btn px-0 text-start text-primary text-decoration-underline" @click="addProduct()">Add another line</span>
                        <div class="px-0">
                            <label class="form-label px-0">Notes and instructions</label>
                            <textarea class="form-control" placeholder="Will be displayed on Sales Order" rows="5" style="resize: none" name="notes" :disabled="isNotEditable">{{ salesOrder.notes }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-4"></div>
                <div class="col-12 col-md-4">
                    <div class="row text-end">
                        <span class="col-4 fw-bold my-auto">Sub Total: </span>
                        <span class="col-8">{{ Number(salesOrder.sub_total).toFixed(2) }}</span>
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
                        <span class="col-8">{{ Number(salesOrder.grand_total).toFixed(2) }}</span>
                    </div>
                </div>
            </div>

            <input type="hidden" name="is_draft" v-model="isDraft">
            <input type="hidden" name="tax_rate" v-model="salesOrder.tax_rate">

            <div class="row col-12">
                <button v-if="!isNotEditable" type="submit" class="col-12 col-md-1 btn btn-success m-2">Next</button>
                <button v-if="!isNotEditable" type="submit" @click="isDraft = 1" class="col-12 col-md-1 btn btn-success m-2">Submit Draft</button>
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
    'currency': '',
    'tax_rate': 7,
    'sub_total': '',
    'grand_total': '',
    'date': '',
    'notes': '',
});
const products = ref([{
    'product': '',
    'quantity': 0,
    'subTotal': 0,
}]);
const isDraft = ref(0);
const isNotEditable = ref(0);

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
    salesOrder.value.sub_total = 0;
    salesOrder.value.grand_total = 0;
    products.value.forEach(item => {
        salesOrder.value.sub_total += item.subTotal;
        salesOrder.value.grand_total += item.subTotal;
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

onBeforeMount(() => {
    if (props.salesOrder !== undefined) {
        salesOrder.value = props.salesOrder;

        if (salesOrder.value.status != 0) {
            isNotEditable.value = true;
        } else {
            isNotEditable.value = false;
        }

        if (props.salesOrder.sales_order_items !== undefined) {
            products.value = [];
            props.salesOrder.sales_order_items.forEach(salesOrderItem => {
                if (salesOrderItem.product_variant == null) {
                    // bundles
                    products.value.push({
                        'sales_order_item_id': salesOrderItem.id,
                        'product': salesOrderItem.product,
                        'quantity': salesOrderItem.quantity,
                        'subTotal': salesOrderItem.quantity * salesOrderItem.product.cost_price,
                    });
                } else {
                    // product variants & single products
                    products.value.push({
                        'sales_order_item_id': salesOrderItem.id,
                        'product': salesOrderItem.product_variant,
                        'quantity': salesOrderItem.quantity,
                        'subTotal': salesOrderItem.quantity * salesOrderItem.product_variant.selling_price,
                    });
                }
            });
        }
    }
})

</script>

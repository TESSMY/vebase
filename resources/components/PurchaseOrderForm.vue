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
                            <input type="hidden" name="supplier_id" :value="purchaseOrder.supplier.id">
                            <multi-select placeholder="Search Supplier" v-model="purchaseOrder.supplier" label="name" :options="supplierArray.options" @search-change="fetchSuppliers" :disabled="purchaseOrder.supplier_id"></multi-select>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6" v-if="purchaseOrder.shipment_type == 1">
                    <div class="form-group row mb-3">
                        <label class="col-md-4 text-right form-label text-sm-start">Client</label>
                        <div class="col-md-12">
                            <input type="hidden" name="client_id" :value="purchaseOrder.client.id">
                            <multi-select placeholder="Search Client" v-model="purchaseOrder.client" label="name" :options="clientArray.options" @search-change="fetchClients" :disabled="purchaseOrder.client_id"></multi-select>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Date</label>
                    <input class="form-control" type="date" name="date" v-model="purchaseOrder.date" required :disabled="isNotEditable">
                </div>
                <div class="col-12 col-md-6 mb-md-0 mb-2">
                    <label class="form-label">Supplier Code</label>
                    <input class="form-control" type="text" name="supplier_code" placeholder="Supplier Code" v-model="purchaseOrder.supplier_code" required :disabled="isNotEditable">
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Payment Term</label>
                    <input class="form-control" type="text" name="payment_term" placeholder="Payment Term" v-model="purchaseOrder.payment_term" required :disabled="isNotEditable">
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Payment Due</label>
                    <input class="form-control" type="date" name="payment_due" v-model="purchaseOrder.payment_due" required :disabled="isNotEditable">
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Currency</label>
                    <input class="form-control" type="text" name="currency" v-model="purchaseOrder.currency" :disabled="isNotEditable">
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Shipment Type</label>
                    <select class="form-select" name="shipment_type" v-model="purchaseOrder.shipment_type" required :disabled="isNotEditable">
                        <option selected value="0">Direct</option>
                        <option value="1">Non Direct</option>
                    </select>
                </div>
                <div class="col-md-12 my-2">
                    <div class="form-group row mb-3">
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Supplier Name</label>
                            <input class="form-control" type="text" name="supplier_name" v-model="purchaseOrder.supplier_name" :disabled="isNotEditable" required>
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Supplier Contact Number</label>
                            <input class="form-control" type="text" name="supplier_contact_number" v-model="purchaseOrder.supplier_contact_number" :disabled="isNotEditable" required>
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Supplier Contact Email</label>
                            <input class="form-control" type="email" name="supplier_contact_email" v-model="purchaseOrder.supplier_contact_email" :disabled="isNotEditable" required>
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Supplier Address 1</label>
                            <input class="form-control" type="text" name="supplier_address_1" v-model="purchaseOrder.supplier_address_1" :disabled="isNotEditable" required>
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Supplier Address 2</label>
                            <input class="form-control" type="text" name="billing_address_2" v-model="purchaseOrder.supplier_address_2" :disabled="isNotEditable" required>
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Supplier City</label>
                            <input class="form-control" type="text" name="supplier_city" v-model="purchaseOrder.supplier_city" :disabled="isNotEditable">
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Supplier State</label>
                            <input class="form-control" type="text" name="supplier_state" v-model="purchaseOrder.supplier_state" :disabled="isNotEditable">
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Supplier Postcode</label>
                            <input class="form-control" type="text" name="supplier_postcode" v-model="purchaseOrder.supplier_postcode" :disabled="isNotEditable">
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Supplier Country</label>
                            <input class="form-control" type="text" name="supplier_country" v-model="purchaseOrder.supplier_country" :disabled="isNotEditable">
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group row mb-3">
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Ship To Name</label>
                            <input class="form-control" type="text" name="ship_to_name" v-model="purchaseOrder.ship_to_name" required :disabled="isNotEditable">
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Ship To Contact Number</label>
                            <input class="form-control" type="text" name="ship_to_contact_number" v-model="purchaseOrder.ship_to_contact_number" required :disabled="isNotEditable">
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Ship To Contact Email</label>
                            <input class="form-control" type="email" name="ship_to_contact_email" v-model="purchaseOrder.ship_to_contact_email" required :disabled="isNotEditable">
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Ship To Address 1</label>
                            <input class="form-control" type="text" name="ship_to_address_1" v-model="purchaseOrder.ship_to_address_1" required :disabled="isNotEditable">
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Ship To Address 2</label>
                            <input class="form-control" type="text" name="ship_to_address_2" v-model="purchaseOrder.ship_to_address_2" required :disabled="isNotEditable">
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Ship To City</label>
                            <input class="form-control" type="text" name="ship_to_city" v-model="purchaseOrder.ship_to_city" :disabled="isNotEditable">
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Ship To State</label>
                            <input class="form-control" type="text" name="ship_to_state" v-model="purchaseOrder.ship_to_state" :disabled="isNotEditable">
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Ship To Postcode</label>
                            <input class="form-control" type="text" name="ship_to_postcode" v-model="purchaseOrder.ship_to_postcode" :disabled="isNotEditable">
                        </div>
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label">Ship To Country</label>
                            <input class="form-control" type="text" name="ship_to_country" v-model="purchaseOrder.ship_to_country" :disabled="isNotEditable">
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status" v-model="purchaseOrder.status" :disabled="isNotEditable">
                        <option selected value="0">Draft</option>
                        <option value="10">Pending</option>
                        <option value="20">Partially Received</option>
                        <option value="30">Order Completed</option>
                        <option value="40">Cancelled</option>
                        <option value="50">Rejected</option>
                    </select>
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
                            <td>
                                <span v-if="!isNotEditable" class="btn" @click="removeProduct(index)">
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
                            <textarea class="form-control" placeholder="Will be displayed on Purchase Order" rows="5" style="resize: none" name="notes" :disabled="isNotEditable">{{ purchaseOrder.notes }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-4"></div>
                <div class="col-12 col-md-4">
                    <div class="row text-end">
                        <span class="col-4 fw-bold my-auto">Sub Total: </span>
                        <span class="col-8">{{ Number(purchaseOrder.sub_total).toFixed(2) }}</span>
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
                        <span class="col-8">{{ Number(purchaseOrder.grand_total).toFixed(2) }}</span>
                    </div>
                </div>
            </div>

            <input type="hidden" name="tax_rate" v-model="purchaseOrder.tax_rate">

            <div class="row col-12" v-if="!isNotEditable">
                <button type="submit" @click="purchaseOrder.status = 10" class="col-12 col-md-1 btn btn-success m-2">Generate P.O.</button>
                <button type="submit" @click="purchaseOrder.status = 0" class="col-12 col-md-1 btn btn-success m-2">Submit Draft</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineComponent, reactive, ref, onBeforeMount, computed } from 'vue';

let props = defineProps({
    purchaseOrder: Object,
});

const purchaseOrder = ref({
    'supplier': {
        'id': ''
    },
    'client': {
        'id': ''
    },
    'supplier_name': '',
    'supplier_contact_number': '',
    'supplier_contact_email': '',
    'supplier_address_1': '',
    'supplier_address_2': '',
    'supplier_city': '',
    'supplier_state': '',
    'supplier_postcode': '',
    'supplier_country': '',
    'ship_to_name': '',
    'ship_to_contact_number': '',
    'ship_to_contact_email': '',
    'ship_to_address_1': '',
    'ship_to_address_2': '',
    'ship_to_city': '',
    'ship_to_state': '',
    'ship_to_postcode': '',
    'ship_to_country': '',
    'notes': '',
    'payment_terms': '',
    'payment_due': '',
    'currency': '',
    'discount_amount': '',
    'shipping_handling': '',
    'other_cost': '',
    'tax_rate': 7,
    'tax_amount': '',
    'sub_total': '',
    'grand_total': '',
    'total_cost': '',
    'supplier_code': '',
    'date': '',
    'notes': '',
    'shipment_type': '',
    'status': '',
});
const products = ref([{
    'product': '',
    'quantity': 0,
    'subTotal': 0,
}]);
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
    purchaseOrder.value.sub_total = 0;
    purchaseOrder.value.grand_total = 0;
    products.value.forEach(item => {
        purchaseOrder.value.sub_total += item.subTotal;
        purchaseOrder.value.grand_total += item.subTotal;
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
                    product.product_variants.forEach(variant => {
                        productArray.options.push(variant)
                    });
                }
            });
        });
    } else {
        axios.get(`/web/products`).then((response) => {
            productArray.options = []
            response.data.response.items.forEach(product => {
                if (product.type == 3) { // bundle type
                    productArray.options.push(product);
                } else {
                    product.product_variants.forEach(variant => {
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
    } else {
        axios.get(`/web/suppliers`).then((response) => {
            supplierArray.options = response.data.response.items;
        });
    }
};

const clientArray = reactive({ options: [] });
const fetchClients = (query) => {
    if (query) {
        axios.get(`/web/clients?search=${query}`).then((response) => {
            clientArray.options = response.data.response.items;
        });
    } else {
        axios.get(`/web/clients`).then((response) => {
            clientArray.options = response.data.response.items;
        });
    }
};

onBeforeMount(() => {
    fetchProducts();
    fetchSuppliers();
    fetchClients();
    if (props.purchaseOrder !== undefined) {
        purchaseOrder.value = props.purchaseOrder;

        if (purchaseOrder.value.status == 10) {
            isNotEditable.value = true;
        } else {
            isNotEditable.value = false;
        }

        if (props.purchaseOrder.purchase_order_items !== undefined) {
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

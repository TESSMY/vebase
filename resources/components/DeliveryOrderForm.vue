<template>
    <div class="bg-white card shadow py-3 px-4">
        <div class="row border-bottom mb-2">
            <span class="h5">Delivery Order Information</span>
        </div>
        <div class="row mb-3">
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Client</label>
                <input type="hidden" name="client_id" :value="client.id">
                <multi-select placeholder="Search Client" v-model="client" label="name" :options="clientArray.options" @search-change="fetchClients" @select="setDeliveryAddress"></multi-select>
            </div>
            <div class="col-12 col-md-6 mb-md-0 mb-2">
                <label class="form-label">Client P.O (optional)</label>
                <input class="form-control" type="text" placeholder="Client P.O" name="client_po">
            </div>
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Date</label>
                <input class="form-control" type="date" name="date" placeholder="date" v-model="date" required>
            </div>
             <div class="col-12 col-md-6 mb-md-0 mb-2">
                <label class="form-label">Created By</label>
                <template v-if="props.deliveryOrder == undefined">
                    <input class="form-control" type="text" placeholder="Created By" :value="user.name" disabled>
                </template>
                <template v-else>
                    <template v-if="deliveryOrder.created_by">
                        <input class="form-control" type="text" placeholder="Created By" :value="deliveryOrder.created_by.name" disabled>
                    </template>
                    <template v-else>
                        <input class="form-control" type="text" placeholder="Created By" value="System Generated" disabled>
                    </template>
                </template>
            </div>
        </div>
        <div class="row border-bottom mb-2">
            <span class="h5">Delivery Address</span>
        </div>
        <div class="row">
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Receiver Name</label>
                <input class="form-control" type="text" placeholder="Receiver Name" name="ship_to_name" v-model="delivery.ship_to_name" required>
            </div>
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Receiver Contact Number</label>
                <input class="form-control" type="text" placeholder="Receiver Contact Number" name="ship_to_contact_number" v-model="delivery.ship_to_contact_number">
            </div>
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Receiver Email Address</label>
                <input class="form-control" type="text" placeholder="Receiver Email Address" name="ship_to_email_address" v-model="delivery.ship_to_email_address">
            </div>
            <div class="col-12 col-md-6 mb-2"></div>
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Delivery Address 1</label>
                <input class="form-control" type="text" placeholder="Delivery Address 1" name="ship_to_address_1" v-model="delivery.ship_to_address_1" required>
            </div>
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Delivery Address 2</label>
                <input class="form-control" type="text" placeholder="Delivery Address 2" name="ship_to_address_2" v-model="delivery.ship_to_address_2">
            </div>
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">City</label>
                <input class="form-control" type="text" placeholder="City" name="ship_to_city" v-model="delivery.ship_to_city">
            </div>
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">State</label>
                <input class="form-control" type="text" placeholder="State" name="ship_to_state" v-model="delivery.ship_to_state">
            </div>
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Postcode</label>
                <input class="form-control" type="text" placeholder="Postcode" name="ship_to_postcode" v-model="delivery.ship_to_postcode" required>
            </div>
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Country</label>
                <input class="form-control" type="text" placeholder="Country" name="ship_to_country" value="Singapore" readonly>
            </div>
        </div>
    </div>
    <div class="border my-2 mb-3"></div>
    <div class="bg-white card shadow py-3 px-4">
        <div class="row mb-2">
            <span class="h4">Add Product</span>
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
                            <input type="hidden" :name="'products[' + index + '][delivery_order_item_id]'" :value="item.delivery_order_item_id">
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
                        <textarea class="form-control" name="notes" placeholder="Notes and instructions" rows="5" style="resize: none">{{ notes }}</textarea>
                    </div>
                </div>
            </div>
            <div class="col-md-4"></div>
            <div class="col-12 col-md-4">
                <div class="row text-end">
                    <span class="col-4 fw-bold my-auto">Sub Total: </span>
                    <span class="col-8">{{ subTotal.toFixed(2) }}</span>
                    <div class="border my-2"></div>
                    <span class="col-4 fw-bold my-auto">Total (SGD): </span>
                    <span class="col-8">{{ grandTotal.toFixed(2) }}</span>
                </div>
            </div>
        </div>
        <div class="row col-12">
            <button type="submit" class="col-12 col-md-1 btn btn-success m-2">
                <template v-if="props.deliveryOrder == undefined">
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
import { defineComponent, reactive, ref, onBeforeMount, computed, onMounted } from 'vue';

let props = defineProps({
    deliveryOrder: Object,
    user: Object,
});

const date = ref('');
const notes = ref('');
const client = ref({});
const subTotal = ref(0);
const grandTotal = ref(0);
const products = ref([{
    'product': '',
    'quantity': 0,
    'subTotal': 0,
}]);
const delivery = ref({
    'ship_to_name': '',
    'ship_to_contact_number': '',
    'ship_to_email_address': '',
    'ship_to_address_1': '',
    'ship_to_address_2': '',
    'ship_to_city': '',
    'ship_to_state': '',
    'ship_to_postcode': '',
    'ship_to_country': '',
});

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

const setDeliveryAddress = (selectedOption) => {
    delivery.value.ship_to_name = selectedOption.name
    delivery.value.ship_to_contact_number = selectedOption.phone
    delivery.value.ship_to_email_address = selectedOption.email
    delivery.value.ship_to_address_1 = selectedOption.address_1
    delivery.value.ship_to_address_2 = selectedOption.address_2
    delivery.value.ship_to_city = selectedOption.city
    delivery.value.ship_to_state = selectedOption.state
    delivery.value.ship_to_postcode = selectedOption.postcode
    delivery.value.ship_to_country = selectedOption.country
}

onBeforeMount(() => {
    if (props.deliveryOrder !== undefined) {
        if (props.deliveryOrder.items !== undefined) {
            client.value = props.deliveryOrder.client
            date.value = props.deliveryOrder.date
            notes.value = props.deliveryOrder.notes
            products.value = [];
            delivery.value.ship_to_contact_number = props.deliveryOrder.ship_to_contact_number
            delivery.value.ship_to_email_address = props.deliveryOrder.ship_to_email_address
            delivery.value.ship_to_address_1 = props.deliveryOrder.ship_to_address_1
            delivery.value.ship_to_address_2 = props.deliveryOrder.ship_to_address_2
            delivery.value.ship_to_city = props.deliveryOrder.ship_to_city
            delivery.value.ship_to_state = props.deliveryOrder.ship_to_state
            delivery.value.ship_to_postcode = props.deliveryOrder.ship_to_postcode
            delivery.value.ship_to_country = props.deliveryOrder.ship_to_country
            props.deliveryOrder.items.forEach(deliveryItem => {
                if (deliveryItem.product_variant == null) {
                    // bundles
                    products.value.push({
                        'delivery_order_item_id': deliveryItem.id,
                        'product': deliveryItem.product,
                        'quantity': deliveryItem.quantity,
                        'subTotal': deliveryItem.quantity * deliveryItem.product.cost_price,
                    });
                } else {
                    // product variants & single products
                    products.value.push({
                        'delivery_order_item_id': deliveryItem.id,
                        'product': deliveryItem.product_variant,
                        'quantity': deliveryItem.quantity,
                        'subTotal': deliveryItem.quantity * deliveryItem.product_variant.selling_price,
                    });
                }
            });
            updateTotalPrice();
        }
    }
})

onMounted(() => {
    fetchProducts();
    fetchClients();
})

</script>
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
                            <input type="hidden" name="supplier_id" :value="selectedSupplier.id">
                            <multi-select placeholder="Search Supplier" v-model="selectedSupplier" label="name" :options="supplierArray" @search-change="fetchSuppliers"></multi-select>
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
                            <th>Description</th>
                            <th>Quantity</th>
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
import { computed, defineComponent, ref } from 'vue';
let props = defineProps({
    quotationRequest: Object,
    taxRate: Number,
});
const subTotal = ref(0);
const quotationRequest = ref(props.quotationRequest);
const supplier = ref({});
const taxRate = ref(props.taxRate);
const status =ref(0);
const supplierArray = ref([]);
const selectedSupplier = ref('');

const subTotalItem = computed(() => {
    let total = 0
    if (quotationRequest.value) {
        for (const item of quotationRequest.value.items) {
        total += (parseFloat(item.product_variant.selling_price) * parseInt(item.quantity))
        }
    }
    return total
});

const grandTotal = computed(() => {
    if (quotationRequest.value) {
        return subTotalItem.value + (subTotalItem.value * quotationRequest.value.tax_rate / 100) + gst.value - discount.value
    } else {
        return 0;
    }
});

// const products = ref([{
//     'productVariant': '',
//     'quantity': 0,
//     'subTotal': 0,
// }]);

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

function fetchSuppliers(query) {
    if (query) {
        axios.get(`/web/suppliers?search=${query}`).then((response) => {
            this.supplierArray = response.data.response.items;
        });
    }
}
</script>

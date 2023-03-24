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
                            <input type="hidden" name="client_id" :value="selectedClient.id">
                            <multi-select placeholder="Search Client" v-model="selectedClient" label="name" :options="clientArray" @search-change="fetchClients"></multi-select>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Date</label>
                    <input v-if="salesOrder" class="form-control" type="date" name="date" placeholder="date" v-model="salesOrder.date" required>
                    <input v-else class="form-control" type="date" name="date" placeholder="date" required>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Customer PO</label>
                    <input v-if="salesOrder" class="form-control" type="text" name="client_address" placeholder="Enter Customer P.O" v-model="salesOrder.client.address_1" required>
                    <input v-else class="form-control" type="text" name="client_address" placeholder="Enter Customer P.O" required>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Customer Name</label>
                    <input v-if="salesOrder" class="form-control" type="text" name="client_name" placeholder="Enter Customer Name" v-model="salesOrder.client.name" required>
                    <input v-else class="form-control" type="text" name="client_name" placeholder="Enter Customer Name" required>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Payment Term</label>
                    <input v-if="salesOrder" class="form-control" type="text" name="payment_terms" placeholder="Payment Term" v-model="salesOrder.payment_terms">
                    <input v-else class="form-control" type="text" name="payment_terms" placeholder="Payment Term">
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Payment Due</label>
                    <input v-if="salesOrder" class="form-control" type="date" name="payment_due" v-model="salesOrder.payment_due">
                    <input v-else class="form-control" type="date" name="payment_due">
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Packed By Date</label>
                    <input v-if="salesOrder" class="form-control" type="date" name="packed_by_date" v-model="salesOrder.packed_by_date" required>
                    <input v-else class="form-control" type="date" name="packed_by_date" required>
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
                                <input type="hidden" :name="'products[' + index + '][product_variant_id]'" :value="item.productVariant.id">
                                <input type="hidden" :name="'products[' + index + '][product_id]'" :value="item.id">
                                <multi-select v-model="item.productVariant" track-by="name" label="name" :options="props.productVariants"></multi-select>
                            </td>
                            <td>{{ item.description }}</td>
                            <td>
                                <input class="form-control" type="number" min="0" :name="'products[' + index + '][quantity]'" v-model="item.quantity" @input="updateProductSubTotal(item)" required>
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
                            <textarea class="form-control" placeholder="Will be displayed on Sales Order" rows="5" style="resize: none" name="notes_and_instructions"></textarea>
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
                        <span class="col-5"><input class="form-control" type="number" v-model="tax_rate" min="0" max="100" step="1" required></span>
                        <span class="col-7 fw-bold my-auto">GST: </span>
                        <span class="col-5">0.00</span>
                        <div class="border my-2"></div>
                        <span class="col-7 fw-bold my-auto">Total (SGD): </span>
                        <span class="col-5">{{ grandTotal.toFixed(2) }}</span>
                    </div>
                </div>
            </div>
            <div class="row col-12">
                <button type="submit" class="col-12 col-md-1 btn btn-success m-2">Next</button>
                <a href="/admin/sales-orders" class="col-12 col-md-1 btn btn-dark m-2">Close</a>
            </div>
        </div>
    </div>

    </template>

    <script setup>
    import { defineComponent, ref } from 'vue';

    let props = defineProps({
        salesOrder: Object,
        tax_rate: Number,
    });

    const subTotal = ref(0);
    const salesOrder = ref(props.salesOrder);
    const client = ref({});
    const grandTotal = ref(0);
    const tax_rate = ref(props.taxRate);
    const clientArray = ref([]);
    const selectedClient = ref('');
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

    function fetchClients(query) {
        if (query) {
            axios.get(`/web/clients?search=${query}`).then((response) => {
                this.clientArray = response.data.response.items;
            });
        } else {
            axios.get(`/web/clients`).then((response) => {
                this.clientArray = response.data.response.items;
            });
        }
    }
</script>

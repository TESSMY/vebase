<template>
    <div class="row">
        <div class="row border-bottom mb-2">
            <span class="h5">QUOTATION DETAILS</span>
        </div>
        <div class="row">
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Client</label>
                <input type="hidden" name="client_id" :value="selectedClient.id" class="form-control"/>
                <multi-select placeholder="Search Client" v-model="selectedClient" label="name" :options="clientArray" @input="fetchClients" :disabled="disabled"></multi-select>
            </div>
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Delivery Date</label>
                <input type="date" name="delivery_date" class="form-control" v-model="deliveryDate" :disabled="disabled"/>
            </div>
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Quotation Name</label>
                <input type="text" name="name" class="form-control" v-model="name" :disabled="disabled"/>
            </div>
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Payment Term</label>
                <input type="text" name="payment_term" class="form-control" v-model="paymentTerm" :disabled="disabled"/>
            </div>
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Billing Address</label>
                <input type="text" name="billing_address" class="form-control" v-model="billingAddress" :disabled="disabled"/>
            </div>
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Delivery Address</label>
                <input type="text" name="delivery_address" class="form-control" v-model="deliveryAddress" :disabled="disabled"/>
            </div>
            <div class="col-12 col-md-6 mb-2">
                <label class="form-label">Expiration Date</label>
                <input type="date" name="expiration_date" class="form-control" v-model="expirationDate" :disabled="disabled"/>
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
                        <th>Unit Price</th>
                        <th>Sub Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, i) in products" :key="i">
                        <td>
                            <input type="hidden" :name="'products[' + i + '][quotation_item_id]'" :value="item.quotation_item_id">
                            <template v-if="item.product.product_id === undefined"> <!-- bundle type  -->
                                <input type="hidden" :name="'products[' + i + '][product_id]'" :value="item.product.id">
                            </template>
                            <template v-else>
                                <input type="hidden" :name="'products[' + i + '][product_id]'" :value="item.product.product_id">
                                <input type="hidden" :name="'products[' + i + '][product_variant_id]'" :value="item.product.product_variant_id">
                            </template>
                            <multi-select placeholder="Search Products"
                                          v-model="item.product"
                                          label="name"
                                          :options="productArray"
                                          :disabled="disabled"
                                          @search-change="fetchProducts">
                            </multi-select>
                        </td>
                        <td>
                            <input class="form-control" type="number" min="0" :name="'products[' + i + '][quantity]'" v-model="item.quantity" @input="updateItemTotalPrice(item)" :disabled="disabled" required>
                        </td>
                        <td>{{ item.product.selling_price }}</td>
                        <td>$ {{ item.subTotal }}</td>
                        <input type="hidden" :name="'products[' + i + '][total_price]'" :value="item.subTotal">
                        <td><i class="uil-trash cursor-pointer mt-2" @click="removeProduct(i)" :disabled="disabled"></i></td>
                    </tr>
                </tbody>
            </table>
            <span class="btn text-primary text-decoration-underline pb-3" @click="addProduct()" :disabled="disabled">Add another item</span>
        </div>
        <div class="row container-fluid">
            <div class="col-12 col-md-4 mb-md-0 mb-3">
                <div class="row px-0">
                    <div class="px-0">
                        <label class="form-label px-0">Notes</label>
                        <textarea class="form-control" placeholder="Notes that will be displayed on sales order." rows="5" style="resize: none" name="notes" v-model="notes" :disabled="disabled"></textarea>
                    </div>
                </div>
            </div>
            <div class="col-md-5"></div>
            <div class="col-12 col-md-3">
                <div class="row text-end">
                    <span class="col-7 fw-bold my-auto">Sub Total: </span>
                    <span class="col-5">$ {{ subTotal }}</span>
                    <div class="border my-2"></div>
                    <span class="col-7 fw-bold my-auto">Tax %: </span>
                    <span class="col-5"><input class="form-control" v-model="taxRate" type="number" name="tax_rate" min="0" max="100" step="1" :disabled="disabled"></span>
                    <div class="border my-2"></div>
                    <span class="col-7 fw-bold my-auto">Total (SGD): </span>
                    <span class="col-5">$ {{ grandTotal }}</span>
                </div>
            </div>
        </div>

        <input type="hidden" name="status" v-model="status">
        <div class="row col-md-12 text-end">
            <div class="col-md-12 text-end">
                <button type="submit" @click="status = 20" class="btn btn-success m-4" :disabled="disabled">Generate S.O.</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "QuotationForm",
    props: ["quotation", "quotationItems", "quotationClient"],

    watch: {
        taxRate: function (newVal, oldVal) {
            this.grandTotal = this.subTotal + (this.subTotal * (newVal / 100));
        }
    },

    data() {
        return {
            isEdit: false,
            disabled: false,
            expirationDate: '',
            deliveryDate: '',
            name: '',
            paymentTerm: '',
            billingAddress: '',
            deliveryAddress: '',
            clientArray: [],
            selectedClient: {},
            productArray: [],
            itemTotal: '',
            subTotal: '',
            grandTotal: '',
            taxRate: '',
            notes: '',
            status: 10,
            products: [{
                product: '',
                quantity: 0,
                subTotal: 0
            }]
        }
    },

    created() {
        this.fetchClients();
        this.fetchProducts();
    },

    mounted() {
        if (this.quotation !== undefined) {
            if (this.quotation.status != 10) {
                this.disabled = true;
            }
            this.selectedClient = this.quotationClient;
            this.deliveryDate = this.quotation.delivery_date;
            let delivery_date = new Date(this.quotation.delivery_date);
            this.deliveryDate = delivery_date.toISOString().split('T')[0];
            this.expirationDate = this.quotation.expirationDate;
            let expiration_date = new Date(this.quotation.expiration_date);
            this.expirationDate = expiration_date.toISOString().split('T')[0];
            this.name = this.quotation.name;
            this.paymentTerm = this.quotation.payment_term;
            this.billingAddress = this.quotation.billing_address;
            this.deliveryAddress = this.quotation.delivery_address;
            this.notes = this.quotation.notes;
            this.taxRate = this.quotation.tax_rate;
            this.subTotal = this.quotation.sub_total;
            this.grandTotal = this.quotation.grand_total;

            let products = [];

            for (let item of this.quotationItems) {
                let product = {
                    quotation_item_id: item.id,
                    product: {
                        id: item.product_id,
                        product_variant_id: item.product_variant_id,
                        product_id: item.product_id,
                        name: item.name,
                        selling_price: item.unit_price,
                    },
                    quantity: item.quantity,
                    subTotal: item.total_price,
                };
                products.push(product);
            }

            this.products = products;
        }
        this.updateItemTotalPrice();
        this.updateTotalPrice();
    },

    methods: {
        fetchClients(query) {
            let parameter = {
                'search': query,
            }
            axios.get(`/web/clients`, {
                params: parameter
            }).then((response) => {
                this.clientArray = response.data.response.items;
            });
        },

        addProduct() {
            this.products.push({
                product: '',
                quantity: 0,
                subTotal: 0,
            })
        },

        removeProduct(i) {
            this.products.splice(i, 1);
            this.updateTotalPrice();
        },

        updateTotalPrice() {
            this.subTotal = 0;
            this.grandTotal = 0;
            this.itemTotal = 0;
            this.products.forEach(item => {
                this.itemTotal = item.product.selling_price * item.quantity;
                this.subTotal += this.itemTotal;
            });
            this.grandTotal += parseFloat(this.subTotal) + (parseFloat(this.subTotal) * (this.taxRate / 100));
        },

        updateItemTotalPrice() {
            this.products.forEach(item => {
                item.subTotal = item.product.selling_price * item.quantity
            });
            this.updateTotalPrice();
        },

        fetchProducts(query) {
            if (query) {
                axios.get(`/web/products?search=${query}`).then((response) => {
                    this.productArray = [];
                    response.data.response.items.forEach(product => {
                        if (product.type === 3) { // bundle type
                            this.productArray.push(product);
                        } else {
                            product.product_variants.forEach(product_variant => {
                                this.productArray.push(product_variant);
                            });
                        }
                    });
                })
            }
        },
    }
}
</script>


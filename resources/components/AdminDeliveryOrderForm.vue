<style>
    .multiselect {
        box-sizing: border-box;
    }
    .multiselect.form-control {
        padding: 0;
        border: 0;
    }

    .multiselect__tags {
        padding: 0px 40px 0px 8px;
    }

    .multiselect__placeholder {
        padding-top: 7px;
    }

    .multiselect__single {
        padding-top: 9px;
    }

    .mr-2 {
        margin-right: 5px;
    }
    #input-item {
        background-color: var(--ct-table-striped-bg);
    }
    #input-item div.multiselect__select {
        display: none;
    }
    #input-item div.multiselect__tags {
        border: 0px;
        background-color: transparent;
    }
    #input-item .multiselect__single {
        background-color: transparent;
    }
    .center {
        text-align: center;
    }
</style>

<style scoped>
    .table-total {
        width: 100%;
    }
    .table-total tr td, .table-total tr th {
        padding: 7px;
    }
    .table-total tr td:nth-child(2), .table-total tr th:nth-child(2) {
        text-align: right;
    }
    #input-item td {
        vertical-align: middle;
    }
    .product-image {
        max-width: 80px;
    }
    .product-info {
        margin-left: 5px;
    }
    .product-sku {
        white-space: nowrap;
    }
    .input-product-quantity {
        background-color: transparent;
        outline: none;
        text-align: center;
        border: 0;
    }
    .input-product-quantity::-webkit-inner-spin-button {
        appearance: none;
    }
    .label-padding {
        padding: 5px 10px 5px 10px;
    }
    .max-w-400 {
        max-width: 400px;
    }
    #select-tax, #selec-tax:focus {
        border: 0;
        outline: none;
        background-color: transparent;
    }
</style>

<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="/admin/delivery-orders">Delivery Orders</a></li>
                            <li class="breadcrumb-item active">Delivery Order</li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{ title }}</h4>
                </div>
            </div>
        </div>

        <form method="post" ref="form_elm" :action="formAction" class="row">
            <input type="hidden" name="_token" :value="props.csrfToken">
            <input type="hidden" name="_method" :value="methodVal">
            <div class="col-12">
                <ul class="nav nav-tabs nav-bordered mb-3">
                    <li class="nav-item">
                        <a @click="gotoForm()" class="nav-link bg-transparent" :class="{'active':show_tab==1}">
                            Add Product & D.O. Details
                        </a>
                    </li>
                    <li class="nav-item">
                        <a @click="goToSummary()" class="nav-link bg-transparent" :class="{'active':show_tab==2}">
                            Delivery Order
                        </a>
                    </li>
                </ul>
                <div v-show="show_tab==1">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title">DELIVERY ORDER DETAIL</h4>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="simpleinput" class="form-label">Client</label>
                                        <input type="hidden" name="client_id" v-if="delivery_order?.client?.id" :value="delivery_order.client?.id">
                                        <multiselect class="form-control"
                                            v-model="delivery_order.client"
                                            placeholder="Select one"
                                            :allow-empty="true"
                                            :searchable="true"
                                            :close-on-select="true"
                                            :options="clients"
                                            :multiple="false"
                                            :loading="loading_client"
                                            :internal-search="false"
                                            :clear-on-select="false"
                                            :options-limit="100"
                                            :show-no-results="true"
                                            :hide-selected="true"
                                            @search-change="searchClient"
                                            @Open="searchClient"
                                            label="id">
                                        </multiselect>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Sales Order</label>
                                        <input type="hidden" name="sales_order_id" v-if="delivery_order?.sales_order?.id" :value="delivery_order.sales_order?.id">
                                        <multiselect class="form-control"
                                            v-model="delivery_order.sales_order"
                                            placeholder="Select one"
                                            :allow-empty="true"
                                            :searchable="true"
                                            :close-on-select="true"
                                            :options="sales_orders"
                                            :multiple="false"
                                            :loading="loading_sales_order"
                                            :internal-search="false"
                                            :clear-on-select="false"
                                            :options-limit="100"
                                            :show-no-results="true"
                                            :hide-selected="true"
                                            @search-change="searchSalesOrder"
                                            @Open="searchSalesOrder"
                                            label="id">
                                        </multiselect>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Issued By</label>
                                        <input type="text" class="form-control" v-model="issued_by" :disabled="true">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="example-email" class="form-label">Date</label>
                                        <input type="text" class="form-control" required name="date" ref="date_input" v-model="delivery_order.date">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Payment Term</label>
                                        <input type="text" class="form-control" name="payment_term" v-model="delivery_order.payment_term">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Payment Due</label>
                                        <input type="text" class="form-control" v-model="payment_due" :disabled="true">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="divider"></div>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-2">ADD PRODUCT</h4>
                            <div class="row">
                                <div class="col-12">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Product Detail</th>
                                                <th>Description</th>
                                                <th class="center">Quantity</th>
                                                <th>Unit Price</th>
                                                <th>Taxes</th>
                                                <th>Total Amount</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr id="input-item">
                                                <td>
                                                    <multiselect
                                                        ref="select_product_input"
                                                        placeholder="Type or click to select an item"
                                                        :close-on-select="true"
                                                        :options="products"
                                                        :multiple="false"
                                                        :loading="loading_products"
                                                        :options-limit="100"
                                                        @search-change="searchProduct"
                                                        @Open="searchProduct"
                                                        track-by="id"
                                                        :custom-label="productLabel"
                                                        :show-labels="false"
                                                        :clear-on-select="true"
                                                        :allow-empty="true"
                                                        @select="addProduct">
                                                    </multiselect>
                                                </td>
                                                <td>
                                                    <div>Enter description</div>
                                                </td>
                                                <td class="center">
                                                    0
                                                </td>
                                                <td>
                                                    $0.00
                                                </td>
                                                <td>
                                                    <select id="select-tax" name="tax_rate" required v-model="delivery_order.tax_rate">
                                                        <option v-for="tax in taxes" :value="parseFloat(tax.tax_rate)">{{ tax.tax_rate }}%</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    $
                                                </td>
                                                <td class="center">
                                                    <i class="mdi mdi-delete font-18 align-middle me-2"></i>
                                                </td>
                                            </tr>
                                            <tr v-for="(item, item_index) in delivery_order.items" :key="item_index">
                                                <td>
                                                    <input type="hidden" :name="`items[${item_index}][id]`" :value="item.product_variant.id">
                                                    <div class="d-flex">
                                                        <img v-if="item.image" class="product-image" :src="item.image" :alt="item.name">
                                                        <div class="product-info">
                                                            <div class="product-name">{{ item.product_variant.name }}</div>
                                                            <div class="product-sku">SKU: <strong>{{ item.product_variant.sku }}</strong></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    {{ item.product_variant.product.description }}
                                                </td>
                                                <td width="5">
                                                    <input type="number" :name="`items[${item_index}][quantity]`" class="input-product-quantity" v-model="item.quantity">
                                                </td>
                                                <td>
                                                    ${{ parseFloat(item.product_variant.selling_price).toLocaleString() }}
                                                </td>
                                                <td class="center" width="5">
                                                    Tax {{ delivery_order.tax_rate }}%
                                                </td>
                                                <td>
                                                    ${{ totalPrice(item).toLocaleString() }}
                                                </td>
                                                <td class="center">
                                                    <span type="button" class="text-danger" @click="delivery_order.items.splice(item_index, 1)"><i class="mdi mdi-delete font-18 align-middle me-2"></i></span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-6 col-sm-9">
                                    <div class="note-label"><strong>Note & Instruction</strong></div>
                                    <div class="label-padding"></div>
                                    <textarea rows="4" name="note" class="form-control max-w-400" v-model="delivery_order.note"></textarea>
                                </div>
                                <div class="col-xs-6 col-sm-3">
                                    <table class="table table-total">
                                        <tr>
                                            <th width="70%">Subtotal</th>
                                            <td>${{ subTotalItem.toLocaleString() }}</td>
                                        </tr>
                                        <tr>
                                            <th>Discount</th>
                                            <td>{{ discount.toLocaleString() }}</td>
                                        </tr>
                                        <tr>
                                            <th>Tax</th>
                                            <td>{{ delivery_order.tax_rate?.toLocaleString() }}%</td>
                                        </tr>
                                        <tr>
                                            <th>GST</th>
                                            <td>{{ gst.toLocaleString() }}</td>
                                        </tr>
                                        <tr>
                                            <th>Total (SGD)</th>
                                            <th class="text-primary">${{ grandTotal.toLocaleString() }}</th>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-show="show_tab==2">
                    <admin-delivery-order-summary ref="delivery_order_summary"
                        :delivery-order="delivery_order"
                        :show-signature="true"
                        :show-bottom-date="true"
                        :show-print-button="false"
                        :show-download-button="false"/>
                </div>
            </div>
            <div class="col-12" v-if="show_tab == 1">
                <button class="btn btn-primary mr-2" type="button" @click="goToSummary()">Next</button>
                <button class="btn btn-light" type="button">Close</button>
            </div>
            <div class="col-12" v-else>
                <button class="btn btn-outline-primary mr-2" :disabled="loading_save" @click="save" type="button">Save & Close</button>
                <button class="btn btn-primary mr-2" :disabled="loading_send_email" @click="sendEmail" type="button">Send Email</button>
                <button class="btn btn-light mr-2" type="button" @click="print">Print</button>
                <button class="btn btn-light mr-2" type="button" @click="downloadPdf">Download PDF</button>
                <button class="btn btn-light" type="button" @click="backToForm()">Back</button>
            </div>
        </form>
    </div>
</template>

<script setup>
    import Multiselect from 'vue-multiselect'
    import flatpickr from "flatpickr"
    import { computed, ref, onMounted } from 'vue'

    const props = defineProps({
        taxes: {
            type: Array,
            required: true
        },
        taxRate: {
            type: Object,
            required: false
        },
        deliveryOrder: {
            type: Object,
            required: false
        },
        formAction: {
            type: String,
            required: true
        },
        csrfToken: {
            type: String,
            required: true
        },
        oldInput: {
            type: Object,
            required: false
        }
    })

    // COMPONENT REFS
    const form_elm = ref(null)
    const date_input = ref(null)
    const select_product_input = ref(null)
    const delivery_order_summary = ref(null)

    // DATA
    const discount = ref(0)
    const gst = ref(0)
    const client_id = ref(null)
    const loading_client = ref(false)
    const loading_sales_order = ref(false)
    const loading_products = ref(false)
    const clients = ref([])
    const sales_orders = ref([])
    const show_tab = ref(1)
    const payment_due = ref('')
    const branc_code = ref('')
    const issued_by = ref('Admin')
    const products = ref([])
    const product_selected = ref([])
    const loading_send_email = ref(false)
    const loading_save = ref(false)
    const delivery_order = ref({
        client: {
            id: null,
            name: null
        },
        sales_order: {},
        sales_order_id: null,
        payment_term: null,
        date: null,
        delivery_eta: null,
        items: [],
        note: null,
        tax_rate: parseFloat(props.taxRate?.tax_rate) || ''
    })


    const title = computed(()=>{
        return props.deliveryOrder ? 'Create Delivery Order' : 'Edit Delivery Order'
    })

    const goToSummary = () => {
        if (!delivery_order.value?.client?.id) {
            return alert("Client Required")
        }
        if (!delivery_order.value?.date) {
            return alert("Date Required")
        }
        if (!delivery_order.value?.sales_order?.id) {
            return alert("Sales Order Required")
        }
        if (!delivery_order.value?.tax_rate) {
            return alert("Tax Required")
        }
        if (!delivery_order.value?.items?.length) {
            return alert("Required at least 1 item")
        }
        show_tab.value = 2
    }

    const backToForm = () => {
        show_tab.value = 1
    }

    const gotoForm = () => {
        show_tab.value = 1
    }

    const searchClient = query => {
        loading_client.value = true
        axios.get('/api/clients', {
            search : { query }
        }).then(res => {
            loading_client.value = false
            clients.value = res.data.response.items
        })
    }

    const searchSalesOrder = query => {
        loading_sales_order.value = true
        axios.get('/api/sales-orders', {
            search : { query }
        }).then(res => {
            loading_sales_order.value = false
            sales_orders.value = res.data.response.items
        })
    }

    const searchProduct = query => {
        loading_products.value = true
        axios.get('/api/product-variants', {
            params: {
                search: query
            }
        })
        .then(res => {
            loading_products.value = false
            products.value = res.data.response.items
        })
    }

    const productLabel = product => {
        return product.name
    }

    const addProduct = product => {
        if (delivery_order.value.items.find(item => item.id == product.id)) {
            return alert("Product added already")
        }
        delivery_order.value.items.push({
            product_variant_id: product.id,
            product_variant: product,
            quantity: 1
        })
    }

    const totalPrice = item => {
        const total = (parseFloat(item.product_variant.selling_price) * parseInt(item.quantity))
        let result = total + (delivery_order.value.tax_rate / 100 * total)
        return result
    }

    const subTotalItem = computed(() => {
        let total = 0
        for (const item of delivery_order.value.items) {
            total += (parseFloat(item.product_variant.selling_price) * parseInt(item.quantity))
        }
        return total
    })

    const grandTotal = computed(() => {
        return subTotalItem.value + (subTotalItem.value * delivery_order.value.tax_rate / 100) + gst.value - discount.value
    })

    const methodVal = computed(() => {
        return props.deliveryOrder ? 'PUT' : 'POST'
    })

    onMounted(() => {
        flatpickr(date_input.value);
        if (props.oldInput) {
            delivery_order.value = {
                client: props.oldInput.client,
                sales_order: props.oldInput.sales_order,
                sales_order_id: props.oldInput.sales_order_id,
                date: props.oldInput.date,
                payment_term: props.oldInput.payment_term,
                delivery_eta: null,
                items: props.oldInput.items,
                note: props.oldInput.note,
                tax_rate: parseFloat(props.oldInput.tax_rate)
            }
        } else if (props.deliveryOrder) {
            delivery_order.value = props.deliveryOrder
        }
    })

    const save = () => {
        loading_save.value = true
        form_elm.value.submit()
    }

    const sendEmail = () => {
        loading_send_email.value = true
        axios.post(`/admin/delivery-orders/send-email/${delivery_order.id}`)
        .then(response => {
            loading_send_email.value = false
        })
    }

    const print = () => {
        delivery_order_summary.value.print()
    }

    const downloadPdf = () => {
        delivery_order_summary.value.downloadPdf()
    }
</script>

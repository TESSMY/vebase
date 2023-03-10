<style scoped>
    #input-item {
        background-color: var(--ct-table-striped-bg);
    }
    #input-item td {
        vertical-align: middle;
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

        <div class="row">
            <div class="col-12">
                <ul class="nav nav-tabs nav-bordered mb-3">
                    <li class="nav-item">
                        <a href="#form1" @click="gotoSummary()" class="nav-link bg-transparent" :class="{'active':show_tab==1}">
                            Add Product & D.O. Details
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#form2" @click="gotoForm()" class="nav-link bg-transparent" :class="{'active':show_tab==2}">
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
                                    <form>
                                        <div class="mb-3">
                                            <label for="simpleinput" class="form-label">Client</label>
                                            <select disabled name="client_id" class="form-control" v-model="client_id"></select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Sales Order</label>
                                            <multiselect class="form-control"
                                                v-model="sales_order"
                                                placeholder="Select one"
                                                :allow-empty="true"
                                                :searchable="true"
                                                :close-on-select="true"
                                                :options="['Select option', 'options', 'selected', 'multiple', 'label', 'searchable', 'clearOnSelect', 'hideSelected', 'maxHeight', 'allowEmpty', 'showLabels', 'onChange', 'touched']"></multiselect>
                                            <!-- <select class="form-control" v-model="sales_order">
                                                <option value="">Select Sales Order</option>
                                            </select> -->
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Branch Code</label>
                                            <input type="text" class="form-control" v-model="branc_code" :disabled="true">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Issued By</label>
                                            <input type="text" class="form-control" v-model="issued_by" :disabled="true">
                                        </div>

                                    </form>
                                </div>

                                <div class="col-lg-6">
                                    <form>
                                        <div class="mb-3">
                                            <label for="example-email" class="form-label">Date</label>
                                            <input type="text" class="form-control" ref="date_input" v-model="date">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Payment Term</label>
                                            <!-- <input type="number" class="form-control" v-model="paymeny_term"> -->
                                            <select name="" class="form-control"></select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Payment Due</label>
                                            <input type="text" class="form-control" v-model="payment_due" :disabled="true">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Packed By Date</label>
                                            <input type="text" class="form-control" v-model="packed_by_date">
                                        </div>
                                    </form>
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
                                                <th>Quantity</th>
                                                <th>Unit Price</th>
                                                <th>Taxes</th>
                                                <th>Total Amount</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr id="input-item">
                                                <td>
                                                    <div>Type or click to select an item</div>
                                                </td>
                                                <td>
                                                    <div>Enter description</div>
                                                </td>
                                                <td>
                                                    <div>0</div>
                                                </td>
                                                <td>
                                                    $<div>0.00</div>
                                                </td>
                                                <td>

                                                </td>
                                                <td>
                                                    $
                                                </td>
                                                <td class="center">
                                                    <i class="mdi mdi-delete font-18 align-middle me-2"></i>
                                                </td>
                                            </tr>
                                            <tr v-for="(item, item_index) in deliveryOrder.items" :key="item_index">
                                                <td>
                                                    {{ item.name }}
                                                </td>
                                                <td>
                                                    {{ item.description }}
                                                </td>
                                                <td>
                                                    {{ item.quantity }}
                                                </td>
                                                <td>
                                                    $00
                                                </td>
                                                <td>
                                                    Tax 15%
                                                </td>
                                                <td>
                                                    $
                                                </td>
                                                <td class="center">
                                                    <button type="button" class="text-danger" @click="deliveryOrder.items.splice(item_index, 1)"><i class="mdi mdi-delete font-18 align-middle me-2"></i></button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-show="show_tab==2">
                    <admin-delivery-order-summary :delivery-order="deliveryOrder"/>
                </div>
            </div>
            <div class="col-12" v-if="show_tab == 1">
                <button class="btn btn-primary mr-2" type="button" @click="gotoSummary()">Next</button>
                <button class="btn btn-light" type="button">Close</button>
            </div>
        </div>
    </div>
</template>

<script setup>
    import Multiselect from 'vue-multiselect'
    import flatpickr from "flatpickr"
    import { computed, ref, onMounted } from 'vue'

    const props = defineProps({
        deliveryOrder: {
            type: Object,
            required: false
        },
        action: {
            type: String,
            required: true
        },
        formAction: {
            type: String,
            required: true
        }
    })

    const date_input = ref(null)
    const sales_order = ref(null)
    const date = ref(null)
    const customer_po = ref('')
    const paymeny_term = ref(0)
    const show_tab = ref(1)
    const payment_due = ref('')
    const branc_code = ref('')
    const issued_by = ref('Admin')
    const packed_by_date = ref('')
    const deliveryOrder = ref(props.deliveryOrder || {
        id: null,
        supplier: {
            name: null,
            id: null,
            address: null
        },
        delivery_eta: null,
        items: []
    })


    const title = computed(()=>{
        return props.action == 'create' ? 'Create Delivery Order' : 'Edit Delivery Order'
    })

    const gotoSummary = () => {
        show_tab.value = 2
    }

    const gotoForm = () => {
        show_tab.value = 1
    }

    onMounted(() => {
        flatpickr(date_input.value, {});
    })
</script>

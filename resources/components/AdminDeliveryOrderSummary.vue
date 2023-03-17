<style scoped>
    #table-1 {
        margin-left: auto;
    }

    #table-1 td {
        padding-left: 10px;
        padding-right: 10px;
    }

    .line {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        width: 100%;
        background-color: var(--bs-link-color);
        box-shadow: 0 0 2px var(--bs-link-color);
        height: 10px;
        margin: 0px 0 10px 0;
    }

    .line strong {
        background-color: white;
        margin-right: 150px;
        padding: 0 15px 0 15px;
    }

    .title {
        background-color: var(--ct-table-striped-bg);
        padding: 5px 10px 5px 10px;
        width: 100%;
    }

    .list {
        list-style: none;
        padding-left: 10px;
    }

    .list li {
        padding-top: 10px;
    }

    .delivery-eta {
        padding: 5px 10px 5px 10px;
        width: 100%;
        background-color: var(--ct-table-striped-bg);
    }

    #table-2 {
        margin-top: 30px;
    }

    #table-2 thead {
        background-color: var(--ct-table-striped-bg);
    }

    #input-item {
        background-color: var(--ct-table-striped-bg);
    }

    .section {
        margin-top: 30px;
    }

    .note-label {
        width: fit-content;
        padding: 5px 10px 5px 10px;
        width: 50%;
        background-color: var(--ct-table-striped-bg);
    }

    .table-total {
        width: 100%;
    }
    .table-total tr td, .table-total tr th {
        padding: 7px;
    }
    .table-total tr td:nth-child(2), .table-total tr th:nth-child(2) {
        text-align: right;
    }

    #signature {
        padding: 10px;
        border: 1px solid var(--ct-table-striped-bg);
        width: 300px;
        margin-left: auto;
    }

    .mr-2 {
        margin-right: 5px;
    }
</style>

<template>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <img src="/logo.svg" alt="logo" height="40" style="filter:invert(0.6)">
                </div>
                <div class="col-lg-6">
                    <table id="table-1">
                        <tr>
                            <td>Date</td>
                            <td>{{ deliveryOrder.date }}</td>
                        </tr>
                        <tr>
                            <td>D.O. No</td>
                            <td>D.O. #123</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="line">
            <strong>DELIVERY ORDER</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-xs-12 col-sm-6 col-md-3">
                    <div class="title"><strong>Vendor</strong></div>
                    <div>
                        <ul class="list">
                            <li>Jenny Lim</li>
                            <li>Supplier #123</li>
                            <li>Bedok Way st 21, 2 Drive</li>
                        </ul>
                    </div>
                </div>
                <div class=".d-none .d-md-block col-6"></div>
                <div class="col-xs-12 col-sm-6 col-md-3">
                    <div class="title"><strong>Ship to</strong></div>
                    <div>
                        <ul class="list">
                            <li>VE Capital Asia Pte Ltd</li>
                            <li>Changi Way</li>
                            <li>Singapore 4123</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="delivery-eta"><strong>Delivery ETA</strong></div>
                    <div style="padding: 5px 10px 5px 10px;"></div>
                </div>
                <div class="col-12">
                    <table class="table" id="table-2">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>Product Name</th>
                                <th>Brand</th>
                                <th>No. of Carton</th>
                                <th>Quantity</th>
                                <th>Unit Cost</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in deliveryOrder.items">
                                <td>{{ item.product_variant.sku }}</td>
                                <td>{{ item.product_variant.name }}</td>
                                <td></td>
                                <td></td>
                                <td>{{ item.quantity }}</td>
                                <td>${{ parseFloat(item.product_variant.selling_price).toLocaleString() }}</td>
                                <td>${{ (item.product_variant.selling_price * item.quantity).toLocaleString() }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row section">
                <div class="col-sm-8 col-xs-6">
                    <div class="note-label"><strong>Note & Instruction</strong></div>
                    <div style="padding: 5px 10px 5px 10px;"></div>
                    <textarea readonly rows="5" class="form-control">{{ deliveryOrder.note }}</textarea>
                </div>
                <div class="col-sm-4 col-xs-6">
                    <table class="table-total">
                        <tr>
                            <td width="70%">Subtotal</td>
                            <td>${{ subTotalItem.toLocaleString() }}</td>
                        </tr>
                        <tr>
                            <td>Discount</td>
                            <td>{{ discount.toLocaleString() }}</td>
                        </tr>
                        <tr>
                            <td>Sales Tax</td>
                            <td>{{ delivery_order.tax_rate?.toLocaleString() }}%</td>
                        </tr>
                        <tr>
                            <td>Other Cost</td>
                            <td>{{ gst.toLocaleString() }}</td>
                        </tr>
                        <tr>
                            <td>S & H</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <th>Grand Total</th>
                            <th class="text-primary">${{ grandTotal.toLocaleString() }}</th>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="row section" v-if="showBottomDate || showSignature">
                <div class="col-6">
                    <input type="text" v-if="showBottomDate" style="width: 300px;" class="form-control" ref="date_input" v-model="date">
                </div>
                <div class="col-6" v-if="showSignature">
                    <div id="signature">Signature</div>
                </div>
            </div>
            <div class="row section" v-if="showPrintButton || showDownloadButton">
                <div class="col-12">
                    <button v-if="showPrintButton" class="btn btn-light mr-2" type="button">Print</button>
                    <button v-if="showDownloadButton" class="btn btn-light" type="button">Download PDF</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import flatpickr from "flatpickr"
    import { ref, computed, onMounted} from 'vue'

    const props = defineProps({
        deliveryOrder: {
            type: Object,
            required: true
        },
        showSignature: {
            type: Boolean,
            default: false
        },
        showBottomDate: {
            type: Boolean,
            default: false
        },
        showPrintButton: {
            type: Boolean,
            default: true
        },
        showDownloadButton: {
            type: Boolean,
            default: true
        }
    })

    const date_input = ref(null)

    const date = ref(null)
    const discount = ref(0)
    const gst = ref(0)

    const delivery_order = computed(() => props.deliveryOrder)

    const subTotalItem = computed(() => {
        let total = 0
        for (const item of delivery_order.value.items || []) {
            total += (parseFloat(item.product_variant.selling_price) * parseInt(item.quantity))
        }
        return total
    })

    const grandTotal = computed(() => {
        return subTotalItem.value + (subTotalItem.value * delivery_order.value.tax_rate / 100) + gst.value - discount.value
    })

    onMounted(() => {
        if (props.showBottomDate) {
            flatpickr(date_input.value, {
                placeholder: "Date"
            });
        }
    })

    const print = () => {

    }

    const downloadPdf = () => {

    }
</script>

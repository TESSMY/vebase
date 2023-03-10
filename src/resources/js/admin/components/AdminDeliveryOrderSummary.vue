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
                            <td></td>
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
            <div class="row" style="margin-top: 30px;">
                <div class="col-12">
                    <div class="delivery-eta"><strong>Delivery ETA</strong></div>
                    <div style="padding: 5px 10px 5px 10px;">19 Jan 2023 - 23 Jan 2023</div>
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
                    
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
    
<script setup>
    defineProps({
        deliveryOrder: {
            type: Object,
            required: true
        }
    })
</script>

<div class="container-fluid" id="app">
    <!-- title -->
    <div class="row justify-content-between align-items-center w-100 border-bottom pb-4">
        <div class="col-md-12">
            <span>
                <h5>Product Details</h5>
            </span>
        </div>
    </div>
    
    <div class="row my-2">
        <div class="d-flex justify-content-between align-items-end">
            <div class="w-25 mt-4">
            </div>
            <div class="col-12 col-md-auto d-flex pt-3">
                <a href="#" class="btn btn-alt px-5">Back</a>
            </div>
        </div>
    </div>

    <div class="card shadow bg-white mt-4">
        <div class="body border-top">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">Overview</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="transaction-tab" data-bs-toggle="tab" data-bs-target="#transaction" type="button" role="tab" aria-controls="transaction" aria-selected="false">Transaction</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="products-tab" data-bs-toggle="tab" data-bs-target="#products" type="button" role="tab" aria-controls="products" aria-selected="false">Related Products</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab" aria-controls="history" aria-selected="false">History</button>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                    @include('product.overview')
                </div>
                <div class="tab-pane fade" id="transaction" role="tabpanel" aria-labelledby="transaction-tab">
                    @include('product.transaction')
                </div>
                <div class="tab-pane fade" id="products" role="tabpanel" aria-labelledby="products-tab">
                    @include('product.products')
                </div>
                <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
                    @include('product.history')
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var app = new Vue({
        el: '#app',
        data: {
        },
        mounted() {
        },
        methods: {
        },
    })
</script>

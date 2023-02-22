<div class="container-fluid" id="app">
    {{-- header --}}
    <div class="d-flex justify-content-between p-3" style="border-bottom-width:2px">
        <div>
            <span>
                <h4><i class="fas fa-store text-identity me-2" style="color: #289983"></i>Create New Bundle</h4>
            </span>
        </div>
        <div class="mt-3 mb-3" style="border-top-width:3px">
            <div>
                <a href="#" class="btn btn-alt px-5">Back</a>
            </div>
        </div>
    </div>


    {{-- form --}}
    <form action="#" method="POST" enctype="multipart/form-data">
    @csrf
        <div class="card shadow bg-white mt-4 mb-4">
            <div class="border-bottom">
                <div class="ms-4 mt-2 mb-2">Bundle Information</div>
            </div>

            <div class="container-fluid mt-5 pb-5 mx-5">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group row mb-3">
                            <label class="col-md-3 text-right form-label">Name</label>
                            <div class="col-md-7">
                                <input type="text" name="name" value ="" class="form-control" required/>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row mb-4">
                            <label class="col-md-3 text-right form-label mt-2">Product Image</label>
                            <div class="col-md-7">
                                <input type="file" name="image" value="" class="form-control" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group row mb-3">
                            <label class="col-md-3 text-right form-label">Description</label>
                            <div class="col-md-7">
                                <textarea class="form-control me-5" name="description" rows="3" cols="13"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row mb-3">
                            <label class="col-md-3 text-right form-label">Bundle Barcode</label>
                            <div class="col-md-7">
                                <input type="text" name="barcode" value ="" class="form-control" required/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group row mb-3">
                            <label class="col-md-3 text-right form-label">Bundle SKU</label>
                            <div class="col-md-7">
                                <input type="text" name="sku" value ="" class="form-control" required/>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row mb-3">
                            <label class="col-md-3 text-right form-label">Bundle Selling Price</label>
                            <div class="col-md-7">
                                <input type="number" name="selling_price" value ="" class="form-control" required/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group row mb-3">
                            <label class="col-md-3 text-right form-label">Bundle Cost</label>
                            <div class="col-md-7">
                                <input type="number" name="cost" value ="" class="form-control" required/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group row mb-3">
                            <label class="col-md-3 text-right form-label">Bundle Quantity</label>
                            <div class="col-md-7">
                                <input type="number" name="quantity" value ="" class="form-control" required/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-bottom mt-5">
                <div class="ms-4 mb-2">Add Product</div>
            </div>
            <div class="body my-5">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product Details</th>
                                <th>Description</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody class="align-middle">
                        
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mt-3 mb-4">
            <button class="btn btn-primary px-5">Create</button>
            <button class="btn btn-secondary px-5">Close</button>
        </div>
    </form>
</div>

<script type="text/javascript">
    var app = new Vue({
        el: '#app',
        data: {
        },

        methods: {
        }
    });
</script>

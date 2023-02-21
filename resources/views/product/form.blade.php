<x-app-layout>
    <div class="container-fluid" id="app">
        {{-- header --}}
        <div class="d-flex justify-content-between p-3" style="border-bottom-width:2px">
            <div>
                <span>
                    <h4><i class="fas fa-store text-identity me-2" style="color: #289983"></i>Create Product</h4>
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
                    <div class="ms-4 mt-2 mb-2">General Information</div>
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
                                <label class="col-md-3 text-right form-label">Brand</label>
                                <div class="col-md-7">
                                    <input type="text" name="brand" value ="" class="form-control" required/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group row mb-3">
                                <label class="col-md-3 text-right form-label">UOM</label>
                                <div class="col-md-7">
                                    <input type="text" name="uom" value ="" class="form-control" required/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group row mb-4">
                                <label class="col-md-3 text-right form-label mt-2">Supplier</label>
                                <div class="col-md-7">
                                    <select name="supplier" id="supplier" class="form-select">
                                        <option class="text-muted" disabled>-- Please Select --</option>
                                        <option value="0">Supplier 1</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="border-bottom mt-5">
                    <div class="ms-4 mb-2">Product Variations</div>
                </div>
                <div class="body my-5">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Image</th>
                                <th>Variant Name</th>
                                <th>Attributes</th>
                                <th>Stock</th>
                                <th>SKU</th>
                                <th>Unit Cost</th>
                                <th>Retail Selling Price</th>
                                <th></th>
                            </tr>
                            </thead>

                            <tbody class="align-middle">
                            <tr v-for="(variant, i) in variants" :key="i">
                                <td>
                                    <input
                                        name="name"
                                        type="file"
                                        class="form-control mt-1 block w-15"
                                        required
                                    />
                                </td>
                                <td>
                                    <input
                                        name="name"
                                        type="text"
                                        class="form-control mt-1 block w-15"
                                        v-model="variant.name"
                                        required
                                    />
                                </td>
                                <td>
                                    <input
                                        name="attribute"
                                        type="text"
                                        class="form-control mt-1 block w-15"
                                        v-model="variant.attribute"
                                        required
                                    />
                                </td>
                                <td>
                                    <input
                                        name="stock"
                                        type="number"
                                        class="form-control mt-1 block w-15"
                                        v-model="variant.stock"
                                        required
                                    />
                                </td>
                                <td>
                                    <input
                                        name="sku"
                                        type="text"
                                        class="form-control mt-1 block w-15"
                                        v-model="variant.name"
                                        required
                                    />
                                </td>
                                <td>
                                    <input
                                        name="unit_cost"
                                        type="number"
                                        class="form-control mt-1 block w-15"
                                        v-model="variant.stock"
                                        required
                                    />
                                </td>
                                <td>
                                    <input
                                        name="selling_price"
                                        type="number"
                                        class="form-control mt-1 block w-15"
                                        v-model="variant.stock"
                                        required
                                    />
                                </td>
                                <td>
                                <i
                                    @click="removeItem(i)"
                                    role="button"
                                    class="fas fa-trash text-identity"
                                ></i>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-primary px-5 mt-3" @click="addVariants">Add More</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3 mb-4">
                <button class="btn btn-primary px-5">Create</button>
                <button class="btn btn-secondary px-5">Close</button>
            </div>
        </form>
    </div>

</x-app-layout>

<script type="text/javascript">
    var app = new Vue({
        el: '#app',
        data: {
            variants: [{
                image: '',
                name: '',
                attribute: '',
                stock: 0,
                sku: '',
                unit_cost: 0,
                selling_price: 0,
            }],
        },

        methods: {
            addVariants() {
                this.variants.push({
                    image: '',
                    name: '',
                    attribute: '',
                    stock: 0,
                    sku: '',
                    unit_cost: 0,
                    selling_price: 0,
                });
            },
            removeItem(i) {
                this.variants.splice(i, 1);
            },
        }
    });
</script>

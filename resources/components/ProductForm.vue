<template>
    <div class="card shadow bg-white mt-2">
        <div class="border-bottom">
            <div class="my-2 mx-2">Product Information</div>
        </div>
        <div class="container-fluid mt-2 mx-2">
            <div class="row col-md-12">
                <div class="col-md-6">
                    <div class="form-group row mb-3">
                        <label class="col-md-4 text-right form-label text-sm-start">Product Name</label>
                        <div class="col-md-12">
                            <input type="text" name="name" class="form-control" required v-model="product.name"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row mb-3">
                        <label class="col-md-4 text-right form-label text-sm-start">Product Image</label>
                        <div class="col-md-12">
                            <input type="file" name="image" value="" class="form-control"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row col-md-12">
                <div class="col-md-6">
                    <div class="form-group row mb-3">
                        <label class="col-md-4 text-right form-label text-sm-start">Description</label>
                        <div class="col-md-12">
                            <textarea class="form-control me-5" name="description" rows="3" cols="13" v-model="product.description"></textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row mb-3">
                        <label class="col-md-4 text-right form-label text-sm-start">Brand</label>
                        <div class="col-md-12">
                            <select name="brand_id" class="form-select" v-model="selectedBrand">
                                <option class="text-muted" disabled>-- Please Select --</option>
                                <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row col-md-12">
                <div class="col-md-6">
                    <div class="form-group row mb-3">
                        <label class="col-md-4 text-right form-label text-sm-start">Supplier</label>
                        <div class="col-md-12">
                            <select class="form-select" name="supplier_id" v-model="selectedSupplier">
                                <option class="text-muted" disabled>-- Please Select --</option>
                                <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row mb-3">
                        <label
                            class="col-md-4 text-right form-label text-sm-start">Total Stock</label>
                        <div class="col-md-12">
                            <input type="text" name="total_stock" value="" class="form-control" required/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row col-md-12" v-if="type == 1">
                <div class="col-md-6">
                    <div class="form-group row mb-3">
                        <label
                            class="col-md-4 text-right form-label text-sm-start">Cost Price</label>
                        <div class="col-md-12">
                            <input type="text" name="cost_price" value="" class="form-control" min="0" step=".01" required/>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row mb-3">
                        <label class="col-md-4 text-right form-label text-sm-start">Selling Price</label>
                        <div class="col-md-12">
                            <input type="text" name="selling_price" value="" class="form-control" min="0" step=".01" required/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row col-md-12">
                <div class="col-md-6">
                    <div class="form-group row mb-3">
                        <label
                            class="col-md-4 text-right form-label text-sm-start">Measurement Unit</label>
                        <div class="col-md-12">
                            <input type="text" name="measurement_unit" value="" class="form-control" required/>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row mb-3">
                        <label
                            class="col-md-4 text-right form-label text-sm-start">Length</label>
                        <div class="col-md-12">
                            <input type="text" name="length" value="" class="form-control" required/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row col-md-12">
                <div class="col-md-6">
                    <div class="form-group row mb-3">
                        <label
                            class="col-md-4 text-right form-label text-sm-start">Width</label>
                        <div class="col-md-12">
                            <input type="text" name="width" value="" class="form-control" required/>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row mb-3">
                        <label
                            class="col-md-4 text-right form-label text-sm-start">Height</label>
                        <div class="col-md-12">
                            <input type="text" name="height" value="" class="form-control" required/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row col-md-12">
                <div class="col-md-6">
                    <div class="form-group row mb-3">
                        <label class="col-md-4 text-right form-label text-sm-start">SKU</label>
                        <div class="col-md-12">
                            <input type="text" name="sku" value="" class="form-control" required/>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row mb-4">
                        <label class="col-md-4 text-right form-label text-sm-start">Product Type</label>
                        <div class="col-md-12">
                            <select name="type" v-model="type" class="form-select">
                                <option class="text-muted" disabled>-- Please Select --</option>
                                <option value="1">Single Product</option>
                                <option value="2">Product Variation</option>
                                <option value="3">Product Bundles</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row col-md-12">
                <div class="col-md-6">
                    <div class="form-group row mb-3">
                        <label class="col-md-4 text-right form-label text-sm-start">Barcode</label>
                        <div class="col-md-12">
                            <input type="text" name="barcode" value="" class="form-control" required/>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row mb-4">
                        <label class="col-md-4 text-right form-label text-sm-start">Status</label>
                        <div class="col-md-12">
                            <select name="status" class="form-select" v-model="product.status">
                                <option class="text-muted" disabled>-- Please Select --</option>
                                <option value="0">Inactive</option>
                                <option value="1">Active</option>
                                <option value="2">Draft</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid mt-2 mx-2">
            <div v-if="type == 2">
                <div class="border-bottom">
                    <div class="my-2 mx-2 fw-bold">Product Variations</div>
                </div>
                <div class="col-md-12 mt-2">
                    <div class="mb-4" v-for="(option, i) in options">
                        <div class="row mb-3">
                            <label class="mb-2">Option Name</label>
                            <div class="d-flex gap-2 align-items-center">
                                <input class="form-control w-50" type="text" v-model="option.name" :name="'options[' +  i  +']'">
                                <i class="uil-trash" @click="removeOption(i)"></i>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="mb-2l">Option Values</label>
                            <div class="d-flex gap-2 align-items-center" v-for="(value, v) in option.value">
                                <input class="form-control w-50 mt-2" type="text" v-model="option.value[v]" @change="addVariant()">
                                <i class="uil-trash cursor mt-2" v-if="v > 0" @click="removeValue(i, v)"></i>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary px-2" @click="addValue(i)">Add another value</button>
                    </div>
                    <button type="button" class="btn btn-light mt-3" @click="addOptions">Add Options</button>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Variant Name</th>
                                    <th>SKU</th>
                                    <th>Stock</th>
                                    <th>Unit Cost</th>
                                    <th>Retail Selling Price</th>
                                    <th>Length</th>
                                    <th>Width</th>
                                    <th>Height</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody class="align-middle">
                                <tr v-for="(variant, i) in product.variants" :key="i">
                                    <td>
                                        <input name="image" type="file" class="form-control mt-1 block w-15"/>
                                    </td>
                                    <td>
                                        <input :name="'variants[' + i + '][name]'" type="text" class="form-control mt-1 block w-15" v-model="variant.name" required/>
                                    </td>
                                    <td>
                                        <input :name="'variants[' + i + '][sku]'" type="text" class="form-control mt-1 block w-15" v-model="variant.sku" required readonly/>
                                    </td>
                                    <td>
                                        <input :name="'variants[' + i + '][quantity]'" type="text" class="form-control mt-1 block w-15" v-model="variant.quantity" required/>
                                    </td>
                                    <td>
                                        <input :name="'variants[' + i + '][unit_cost]'" type="number" class="form-control mt-1 block w-15 " min="0" step=".01" v-model="variant.unit_cost" required/>
                                    </td>
                                    <td>
                                        <input :name="'variants[' + i + '][selling_price]'" type="number" class="form-control mt-1 block w-15" min="0" step=".01" v-model="variant.selling_price" required/>
                                    </td>
                                    <td>
                                        <input :name="'variants[' + i + '][length]'" type="number" class="form-control mt-1 block w-15" min="0" step=".01" v-model="variant.length" required/>
                                    </td>
                                    <td>
                                        <input :name="'variants[' + i + '][width]'" type="number" class="form-control mt-1 block w-15" min="0" step=".01" v-model="variant.width" required/>
                                    </td>
                                    <td>
                                        <input :name="'variants[' + i + '][height]'" type="number" class="form-control mt-1 block w-15" min="0" step=".01" v-model="variant.height" required/>
                                    </td>
                                    <td>
                                        <i class="uil-trash cursor-pointer mt-2" v-if="i > 0" @click="removeVariant(i)"></i>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid mt-2 mx-2">
            <div v-if="type == 3">
                <div class="border-bottom">
                    <div class="my-2 mx-2 fw-bold">Add Products</div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product Details</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle">
                            <tr>
                                <td>Product 1</td>
                                <td>10</td>
                                <td>$ 450.00</td>
                                <td>$ 4,500.00</td>
                                <td><i class="uil-trash cursor-pointer mt-2" disabled></i></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import Swal from "sweetalert2";

export default {
    name: "ProductForm",
    props: ['suppliers', 'products', 'brands'],
    data() {
        return {
            value:'',
            suppliers: this.suppliers,
            selectedSupplier: '',
            brands: this.brands,
            selectedBrand: '',
            options: [],
            type: 1,
            product: [{
               name: '',
               image: '',
               description: '',
               brand: '',
               supplier: '',
                variants: [],
            }],
        }
    },

    methods: {
        addOptions() {
            if (this.product.name) {
                if (this.options.length < 3) {
                    if (this.type == 2) {
                        this.options.push({
                            name: '',
                            value: [null]
                        });
                    }
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Maximum number of options allowed for a product is 3.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    })
                }
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please input a product name',
                    icon: 'error',
                    confirmButtonText: 'OK'
                })
            }
        },
        removeOption(i) {
            this.options.splice(i, 1);
        },
        removeVariant(i) {
            this.product.variants.splice(i, 1);
        },
        addValue(i) {
            this.options[i].value.push(null);
        },
        removeValue(i, v) {
            this.options[i].value.splice(v, 1);
            this.removeVariant(i);
            this.addVariant();
        },
        addVariant() {
            let optionsArr = [];
            this.options.forEach((opt, index) => {
                let indexCount = index+1
                let optionName =  'option_' + indexCount
                if (opt.value.length > 0 && index == 0) {
                    opt.value.forEach(value => {
                        optionsArr.push({
                            'name': this.product.name + '/' + value,
                            'sku': this.product.name + '-' + value,
                        })
                    })
                }
                if (index >= 1) {
                    let tempOptionArr = [];
                    optionsArr.forEach(optArr => {
                        opt.value.forEach(value => {
                            tempOptionArr.push({
                                'name': optArr.name + '/' + value,
                                'sku': optArr.sku + '-' + value,
                            })
                        })
                    });
                    optionsArr = tempOptionArr;
                }
            })
            this.product.variants = [];
            optionsArr.forEach(variant => {
                this.product.variants.push({
                    'name': variant.name,
                    'sku': variant.sku,
                })
            });
        }
    }
}
</script>



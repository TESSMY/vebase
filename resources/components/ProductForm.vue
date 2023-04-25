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
              <input type="hidden" name="brand_id" :value="selectedBrand.id" v-if="selectedBrand">
              <multi-select placeholder="Search Brand" v-model="selectedBrand" label="name" :options="brandArray" @search-change="fetchBrands"></multi-select>
            </div>
          </div>
        </div>
      </div>
      <div class="row col-md-12">
        <div class="col-md-6">
          <div class="form-group row mb-3">
            <label class="col-md-4 text-right form-label text-sm-start">Supplier</label>
            <div class="col-md-12">
              <input type="hidden" name="supplier_id" :value="selectedSupplier.id" v-if="selectedSupplier">
              <multi-select placeholder="Search Supplier" v-model="selectedSupplier" label="name" :options="supplierArray" @search-change="fetchSuppliers"></multi-select>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group row mb-3">
            <label class="col-md-4 text-right form-label text-sm-start">Total Stock</label>
            <div class="col-md-12">
              <input type="text" name="total_stock"  class="form-control" v-model="product.total_stock" :readonly="isEdit == true || type == 2" required/>
            </div>
          </div>
        </div>
      </div>
      <div class="row col-md-12" v-if="(type == 1 || type == 3) && isEdit == false">
        <div class="col-md-6" v-if="type == 1">
          <div class="form-group row mb-3">
            <label class="col-md-4 text-right form-label text-sm-start">Cost Price</label>
            <div class="col-md-12">
              <input type="text" name="cost_price" class="form-control" min="0" step=".01" :required="type == 1" />
            </div>
          </div>
        </div>
        <div class="col-md-6" v-if="type == 3">
          <div class="form-group row mb-3">
            <label class="col-md-4 text-right form-label text-sm-start">Bundle Value</label>
            <div class="col-md-12">
              <input type="number" name="bundle_value" v-model="bundle_value" class="form-control" min="0" step=".01" :readonly="type == 3" />
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group row mb-3">
            <label class="col-md-4 text-right form-label text-sm-start">Selling Price</label>
            <div class="col-md-12">
              <input type="number" name="selling_price" value="" class="form-control" min="0" step=".01" required/>
            </div>
          </div>
        </div>
      </div>
      <div class="row col-md-12" v-if="type == 1 || type == 2">
        <div class="col-md-6">
          <div class="form-group row mb-3">
            <label
                class="col-md-4 text-right form-label text-sm-start">Measurement Unit</label>
            <div class="col-md-12">
              <input type="text" v-if="isEdit" name="measurement_unit" v-model="product.variants[0].measurement_unit" class="form-control" required/>
              <input type="text" v-else name="measurement_unit" value="" class="form-control" required/>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group row mb-3">
            <label
                class="col-md-4 text-right form-label text-sm-start">Length</label>
            <div class="col-md-12">
              <input type="text" v-if="isEdit" name="length" v-model="product.variants[0].length" class="form-control" required/>
              <input type="text" v-else name="length" value="" class="form-control" required/>
            </div>
          </div>
        </div>
      </div>
      <div class="row col-md-12" v-if="type == 1 || type == 2">
        <div class="col-md-6">
          <div class="form-group row mb-3">
            <label class="col-md-4 text-right form-label text-sm-start">Width</label>
            <div class="col-md-12">
              <input type="text" v-if="isEdit" name="width" v-model="product.variants[0].width" class="form-control" required/>
              <input type="text" v-else name="width" value="" class="form-control" required/>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group row mb-3">
            <label
                class="col-md-4 text-right form-label text-sm-start">Height</label>
            <div class="col-md-12">
              <input type="text" v-if="isEdit" name="height" v-model="product.variants[0].height" class="form-control" required/>
              <input type="text" v-else name="height" value="" class="form-control" required/>
            </div>
          </div>
        </div>
      </div>
      <div class="row col-md-12">
        <div class="col-md-6">
          <div class="form-group row mb-3">
            <label class="col-md-4 text-right form-label text-sm-start">SKU</label>
            <div class="col-md-12">
              <input type="text" name="sku" v-model="product.sku" class="form-control" required/>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group row mb-4">
            <label class="col-md-4 text-right form-label text-sm-start">Product Type</label>
            <div class="col-md-12">
              <select name="type" v-model="type" class="form-select" @change="fetchVariants()">
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
              <input type="text" name="barcode" v-model="product.barcode" class="form-control"/>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group row mb-4">
            <label class="col-md-4 text-right form-label text-sm-start">Status</label>
            <div class="col-md-12">
              <select name="status" class="form-select" v-model="product.status">
                <option class="text-muted" disabled>-- Please Select --</option>
                <option value="0">Active</option>
                <option value="1">Inactive</option>
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
          <div class="mb-4" v-for="(option, i) in options" :key="i">
            <div class="row mb-3">
              <label class="mb-2">Option Name</label>
              <div class="d-flex gap-2 align-items-center">
                <input class="form-control w-50" type="text" v-model="option.name" :name="'options[' +  i  +']'">
                <i class="uil-trash" @click="removeOption(i)"></i>
              </div>
            </div>
            <div class="row mb-3">
              <label class="mb-2l">Option Values</label>
              <div class="d-flex gap-2 align-items-center" v-for="(value, v) in option.value" :key="v">
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
                <input type="hidden" :name="'variants[' + i + '][product_variant_id]'" :value="variant.id" v-if="variant.id !== 'undefined'">
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
                  <input :name="'variants[' + i + '][quantity]'" type="text" class="form-control mt-1 block w-15" v-model="variant.quantity" @input="recalculateTotalStock()" required/>
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
            <tr v-for="(bundle, i) in bundles" :key="i">
              <td>
                <multi-select placeholder="Search Variant" v-model="bundle.product_variant" track-by="name" label="name" :options="variantArray" @search-change="fetchVariants"></multi-select>
                <input type="hidden" :name="'bundles[' + i + '][product_variant_id]'" :value="bundle.product_variant.id">
                <input type="hidden" :name="'bundles[' + i + '][product_bundle_id]'" :value="bundle.id">
              </td>
              <td><input type="text" :name="'bundles[' + i + '][quantity]'" v-model="bundle.quantity" class="form-control" @input="recalculateTotal()" required/></td>
              <td>{{ bundle.product_variant.selling_price }}</td>
              <td>{{ bundle.subtotal }}</td>
              <td><i class="uil-trash cursor-pointer mt-2" @click="removeBundle(i)"></i></td>
            </tr>
            </tbody>
          </table>
          <span class="btn text-primary text-decoration-underline pb-3" @click="addBundle()">Add another item</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>

import Swal from "sweetalert2";

export default {
  name: "ProductForm",
  props: ['products', 'edit_product', 'variants', 'product_bundles'],
  data() {
    return {
      isEdit: false,
      bundles: [{
        product_variant: '',
        quantity: 0,
        subtotal: 0
      }],
      bundle_value: 0,
      supplierArray: [],
      selectedSupplier: '',
      brandArray: [],
      variantArray: [],
      selectedVariant: '',
      selectedBrand: '',
      options: [],
      type: 1,
      product: [{
        name: '',
        image: '',
        description: '',
        brand: '',
        supplier: '',
        barcode: '',
        bundle_value: '',
        total_stock: 0,
        variants: [{
          measurement_unit: '',
          length: '',
          width: '',
          height: '',
          quantity: '',
          unit_cost: '',
        }],
        bundles: [{
          product_variant: '',
          quantity: '',
        }],
      }],
    }
  },

  created() {
    this.fetchBrands();
    this.fetchSuppliers();
    if (typeof(this.edit_product) != 'undefined') {
      this.isEdit = true;
      this.product = this.edit_product;
      this.product.variants = this.variants;
      this.type = this.product.type;
      this.selectedBrand = this.edit_product.brand;
      this.selectedSupplier = this.edit_product.supplier;
      this.bundles = this.edit_product.bundles;
      this.getOptions();
      this.recalculateTotal();
    }
  },

  methods: {
    getOptions() {
      let values = [];
      this.product.variants.forEach(variant => {
        values.push(variant.option_1)
      });
      this.options.push({
        name: this.product.option_1,
        value: values
      })

      if (this.product.option_2) {
        values = [];
        this.product.variants.forEach(variant => {
          if (!values.includes(variant.option_2)) {
            values.push(variant.option_2)
          }
        });
        this.options.push({
          name: this.product.option_2,
          value: values
        })
      }

      if (this.product.option_3) {
        values = [];
        this.product.variants.forEach(variant => {
          if (!values.includes(variant.option_3)) {
            values.push(variant.option_3)
          }
        });
        this.options.push({
          name: this.product.option_3,
          value: values
        })
      }
    },
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
    },

    addBundle() {
      this.bundles.push({
        product_variant: '',
        quantity: 0,
        subtotal: 0
      });
    },

    removeBundle(i) {
      this.bundles.splice(i, 1);
    },

    recalculateTotal() {
      this.bundle_value = 0;
      this.bundles.forEach(bundle => {
        bundle.subtotal = bundle.quantity * bundle.product_variant.selling_price
        this.bundle_value += bundle.subtotal
      })
    },

    recalculateTotalStock() {
      let total = 0;
      this.product.variants.forEach(variant => {
        total += parseInt(variant.quantity)
      })
      this.product.total_stock = total;
    },

    fetchBrands(query) {
      let parameter = {
        'search' : query,
      }
      axios.get(`/web/brands`, {
        params: parameter
      }).then((response) => {
        this.brandArray = response.data.response.items;
      });
    },

    fetchSuppliers(query) {
      let parameter = {
        'search' : query,
      }
      axios.get(`/web/suppliers`, {
        params: parameter
      }).then((response) => {
        this.supplierArray = response.data.response.items;
      });
    },

    fetchVariants (query) {
      let variants = [];
      let parameter = {
        'search' : query,
        'type' : [1,2]
      }
      axios.get(`/web/products`, {
        params: parameter
      }).then((response) => {
        response.data.response.items.forEach(product => {
          product.product_variants.forEach(variant => {
            variants.push(variant)
          });
        });
        this.variantArray = variants
      });
    }
  }
}
</script>



